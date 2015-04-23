<?php namespace Owlgrin\Wallet\Transaction;

use Illuminate\Database\DatabaseManager as Database;

use Owlgrin\Wallet\Exceptions;
use Owlgrin\Wallet\Wallet\WalletRepo;

use PDOException, Config;;

class SampleTransactionRepo {

	const DIRECTION_DEBIT = 'DEBIT';
	const DIRECTION_CREDIT = 'CREDIT';

	const TYPE_AMOUNT = 'AMOUNT';
	const TYPE_REDEMPTION = 'REDEMPTION';

	public function store($walletId, $transactions, $trigger)
	{
		if(count($transactions) === 0) throw new Exceptions\InvalidTransactionException;

		try
		{
			$this->db->table(Config::get('wallet::tables.transactions'))->insert([
				'wallet_id'    => $walletId,
				'amount'       => $amount,
				'direction'    => $direction,
				'type'         => $type,
				'trigger_type' => $triggerType,
				'trigger_id'   => $triggerId,
				'created_at'   => $this->db->raw('now()'),
				'updated_at'   => $this->db->raw('now()')
			]);

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
			$amountRedeemed = $this->calculateRedemption($amount, $wallet['balance']);

			$transactions = [];
			if($amountRedeemed > 0)
			{
				$transactions[] = [$amountRedeemed, self::DIRECTION_DEBIT, self::TYPE_AMOUNT];
				$transactions[] = [1, self::DIRECTION_DEBIT, self::TYPE_REDEMPTION];
			}

			$this->store($walletId, $transactions, $trigger);

			// updating wallet balance
			$this->updateWallet($walletId, $transactions);

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

			// prepare transactions
			$transactions = [];
			if($amount > 0) $transactions[] = [$amount, self::DIRECTION_CREDIT, self::TYPE_AMOUNT];
			if($redemptions > 0) $transactions[] = [$redemptions, self::DIRECTION_CREDIT, self::TYPE_REDEMPTION];

			// storing
			$this->store($walletId, $transactions, $trigger);

			// updating wallet balance
			$this->updateWallet($walletId, $transactions);

			$this->db->commit();
		}
		catch(Exceptions\InternalException $e)
		{
			$this->db->rollback();

			throw new Exceptions\InternalException;
		}
	}

	/**
	 * Updates the information in wallet
	 *
	 * @param  int $walletId
	 * @param  array $transactions
	 */
	private function updateWallet($walletId, $transactions)
	{
		$balance = $this->calculateTransactions($transactions);
		$this->walletRepo->deposit(
			$walletId, $balance[self::TYPE_AMOUNT], $balance[self::TYPE_REDEMPTION]
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
	private function calculateTransactions($transactions)
	{
		$balance = [
			self::TYPE_AMOUNT => 0,
			self::TYPE_REDEMPTION => 0
		];

		foreach($transactions as $transaction)
		{
			$balance[$transaction['type']] += $this->calculateTransaction($transaction);
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
	private function calculateTransaction($transaction)
	{
		return $transaction['direction'] === self::DIRECTION_CREDIT)
				? $transaction['amount'] * 1 		// as it is
				: $transaction['amount'] * -1; 		// make it negative
	}
}