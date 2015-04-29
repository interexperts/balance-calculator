<?php

namespace InterExperts\BalanceCalculator;

/**
 * ExpireAction is an action for expiring balance.
 * The main additional functionality of this object is that it
 * calculates which UsedBalanceAction's this ExpiringBalance can account for.
 *
 * If this object can account for an UsedBalanceAction(), the used balance
 * will be deducted from the balance that would originally be deducted when
 * the quotum expires.
 */
class ExpireAction extends Action {
	/**
	 * Calculate the balance that should not be deducted because UsedBalanceAction's
	 * already deduct this balance.
	 *
	 * This method inspects the $actions array and modifies the UsedBalanceAction objects
	 * inside the array (they are flagged as 'processed' by this ExpireAction).
	 *
	 * @param  array &$actions Array of Action objects
	 */
	public function calculateExpiringBalance(&$actions){
		$subOperation = $this->subOperation;

		// Iterate over the $actions array:
		foreach($actions as &$action){
			// Only operate on UsedBalanceAction objects which are not processed yet:
			if (is_a($action, '\InterExperts\BalanceCalculator\UsedBalanceAction') && is_null($action->processedBy)) {
				// Check whether the UsedBalanceAction is before the current ExpireAction:
				if($action->date <= $this->date){
					// Check whether the deduction would not result in negative balance:
					if ($subOperation - $action->subOperation >= 0) {
						$subOperation -= $action->subOperation;
						$action->processedBy = $this;
					}
				}
			}
		}

		// Set this object to be processed by itself:
		$this->processedBy = $this;

		// New balance deduction value:
		$this->subOperation = $subOperation;
	}
}
