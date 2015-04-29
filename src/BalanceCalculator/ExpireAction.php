<?php

namespace InterExperts\BalanceCalculator;

class ExpireAction extends Action{
	public function calculateExpiringBalance(&$actions){
		$subOperation = $this->subOperation;
		// Loop door acties van bovenliggend om huidige overblijfsels te verwijderen
		foreach($actions as &$action){
			if($action->date <= $this->date){
				if(is_a($action, '\InterExperts\BalanceCalculator\UsedBalanceAction') && is_null($action->processedBy)){
					if ($subOperation - $action->subOperation >= 0) {
						$subOperation -= $action->subOperation;
						$action->processedBy = $this;
					}
				}
			}
		}
		$this->processedBy = $this;
		$this->subOperation = $subOperation;
	}
}