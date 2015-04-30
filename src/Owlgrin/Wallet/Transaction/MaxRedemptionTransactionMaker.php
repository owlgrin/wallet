<?php namespace Owlgrin\Wallet\Transaction;

class MaxRedemptionTransactionMaker extends RedemptionTransactionMaker {
	public function make($action, $amount, $current = null)
	{
		return [
			'direction' => $this->getDirection($action),
			'type' => self::TYPE,
			'amount' => $this->getMaxAmount($action, $amount, $current)
		];
	}

	protected function getDirection($action)
	{
		return self::DIRECTION_ADJUST;
	}

	protected function getMaxAmount($action, $amount, $current)
	{
		switch($action)
		{
			case self::ACTION_DEPOSIT:
				if($current >= $amount) return $current; // 2 should be coming from config
				return $amount;

			case self::ACTION_WITHDRAW:
				return $current - 1;
		}
	}
}