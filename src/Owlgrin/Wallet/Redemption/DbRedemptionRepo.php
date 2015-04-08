<?php namespace Owlgrin\Wallet\Redemption;

use Illuminate\Database\DatabaseManager as Database;

use PDOException, Exception, Config;

class DbRedemptionRepo implements RedemptionRepo {

	protected $db;

	public function __construct(Database $db)
	{
		$this->db = $db;
	}

	public function add($userId, $creditId, $creditAmount, $totalAmount)
	{
		try
		{
			$redemptionId = $this->db->table(Config::get('wallet::tables.redemptions'))->insertGetId([
				'user_id'         => $userId,
				'credit_id'       => $creditId,
				'credit_amount'   => $creditAmount,
				'invoiced_amount' => $totalAmount
			]);

			return $creditAmount;
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}
}