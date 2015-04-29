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
			$this->addAction($year);
			$this->expiresAction($year);
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
		$this->actions[] = new Action($year->startDate, $year->quotumLegal, 0);
		$this->actions[] = new Action($year->startDate, $year->quotumExtra, 0);
	}

	protected function expiresAction(Year $year){
		$this->actions[] = new Action($year->getQuotumLegalExpirationDate(), 0, $year->quotumLegal);
		$this->actions[] = new Action($year->getQuotumExtraExpirationDate(), 0, $year->quotumExtra);
	}
}