<?php

class CouponTest extends TestCase {

	public function tearDown()
	{
		Mockery::close();
	}

    public function testToAddCoupons()
    {
        $userId = '1';

        $coupon = [
            'name' => 'fresh Coupon',
            'identifier' => 'freshCoupon',
            'description' => 'Weel this is the description of the coupon',
            'amount' => 500,
            'amount_redemptions' => 5,
            'redemptions' => 3
        ];

        $db = Mockery::mock('Illuminate\Database\DatabaseManager');

        $db->shouldReceive('table')->with(Config::get('wallet::tables.coupons'))->andReturn($query = Mockery::mock('stdClass'));

        $query->shouldReceive('insert')->with([
            'name'               => $coupon['name'],
            'identifier'         => $coupon['identifier'],
            'description'        => $coupon['description'],
            'amount'             => $coupon['amount'],
            'amount_redemptions' => $coupon['amount_redemptions'],
            'redemptions'        => $coupon['redemptions']
        ])->andReturn(null);

        $couponRepo = new Owlgrin\Wallet\Coupon\DbCouponRepo($db);

        $this->assertNull($couponRepo->add($coupon));
    }

    public function testToAddMultipleCoupons()
    {
        $coupons = [
            [
                'name' => 'first Coupon',
                'identifier' => 'firstCoupon',
                'amount' => 500,
                'amount_redemptions' => 5,
                'redemptions' => 3
            ],
            [
                'name' => 'second Coupon',
                'identifier' => 'secondCoupon',
                'amount' => 500,
                'amount_redemptions' => 5,
                'redemptions' => 3
            ]
        ];

        $db = Mockery::mock('Illuminate\Database\DatabaseManager');
        $couponMock = $this->getMock('Owlgrin\Wallet\Coupon\DbCouponRepo', array('add'), array($db));

        $couponMock->expects($this->at(0))->method('add')->with($this->equalTo($coupons[0]))->will($this->returnValue(null));
        $couponMock->expects($this->at(1))->method('add')->with($this->equalTo($coupons[1]))->will($this->returnValue(null));

        $this->assertNull($couponMock->addMultiple($coupons));
    }

    public function testToStoreCouponForUser()
    {
        $userId = '1';
        $couponId = '1';

        $db = Mockery::mock('Illuminate\Database\DatabaseManager');

        $db->shouldReceive('raw')->once()->with('now()')->andReturn('2015-04-17 13:00:00');
        $db->shouldReceive('table')->with(Config::get('wallet::tables.user_coupons'))->andReturn($query = Mockery::mock('stdClass'));

        $query->shouldReceive('insert')->with([
            'user_id'    => $userId,
            'coupon_id'  => $couponId,
            'created_at' => '2015-04-17 13:00:00'
        ])->andReturn(null);

        $couponRepo = new Owlgrin\Wallet\Coupon\DbCouponRepo($db);

        $this->assertNull($couponRepo->storeForUser($userId, $couponId));
    }

    public function testToCheckIfCouponCanBeUsed()
    {
        $couponIdentifier = 'firstCoupon';

        $db = Mockery::mock('Illuminate\Database\DatabaseManager');

        $db->shouldReceive('table')->with(Config::get('wallet::tables.coupons'))->andReturn($query = Mockery::mock('stdClass'));
        $query->shouldReceive('where')->with('identifier', $couponIdentifier)->andReturn($query);
        $query->shouldReceive('where')->with('redemptions', '!=', 0)->andReturn($query);
        $query->shouldReceive('first')->andReturn('coupon');

        $couponRepo = new Owlgrin\Wallet\Coupon\DbCouponRepo($db);

        $this->assertEquals('coupon', $couponRepo->canBeUsed($couponIdentifier));
    }

    public function testToDecrementTheRedemptionsOfTheCoupon()
    {
        $couponId = '1';

        $db = Mockery::mock('Illuminate\Database\DatabaseManager');

        $db->shouldReceive('table')->with(Config::get('wallet::tables.coupons'))->andReturn($query = Mockery::mock('stdClass'));
        $query->shouldReceive('where')->with('id', $couponId)->andReturn($query);
        $query->shouldReceive('decrement')->with('redemptions')->andReturn(null);

        $couponRepo = new Owlgrin\Wallet\Coupon\DbCouponRepo($db);

        $this->assertNull($couponRepo->decrementRedemptions($couponId));
    }
}