<?php namespace Owlgrin\Wallet;

/**
 * The Wallet core
 */
use Owlgrin\Wallet\Coupon\CouponRepo;
use Owlgrin\Wallet\Wallet\WalletRepo;
use Owlgrin\Wallet\Transaction\TransactionRepo;

use Owlgrin\Wallet\Exceptions;

class Wallet {

	protected $couponRepo;
	protected $walletRepo;
	protected $transactionRepo;

	public function __construct(
		CouponRepo $couponRepo,
		WalletRepo $walletRepo,
		TransactionRepo $transactionRepo
	)
	{
		$this->couponRepo      = $couponRepo;
		$this->walletRepo      = $walletRepo;
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
		$this->wallet = $this->walletRepo->findByUser($user);

		if(is_null($this->wallet)) throw new Exceptions\WalletNotCreated;

		return $this;
	}

	/**
	 * @return the user identifier
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * returns the wallet of the user
	 */
	public function getWallet()
	{
		return $this->wallet;
	}

	/**
	 * create a new wallet
	 */
	public function create($userId)
	{
		$this->walletRepo->create($userId);
	}

	/**
	 * withdraw the amount
	 * @param  [integer] $amount [inputs the amount]
	 * @return the credited amount
	 */
	public function withdraw($amount, $trigger = null)
	{
		$trigger = [
			'type' => array_get($trigger, 'type', 'WITHDRAW'),
			'id'   => array_get($trigger, 'id')
		];

		return $this->transactionRepo->withdraw($this->wallet['id'], $amount, $trigger);
	}

	/**
	 * withdraw the amount
	 * @param  [integer] $amount [inputs the amount]
	 * @return the credited amount
	 */
	public function redeem($amount)
	{
		return $this->transactionRepo->withdraw(
			$this->wallet['id'], $amount, ['type' => 'REDEMPTION']
		);
	}

	/**
	 * @return [integer|null] [returns amount left in of the user]
	 */
	public function amount()
	{
		return $this->walletRepo->amount($this->wallet['id']);
	}

	/**
	 * @return [details of the user]
	 */
	public function findByUser()
	{
		return $this->walletRepo->find($this->wallet['id']);
	}

	/**
	 * credits a coupon for the user
	 * @param  [string] $coupon [identifier of the coupon]
	 * @return void
	 */
	public function deposit($amount, $redemptionLeft, $trigger)
	{
		$trigger = [
			'type' => array_get($trigger, 'type', 'DEPOSIT'),
			'id'   => array_get($trigger, 'id')
		];

		$this->transactionRepo->deposit($this->wallet['id'], $amount, $redemptionLeft, $trigger);
	}

	/**
	 * add the new coupon
	 * @param  array  $coupon [contains details of the coupon]
	 * @return null
	 */
	public function createCoupon($coupon)
	{
		$this->couponRepo->create($coupon);
	}

	/**
	 * find if the coupon could be used
	 * @param  string $coupon accepts coupon identifier
	 * @return null if coupon is expired or invalid
	 * @return detail of the coupon if valid
	 */
	public function findCoupon($identifier)
	{
		return $this->couponRepo->find($identifier);
	}

	/**
	 * find the transactions which has been done by user
	 * @return list of transactions containing amount and direction
	 */
	public function transactions($direction = 'all')
	{
		return $this->transactionRepo->findByWallet($this->wallet['id'], $direction);
	}

	public function redeemCoupon($couponIdentifier)
	{
		$coupon = $this->couponRepo->redeemCoupon($couponIdentifier);

		$this->deposit(
			$coupon['amount'], $coupon['amount_redemptions'], ['type' => 'COUPON', 'id' => $coupon['id']]
		);
	}
}