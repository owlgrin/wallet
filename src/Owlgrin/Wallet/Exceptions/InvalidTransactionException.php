<?php namespace Owlgrin\Wallet\Exceptions;

use Illuminate\Support\MessageBag;

class InvalidTransactionException extends Exception {

	/**
	 * Message
	 */
	const MESSAGE = 'wallet::exception.message.invalid_transaction';

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