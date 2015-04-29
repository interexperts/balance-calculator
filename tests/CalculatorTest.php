<?php

use \InterExperts\BalanceCalculator\Year;
use \InterExperts\BalanceCalculator\Calculator;

class CalculatorTest extends PHPUnit_Framework_TestCase{

	public function testGetQuotum(){
		$testFiles = glob(dirname(__FILE__) . '/balance-testdata/balance-*.csv');
		foreach($testFiles as $testFile){
			$filename = basename($testFile);
			$fh = fopen(dirname(__FILE__). '/balance-testdata/'. $filename, 'r');
			$header = fgetcsv($fh);
			$calculator = new Calculator();
			while($inputYear = fgetcsv($fh)){
				$calculator->addYear(new Year(new \DateTime($inputYear[1]), $inputYear[2], $inputYear[3]));
			}
			fclose($fh);
			$fh = fopen(dirname(__FILE__). '/balance-testdata/expected-'. $filename, 'r');
			$header = fgetcsv($fh);
			while($testDate = fgetcsv($fh)){
				$this->assertEquals($testDate[1], $calculator->getBalanceForDate(new \DateTime($testDate[0])), 
					"
					  Filename: {$filename}\r\n
   					  TestDatum: {$testDate[0]}\r\n
					"
					);
			}
			fclose($fh);
		}
	}
}