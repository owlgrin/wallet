<?php

class BalanceTest extends TestCase {

	public function tearDown()
	{
		Mockery::close();
	}

    public function testToAddBalanceWithAmount()
    {
    	$userId = '1';

    	$coupon = [
    		'amount' => 4355,
    		'amount_redemptions' => 5
    	];

    	$db = Mockery::mock('Illuminate\Database\DatabaseManager');

		$db->shouldReceive('raw')->with('now()')->andReturn('2015-04-17 13:00:00');

		$db->shouldReceive('table')->andReturn($query = Mockery::mock('stdClass'));
		$query->shouldReceive('insertGetId')->with([
			'user_id'     => $userId,
			'amount'      => $coupon['amount'],
			'redemptions' => $coupon['amount_redemptions'],
			'expired_at'  => null,
			'created_at'  => '2015-04-17 13:00:00',
			'updated_at'  => '2015-04-17 13:00:00'
		])->andReturn('1');

    	$balanceRepo = new Owlgrin\Wallet\Balance\DbBalanceRepo($db);

    	$result = $balanceRepo->add($userId, $coupon['amount'], $coupon['amount_redemptions']);

        $this->assertEquals('1', $result);
    }

    public function testToAddBlankBalance()
    {
    	$userId = 1;

    	$db = Mockery::mock('Illuminate\Database\DatabaseManager');
    	$balanceMock = $this->getMock('Owlgrin\Wallet\Balance\DbBalanceRepo', array('add'), array($db));
    	$balanceMock->expects($this->once())->method('add')->with($this->equalTo($userId, 0, 0))->will($this->returnValue('1'));

    	$this->assertEquals('1', $balanceMock->addBlank($userId));
    }

    public function testToFindBalanceByUser()
    {
    	$userId = '1';

    	$db = Mockery::mock('Illuminate\Database\DatabaseManager');

		$db->shouldReceive('table')->andReturn($query = Mockery::mock('stdClass'));
		$query->shouldReceive('where')->with('user_id', $userId)->andReturn($query);
		$query->shouldReceive('first')->andReturn('user');

    	$balanceRepo = new Owlgrin\Wallet\Balance\DbBalanceRepo($db);

    	$result = $balanceRepo->findByUser($userId);

        $this->assertEquals('user', $result);
    }

    public function testToFindTheLeftBalanceOfTheUser()
    {
		$userId = '1';

    	$db = Mockery::mock('Illuminate\Database\DatabaseManager');

		$db->shouldReceive('table')->andReturn($query = Mockery::mock('stdClass'));
		$query->shouldReceive('where')->with('user_id', $userId)->andReturn($query);
		$query->shouldReceive('where')->with('expired_at', null)->andReturn($query);
		$query->shouldReceive('pluck')->with('amount')->andReturn('amount');

    	$balanceRepo = new Owlgrin\Wallet\Balance\DbBalanceRepo($db);

    	$result = $balanceRepo->left($userId);

        $this->assertEquals('amount', $result);
    }

    public function testToUpdateBalancesOnRedemption()
    {
    	$balanceId = '1';
    	$leftAmount = '400';
    	$leftRedemption = '4';

    	$db = Mockery::mock('Illuminate\Database\DatabaseManager');

		$db->shouldReceive('table')->andReturn($query = Mockery::mock('stdClass'));
		$query->shouldReceive('where')->with('id', $balanceId)->andReturn($query);
		$query->shouldReceive('update')->with([
			'amount' => $leftAmount,
			'redemptions' => $leftRedemption
		])->andReturn(null);

    	$balanceRepo = new Owlgrin\Wallet\Balance\DbBalanceRepo($db);

    	$result = $balanceRepo->updateOnRedemption($balanceId, $leftAmount, $leftRedemption);

        $this->assertEquals(null, $result);
    }

    public function testToUpdateBalancesOnCredit()
    {
    	$balanceId = '1';
    	$amount = '400';
    	$redemption = '4';

    	$db = Mockery::mock('Illuminate\Database\DatabaseManager');

		$db->shouldReceive('table')->andReturn($query = Mockery::mock('stdClass'));
		$query->shouldReceive('where')->with('id', $balanceId)->andReturn($query);
		$query->shouldReceive('update')->with([
			'amount' => $amount,
			'redemptions' => $redemption,
			'expired_at' => null
		])->andReturn(null);

    	$balanceRepo = new Owlgrin\Wallet\Balance\DbBalanceRepo($db);

    	$result = $balanceRepo->updateOnCredit($balanceId, $amount, $redemption);

        $this->assertEquals(null, $result);
    }

    public function testToCheckIfUserHasCredit()
    {
		$userId = '1';

    	$db = Mockery::mock('Illuminate\Database\DatabaseManager');

		$db->shouldReceive('table')->andReturn($query = Mockery::mock('stdClass'));
		$query->shouldReceive('where')->with('user_id', $userId)->andReturn($query);
		$query->shouldReceive('where')->with('expired_at', null)->andReturn($query);
		$query->shouldReceive('where')->with('redemptions', '>', 0)->andReturn($query);
		$query->shouldReceive('where')->with('amount', '>', 0)->andReturn($query);
		$query->shouldReceive('first')->andReturn('credit');

    	$balanceRepo = new Owlgrin\Wallet\Balance\DbBalanceRepo($db);

    	$result = $balanceRepo->hasCredit($userId);

        $this->assertEquals('credit', $result);
    }

    //
	//here we will perfom tests for function credits with different scenerios
	//

    public function testToAddCreditWhenUserHasNoAmountLeft()
    {
    	$userId = 1;

    	$balance = [
    		'id'		  => 1,
			'amount'      => 0,
			'redemptions' => 4,
			'expired_at'  => null
    	];

    	$coupon = [
			'amount'             => 100,
			'amount_redemptions' => 5
    	];

    	$db = Mockery::mock('Illuminate\Database\DatabaseManager');
    	$balanceMock = $this->getMock('Owlgrin\Wallet\Balance\DbBalanceRepo', array('findByUser', 'updateOnCredit'), array($db));

    	$balanceMock->expects($this->once())->method('findByUser')->with($this->equalTo($userId))->will($this->returnValue($balance));

    	$balanceMock->expects($this->once())->method('updateOnCredit')->with($this->equalTo($balance['id'], $coupon['amount'], $coupon['amount_redemptions']))->will($this->returnValue(null));

    	$this->assertEquals($balance['id'], $balanceMock->credit($userId, $coupon));
    }

    public function testToAddCreditWhenUserHasNoRedemptionsLeft()
    {
    	$userId = 1;

    	$balance = [
    		'id'		  => 1,
			'amount'      => 59,
			'redemptions' => 0,
			'expired_at'  => null
    	];

    	$coupon = [
			'amount'             => 100,
			'amount_redemptions' => 5
    	];

    	$db = Mockery::mock('Illuminate\Database\DatabaseManager');
    	$balanceMock = $this->getMock('Owlgrin\Wallet\Balance\DbBalanceRepo', array('findByUser', 'updateOnCredit'), array($db));

    	$balanceMock->expects($this->once())->method('findByUser')->with($this->equalTo($userId))->will($this->returnValue($balance));

    	$balanceMock->expects($this->once())->method('updateOnCredit')->with($this->equalTo($balance['id'], $coupon['amount'], $coupon['amount_redemptions']))->will($this->returnValue(null));

    	$this->assertEquals($balance['id'], $balanceMock->credit($userId, $coupon));
    }

    public function testToAddCreditWhenUserHasLeftSomeAmountAndRedemptions()
    {
    	$userId = 1;

    	$balance = [
    		'id'		  => 1,
			'amount'      => 50,
			'redemptions' => 4,
			'expired_at'  => null
    	];

    	$coupon = [
			'amount'             => 100,
			'amount_redemptions' => 5
    	];

    	$db = Mockery::mock('Illuminate\Database\DatabaseManager');
    	$balanceMock = $this->getMock('Owlgrin\Wallet\Balance\DbBalanceRepo', array('findByUser', 'updateOnCredit'), array($db));

    	$balanceMock->expects($this->once())->method('findByUser')->with($this->equalTo($userId))->will($this->returnValue($balance));

    	$balanceMock->expects($this->once())->method('updateOnCredit')->with($this->equalTo($balance['id'], 150, 9))->will($this->returnValue(null));

    	$this->assertEquals($balance['id'], $balanceMock->credit($userId, $coupon));
    }

    public function testToAddCreditWhenUserIsExpired()
    {
    	$userId = 1;

    	$balance = [
    		'id'		  => 1,
			'amount'      => 50,
			'redemptions' => 4,
			'expired_at'  => '2015-10-03'
    	];

    	$coupon = [
			'amount'             => 100,
			'amount_redemptions' => 5
    	];

    	$db = Mockery::mock('Illuminate\Database\DatabaseManager');
    	$balanceMock = $this->getMock('Owlgrin\Wallet\Balance\DbBalanceRepo', array('findByUser', 'updateOnCredit'), array($db));

    	$balanceMock->expects($this->once())->method('findByUser')->with($this->equalTo($userId))->will($this->returnValue($balance));

    	$balanceMock->expects($this->once())->method('updateOnCredit')->with($this->equalTo($balance['id'], $coupon['amount'], $coupon['amount_redemptions']))->will($this->returnValue(null));

    	$this->assertEquals($balance['id'], $balanceMock->credit($userId, $coupon));
    }
}




	// public function testCreateInsertsNewRecordIntoTable()
	// {
	// 	$repo = $this->getRepo();
	// 	$repo->getConnection()->shouldReceive('table')->with('table')->andReturn($query = m::mock('StdClass'));
	// 	$query->shouldReceive('where')->with('email', 'email')->andReturn($query);
	// 	$query->shouldReceive('delete')->once();
	// 	$query->shouldReceive('insert')->once();
	// 	$user = m::mock('Illuminate\Auth\Reminders\RemindableInterface');
	// 	$user->shouldReceive('getReminderEmail')->andReturn('email');

	// 	$results = $repo->create($user);

	// 	$this->assertInternalType('string', $results);
	// 	$this->assertGreaterThan(1, strlen($results));
	// }


	// public function testExistReturnsFalseIfNoRowFoundForUser()
	// {
	// 	$repo = $this->getRepo();
	// 	$repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));
	// 	$query->shouldReceive('where')->once()->with('email', 'email')->andReturn($query);
	// 	$query->shouldReceive('where')->once()->with('token', 'token')->andReturn($query);
	// 	$query->shouldReceive('first')->andReturn(null);
	// 	$user = m::mock('Illuminate\Auth\Reminders\RemindableInterface');
	// 	$user->shouldReceive('getReminderEmail')->andReturn('email');

	// 	$this->assertFalse($repo->exists($user, 'token'));
	// }



