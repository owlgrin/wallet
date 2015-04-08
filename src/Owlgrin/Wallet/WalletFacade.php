<?php namespace Owlgrin\Wallet;

use Illuminate\Support\Facades\Facade;

/**
 * The Wallet Facade
 */
class WalletFacade extends Facade
{
	/**
	 * Returns the binding in IoC container
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'wallet';
	}
}