<?php

namespace InterExperts\BalanceCalculator;

class UsedBalanceAction extends Action {
	public $remainingBalance = 0;

	public function __construct(\DateTime $date, $addOperation, $subOperation){
		parent::__construct($date, $addOperation, $subOperation);
		$this->remainingBalance = $subOperation;
	}
}
