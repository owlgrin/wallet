<?php namespace Owlgrin\Wallet\Transaction;

class MaxRedemptionTransactionMaker extends RedemptionTransactionMaker {
	public function make($action, $amount, $current = null)
	{
		return [
			'direction' => $this->getDirection($action),
			'type' => self::TYPE,
			'amount' => $this->getAdjustedAmount($action, $amount, $current)
		];
	}

	protected function getDirection($action)
	{
		return self::DIRECTION_ADJUST;
	}

	protected function getAdjustedAmount($action, $amount, $current)
	{
		switch($action)
		{
			case self::ACTION_DEPOSIT:
				if($current >= 2) return $current; // 2 should be coming from config
				return 2;

			case self::ACTION_WITHDRAW:
				return $current - 1;
		}
	}
}