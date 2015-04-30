<?php namespace Owlgrin\Wallet\Exceptions;

use Illuminate\Support\MessageBag;

class WalletNotCreatedException extends Exception {

	/**
	 * Message
	 */
	const MESSAGE = 'wallet::exception.message.wallet_not_created';

	/**
	 * Constructor
	 * @param mixed $messages
	 * @param array $replacers
	 */
	public function __construct($messages = self::MESSAGE, $replacers = array())
	{
		parent::__construct($messages, $replacers);
	}
}