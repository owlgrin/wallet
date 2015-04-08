<?php namespace Owlgrin\Wallet;

use Illuminate\Support\ServiceProvider;

class WalletServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('owlgrin/wallet');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerCommands();
		$this->registerRepositories();

		$this->app->singleton('wallet', 'Owlgrin\Wallet\Wallet');
	}

	protected function registerCommands()
	{
		$this->app->bindShared('command.wallet.table', function($app)
		{
			return $app->make('Owlgrin\Wallet\Commands\WalletTableCommand');
		});

		$this->app->bindShared('command.wallet.add.credit', function($app)
		{
			return $app->make('Owlgrin\Wallet\Commands\AddCreditsCommand');
		});

		$this->commands('command.wallet.table');
		$this->commands('command.wallet.add.credit');
	}

	protected function registerRepositories()
	{
		$this->app->bind('Owlgrin\Wallet\Credit\CreditRepo', 'Owlgrin\Wallet\Credit\DbCreditRepo');
		$this->app->bind('Owlgrin\Wallet\Redemption\RedemptionRepo', 'Owlgrin\Wallet\Redemption\DbRedemptionRepo');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
