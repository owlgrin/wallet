<?php namespace Owlgrin\Wallet\Exceptions;

class InternalException extends Exception {

	/**
	 * Message
	 */
	const MESSAGE = 'wallet::exception.message.internal_error';

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