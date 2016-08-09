<?php
require 'vendor/autoload.php';

use \InterExperts\BalanceCalculator\Year;
use \InterExperts\BalanceCalculator\Calculator;
use \InterExperts\BalanceCalculator\UsedBalance;

$calculator = new Calculator();

// Total 35 days (25 legal + 10 extra)
$quotumLegal = 25;
$quotumExtra = 10;

$quotumLegalValidity = 6;
$quotumExtraValidity = 60;

$calculator->addYear(new Year(new \DateTime("2015-01-01"), $quotumLegal, $quotumExtra, $quotumLegalValidity, $quotumExtraValidity));

echo $calculator->getBalanceForDate(new \DateTime('2015-02-02')) . "\n";
// 35 days

echo $calculator->getBalanceForDate(new \DateTime('2016-02-02')) . "\n";
// 35 days

echo $calculator->getBalanceForDate(new \DateTime('2016-12-02')) . "\n";
// 10 days (legal days expired, extra days still valid)

echo $calculator->getBalanceForDate(new \DateTime('2021-02-02')) . "\n";
// 0 days (legal days expired, extra days also expired)

// Take some days off:
$calculator->addUsedBalance(new UsedBalance(new \DateTime("2015-02-02"), 1));

// Before:
echo $calculator->getBalanceForDate(new \DateTime('2015-02-01')) . "\n";
// 35 days

// After:
echo $calculator->getBalanceForDate(new \DateTime('2015-02-02')) . "\n";
// 34 days
?>