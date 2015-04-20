<?php


class CreditTest extends TestCase {

	public function tearDown()
	{
		Mockery::close();
	}

	public function testToFindCreditOfTheUser()
	{
		$userId = '1';
        $userBalance = [
            'id'          => 1,
            'amount'      => 50,
            'redemptions' => 4,
            'expired_at'  => null
        ];

        list($db, $coupon, $balance, $transaction) = $this->getMocks();

        $balance->shouldReceive('findByUser')->with($userId)->andReturn($userBalance);

        $creditRepo = new Owlgrin\Wallet\Credit\DbCreditRepo($db, $coupon, $balance, $transaction);

        $this->assertEquals($userBalance, $creditRepo->findByUser($userId));
	}

	public function testToFindLeftCreditAmountOfTheUser()
	{
		$userId = '1';
		$amount = 900;

        list($db, $coupon, $balance, $transaction) = $this->getMocks();

        $balance->shouldReceive('left')->with($userId)->andReturn($amount);

        $creditRepo = new Owlgrin\Wallet\Credit\DbCreditRepo($db, $coupon, $balance, $transaction);

        $this->assertEquals($amount, $creditRepo->left($userId));
	}

	public function testToAddBlankCreditForTheUser()
	{
		$userId = '1';

        list($db, $coupon, $balance, $transaction) = $this->getMocks();

        $balance->shouldReceive('addBlank')->with($userId)->andReturn(null);

        $creditRepo = new Owlgrin\Wallet\Credit\DbCreditRepo($db, $coupon, $balance, $transaction);

        $this->assertNull($creditRepo->blank($userId));
	}

	//tests for applying credits to the user
	public function testApplyCreditThrowsExceptionIfCouponCannotBeUsed()
	{
		$userId = '1';
		$couponIdentifier = 'firstCoupon';

        list($db, $coupon, $balance, $transaction) = $this->getMocks();
        $coupon->shouldReceive('canBeUsed')->with($couponIdentifier)->andReturn(false);

        $creditRepo = new Owlgrin\Wallet\Credit\DbCreditRepo($db, $coupon, $balance, $transaction);

        $this->setExpectedException('Owlgrin\Wallet\Exceptions\CouponLimitReachedException', 'Coupon limit has been reached.');

	    $creditRepo->apply($userId, $couponIdentifier);
	}

	public function testApplyCreditWhenCoUponCanBeUsed()
	{
		$userId = 1;
		$couponIdentifier = 'firstCoupon';
		$balanceId = 1;

        $coupon = [
			'id'                 => 1,
			'name'               => 'fresh Coupon',
			'identifier'         => 'freshCoupon',
			'amount'             => 500,
			'amount_redemptions' => 5,
			'redemptions'        => 3
        ];


        list($dbMock, $couponMock, $balanceMock, $transactionMock) = $this->getMocks();
        $couponMock->shouldReceive('canBeUsed')->with($couponIdentifier)->andReturn($coupon);

        $dbMock->shouldReceive('beginTransaction')->andReturn($dbMock);
        $dbMock->shouldReceive('commit')->andReturn(null);

        $couponMock->shouldReceive('storeForUser')->with($userId, $coupon['id'])->andReturn(null);

        $balanceMock->shouldReceive('credit')->with($userId, $coupon)->andReturn($balanceId);

        $transactionMock->shouldReceive('add')->with($balanceId, $coupon['amount'], 'credit')->andReturn(null);

        $couponMock->shouldReceive('decrementRedemptions')->with($coupon['id'])->andReturn(null);

        $creditRepo = new Owlgrin\Wallet\Credit\DbCreditRepo($dbMock, $couponMock, $balanceMock, $transactionMock);

	    $creditRepo->apply($userId, $couponIdentifier);
	}

    protected function getMocks()
    {
        return array(
            Mockery::mock('Illuminate\Database\DatabaseManager'),
            Mockery::mock('Owlgrin\Wallet\Coupon\CouponRepo'),
            Mockery::mock('Owlgrin\Wallet\Balance\BalanceRepo'),
            Mockery::mock('Owlgrin\Wallet\Transaction\TransactionRepo'),
        );
    }
}

