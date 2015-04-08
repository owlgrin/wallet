<?php namespace Owlgrin\Wallet\Credit;

interface CreditRepo {

	public function add($userId, $credit, $redemption);

}
