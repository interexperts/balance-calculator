<?php

namespace InterExperts\BalanceCalculator;

class Year{
	public $startDate = null;
	public $quotumLegal = 0;
	public $quotumExtra = 0;
	public $quotumLegalValidity = 0; // maanden + 1 jaar
	public $quotumExtraValidity = 0; // maanden + 1 jaar

	public function __construct(\DateTime $startDate, $quotumLegal, $quotumExtra, $quotumLegalValidity, $quotumExtraValidity) {
		$this->startDate = $startDate;
		$this->quotumLegal = $quotumLegal;
		$this->quotumExtra = $quotumExtra;
		$this->quotumLegalValidity = $quotumLegalValidity;
		$this->quotumExtraValidity = $quotumExtraValidity;
	}

	public function getQuotumLegalExpirationDate(){
		$validityInMonths = 12 + $this->quotumLegalValidity;
		$startDate = clone $this->startDate;
		return $startDate->add(new \DateInterval('P' . $validityInMonths . 'M'));
	}

	public function getQuotumExtraExpirationDate(){
		$validityInMonths = 12 + $this->quotumExtraValidity;
		$startDate = clone $this->startDate;
		return $startDate->add(new \DateInterval('P' . $validityInMonths . 'M'));
	}
}