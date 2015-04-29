<?php

require_once('./vendor/autoload.php');

use \InterExperts\BalanceCalculator\Year;
use \InterExperts\BalanceCalculator\Calculator;

$jaar2012 = new Year(new \DateTime('2012-01-01'), 20, 6);
$jaar2013 = new Year(new \DateTime('2013-01-01'), 20, 6);
$jaar2014 = new Year(new \DateTime('2014-01-01'), 20, 6);
$jaar2015 = new Year(new \DateTime('2015-01-01'), 20, 6);

$berekenaar = new Calculator();
$berekenaar->addYear($jaar2012);
$berekenaar->addYear($jaar2013);
$berekenaar->addYear($jaar2014);
$berekenaar->addYear($jaar2015);

$testDatums = array('2012-01-01', '2012-12-31', '2013-01-01', '2013-07-01', '2013-12-31', '2014-01-01', '2014-07-01', '2014-12-31', '2015-01-01', '2015-07-01', '2015-12-31');

foreach ($testDatums as $testDatum) {
	echo "\r\n". $testDatum. ' = ';
	echo $berekenaar->getBalanceForDate(new DateTime($testDatum));
}

