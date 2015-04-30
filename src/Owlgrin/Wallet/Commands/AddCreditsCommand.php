<?php namespace Owlgrin\Wallet\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Owlgrin\Wallet\Exceptions;
use Wallet;

/**
 * Command to generate the required migration
 */
class AddCreditsCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'wallet:add-credits';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This command adds credits for user.';


	/**
	 * Execute the console command.
	 *
	 * @return void
	 */

	public function __construct()
	{
 		parent::__construct();
	}

	public function fire()
	{
		try
		{
			$user       = $this->argument('user');
			$credit     = $this->argument('credit');
			$redemption = $this->argument('redemption');

			Wallet::user($user)->deposit($credit, $redemption);

			$this->info("Your user (". $user .") has been credited with amount (" .$credit. ") with redemptions (" .$redemption. ")");
		}
		catch(Exceptions\CreditsLimitReachedExcetion $e)
		{
			$this->error($e->getMessage());
		}
		catch(Exceptions\InternalExcetion $e)
		{
			$this->error($e->getMessage());
		}
		catch(\Exception $e)
		{
			$this->error($e->getMessage());
		}

	}

	protected function getArguments()
	{
		return array(
			array('user', InputArgument::REQUIRED, 'The user id of whose credit you want to add'),
			array('credit', InputArgument::REQUIRED, 'The amount(in cents) you want to add for you user'),
			array('redemption', InputArgument::REQUIRED, 'The number of redemptions')
		);
	}

}