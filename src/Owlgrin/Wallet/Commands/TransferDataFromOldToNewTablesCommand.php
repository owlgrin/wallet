<?php namespace Owlgrin\Wallet\Commands;


use Illuminate\Database\DatabaseManager as Database;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Command to generate the required migration
 */
use PDOException, Config;

class TransferDataFromOldToNewTablesCommand extends Command {

	protected $db;
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'wallet:transfer-data';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This command transfer data from old tables to new tables.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */

	public function __construct(Database $db)
	{
 		parent::__construct();
 		$this->db = $db;
	}

	public function fire()
	{
		try
		{
			//starting the transaction
			$this->db->beginTransaction();

	        $this->db->insert( $this->db->raw("INSERT into ". Config::get('wallet::tables.wallets') .
				"(`user_id`, `amount`, `redemption_limit`, `deleted_at`, `created_at`, `updated_at`) SELECT
				 `user_id`, `amount_left`, `redemptions_left`, `expired_at`, `created_at`, `updated_at`
				FROM ". Config::get('wallet::tables.credits')));

	        $this->db->insert( $this->db->raw("INSERT into ". Config::get('wallet::tables.transactions') .
				"(`wallet_id`, `amount`, `direction`, `type`, `trigger_type`, `trigger_id`, `created_at`,
				`updated_at`) Select w.id, c.amount_initial, 'CREDIT', 'AMOUNT', 'DEPOSIT', null,
				c.created_at, c.updated_at FROM ". Config::get('wallet::tables.wallets') ." as w Join ".
				Config::get('wallet::tables.credits') ." as c On c.user_id = w.user_id Group By w.id "));

	        $this->db->insert( $this->db->raw("INSERT into ". Config::get('wallet::tables.transactions') .
				"(`wallet_id`, `amount`, `direction`, `type`, `trigger_type`, `trigger_id`, `created_at`,
				`updated_at`) Select w.id, c.redemptions_initial, 'CREDIT', 'REDEMPTION', 'DEPOSIT', null,
				c.created_at, c.updated_at FROM ". Config::get('wallet::tables.wallets') ." As w Join ".
				Config::get('wallet::tables.credits') ." As c On c.user_id = w.user_id group by w.id"));

	        $this->db->insert( $this->db->raw("INSERT into ". Config::get('wallet::tables.transactions') .
				"(`wallet_id`, `amount`, `direction`, `type`, `trigger_type`, `trigger_id`, `created_at`,
				`updated_at`) Select w.id, r.invoiced_amount, 'DEBIT', 'AMOUNT', 'WITHDRAW', null, now(), now()
				 FROM ". Config::get('wallet::tables.wallets') ." As w  Join ".
				Config::get('wallet::tables.redemptions') ." As r On r.user_id = w.user_id group by r.id"));

	        $this->db->insert( $this->db->raw("INSERT into ". Config::get('wallet::tables.transactions') .
				"(`wallet_id`, `amount`, `direction`, `type`, `trigger_type`, `trigger_id`, `created_at`,
				`updated_at`) Select w.id, 1, 'DEBIT', 'REDEMPTION', 'WITHDRAW', null, now(), now() FROM ".
				 Config::get('wallet::tables.wallets') ." As w Join ".
				 Config::get('wallet::tables.redemptions') ." As r where r.user_id = w.user_id group by r.id"));

			$this->db->commit();
		}
		catch(PDOException $e)
		{
			$this->db->rollback();

			throw $e;
		}
	}

	protected function getArguments()
	{
		return array(
			// array('', InputArgument::OPTIONAL, 'Description of the argument'),
		);
	}

	protected function getOptions()
	{
		return array(
			// array('', null, InputOption::VALUE_NONE, 'Description of the option', null)
		);
	}

}