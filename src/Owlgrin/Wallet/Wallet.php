<?php namespace Owlgrin\Wallet;

/**
 * The Wallet core
 */
use Owlgrin\Wallet\Redemption\RedemptionRepo;
use Owlgrin\Wallet\Credit\CreditRepo;
use Owlgrin\Wallet\Exceptions;

class Wallet {

	protected $redemptionRepo;
	protected $creditRepo;

	public function __construct(RedemptionRepo $redemptionRepo, CreditRepo $creditRepo)
	{
		$this->redemptionRepo = $redemptionRepo;
		$this->creditRepo = $creditRepo;
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

	public function credit($coupon)
	{
		$this->creditRepo->apply($this->user, $coupon);
	}

}