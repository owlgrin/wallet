<?php namespace Owlgrin\Wallet\Transaction;

interface TransactionRepo {

		public function add($balanceId, $amount, $direction);

		public function findByUser($userId);

}
