<?php namespace Owlgrin\Wallet\Transaction;

class SimpleRedemptionTransactionMaker extends RedemptionTransactionMaker {
	public function make($action, $amount, $current = null)
	{
		return [
			'direction' => $this->getDirection($action),
			'type' => self::TYPE,
			'amount' => $amount
		];
	}
}