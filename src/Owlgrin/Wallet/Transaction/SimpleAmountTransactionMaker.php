<?php namespace Owlgrin\Wallet\Transaction;

class SimpleAmountTransactionMaker extends AmountTransactionMaker {
	public function make($action, $amount, $current = null)
	{
		return [
			'direction' => $this->getDirection($action),
			'type' => self::TYPE,
			'amount' => $amount
		];
	}
}