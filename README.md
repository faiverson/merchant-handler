# Merchant Handler
Use different merchant with Laravel for a direct sale/transaction

# Installation
Using composer
`composer require merchant-handler`
Or add to composer.json
"faiverson/merchant-handler" : "^1.0.0"

# How to use
Go to the app.php and add in the providers:
`composer require merchant-handler`
Faiverson\Merchant\MerchantServiceProvider::class,

The you can inject the dependency in your object
'use Faiverson\Merchant\contract\Merchant;'

public function __construct(Merchant $merchant)

And you can use the 2 methods: purchase and refund