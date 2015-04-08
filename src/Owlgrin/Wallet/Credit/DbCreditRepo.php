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

	public function add($userId, $credit, $redemptionCount)
	{
		if(! $this->canCredit($userId)) throw new Exceptions\CreditsLimitReachedException;

		try
		{

			$this->db->table(Config::get('wallet::tables.credits'))->insert([
				'user_id'            => $userId,
				'amount_initial'     => $credit,
				'amount_left'        => $credit,
				'redemptions_initial' => $redemptionCount,
				'redemptions_left'    => $redemptionCount,
				'created_at'         => $this->db->raw('now()'),
				'updated_at'         => $this->db->raw('now()')
			]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function redeem($userId, $requestedAmount)
	{
		if( ! $this->hasCredit($userId)) throw new Exceptions\NoCreditsException;

		try
		{
			$this->db->beginTransaction();

			$credits = $this->findByUser($userId);

			$redeemedAmount = $this->getRedemptionAmount($requestedAmount, $credits['amount_left']);

			$this->db->table(Config::get('wallet::tables.credits'))
				->where('id', $credits['id'])
				->update([
					'amount_left' => $credits['amount_left'] - $redeemedAmount,
					'redemptions_left' => $credits['redemptions_left'] - 1
				]);

			$this->expireIfExhausted($credits['id']);

			$redemed = $this->redemptionRepo->add($userId, $credits['id'], $redeemedAmount, $requestedAmount);

			$this->db->commit();

			return $redemed;
		}
		catch(PDOException $e)
		{
			$this->db->rollback();
			throw new Exceptions\InternalException;
		}
	}

	private function getRedemptionAmount($requestedAmount, $amountLeft)
	{
		if($amountLeft >= $requestedAmount)
		{
			return $requestedAmount;
		}
		else
		{
			return $amountLeft;
		}
	}

	private function expireIfExhausted($creditId)
	{
		try
		{
			// update table where id = $creditId and (amount_left = 0 or redemptions_left = 0) set expired_at = now();
			$this->db->table(Config::get('wallet::tables.credits'))
				->where('id', $creditId)
				->where(function($query)
	            {
	                $query->where('redemptions_left', 0)
	                      ->orWhere('amount_left', 0);
	            })
				->update([
					'expired_at' => $this->db->raw('now()')
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
			return $this->db->table(Config::get('wallet::tables.credits'))
				->select('amount_left', 'id', 'amount_initial', 'redemptions_initial', 'redemptions_left')
				->where('user_id', $userId)
				->where('expired_at', null)
				->first();
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
				->where('user_id', $userId)
				->where('expired_at', null)
				->sum('amount_left');
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
				->where('expired_at', null)
				->count() > 0;
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function canCredit($userId)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.credits'))
				->where('user_id', $userId)
				->where('expired_at', null)
				->count() < Config::get('wallet::limit');
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}
}