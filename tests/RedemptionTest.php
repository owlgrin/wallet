<?php

class RedemptionTest extends TestCase {

	public function tearDown()
	{
		Mockery::close();
	}

    public function testToGetRedemptionAmountWhenRedemptionAmountIsGreaterThanRequestedAmount()
    {
        $requestedAmount = 30;
        $amountLeft = 50;

        $db = Mockery::mock('Illuminate\Database\DatabaseManager');
        $balance = Mockery::mock('Owlgrin\Wallet\Balance\BalanceRepo');
        $transaction = Mockery::mock('Owlgrin\Wallet\Transaction\TransactionRepo');

        $redemptionRepo = new Owlgrin\Wallet\Redemption\DbRedemptionRepo($db, $balance, $transaction);
        $reflector = new ReflectionClass( 'Owlgrin\Wallet\Redemption\DbRedemptionRepo' );

        $method = $reflector->getMethod( 'getRedemptionAmount' );
        $method->setAccessible(true);

        $result = $method->invokeArgs( $redemptionRepo, array( $requestedAmount, $amountLeft) );

        $this->assertEquals(30, $result);
    }

    public function testToGetRedemptionAmountWhenRequestedAmountIsGreaterThanRedemptionAmount()
    {
        $requestedAmount = 50;
        $amountLeft = 30;

        $db = Mockery::mock('Illuminate\Database\DatabaseManager');
        $balance = Mockery::mock('Owlgrin\Wallet\Balance\BalanceRepo');
        $transaction = Mockery::mock('Owlgrin\Wallet\Transaction\TransactionRepo');

        $redemptionRepo = new Owlgrin\Wallet\Redemption\DbRedemptionRepo($db, $balance, $transaction);
        $reflector = new ReflectionClass( 'Owlgrin\Wallet\Redemption\DbRedemptionRepo' );

        $method = $reflector->getMethod( 'getRedemptionAmount' );
        $method->setAccessible(true);

        $result = $method->invokeArgs( $redemptionRepo, array( $requestedAmount, $amountLeft) );

        $this->assertEquals(30, $result);
    }

    public function testRedeemThrowsExceptionWhenUserHasNoBalance()
    {
        $userId = '1';
        $requestedAmount = 40;

        list($db, $balance, $transaction) = $this->getMocks();

        $balance->shouldReceive('hasCredit')->with($userId)->andReturn(false);

        $redemptionRepo = new Owlgrin\Wallet\Redemption\DbRedemptionRepo($db, $balance, $transaction);
        $this->setExpectedException('Owlgrin\Wallet\Exceptions\NoCreditsException', 'You dont have any credits.');

        $redemptionRepo->redeem($userId, $requestedAmount);
    }

    public function testRedeemWhenUserHasBalanceWhichIsGreaterThanRequestAmount()
    {
        $userId = '1';
        $requestedAmount = 40;

        $userBalance = [
            'id'          => 1,
            'amount'      => 50,
            'redemptions' => 4,
            'expired_at'  => null
        ];

        list($dbMock, $balanceMock, $transactionMock) = $this->getMocks();

        $balanceMock->shouldReceive('hasCredit')->with($userId)->andReturn(true);

        $dbMock->shouldReceive('beginTransaction')->andReturn($dbMock);
        $dbMock->shouldReceive('commit')->andReturn(null);

        $balanceMock->shouldReceive('findByUser')->with($userId)->andReturn($userBalance);

        $redemptionMock = new Owlgrin\Wallet\Redemption\DbRedemptionRepo($dbMock, $balanceMock, $transactionMock);

        $leftAmount = $userBalance['amount'] - $requestedAmount;
        $leftRedemptions = $userBalance['redemptions'] - 1;

        $balanceMock->shouldReceive('updateOnRedemption')->with($userBalance['id'], $leftAmount, $leftRedemptions)->andReturn(null);
        $transactionMock->shouldReceive('add')->with($userBalance['id'], $requestedAmount, 'debit')->andReturn(null);

        $result = $redemptionMock->redeem($userId, $requestedAmount);
        $this->assertEquals($requestedAmount, $result);
    }


    public function testRedeemWhenUserHasBalanceWhichIsLessThanRequestAmount()
    {
        $userId = '1';
        $requestedAmount = 40;

        $userBalance = [
            'id'          => 1,
            'amount'      => 30,
            'redemptions' => 4,
            'expired_at'  => null
        ];

        list($dbMock, $balanceMock, $transactionMock) = $this->getMocks();

        $balanceMock->shouldReceive('hasCredit')->with($userId)->andReturn(true);

        $dbMock->shouldReceive('beginTransaction')->andReturn($dbMock);
        $dbMock->shouldReceive('commit')->andReturn(null);

        $balanceMock->shouldReceive('findByUser')->with($userId)->andReturn($userBalance);

        $redemptionMock = new Owlgrin\Wallet\Redemption\DbRedemptionRepo($dbMock, $balanceMock, $transactionMock);

        $leftAmount = 0;
        $leftRedemptions = $userBalance['redemptions'] - 1;

        $balanceMock->shouldReceive('updateOnRedemption')->with($userBalance['id'], $leftAmount, $leftRedemptions)->andReturn(null);
        $transactionMock->shouldReceive('add')->with($userBalance['id'], $userBalance['amount'], 'debit')->andReturn(null);

        $result = $redemptionMock->redeem($userId, $requestedAmount);
        $this->assertEquals($userBalance['amount'], $result);
    }


    protected function getMocks()
    {
        return array(
            Mockery::mock('Illuminate\Database\DatabaseManager'),
            Mockery::mock('Owlgrin\Wallet\Balance\BalanceRepo'),
            Mockery::mock('Owlgrin\Wallet\Transaction\TransactionRepo'),
        );
    }
}