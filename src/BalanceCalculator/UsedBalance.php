<?php

namespace InterExperts\BalanceCalculator;

class UsedBalance {
	public $date = null;
	public $amount = 0;

	public function __construct(\DateTime $date, $amount) {
		$this->date = $date;
		$this->amount = $amount;
	}
}