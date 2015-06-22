<?php
namespace InterExperts\BalanceCalculator;

use \InterExperts\BalanceCalculator\Year;
use \InterExperts\BalanceCalculator\Action;
use \InterExperts\BalanceCalculator\UsedBalance;
use \InterExperts\BalanceCalculator\AddBalanceAction;

/**
 * Calculator for quota and balance operations
 */
class Calculator {
	/**
	 * Array of balance actions, sorted on the action date.
	 * @var array<Action> Array with Action objects
	 */
	protected $actions = array();

	/**
	 * Array of years used in calculating quota. Use addYear() to add new years
	 *
	 * @see addYear()
	 * @var array<Year> Array of Year objects
	 */
	public $years = array();


	/**
	 * Array of used balance. Use addUsedBalance() to add new used balance
	 *
	 * @see addUsedBalance()
	 * @var array<UsedBalance> Array of UsedBalance objects
	 */
	public $usedBalance = array();

	/**
	 * Flag if the calculated version is up to date
	 *
	 * @see recalculate()
	 * @var boolean Flag if the calculated version is up to date
	 */
	protected $isDirty = false;

	/**
	 * Add a Year object to be used for calculations.
	 * This method triggers recalculating quota.
	 *
	 * @param Year $year
	 */
	public function addYear(Year $year) {
		$this->years[] = $year;
		$this->isDirty = true;
		
	}

	public function addUsedBalance(UsedBalance $balance) {
		$this->usedBalance[] = $balance;
		$this->isDirty = true;
	}


	/**
	 * Recalculate quota based on $this->years.
	 * This method fills $this->actions which is used for balance queries
	 * like getBalanceForDate().
	 */
	protected function recalculate(){
		// Empty actions array:
		$this->actions = array();

		// Add balance actions to $this->actions for each year.
		// Each year triggers an 'add balance' action and a 'expire balance'
		// action. One add/expire pair for legal quotum and one pair for extra quotum.
		foreach($this->years as $year){
			$this->addDefaultActions($year);
		}

		// Process used balance
		foreach($this->usedBalance as $balance){
			$this->addUsedBalanceAction($balance);
		}

		// Sort $this->actions by the date attribute:
		usort($this->actions, function($a, $b){
			if($a->date == $b->date){
				return 0;
			}
			return ($a->date < $b->date) ? -1 : 1;
		});

		// Loop over actions, calculate expiring balance correctly:
		foreach($this->actions as &$action){
			if(is_a($action, '\InterExperts\BalanceCalculator\ExpireAction')){
				$action->calculateExpiringBalance($this->actions);
			}
		}
		$this->isDirty = false;
	}

	/**
	 * Retrieve the actual quotum for the given $date.
	 *
	 * This method returns the actual quotum for the given date,
	 * including quota from previous years and including quota expiry dates.
	 *
	 * @param  \DateTime $date Date for which the balance is queried
	 * @return int             Quotum
	 */
	public function getBalanceForDate(\DateTime $date, \DateTime $expireDate = null){
		$balanceActions = $this->getBalanceActionsForDate($date, $expireDate);
		return $balanceActions['balance'];
	}

	public function getAddBalanceActionsForDate(\DateTime $date, \DateTime $startDate){
		$balanceActions = $this->getBalanceActionsForDate($date);
		$actions = array();
		foreach($balanceActions['actions'] as $action){
			if($action->remainingBalance <> 0 && $action->expirationDate >= $startDate){
				$actions[] = $action;
			}
		}
		return $actions;
	}

	/**
	 * Retrieve the actual quotum and addBalanceActions for the given $date.
	 *
	 * This method returns the actual quotum  and addBalanceActions for the given date,
	 * including quota from previous years and including quota expiry dates.
	 *
	 * @param  \DateTime $date Date for which the balance is queried
	 * @return Array           Balance and AddBalance actions
	 */
	public function getBalanceActionsForDate(\DateTime $date, \DateTime $expireDate = null){
		if($this->isDirty){
			$this->recalculate();
		}
		$addActions = array();
		$currentBalance = 0;
		foreach($this->actions as &$action){
			if($action->date <= $date){
				if(is_a($action, '\InterExperts\BalanceCalculator\AddBalanceAction')){
					$action->remainingBalance = $action->addOperation;
					$addActions[] = &$action;
					$currentBalance -= $action->subOperation;
					$currentBalance += $action->addOperation;
				}elseif(is_a($action, '\InterExperts\BalanceCalculator\ExpireAction')){
					if(is_null($expireDate) || $action->date <= $expireDate){
						$action->originalAdd->remainingBalance -= $action->subOperation;
						$action->originalAdd->remainingBalance += $action->addOperation;
						$currentBalance -= $action->subOperation;
						$currentBalance += $action->addOperation;
					}
				}elseif(is_a($action, '\InterExperts\BalanceCalculator\UsedBalanceAction')){
					if(!is_null($action->processedBy) && !empty($action->processedBy)){
						foreach($action->processedBy as &$processAction) {
							$processAction->originalAdd->remainingBalance -= $action->subOperation;
							$processAction->originalAdd->remainingBalance += $action->addOperation;
						}
					}
					$currentBalance -= $action->subOperation;
					$currentBalance += $action->addOperation;
				}
				
			}else{
				break;
			}
		}
		return array('balance'=>$currentBalance, 'actions'=>$addActions);
	}

	/**
	 * Retrieve the actual quotum for the given $date.
	 *
	 * This method returns the actual quotum for the given date,
	 * including quota from previous years and including quota expiry dates.
	 *
	 * @param  \DateTime $date Date for which the balance is queried
	 * @return int             Quotum
	 */
	public function getUnexpiredBalance(\DateTime $date, \DateTime $expireDate){
		if($this->isDirty){
			$this->recalculate();
		}
		$currentBalance = 0;
		foreach($this->actions as $action){
			if($action->date <= $date){
				if(!($action->date > $expireDate && is_a($action, '\InterExperts\BalanceCalculator\ExpireAction'))){
					$currentBalance -= $action->subOperation;
					$currentBalance += $action->addOperation;
				}
			}else{
				break;
			}
		}
		return $currentBalance;
	}

	protected function addDefaultActions(Year $year){
		$legalAction = new AddBalanceAction($year->startDate, $year->quotumLegal, 0);
		$legalAction->expirationDate = $year->getQuotumLegalExpirationDate();
		$this->actions[] = $legalAction;
		$this->actions[] = new ExpireAction($year->getQuotumLegalExpirationDate(), 0, $year->quotumLegal, $legalAction);

		$extraAction = new AddBalanceAction($year->startDate, $year->quotumExtra, 0);
		$extraAction->expirationDate = $year->getQuotumExtraExpirationDate();
		$this->actions[] = $extraAction;
		$this->actions[] = new ExpireAction($year->getQuotumExtraExpirationDate(), 0, $year->quotumExtra, $extraAction);
	}

	/**
	 * Add 'used balance' action to $this->actions for the given UsedBalance.
	 *
	 * @param UsedBalance $balance UsedBalance object
	 */
	protected function addUsedBalanceAction(UsedBalance $balance){
		$this->actions[] = new UsedBalanceAction($balance->date, 0, $balance->amount);
	}
}