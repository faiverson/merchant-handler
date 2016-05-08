<?php
namespace Faiverson\Merchant\contract;

interface Merchant {
	public function purchase(array $data);
	public function refund(array $data);
}