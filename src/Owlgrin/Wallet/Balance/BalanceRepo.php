<?php namespace Owlgrin\Wallet\Balance;

interface BalanceRepo {

	public function add($userId, $coupon);

	public function credit($userId, $coupon);

	public function updateOnCredit($balanceId, $amount, $redemptions);

	public function hasCredit($userId);

	public function findByUser($userId);

	public function updateOnRedemption($balanceId, $leftAmount, $leftRedemption);

	public function left($userId);

}
