<?php namespace Owlgrin\Wallet\Credit;

use Illuminate\Database\DatabaseManager as Database;
use Owlgrin\Wallet\Exceptions;
use Owlgrin\Wallet\Redemption\RedemptionRepo;

use PDOException, Exception, Config;

class DbCreditRepo implements CreditRepo {

	protected $db;

	public function __construct(Database $db, RedemptionRepo $redemptionRepo)
	{
		$this->db = $db;
		$this->redemptionRepo = $redemptionRepo;
	}

	public function add($userId, $credit, $redemption)
	{
		try
		{
			$this->db->table(Config::get('wallet::tables.credits'))->insert([
				'user_id'            => $userId,
				'amount_initial'     => $credit,
				'amount_left'        => $credit,
				'redemption_initial' => $redemption,
				'redemption_left'    => $redemption,
				'created_at'         => $this->db->raw('now()'),
				'updated_at'         => $this->db->raw('now()')
			]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function redeem($userId, $requestAmount)
	{
		try
		{
			//starting a transition
			$this->db->beginTransaction();

			//find amount to redeem
			list($redeemAmount, $creditId, $redemptionLeft, $discard) = $this->amountRedeem($userId, $requestAmount);

			$update = [ 'redemption_left' => $redemptionLeft-1 ];

			if($discard)
			{
				$update = array_merge($update, ['is_discarded' => true]);
			}

			$update = $this->db->table(Config::get('wallet::tables.credits'))
						->where('id', $creditId)
						->decrement('amount_left', $redeemAmount, $update);

			$redemption = $this->redemptionRepo->add($userId, $creditId, $redeemAmount, $requestAmount);

			$this->db->commit();

			return $redemption;
		}
		catch(PDOException $e)
		{
			$this->db->rollback();

			throw new Exceptions\InternalException;
		}
	}

	public function amountRedeem($userId, $amount)
	{
		try
		{
			$discard = false;

			$left = $this->left($userId);

			if($left['amount_left'] == $amount or $left['redemption_left'] == 1)
			{
				$discard = true;
			}

			if($left['amount_left'] >= $amount)
			{
				return [$amount, $left['id'], $left['redemption_left'], $discard];
			}
			else
			{
				return [$left['amount_left'], $left['id'], $left['redemption_left'], $discard = true];
			}
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function left($userId)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.credits'))
				->select('amount_left', 'id', 'amount_initial', 'redemption_initial', 'redemption_left')
				->where('user_id', $userId)
				->where('is_discarded', false)
				->first();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function hasWallet($userId)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.credits'))
				->where('user_id', $userId)
				->first();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function hasCredit($userId)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.credits'))
				->where('user_id', $userId)
				->where('is_discarded', false)
				->first();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function discardCredit($userId)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.credits'))
				->where('user_id', $userId)
				->where('is_discarded', true)
				->update('is_discarded', false);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}
}