wallet
========
Wallet allows you to maintain credits for your users.

Installation
============
To install the package, include the following in your composer.json.
```
"owlgrin/wallet": "dev-master"
```

And then include the following service provider in your app.php.

```
'Owlgrin\Wallet\WalletServiceProvider'
```


And lastly, publish the config.
```
php artisan config:publish owlgrin/wallet
```

Usage
=====

Write this command in your artisan to create migrations

```
php artisan wallet:table
```

Now migrate all the tables to your mysql db

```
php artisan migrate
```


You can initiate wallet by writing

```
Wallet::user($userId)
```
where `$userId` is the unique id of your user

Credits

You can add credits of your user

```
Wallet::credits($credits, $redemptions)
```

where `$credits` is the amount of credits you want to add for your user
and `$redemptions` is number of times you want your user to use these credits


Redemptions

You can redeem the credits by using

```
Wallet::redeem($amount)
```

where `$amount` is the requested amount on which you want to access the credit

Left Credits

You can see the left credits by using

```
Wallet::left();
```

Exceptions


Wallet comes with custom exceptions, to make them easier to handle. These are the followin custom exceptions that you can use:

```php
Owlgrin\Wallet\Exceptions\CreditsLimitReachedException;
Owlgrin\Wallet\Exceptions\NoCreditsException;
Owlgrin\Wallet\Exceptions\InternalException;
```

Each of these extend an abstract class `Owlgrin\Wallet\Exceptions\Exception`.

You can use it like following:

```php

try
{
	Wallet::Redeem(5445);
}
catch(Owlgrin\Wallet\Exceptions\NoCreditsException $e)
{
	return $e;
}
catch(Owlgrin\Wallet\Exceptions $e)
{
	return $e;
}
```
