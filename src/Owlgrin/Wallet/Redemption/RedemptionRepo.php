<?php namespace Owlgrin\Wallet\Redemption;

interface RedemptionRepo {
	public function redeem($userId, $requestedAmount);

}
