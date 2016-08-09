[![Latest Stable Version](https://img.shields.io/packagist/v/interexperts/balancecalculator.svg|)](https://packagist.org/packages/interexperts/balancecalculator)
[![Total Downloads](https://img.shields.io/packagist/dt/interexperts/balancecalculator.svg)](https://packagist.org/packages/interexperts/balancecalculator)
[![License](https://img.shields.io/packagist/l/interexperts/balancecalculator)](https://packagist.org/packages/interexperts/balancecalculator)
[![Build Status](https://travis-ci.org/interexperts/balance-calculator.svg?branch=master)](https://travis-ci.org/interexperts/balance-calculator)
[![codecov](https://codecov.io/gh/interexperts/balance-calculator/branch/master/graph/badge.svg)](https://codecov.io/gh/interexperts/balance-calculator)

# Balance Calculator

Library for calculating holiday balances.

## Example

```php
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
```