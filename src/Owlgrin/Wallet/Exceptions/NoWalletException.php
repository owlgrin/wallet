<?php namespace Owlgrin\Wallet\Exceptions;

use Illuminate\Support\MessageBag;

class NoWalletException extends Exception {

	/**
	 * Message
	 */
	const MESSAGE = 'No wallet exists';

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