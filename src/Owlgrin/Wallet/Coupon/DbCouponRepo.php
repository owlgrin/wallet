<?php namespace Owlgrin\Wallet\Coupon;

use Illuminate\Database\DatabaseManager as Database;

use PDOException, Exception, Config;

class DbCouponRepo implements CouponRepo {

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
				->where('id',  $coupon['id'])
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
	public function decrementRedemptions($couponIdentifier)
	{
		$coupon = $this->find($couponIdentifier);

		//checking if the coupon has credit
		if(! $coupon) throw new Exceptions\CouponLimitReachedException;

		try
		{
			$this->db->table(Config::get('wallet::tables.coupons'))
				->where('id',  $coupon['id'])
				->decrement('redemptions');

			//exhaust if used
			return $coupon;

		}
		catch(PDOException $e)
		{
			throw new Exceptions\InternalException;
		}
	}
}