<?php

namespace InterExperts\BalanceCalculator;

/**
 * ExpireAction is an action for negative balance.
 */
class FinalizeAction extends Action {
	public function __construct(\DateTime $date){
		parent::__construct($date, 0, 0);
	}

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
		return $actions;
		$subOperation = $this->subOperation;

		if ($subOperation > 0) {
			// Iterate over the $actions array:
			foreach($actions as &$action){
				// Only operate on UsedBalanceAction objects which are not processed yet:
				if (is_a($action, '\InterExperts\BalanceCalculator\UsedBalanceAction')
					&& (is_null($action->processedBy) || empty($action->processedBy) || $action->remainingBalance)) {
					// Check whether the UsedBalanceAction is before the current ExpireAction:
					if($action->date <= $this->date){
						// Check whether the deduction would not result in negative balance:
						$actionSubOp = $action->subOperation;
						if ($action->remainingBalance > 0) {
							$actionSubOp = $action->remainingBalance;
						}
						if ($subOperation - $actionSubOp >= 0) {
							$subOperation -= $actionSubOp;
							$action->remainingBalance = 0;
							$action->processedBy[] = &$this;
						} else {

							$action->remainingBalance -= $subOperation;
							$action->processedBy[] = &$this;
							$subOperation = 0;
						}
					}
				}
			}

			// New balance deduction value:
			$this->subOperation = $subOperation;
		}

		// Set this object to be processed by itself:
		$this->processedBy[] = &$this;
	}
}
