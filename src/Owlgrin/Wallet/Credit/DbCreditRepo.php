<?php namespace Owlgrin\Wallet\Credit;

use Illuminate\Database\DatabaseManager as Database;

use Owlgrin\Wallet\Exceptions;
use Owlgrin\Wallet\Coupon\CouponRepo;
use Owlgrin\Wallet\Balance\BalanceRepo;
use Owlgrin\Wallet\Transaction\TransactionRepo;

class DbCreditRepo implements CreditRepo {

	protected $db;
	protected $couponRepo;
	protected $balanceRepo;
	protected $transactionRepo;

	const ACTION_CREDIT = 'credit';

	public function __construct(Database $db,
		CouponRepo $couponRepo,
		BalanceRepo $balanceRepo,
		TransactionRepo $transactionRepo)
	{
		$this->db = $db;
		$this->couponRepo = $couponRepo;
		$this->balanceRepo = $balanceRepo;
		$this->transactionRepo = $transactionRepo;
	}

	public function blank($userId)
	{
		$this->balanceRepo->addBlank($userId);
	}

	public function apply($userId, $couponIdentifier)
	{
		//checking if the coupon has credit
		if(! $this->couponRepo->canBeUsed($couponIdentifier)) throw new Exceptions\CouponLimitReachedException;

		try
		{
			//starting the transaction
			$this->db->beginTransaction();

			// find the details of the coupon
			$coupon = $this->couponRepo->findByIdentifier($couponIdentifier);

			// entry of the coupon for the user
			$this->couponRepo->storeForUser($userId, $coupon['id']);

			//adding balance for the user
			$balanceId = $this->balanceRepo->credit($userId, $coupon);

			//entry of the credit in transaction table
			$this->transactionRepo->add($balanceId, $coupon['amount'], self::ACTION_CREDIT);

			// finally decrementing the redemptions of coupons table
			$this->couponRepo->decrementRedemptions($coupon['id']);

			$this->db->commit();
		}
		catch(Exceptions\InternalException $e)
		{
			$this->db->rollback();

			throw new Exceptions\InternalException;
		}
	}

	//find the left balance of the user
	public function left($userId)
	{
		return $this->balanceRepo->left($userId);
	}

	public function findByUser($userId)
	{
		return $this->balanceRepo->findByUser($userId);
	}
}