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
class AddCouponForUserCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'wallet:add-coupon-for-user';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This command adds coupons to the user.';

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
		$user       = $this->argument('user');
		$coupon     = $this->argument('coupon');

		Wallet::user($user)->coupon($coupon);

		$this->info("Your user (". $user .") has been applied with coupon (" .$coupon. ")");
	}

	protected function getArguments()
	{
		return array(
			array('user', InputArgument::REQUIRED, 'The user id for which you want to add coupon'),
			array('coupon', InputArgument::REQUIRED, 'The identifier of the couponyou wnt to credit')
		);
	}

}