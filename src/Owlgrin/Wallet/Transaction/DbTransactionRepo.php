<?php namespace Owlgrin\Wallet\Transaction;

use Illuminate\Database\DatabaseManager as Database;

use Owlgrin\Wallet\Exceptions;
use Owlgrin\Wallet\Wallet\WalletRepo;

use PDOException, Config;

class DbTransactionRepo implements TransactionRepo {

	protected $db;
	protected $walletRepo;

	public function __construct(Database $db, WalletRepo $walletRepo)
	{
		$this->db         = $db;
		$this->walletRepo = $walletRepo;
	}

	public function create($walletId, $amount, $direction, $type, $triggerType, $triggerId)
	{
		try
		{
			$this->db->table(Config::get('wallet::tables.transactions'))->insert([
				'wallet_id'    => $walletId,
				'amount'       => $amount,
				'direction'    => $direction,
				'type'         => $type,
				'trigger_type' => $triggerType,
				'trigger_id'   => $triggerId,
				'created_at'   => $this->db->raw('now()'),
				'updated_at'   => $this->db->raw('now()')
			]);

		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function deposit($walletId, $amount, $redemptionLeft, $triggerType, $triggerId)
	{
		try
		{
			//starting the transaction
			$this->db->beginTransaction();

			$wallet = $this->walletRepo->find($walletId);

			//check if we will make a fresh entry of the amount and redemption
			//or we will just sum up the amount and redemptions with old one
			if(($wallet['balance'] > 0) and ($wallet['redemption_limit'] > 0) and is_null($wallet['deleted_at']))
			{
				$newAmount = $wallet['balance'] + $amount;
				$newRedemptions = $wallet['redemption_limit'] + $redemptionLeft;
			}
			else
			{
				$newAmount = $amount;
				$newRedemptions = $redemptionLeft;
			}

			$this->create($walletId, $amount, $direction = 'credit', $type = 'amount', $triggerType, $triggerId);

			$this->create($walletId, $amount, $direction = 'credit', $type = 'redemption', $triggerType, $triggerId);

			$this->walletRepo->update($walletId, $newAmount, $newRedemptions);

			$this->db->commit();
		}
		catch(Exceptions\InternalException $e)
		{
			$this->db->rollback();

			throw new Exceptions\InternalException;
		}
	}

	public function redeem($walletId, $requestedAmount)
	{
		//checking if user has balance left
		if( ! $this->walletRepo->hasCredit($walletId)) throw new Exceptions\NoCreditsException;

		try
		{
			//starting the transaction
			$this->db->beginTransaction();

			//find the existing balance of the user
			$wallet = $this->walletRepo->find($walletId);

			//find the amount which is to be redeemed
			$redeemedAmount = $this->getRedemptionAmount($requestedAmount, $wallet['balance']);

			//find the left amount and left redemptions
			$leftAmount = $wallet['balance'] - $redeemedAmount;
			$leftRedemptions = $wallet['redemption_limit'] - 1;

			// credit entry in transaction table
			$this->create($walletId, $redeemedAmount, $direction = 'debit', $type = 'amount', $triggerType = 'redemption', null);

			$this->create($walletId, $redeemedAmount, $direction = 'debit', $type = 'redemption', $triggerType = 'redemption', null);

			//update the balance on redemption
			$this->walletRepo->update($walletId, $leftAmount, $leftRedemptions);

			//finally commiting all the changes
			$this->db->commit();

			//returning thr redeemed amount
			return $redeemedAmount;
		}
		catch(Exceptions\InternalException $e)
		{
			//rolling back is exception occurs
			$this->db->rollback();

			throw new Exceptions\InternalException;
		}
	}

	/**
	 * find redemption amount
	 * @param  [int] $requestedAmount [maount which is requested to user]
	 * @param  [int] $amountLeft      [amount which is left with user]
	 * @return [int]                  [amount]
	 */
	private function getRedemptionAmount($requestedAmount, $amountLeft)
	{
		//if the amount left is greater than or equal to requested amount
		//then we will return requested amount
		//else we will return amount left for redemption
		if($amountLeft >= $requestedAmount)
		{
			return $requestedAmount;
		}
		else
		{
			return $amountLeft;
		}
	}

	public function findByWallet($walletId, $direction)
	{
		try
		{
			$query = $this->db->table(Config::get('wallet::tables.transactions'))
				->select('amount', 'direction', 'type', 'trigger_type', 'trigger_id')
				->where('wallet_id', $walletId);

			if($direction != 'all')
			{
				$query = where('direction', $direction);
			}

			return $query->get();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

}