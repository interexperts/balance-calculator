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
	 * Add a Year object to be used for calculations.
	 * This method triggers recalculating quota.
	 *
	 * @param Year $year
	 */
	public function addYear(Year $year) {
		$this->years[] = $year;
		$this->recalculate();
	}

	public function addUsedBalance(UsedBalance $balance) {
		$this->usedBalance[] = $balance;
		$this->recalculate();
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
			$this->addAction($year);
			$this->expiresAction($year);
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
	}

	/**
	 * Retrieve all valid points at which balance has been added thats stil valid. 
     * @param  \DateTime $date Date for which the balance is queried
	 */
	public function giveStillValidAddBalancesForDate(\DateTime $date){
		$addActions = [];
		$toProcess = 0;
		foreach($this->actions as $key=>$action){
			if($action->date <= $date){
				if(is_a($action, '\InterExperts\BalanceCalculator\AddBalanceAction')){
					if($action->addOperation > 0){
						$addActions[] = $action;
					}
				}
				if(is_a($action, '\InterExperts\BalanceCalculator\ExpireAction')){
					if(empty($addActions)){
						$toProcess -= $action->subOperation;
					}else{
						$addAction = array_shift($addActions);
						$addAction->addOperation -= $action->subOperation;
						if($addAction->addOperation > 0){
							array_unshift($addActions, $addAction);
						}
					}
				}
			}else{
				break;
			}
		}
		for($i=$toProcess; $i > 0; $i--){
			$addAction = array_shift($addActions);
			$addAction->addOperation -= 1;
			if($addAction->addOperation > 0){
				array_unshift($addActions, $addAction);
			}
		}
		return $addActions;
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
	public function getBalanceForDate(\DateTime $date){
		$currentBalance = 0;
		foreach($this->actions as $action){
			if($action->date <= $date){
				$currentBalance -= $action->subOperation;
				$currentBalance += $action->addOperation;
			}else{
				break;
			}
		}
		return $currentBalance;
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

	/**
	 * Add 'add balance' action to $this->actions for the given Year.
	 *
	 * The balance is added on the startDate of the Year.
	 *
	 * @param Year $year Year object
	 */
	protected function addAction(Year $year){
		$legalAction = new AddBalanceAction($year->startDate, $year->quotumLegal, 0);
		$legalAction->expirationDate = $year->getQuotumLegalExpirationDate();
		$this->actions[] = $legalAction;

		$extraAction = new AddBalanceAction($year->startDate, $year->quotumExtra, 0);
		$extraAction->expirationDate = $year->getQuotumExtraExpirationDate();
		$this->actions[] = $extraAction;
	}


	/**
	 * Add 'used balance' action to $this->actions for the given UsedBalance.
	 *
	 * @param UsedBalance $balance UsedBalance object
	 */
	protected function addUsedBalanceAction(UsedBalance $balance){
		$this->actions[] = new UsedBalanceAction($balance->date, 0, $balance->amount);
	}


	/**
	 * Add 'expire balance' action to $this->actions for the given Year.
	 *
	 * The quota is deducted on the expiry date for the quotum
	 * as determined by the Year object.
	 *
	 * @param Year $year Year object
	 */
	protected function expiresAction(Year $year){
		$this->actions[] = new ExpireAction($year->getQuotumLegalExpirationDate(), 0, $year->quotumLegal);
		$this->actions[] = new ExpireAction($year->getQuotumExtraExpirationDate(), 0, $year->quotumExtra);
	}
}