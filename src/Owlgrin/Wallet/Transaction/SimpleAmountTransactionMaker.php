<?php namespace Owlgrin\Wallet\Transaction;

class SimpleAmountTransactionMaker extends AmountTransactionMaker {
	public function make($action, $amount)
	{
		return [
			'direction' => $this->getDirection($action),
			'type' => self::TYPE,
			'amount' => $amount
		];
	}
}