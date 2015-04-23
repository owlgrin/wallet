<?php namespace Owlgrin\Wallet\Wallet;

use Illuminate\Database\DatabaseManager as Database;

use Owlgrin\Wallet\Exceptions;
use Config;

class DbWalletRepo implements WalletRepo {

	protected $db;

	const ACTION_CREDIT = 'credit';

	public function __construct(Database $db)
	{
		$this->db = $db;
	}

	public function create($userId)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.wallets'))->insertGetId([
				'user_id'          => $userId,
				'balance'          => 0,
				'redemption_limit' => 0,
				'deleted_at'       => null,
				'created_at'       => $this->db->raw('now()'),
				'updated_at'       => $this->db->raw('now()')
			]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function update($walletId, $balance, $redemptionLeft)
	{
		try
		{

			$this->db->table(Config::get('wallet::tables.wallets'))
				->where('id', $walletId)
				->update([
					'balance'         => $balance,
					'redemption_limit' => $redemptionLeft,
					'deleted_at'	  => null,
					'updated_at'	  => $this->db->raw('now()')
				]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function find($id)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.wallets'))
				->where('id', $id)
				->where('deleted_at', null)
				->first();
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
			return $this->db->table(Config::get('wallet::tables.wallets'))
				->where('user_id', $userId)
				->first();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	//check if the user has credits left
	public function hasCredit($walletId)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.wallets'))
				->where('id', $walletId)
				->where('deleted_at', null)
				->where('redemption_limit', '>', 0)
				->where('balance', '>', 0)
				->first();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

}