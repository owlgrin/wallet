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

		$this->app->bindShared('command.wallet.add.coupon', function($app)
		{
			return $app->make('Owlgrin\Wallet\Commands\AddCouponCommand');
		});

		$this->app->bindShared('command.wallet.add.coupon.for.user', function($app)
		{
			return $app->make('Owlgrin\Wallet\Commands\AddCouponForUserCommand');
		});

		$this->commands('command.wallet.table');
		$this->commands('command.wallet.add.credit');
		$this->commands('command.wallet.add.coupon');
		$this->commands('command.wallet.add.coupon.for.user');
	}

	protected function registerRepositories()
	{
		$this->app->bind('Owlgrin\Wallet\Credit\CreditRepo', 'Owlgrin\Wallet\Credit\DbCreditRepo');
		$this->app->bind('Owlgrin\Wallet\Redemption\RedemptionRepo', 'Owlgrin\Wallet\Redemption\DbRedemptionRepo');
		$this->app->bind('Owlgrin\Wallet\Coupon\CouponRepo', 'Owlgrin\Wallet\Coupon\DbCouponRepo');
		$this->app->bind('Owlgrin\Wallet\Transaction\TransactionRepo', 'Owlgrin\Wallet\Transaction\DbTransactionRepo');
		$this->app->bind('Owlgrin\Wallet\Balance\BalanceRepo', 'Owlgrin\Wallet\Balance\DbBalanceRepo');
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
