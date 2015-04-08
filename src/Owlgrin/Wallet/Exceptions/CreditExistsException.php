<?php namespace Owlgrin\Wallet\Exceptions;

use Illuminate\Support\MessageBag;

class CreditExistsException extends Exception {

	/**
	 * Message
	 */
	const MESSAGE = 'wallet::responses.message.credit_exists';

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