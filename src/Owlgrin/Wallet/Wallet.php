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
	 * [getUser]
	 * @return [string] [returns user identifier]
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * adds credits and redemption count of the user
	 * @param  [type] $credit          [description]
	 * @param  [type] $redemptionCount [description]
	 * @return [type]                  [description]
	 */
	public function credit($credit, $redemptionCount)
	{
		$this->creditRepo->add($this->user, $credit, $redemptionCount);
	}

	public function redeem($amount)
	{
		return $this->creditRepo->redeem($this->user, $amount);
	}

	public function left()
	{
		return $this->creditRepo->left($this->user);
	}

}