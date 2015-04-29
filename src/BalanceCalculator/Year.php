<?php

namespace InterExperts\BalanceCalculator;

class Year{
	public $startDate = null;
	public $receivedQuotum = 0;
	public $validityInMonths = 0; // maanden + 1 jaar

	public function __construct(\DateTime $startDate, $receivedQuotum, $validityInMonths){
		$this->startDate = $startDate;
		$this->receivedQuotum = $receivedQuotum;
		$this->validityInMonths = $validityInMonths;
	}

	public function getExpirationDate(){
		$validityInMonths = 12 + $this->validityInMonths;
		$startDate = clone $this->startDate;
		return $startDate->add(new \DateInterval('P' . $validityInMonths . 'M'));
	}
}