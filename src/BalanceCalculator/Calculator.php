<?php
namespace InterExperts\BalanceCalculator;

use \InterExperts\BalanceCalculator\Year;
use \InterExperts\BalanceCalculator\Action;

class Calculator{
	protected $actions = array();
	public $years = array();
	public function addYear(Year $year){
		$this->years[] = $year;
		$this->recalculate();
	}

	protected function recalculate(){
		$this->actions = array();
		foreach($this->years as $year){
			$this->actions[] = $this->addAction($year);
			$this->actions[] = $this->expiresAction($year);
		}
		usort($this->actions, function($a, $b){
			if($a->date == $b->date){
				return 0;
			}
			return ($a->date < $b->date) ? -1 : 1;
		});
	}

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

	protected function addAction(Year $year){
		return new Action($year->startDate, $year->receivedQuotum, 0);
	}

	protected function expiresAction(Year $year){
		return new Action($year->getExpirationDate(), 0, $year->receivedQuotum);
	}
}