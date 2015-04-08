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

	public function user($user)
	{
		$this->user = $user;

		return $this;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function credit($credit, $redemptionCount, $user = null)
	{
		$userId = is_null($user)? $this->user : $user;

		if($this->creditRepo->hasCredit($userId))
			throw new Exceptions\CreditExistsException;

		$this->creditRepo->add($userId, $credit, $redemptionCount);
	}

	public function redeem($amount)
	{
		if(! $this->creditRepo->hasCredit($this->user))
				throw new Exceptions\CreditLimitException;

		return $this->creditRepo->redeem($this->user, $amount);
	}

	public function canRedeem()
	{
		if($this->creditRepo->hasCredit($this->user))
			return true;
		return false;
	}

	public function left()
	{
		return $this->creditRepo->left($this->user);
	}

}