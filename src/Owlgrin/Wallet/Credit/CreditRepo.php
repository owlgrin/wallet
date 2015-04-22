<?php namespace Owlgrin\Wallet\Credit;

interface CreditRepo {

	public function blank($userId);

	public function apply($userId, $couponIdentifier);

	public function left($userId);

	public function findByUser($userId);
}
