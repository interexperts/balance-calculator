<?php
namespace InterExperts\BalanceCalculator;

use \InterExperts\BalanceCalculator\Year;
use \InterExperts\BalanceCalculator\Action;

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
	 * Add a Year object to be used for calculations.
	 * This method triggers recalculating quota.
	 * 
	 * @param Year $year
	 */
	public function addYear(Year $year) {
		$this->years[] = $year;
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

		// Sort $this->actions by the date attribute:
		usort($this->actions, function($a, $b){
			if($a->date == $b->date){
				return 0;
			}
			return ($a->date < $b->date) ? -1 : 1;
		});
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
	 * Add 'add balance' action to $this->actions for the given Year.
	 *
	 * The balance is added on the startDate of the Year.
	 * 
	 * @param Year $year Year object
	 */
	protected function addAction(Year $year){
		$this->actions[] = new Action($year->startDate, $year->quotumLegal, 0);
		$this->actions[] = new Action($year->startDate, $year->quotumExtra, 0);
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
		$this->actions[] = new Action($year->getQuotumLegalExpirationDate(), 0, $year->quotumLegal);
		$this->actions[] = new Action($year->getQuotumExtraExpirationDate(), 0, $year->quotumExtra);
	}
}