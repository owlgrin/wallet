<?php

class TransactionTest extends TestCase {

	public function tearDown()
	{
		Mockery::close();
	}

    public function testToAddTransactionWithDirectionAsCredit()
    {
        $balanceId = '1';
        $amount = 4;
        $direction = 'credit';

        $db = Mockery::mock('Illuminate\Database\DatabaseManager');

        $db->shouldReceive('table')->with(Config::get('wallet::tables.transactions'))->andReturn($query = Mockery::mock('stdClass'));
        $query->shouldReceive('insert')->with([
                'balance_id' => $balanceId,
                'amount'     => $amount,
                'direction'  => $direction
        ])->andReturn(null);

        $transactionRepo = new Owlgrin\Wallet\Transaction\DbTransactionRepo($db);

        $result = $transactionRepo->add($balanceId, $amount, $direction);

        $this->assertNull($result);
    }

    public function testToAddTransactionWithDirectionAsDebit()
    {
        $balanceId = '1';
        $amount = 4;
        $direction = 'debit';

        $db = Mockery::mock('Illuminate\Database\DatabaseManager');

        $db->shouldReceive('table')->with(Config::get('wallet::tables.transactions'))->andReturn($query = Mockery::mock('stdClass'));

        $query->shouldReceive('insert')->with([
                'balance_id' => $balanceId,
                'amount'     => $amount,
                'direction'  => $direction
        ])->andReturn(null);

        $transactionRepo = new Owlgrin\Wallet\Transaction\DbTransactionRepo($db);

        $result = $transactionRepo->add($balanceId, $amount, $direction);

        $this->assertNull($result);
    }
}