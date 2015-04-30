<?php namespace Owlgrin\Wallet\Transaction;

interface TransactionMaker {
	public function make($action, $amount, $currentBalance = null);
}