<?php

return array(

	'tables' => array(

		/**
		 * This table is required to keep track of the
		 * balances for our users.
		 */
		'balances' => '_wallet_balances',

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
		 * This table is required to store various coupons given
		 * to our users
		 */
		'user_coupons' => '_wallet_user_coupons'
	),

	'transactions' = array(
		'redemption' => 'Owlgrin\Wallet\Transaction\MaxRedemptionTransactionMaker',
		'amount' => 'Owlgrin\Wallet\Transaction\SimpleAmountTransactionMaker'
	)


	'limit' => 1

);