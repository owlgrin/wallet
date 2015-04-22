<?php namespace Owlgrin\Wallet;

/**
 * The Wallet core
 */
use Owlgrin\Wallet\Redemption\RedemptionRepo;
use Owlgrin\Wallet\Credit\CreditRepo;
use Owlgrin\Wallet\Coupon\CouponRepo;
use Owlgrin\Wallet\Transaction\TransactionRepo;
use Owlgrin\Wallet\Exceptions;

class Wallet {

	protected $redemptionRepo;
	protected $creditRepo;
	protected $couponRepo;
	protected $transactionRepo;

	public function __construct(RedemptionRepo $redemptionRepo, CreditRepo $creditRepo, CouponRepo $couponRepo, TransactionRepo $transactionRepo)
	{
		$this->redemptionRepo  = $redemptionRepo;
		$this->creditRepo      = $creditRepo;
		$this->couponRepo      = $couponRepo;
		$this->transactionRepo = $transactionRepo;
	}

	/**
	 * Initiating the wallet
	 * @param  [string] $user [unique identifier of the user]
	 * @return [object]       [object of wallet]
	 */
	public function user($user)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * @return [string] [returns user identifier]
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * add balnk credit for the user
	 */
	public function blankCredit()
	{
		$this->creditRepo->blank($this->user);
	}

	/**
	 * redeems the amount
	 * @param  [integer] $amount [inputs the amount]
	 * @return the credited amount
	 */
	public function redeem($amount)
	{
		return $this->redemptionRepo->redeem($this->user, $amount);
	}

	/**
	 * @return [integer|null] [returns amount left in of the user]
	 */
	public function left()
	{
		return $this->creditRepo->left($this->user);
	}

	/**
	 * @return [details of the user]
	 */
	public function findByUser()
	{
		return $this->creditRepo->findByUser($this->user);
	}

	/**
	 * credits a coupon for the user
	 * @param  [string] $coupon [identifier of the coupon]
	 * @return void
	 */
	public function credit($coupon)
	{
		$this->creditRepo->apply($this->user, $coupon);
	}

	/**
	 * add the new coupon
	 * @param  array  $coupon [contains details of the coupon]
	 * @return null
	 */
	public function coupon($coupon)
	{
		$this->couponRepo->add($coupon);
	}

	/**
	 * find if the coupon could be used
	 * @param  string $coupon accepts coupon identifier
	 * @return null if coupon is expired or invalid
	 * @return detail of the coupon if valid
	 */
	public function findCoupon($coupon)
	{
		return $this->couponRepo->canBeUsed($coupon);
	}

	/**
	 * find the transactions which has been done by user
	 * @return list of transactions containing amount and direction
	 */
	public function transactions()
	{
		return $this->transactionRepo->findByUser($this->user);
	}

	/**
	 * [returns all the coupons applied by the user]
	 * @return list of all the coupons
	 */
	public function userCoupons()
	{
		return $this->couponRepo->findByUser($this->user);
	}

}