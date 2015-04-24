<?php namespace Owlgrin\Wallet\Transaction;

abstract class AbstractTransactionMaker implements TransactionMaker {

	const ACTION_DEPOSIT = 'DEPOSIT';
	const ACTION_WITHDRAW = 'WITHDRAW';
	const DIRECTION_CREDIT = 'CREDIT';
	const DIRECTION_DEBIT = 'DEBIT';
	const DIRECTION_ADJUST = 'ADJUST';

	abstract public function make($action, $amount);

	protected function getDirection($action)
	{
		return $action == self::ACTION_DEPOSIT
			? self::DIRECTION_CREDIT
			: self::DIRECTION_DEBIT;
	}
}