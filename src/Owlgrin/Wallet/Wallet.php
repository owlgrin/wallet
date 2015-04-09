<?php namespace Owlgrin\Wallet;

/**
 * The Wallet core
 */
use Owlgrin\Wallet\Credit\CreditRepo;
use Owlgrin\Wallet\Exceptions;

class Wallet {

	protected $creditRepo;

	public function __construct(CreditRepo $creditRepo)
	{
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
	 * adds credits and redemption count of the user
	 * @param  [integer] $credit          [description]
	 * @param  [integer] $redemptionCount [description]
	 */
	public function credit($credit, $redemptionCount)
	{
		$this->creditRepo->add($this->user, $credit, $redemptionCount);
	}

	/**
	 * redeems the amount
	 * @param  [integer] $amount [inputs the amount]
	 * @return the credited amount
	 */
	public function redeem($amount)
	{
		return $this->creditRepo->redeem($this->user, $amount);
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

}