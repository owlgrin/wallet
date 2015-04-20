<?php

class AddCouponCommandTest extends TestCase {

	public function tearDown()
	{
		Mockery::close();
	}

    public function testToFireCommandInteractively()
    {
        $couponIdentifier  = 'firstCoupon';

        $coupon = Mockery::mock('Owlgrin\Wallet\Coupon\CouponRepo');

        $balanceRepo = $this->getMock('Owlgrin\Wallet\Commands\AddCouponCommand', array('argument', 'option', 'addCouponInteractively', 'representCouponsInTable', 'addCouponsToDatabase'), array($coupon));

        $balanceRepo->expects($this->once())->
            method('argument')->
            with($this->equalTo('coupon'))->
            will($this->returnValue($couponIdentifier));

        $balanceRepo->expects($this->once())->
            method('option')->
            with($this->equalTo('i'))->
            will($this->returnValue('interactive'));

        $balanceRepo->expects($this->once())->
            method('addCouponInteractively')->
            will($this->returnValue(['coupons' => []]));

        $balanceRepo->expects($this->once())->
            method('representCouponsInTable')->
            with($this->equalTo([]))->
            will($this->returnValue(null));

        $balanceRepo->expects($this->once())->
            method('addCouponsToDatabase')->
            with($this->equalTo([]))->
            will($this->returnValue(null));

        $balanceRepo->fire();
    }

    public function testToFireCommandWithoutInteractively()
    {
        $couponIdentifier  = 'firstCoupon';
        $couponData = '{
            "coupons" : [
                {
                    "name" : "First Coupon",
                    "identifier" : "firstCoupon",
                    "amount" : 500,
                    "amount_redemptions" : 5,
                    "redemptions" : 5
                }
            ]
        }';

        $couponArray = [
            'coupons' => [
                [
                  'name' => "First Coupon",
                  'identifier' => "firstCoupon",
                  'amount' => 500,
                  'amount_redemptions' => 5,
                  'redemptions' => 5
                ]
            ]
        ];


        $coupon = Mockery::mock('Owlgrin\Wallet\Coupon\CouponRepo');

        $balanceRepo = $this->getMock('Owlgrin\Wallet\Commands\AddCouponCommand', array('argument', 'option', 'addCouponInteractively', 'representCouponsInTable', 'addCouponsToDatabase'), array($coupon));

        $balanceRepo->expects($this->once())->
            method('argument')->
            with($this->equalTo('coupon'))->
            will($this->returnValue($couponData));

        $balanceRepo->expects($this->once())->
            method('option')->
            with($this->equalTo('i'))->
            will($this->returnValue(null));

        $balanceRepo->expects($this->once())->
            method('representCouponsInTable')->
            with($this->equalTo($couponArray['coupons']))->
            will($this->returnValue(null));

        $balanceRepo->expects($this->once())->
            method('addCouponsToDatabase')->
            with($this->equalTo($couponArray['coupons']))->
            will($this->returnValue(null));

        $balanceRepo->fire();
    }

}