<?php namespace Owlgrin\Wallet\Coupon;

use Illuminate\Database\DatabaseManager as Database;

use PDOException, Exception, Config;

class DbCouponRepo implements CouponRepo {

	protected $db;

	public function __construct(Database $db)
	{
		$this->db = $db;
	}

	//add the coupons
	public function add($coupon)
	{
		try
		{
			$this->db->table(Config::get('wallet::tables.coupons'))->insert([
				'name'               => $coupon['name'],
				'identifier'         => $coupon['identifier'],
				'amount'             => $coupon['amount'],
				'amount_redemptions' => $coupon['amount_redemptions'],
				'redemptions'        => $coupon['redemptions']
			]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	//add multiple coupons
	public function addMultiple($coupons)
	{
		try
		{
			foreach ($coupons as $coupon)
			{
				$this->add($coupon);
			}
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}

	}

	//find the coupon by identifier
	public function findByIdentifier($couponIdentifier)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.coupons'))
				->where('identifier', $couponIdentifier)
				->where('redemptions', '>', 0)
				->first();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	// add coupon for user
	public function storeForUser($userId, $couponId)
	{
		try
		{
			$this->db->table(Config::get('wallet::tables.user_coupons'))->insert([
				'user_id'    => $userId,
				'coupon_id'     => $couponId,
				'created_at' => $this->db->raw('now()')
			]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	//check coupon could be used
	public function canBeUsed($couponIdentifier)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.coupons'))
				->where('identifier', $couponIdentifier)
				->where('redemptions', '>', 0)
				->first();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	// decrementing redemptions of the coupon
	public function decrementRedemptions($couponId)
	{
		try
		{
			$this->db->table(Config::get('wallet::tables.coupons'))
				->where('id',  $couponId)
				->decrement('redemptions');
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}
}