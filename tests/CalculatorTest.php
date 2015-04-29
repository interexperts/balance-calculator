<?php

use \InterExperts\BalanceCalculator\Year;
use \InterExperts\BalanceCalculator\Calculator;
use \InterExperts\BalanceCalculator\UsedBalance;

class CalculatorTest extends PHPUnit_Framework_TestCase{

	public function testGetQuotum(){
		$this->doQuotumTest(dirname(__FILE__) . '/balance-testdata/testGetQuotum/');
	}

	public function testGetQuotumExtra(){
		$this->doQuotumTest(dirname(__FILE__) . '/balance-testdata/testGetQuotumExtra/');
	}

	public function testGetQuotumUsedDays(){
		$this->doQuotumTest(dirname(__FILE__) . '/balance-testdata/testGetQuotumUsedDays/', true);
	}

	protected function doQuotumTest($directory, $usedDays = false) {
		$filenamePattern = 'balance-*.csv';
		$testFiles = glob($directory . $filenamePattern);

		foreach($testFiles as $testFile){
			$filename = basename($testFile);
			$fh = fopen($directory . $filename, 'r');
			$header = fgetcsv($fh);
			$calculator = new Calculator();
			while($inputYear = fgetcsv($fh)){
				$calculator->addYear(new Year(new \DateTime($inputYear[1]), $inputYear[2], $inputYear[4], $inputYear[3], $inputYear[5]));
			}
			fclose($fh);

			if ($usedDays) {
				$fh = fopen($directory . 'used-days-'. $filename, 'r');
				$header = fgetcsv($fh);
				while($testDate = fgetcsv($fh)){
					$calculator->addUsedBalance(new UsedBalance(new \DateTime($testDate[0]), $testDate[1]));
				}
				fclose($fh);
			}

			$fh = fopen($directory . 'expected-'. $filename, 'r');
			$header = fgetcsv($fh);
			while($testDate = fgetcsv($fh)){
				$this->assertEquals($testDate[1], $calculator->getBalanceForDate(new \DateTime($testDate[0])), 
					"Calculated balance does not match expected balance.\nFilename: {$filename}\nTest date: {$testDate[0]}"
					);
			}
			fclose($fh);
		}
	}
}
