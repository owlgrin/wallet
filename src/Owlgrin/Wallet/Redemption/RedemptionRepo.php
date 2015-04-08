<?php namespace Owlgrin\Wallet\Redemption;

interface RedemptionRepo
{
	public function add($userId, $creditId, $creditAmount, $totalAmount);
}
