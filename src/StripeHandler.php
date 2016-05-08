<?php

/**
 * Validates popular debit and credit cards numbers against regular expressions and Luhn algorithm.
 * Also validates the CVC and the expiration date.
 *
 * @author    Ignacio de Tomás <nacho@inacho.es>
 * @copyright 2014 Ignacio de Tomás (http://inacho.es)
 */

namespace App\Helpers;

use App\Models\Product;

class StripeHandler
{
	public function __construct($stripe_key)
	{
		\Stripe\Stripe::setApiKey($stripe_key);
	}

	public function addCustomer($data)
	{
		$customer = \Stripe\Customer::create([
			"description" => "Customer for " . $data['email'],
			"email" => "Customer for " . $data['email'],
			"source" => [
				"object" => "card",
				"number" => $data['number'],
				"exp_month" => $data['exp_month'],
				"exp_year" => $data['exp_year'],
				"cvc" => $data['cvc']
			]
		]);
		$source = $customer->sources->data[0];

		return [
			'customer_id' => $customer->id,
			'source_id' => $source->id
		];
	}

	public function addCard($customer_id, $data)
	{
		$customer = \Stripe\Customer::retrieve($customer_id);
		$source = $customer->sources->create([
			"source" => [
				"object" => "card",
				"number" => $data['number'],
				"exp_month" => $data['exp_month'],
				"exp_year" => $data['exp_year'],
				"cvc" => $data['cvc']
			]
		]);

		return $source->id;
	}

	public function purchase($data)
	{
		if(!array_key_exists('stripe_source_id', $data)) {
			$t = $this->addCard($data);
			$data['stripe_source_id'] = $t->id;
		}
		$price = $data['amount'];
		$name = $data['fullname'];
		$order_id = $data['transaction_id'];
		$product = $data['product_name'];
		$stripe_id = $data['stripe_id'];
		$source_id = $data['stripe_source_id'];
		return \Stripe\Charge::create(array(
				"amount" => $price * 100, // amount in cents
				"currency" => "usd",
				"customer" => $stripe_id,
				"source" => $source_id,
				"description" => "Product " . $product . " bought by " . $name,
    			"metadata" => ["order_id" => $order_id]
		));
	}

	public function refund($transaction_stripe_id)
	{
		return \Stripe\Refund::create(array(
			"charge" => $transaction_stripe_id
		));
	}

	public function has_subscription($data)
	{
		$customer = \Stripe\Customer::retrieve($data['stripe_id']);
		foreach ($customer->subscriptions['data'] as $sub) {
			if($sub['plan']->id == $data['code_name']) {
				return true;
			}
		}
		return false;
	}

	public function subscribe($data)
	{
		$customer = \Stripe\Customer::retrieve($data['stripe_id']);
		return $customer->subscriptions->create([
			'plan' => $data['code_name']
		]);
	}

	public function create_plan(Product $product)
	{
		\Stripe\Plan::create([
			"amount" => $product->price * 100,
			"interval" => $product->billing_period,
			"name" => $product->name,
			"currency" => "usd",
			"id" => $product->code_name
		]);
	}

}