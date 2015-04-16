<?php namespace Owlgrin\Wallet\Coupon;

interface CouponRepo {

	public function add($coupon);

	public function addMultiple($coupons);

	public function findByIdentifier($couponIdentifier);

	public function storeForUser($userId, $couponId);

	public function canBeUsed($couponIdentifier);

	public function decrementRedemptions($couponId);

}
