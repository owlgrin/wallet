<?php namespace Owlgrin\Wallet\Redemption;

use Illuminate\Database\DatabaseManager as Database;

use Owlgrin\Wallet\Balance\BalanceRepo;
use Owlgrin\Wallet\Transaction\TransactionRepo;
use Owlgrin\Wallet\Exceptions;

class DbRedemptionRepo implements RedemptionRepo {

	protected $db;
	protected $balanceRepo;
	protected $transactionRepo;

	const ACTION_DEBIT = 'debit';

	public function __construct(Database $db, BalanceRepo $balanceRepo, TransactionRepo $transactionRepo)
	{
		$this->db              = $db;
		$this->balanceRepo     = $balanceRepo;
		$this->transactionRepo = $transactionRepo;
	}

	public function redeem($userId, $requestedAmount)
	{
		//checking if user has balance left
		if( ! $this->balanceRepo->hasCredit($userId)) throw new Exceptions\NoCreditsException;

		try
		{
			//starting the transaction
			$this->db->beginTransaction();

			//find the existing balance of the user
			$balance = $this->balanceRepo->findByUser($userId);

			//find the amount which is to be redeemed
			$redeemedAmount = $this->getRedemptionAmount($requestedAmount, $balance['amount']);

			//find the left amount and left redemptions
			$leftAmount = $balance['amount'] - $redeemedAmount;
			$leftRedemptions = $balance['redemptions'] - 1;

			//update the balance on redemption
			$this->balanceRepo->updateOnRedemption($balance['id'], $leftAmount, $leftRedemptions);

			// credit entry in transaction table
			$this->transactionRepo->add($balance['id'], $redeemedAmount, self::ACTION_DEBIT);

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

}