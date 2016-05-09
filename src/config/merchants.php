<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Merchant settings
	|--------------------------------------------------------------------------
	|
	| Select your merchant and set the credentials
	|
	*/
	'handler'          => env('MERCHANT_HANDLER', 'nmi'),

	'nmi' => array(
		'username' => env('NMI_LOGIN', 'demo'),
		'password' => env('NMI_PASSWORD', 'password')
	),

	'stripe' => array(
		'key' => env('STRIPE_KEY', 'demo')
	),

);