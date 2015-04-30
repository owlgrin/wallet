<?php namespace Owlgrin\Wallet\Wallet;

use Illuminate\Database\DatabaseManager as Database;

use Owlgrin\Wallet\Exceptions;
use Config;

class DbWalletRepo implements WalletRepo {

	protected $db;

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
				'amount'          => 0,
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

	public function deposit($walletId, $amount, $redemptionLeft)
	{
		try
		{

			$this->db->table(Config::get('wallet::tables.wallets'))
				->where('id', $walletId)
				->update([
					'amount'         => $this->db->raw('`amount` + ' . $amount),
					'redemption_limit' => $this->db->raw('`redemption_limit` + ' . $redemptionLeft),
					'deleted_at'	  => null,
					'updated_at'	  => $this->db->raw('now()')
				]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function update($walletId, $amount, $redemptionLeft)
	{
		try
		{

			$this->db->table(Config::get('wallet::tables.wallets'))
				->where('id', $walletId)
				->update([
					'amount'         => $amount,
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
				->where('amount', '>', 0)
				->first();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function amount($walletId)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.wallets'))
				->where('id', $walletId)
				->where('deleted_at', null)
				->pluck('amount');
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

}