<?php  namespace faiverson\Merchant;

use Faiverson\Merchant\contract\Merchant as Merchant;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Plan;

class StripeHandler implements Merchant
{
	public function __construct($stripe_key)
	{
		Stripe::setApiKey($stripe_key);
	}

	public function setStripeKey($stripe_key)
	{
		Stripe::setApiKey($stripe_key);
	}

	public function purchase(array $data)
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
		return Charge::create(array(
				"amount" => $price * 100, // amount in cents
				"currency" => "usd",
				"customer" => $stripe_id,
				"source" => $source_id,
				"description" => "Product " . $product . " bought by " . $name,
    			"metadata" => ["order_id" => $order_id]
		));
	}

	public function refund(array $data)
	{
		return \Stripe\Refund::create(array(
			"charge" => $data['transaction_stripe_id']
		));
	}

	public function addCustomer($data)
	{
		$customer = Customer::create([
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

	public function addCard($data)
	{
		$customer = Customer::retrieve($data['stripe_source_id']);
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

	public function has_subscription($data)
	{
		$customer = Customer::retrieve($data['stripe_id']);
		foreach ($customer->subscriptions['data'] as $sub) {
			if($sub['plan']->id == $data['code_name']) {
				return true;
			}
		}
		return false;
	}

	public function subscribe($data)
	{
		$customer = Customer::retrieve($data['stripe_id']);
		return $customer->subscriptions->create([
			'plan' => $data['code_name']
		]);
	}

	public function create_plan($data)
	{
		return Plan::create([
			"amount" => $data['price'] * 100,
			"interval" => $data['billing_period'],
			"name" => $data['product_name'],
			"currency" => "usd",
			"id" => $data['code_name']
		]);
	}

}