<?php namespace Owlgrin\Wallet\Coupon;

use Illuminate\Database\DatabaseManager as Database;

use PDOException, Exception, Config;

class DbCouponRepo implements CouponRepo {

	const TYPE_REDEMPTION = 'REDEMPTION';
	const DIRECTION_CREDIT = 'CREDIT';

	protected $db;

	public function __construct(Database $db)
	{
		$this->db = $db;
	}

	public function create($coupon)
	{
		try
		{
			$this->db->table(Config::get('wallet::tables.coupons'))->insert([
				'name'               => $coupon['name'],
				'identifier'         => $coupon['identifier'],
				'description'        => $coupon['description'],
				'amount'             => $coupon['amount'],
				'amount_redemptions' => $coupon['amount_redemptions'],
				'redemptions'        => $coupon['redemptions'],
				'created_at'         => $this->db->raw('now()'),
				'deleted_at'         => null,
				'exhausted_at'       => null
			]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function find($identifier)
	{
		try
		{
			return $this->db->table(Config::get('wallet::tables.coupons'))
				->where('identifier', $identifier)
	   			->where('exhausted_at', null)
				->first();
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}

	public function exhaust($id)
	{
		try
		{
			$this->db->table(Config::get('wallet::tables.coupons'))
				->where('id',  $id)
				->where('exhausted_at', 'null')
				->where('redemptions', 0)
				->update([
					'exhausted_at' => $this->db->raw('now()')
				]);
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}

	}

	public function createMultiple($coupons)
	{
		try
		{
			foreach ($coupons as $coupon)
			{
				$this->create($coupon);
			}
		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}


	// decrementing redemptions of the coupon
	public function redeemCoupon($couponIdentifier)
	{
		//use the transaction repo to check whether the coupon with
		$coupon = $this->find($couponIdentifier);

		//checking if the coupon has credit
		if(! $coupon) throw new Exceptions\CouponLimitReachedException;

		try
		{

			$count = $this->db->table(Config::get('wallet::tables.transactions'))
				->where('type', self::TYPE_REDEMPTION)
				->where('trigger_type', 'COUPON')
				->where('direction', self::DIRECTION_CREDIT)
				->where('trigger_id', $coupon['id'])
				->count();

			if($count < $coupon['redemptions'] or $coupon['redemptions'] == -1)
			{
				return $coupon;
			}

			//exhaust if used
			$this->exhaust($coupon['id']);

		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}
}