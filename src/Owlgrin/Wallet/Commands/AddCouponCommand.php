<?php namespace Owlgrin\Wallet\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Owlgrin\Wallet\Coupon\CouponRepo;
use Owlgrin\Wallet\Exceptions;
use Wallet;

/**
 * Command to generate the required migration
 */
class AddCouponCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'wallet:add-coupons';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This command adds coupons for the wallet.';

	protected $couponRepo;

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */

	public function __construct(CouponRepo $couponRepo)
	{
 		parent::__construct();
 		$this->couponRepo = $couponRepo;
	}

	public function fire()
	{
		$coupon = $this->argument('coupon');

		$interactive = $this->option('i');

		if($interactive)
		{
			$coupons = $this->addCouponInteractively();
		}
		else
		{
			$coupons = json_decode($coupon, true);
		}

		$this->representCouponsInTable($coupons['coupons']);

		$this->addCouponsToDatabase($coupons['coupons']);
	}

	protected function addCouponsToDatabase($coupons)
	{
		if ($this->confirm('Do you wish to add coupon to database ? [yes|no]'))
		{
			$this->couponRepo->addMultiple($coupons);

			$this->info('Coupons Added Successfully');
		}
	}

	protected function addCouponInteractively()
	{
		$i = 0;
		$coupon = [];

		do
		{
			$coupon['coupons'][$i]['name'] = $this->ask('What is the name of the coupon ?');

			$coupon['coupons'][$i]['identifier'] = $this->ask('What is the identifier of the coupon ?');

			$coupon['coupons'][$i]['amount'] = $this->ask('What is the amount of the coupon?');

			$coupon['coupons'][$i]['amount_redemptions'] = $this->ask('How many times this amount can be redeemed ?');

			$coupon['coupons'][$i]['redemptions'] = $this->ask('How many times this coupon can be redeemed ?');

			$i++;

		} while($this->confirm('Do you wish to add more coupons ? [yes|no]'));

		return $coupon;
	}

	protected function representCouponsInTable($coupons)
	{
		foreach($coupons as $coupon)
		{
			$this->info('Representing coupons : "'. $coupon['name'] );

			$this->table(['name', 'identifier', 'amount', 'amount_redemptions', 'redemptions'], [ ['name' => $coupon['name'] ,'identifier' => $coupon['identifier'], 'amount' => $coupon['amount'], 'amount_redemptions' => $coupon['amount_redemptions'], 'redemptions' => $coupon['redemptions']]]);
		}
	}


	protected function getArguments()
	{
		return array(
			array('coupon', InputArgument::OPTIONAL, 'Stores a coupon and its corresponding details'),
		);
	}

	protected function getOptions()
	{
		return array(
			array('i', null, InputOption::VALUE_NONE, 'If you wants to add coupon in interactive way', null)
		);
	}

}