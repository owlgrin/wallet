<?php

return array(

	'tables' => array(


		/**
		 * This table is required to store all the transactions
		 * made by our users
		 */
		'transactions' => '_wallet_transactions',

		/**
		 * This table is required to keep track of the
		 * various coupons in the system.
		 */
		'coupons' => '_wallet_coupons',

		/**
		 * tracks wallets of the users
		 */
		'wallets' => '_wallet_wallets',

		'redemptions' => '_wallet_redemptions',

		'credits' => '_wallet_credits'
	),

	'transactions' => array(
		'redemption' => 'Owlgrin\Wallet\Transaction\MaxRedemptionTransactionMaker',
		'amount' => 'Owlgrin\Wallet\Transaction\SimpleAmountTransactionMaker'
	),


	'limit' => 1

);