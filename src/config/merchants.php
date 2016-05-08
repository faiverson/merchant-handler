<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Services settings
	|--------------------------------------------------------------------------
	|
	| Service specific settings.
	|
	*/
	'handler'          => env('MERCHANT_HANDLER', 'nmi'),

	'nmi' => array(
		'username' => env('NMI_LOGIN', 'demo'),
		'password' => env('NMI_PASSWORD', 'password')
	),

);