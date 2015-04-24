<?php namespace Owlgrin\Wallet\Transaction;

class AdjusterRedemptionTransactionMaker extends RedemptionTransactionMaker {
	public function make($action, $amount)
	{
		return [
			'direction' => $this->getDirection($action),
			'type' => self::TYPE,
			'amount' => $this->getAdjustedAmount()
		];
	}

	protected function getDirection($action)
	{
		return self::DIRECTION_ADJUST;
	}

	protected function getAdjustedAmount()
	{
		return 2; // should come from config
	}
}