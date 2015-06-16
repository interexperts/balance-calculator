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

	public function testgiveStillValidAddBalancesForDate(){
		$directory = dirname(__FILE__) . '/balance-testdata/testGetQuotumUsedDays/';
		$testFile = 'balance-01.csv';

		$filename = basename($testFile);
		$fh = fopen($directory . $filename, 'r');
		$header = fgetcsv($fh);
		$calculator = new Calculator();
		while($inputYear = fgetcsv($fh)){
			$calculator->addYear(new Year(new \DateTime($inputYear[1]), $inputYear[2], $inputYear[4], $inputYear[3], $inputYear[5]));
		}
		fclose($fh);

		$fh = fopen($directory . 'used-days-'. $filename, 'r');
		$header = fgetcsv($fh);
		while($testDate = fgetcsv($fh)){
			$calculator->addUsedBalance(new UsedBalance(new \DateTime($testDate[0]), $testDate[1]));
		}
		fclose($fh);

		$fh = fopen($directory . 'expected-'. $filename, 'r');
		$header = fgetcsv($fh);
		while($testDate = fgetcsv($fh)){
			$calculator->giveStillValidAddBalancesForDate(new \DateTime($testDate[0]));
		}
		fclose($fh);
	}

	public function testGiveQuotumAddBalanceActions(){
		$directory = dirname(__FILE__) . '/balance-testdata/testGiveQuotumAddBalanceActions/';
		$testFile = 'balance-01.csv';

		$filename = basename($testFile);
		$fh = fopen($directory . $filename, 'r');
		$header = fgetcsv($fh);
		$calculator = new Calculator();
		while($inputYear = fgetcsv($fh)){
			$calculator->addYear(new Year(new \DateTime($inputYear[1]), $inputYear[2], $inputYear[4], $inputYear[3], $inputYear[5]));
		}
		fclose($fh);

		$fh = fopen($directory . 'used-days-'. $filename, 'r');
		$header = fgetcsv($fh);
		while($testDate = fgetcsv($fh)){
			$calculator->addUsedBalance(new UsedBalance(new \DateTime($testDate[0]), $testDate[1]));
		}
		fclose($fh);
		
		$fh = fopen($directory . 'expected-remaining-'. $filename, 'r');
		$header = fgetcsv($fh);
		while($testDate = fgetcsv($fh)){
			$actions = $calculator->getAddBalanceActionsForDate(new \DateTime($testDate[0]));
			$found = false;
			foreach($actions as $action){
				if($action->expirationDate->format('Y-m-d') == $testDate[2]){
					$this->assertEquals($testDate[1], $action->remainingBalance);
					$found = true;
				}
			}
			if(!$found){
				$this->fail("Expire date not found. {$testDate[2]}");
			}
		}
		fclose($fh);
	
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
