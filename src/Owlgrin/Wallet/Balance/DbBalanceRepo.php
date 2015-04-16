<?php namespace Owlgrin\Wallet\Balance;

use Illuminate\Database\DatabaseManager as Database;

use PDOException, Exception, Config;

class DbBalanceRepo implements BalanceRepo {

	protected $db;

	public function __construct(Database $db)
	{
		$this->db = $db;
	}

	//add balances to the user
	public function add($userId, $coupon)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.balances'))->insertGetId([
				'user_id'     => $userId,
				'amount'      => $coupon['amount'],
				'redemptions' => $coupon['amount_redemptions'],
				'expired_at'  => null,
				'created_at'  => $this->db->raw('now()'),
				'updated_at'  => $this->db->raw('now()')
			]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function credit($userId, $coupon)
	{
		try
		{
			//find if balance exists for user
			$balance = $this->findByUser($userId);

			//check if we will make a fresh entry of the amount and redemption
			//or we will just sum up the amount and redemptions with old one
			if(($balance['amount'] > 0) and ($balance['redemptions'] > 0) and is_null($balance['expired_at']))
			{
				$newAmount = $balance['amount'] + $coupon['amount'];
				$newRedemptions = $balance['redemptions'] + $coupon['amount_redemptions'];
			}
			else
			{
				$newAmount = $coupon['amount'];
				$newRedemptions = $coupon['amount_redemptions'];
			}

			//updating the balances of the user while crediting
			$this->updateOnCredit($balance['id'], $newAmount, $newRedemptions);

			return $balance['id'];
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	//updating the balances while crediting
	public function updateOnCredit($balanceId, $amount, $redemptions)
	{
		try
		{
			$this->db->table(Config::get('wallet::tables.balances'))
				->where('id', $balanceId)
				->update([
					'amount' => $amount,
					'redemptions' => $redemptions,
					'expired_at' => null
				]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	//check if the user has credits left
	public function hasCredit($userId)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.balances'))
				->where('user_id', $userId)
				->where('expired_at', null)
				->where('redemptions', '>', 0)
				->where('amount', '>', 0)
				->first();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	//find the balances of the user
	public function findByUser($userId)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.balances'))
				->where('user_id', $userId)
				->first();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	//updating the balances on redemption
	public function updateOnRedemption($balanceId, $leftAmount, $leftRedemption)
	{
		try
		{
			$this->db->table(Config::get('wallet::tables.balances'))
				->where('id', $balanceId)
				->update([
					'amount'      => $leftAmount,
					'redemptions' => $leftRedemption
				]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	//find the left balances of the user
	public function left($userId)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.balances'))
				->where('user_id', $userId)
				->where('expired_at', null)
				->pluck('amount');
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function addBlank($userId)
	{
		$coupon = [
			'amount'      => 0,
			'redemptions' => 0
		];

		$this->add($userId, $coupon);
	}
}