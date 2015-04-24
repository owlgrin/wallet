<?php namespace Owlgrin\Wallet\Transaction;

use Illuminate\Database\DatabaseManager as Database;

use Owlgrin\Wallet\Exceptions;
use Owlgrin\Wallet\Wallet\WalletRepo;

use PDOException, Config;;

class DbTransactionRepo implements TransactionRepo {

	const ACTION_DEPOSIT = 'DEPOSIT';
	const ACTION_WITHDRAW = 'WITHDRAW';

	const DIRECTION_DEBIT = 'DEBIT';
	const DIRECTION_CREDIT = 'CREDIT';
	const DIRECTION_ADJUST = 'ADJUST';

	const TYPE_AMOUNT = 'AMOUNT';
	const TYPE_REDEMPTION = 'REDEMPTION';

	protected $db;
	protected $walletRepo;
	protected $amountTransactionMaker;
	protected $redemptionTransactionMaker;

	public function __construct(Database $db, WalletRepo $walletRepo, SimpleAmountTransactionMaker $amountTransactionMaker, AdjusterRedemptionTransactionMaker $redemptionTransactionMaker)
	{
		$this->db = $db;
		$this->walletRepo = $walletRepo;
		$this->amountTransactionMaker = $amountTransactionMaker;
		$this->redemptionTransactionMaker = $redemptionTransactionMaker;
	}

	public function store($walletId, $transactions, $trigger)
	{
		if(count($transactions) === 0) throw new Exceptions\InvalidTransactionException;

		try
		{
			foreach($transactions as $transaction)
			{
				$query[] = [
					'wallet_id'    => $walletId,
					'amount'       => $transaction['amount'],
					'direction'    => $transaction['direction'],
					'type'         => $transaction['type'],
					'trigger_type' => $trigger['type'],
					'trigger_id'   => $trigger['id'],
					'created_at'   => $this->db->raw('now()'),
					'updated_at'   => $this->db->raw('now()')
				];
			}

			$this->db->table(Config::get('wallet::tables.transactions'))->insert($query);

		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function withdraw($walletId, $amount = 0, $trigger)
	{
		try
		{
			//starting the transaction
			$this->db->beginTransaction();

			$wallet = $this->walletRepo->find($walletId);
			$amountRedeemed = $this->calculateRedemption($amount, $wallet['amount']);

			$transactions = [];
			if($amountRedeemed > 0)
			{
				$transactions[] = $this->makeTransaction(self::ACTION_WITHDRAW, self::TYPE_AMOUNT, $amountRedeemed);
				$transactions[] = $this->makeTransaction(self::ACTION_WITHDRAW, self::TYPE_REDEMPTION, 1, $wallet['redemption_limit']);
			}

			$this->store($walletId, $transactions, $trigger);

			// updating wallet balance
			$this->updateWallet($wallet, $transactions);

			$this->db->commit();
		}
		catch(Exceptions\InternalException $e)
		{
			$this->db->rollback();

			throw new Exceptions\InternalException;
		}
	}

	private function calculateRedemption($requestedAmount, $walletAmount)
	{
		if($walletAmount === 0)
		{
			throw new Exceptions\EmptyWalletException;
		}

		return $walletAmount >= $requestedAmount ? $requestedAmount : $walletAmount;
	}

	/**
	 * Makes a deposit in the wallet
	 *
	 * @param  int  $walletId
	 * @param  int $amount
	 * @param  int $redemptions
	 * @param  array  $trigger
	 */
	public function deposit($walletId, $amount = 0, $redemptions = 0, $trigger)
	{
		try
		{
			//starting the transaction
			$this->db->beginTransaction();

			$wallet = $this->walletRepo->find($walletId);

			// prepare transactions
			$transactions = [];
			if($amount > 0) $transactions[] = $this->makeTransaction(self::ACTION_DEPOSIT, self::TYPE_AMOUNT, $amount);
			if($redemptions > 0) $transactions[] = $this->makeTransaction(self::ACTION_DEPOSIT, self::TYPE_REDEMPTION, $redemptions, $wallet['redemption_limit']);

			// storing
			$this->store($walletId, $transactions, $trigger);

			// updating wallet balance
			$this->updateWallet($wallet, $transactions);

			$this->db->commit();
		}
		catch(Exceptions\InternalException $e)
		{
			$this->db->rollback();

			throw new Exceptions\InternalException;
		}
	}

	protected function makeTransaction($action, $type, $amount, $current = null)
	{
		return $this->getTransactionMaker($type)->make($action, $amount, $current);
	}

	private function getTransactionMaker($type)
	{
		return $this->{camel_case(strtolower($type)) . 'TransactionMaker'};
	}

	/**
	 * Updates the information in wallet
	 *
	 * @param  int $walletId
	 * @param  array $transactions
	 */
	private function updateWallet($wallet, $transactions)
	{
		$balance = $this->calculateTransactions($wallet, $transactions);
		$this->walletRepo->update(
			$wallet['id'], $balance[self::TYPE_AMOUNT], $balance[self::TYPE_REDEMPTION]
		);
	}

	/**
	 * Calculates the total computed amount and
	 * redemptions for all transactions
	 *
	 * @param  array $transactions
	 *
	 * @return array
	 */
	private function calculateTransactions($wallet, $transactions)
	{
		$balance = [
			self::TYPE_AMOUNT => $wallet['amount'],
			self::TYPE_REDEMPTION => $wallet['redemption_limit']
		];

		foreach($transactions as $transaction)
		{
			$balance[$transaction['type']] = $this->calculateTransaction($balance[$transaction['type']], $transaction);
		}

		return $balance;
	}

	/**
	 * Calculates the computation of a single transaction
	 *
	 * @param  array $transaction
	 *
	 * @return int
	 */
	private function calculateTransaction($currentBalance, $transaction)
	{
		switch($transaction['direction'])
		{
			case self::DIRECTION_CREDIT:
				return $currentBalance + $transaction['amount'];

			case self::DIRECTION_DEBIT:
				return $currentBalance - $transaction['amount'];

			case self::DIRECTION_ADJUST:
				return $transaction['amount'];
		}
	}

	public function findByWallet($walletId, $direction)
	{
		try
		{
			$query = $this->db->table(Config::get('wallet::tables.transactions'))
				->where('wallet_id', $walletId);

			if($direction != 'all' or $direction != 'ALL')
			{
				$query = $query->where('direction', $direction);
			}

			return $query->get();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}
}