<?php

namespace InterExperts\BalanceCalculator;

class Action{
	public $date = null;
	public $addOperation = 0;
	public $subOperation = 0;

	public function __construct(\DateTime $date, $addOperation, $subOperation){
		$this->date = $date;
		$this->addOperation = $addOperation;
		$this->subOperation = $subOperation;
	}
}