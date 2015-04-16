<?php namespace Owlgrin\Wallet\Transaction;

use Illuminate\Database\DatabaseManager as Database;

use Owlgrin\Wallet\Exceptions;

use PDOException, Exception, Config;

class DbTransactionRepo implements TransactionRepo {

	protected $db;

	public function __construct(Database $db)
	{
		$this->db = $db;
	}

	public function add($balanceId, $amount, $direction)
	{
		try
		{
			$this->db->table(Config::get('wallet::tables.transactions'))->insert([
				'balance_id'    => $balanceId,
				'amount'     => $amount,
				'direction' => $direction
			]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

}