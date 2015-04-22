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

	public function findByUser($userId)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.transactions').' AS t')
				->join(Config::get('wallet::tables.balances').' AS b', 'b.id', '=', 't.balance_id')
				->select('t.amount', 't.direction')
				->where('b.user_id', $userId)
				->get();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

}