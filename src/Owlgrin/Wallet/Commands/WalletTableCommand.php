<?php namespace Owlgrin\Wallet\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Config;

/**
 * Command to generate the required migration
 */
class WalletTableCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'wallet:table';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a migration for the wallet database table';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
		$path = $this->createBaseMigration();

		file_put_contents($path, $this->getMigrationStub());

		$this->info('Migration created successfully!');

		$this->call('dump-autoload');
	}

	/**
	 * Creates the base file for migration o reside into
	 * @return Migration
	 */
	protected function createBaseMigration()
	{
		$name = 'create_wallet_table';

		$path = $this->laravel['path'].'/database/migrations';

		return $this->laravel['migration.creator']->create($name, $path);
	}

	/**
	 * Get the contents of the reminder migration stub.
	 *
	 * @return string
	 */
	protected function getMigrationStub()
	{
		$stub = file_get_contents(__DIR__.'/../../../stubs/migration.stub');

		$stub = str_replace('_wallet_balances', Config::get('wallet::tables.balances'), $stub);
		$stub = str_replace('_wallet_transactions', Config::get('wallet::tables.transactions'), $stub);
		$stub = str_replace('_wallet_coupons', Config::get('wallet::tables.coupons'), $stub);
		$stub = str_replace('_wallet_user_coupons', Config::get('wallet::tables.user_coupons'), $stub);

		return $stub;
	}
}