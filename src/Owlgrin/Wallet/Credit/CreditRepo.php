<?php namespace Owlgrin\Wallet\Credit;

interface CreditRepo {

	public function add($userId, $credit, $redemption);

	public function redeem($userId, $requestAmount);

	public function left($userId);

	public function hasCredit($userId);

}
