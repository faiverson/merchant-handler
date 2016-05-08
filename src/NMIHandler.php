<?php  namespace faiverson\Merchant;

use Faiverson\Merchant\contract\Merchant as Merchant;
use anlutro\cURL\Laravel\cURL;
use Log;

class NMIHandler implements Merchant {

	const APPROVED = 1;
	const DECLINED = 2;
	const ERROR = 3;
	const URL = 'https://secure.networkmerchants.com/api/transact.php';

	protected $username;
	protected $password;

	protected $messages = [
		'1' => 'Transaction Approved.',
		'2' => 'Transaction Declined.',
		'3' => 'Error in transaction data or system error.'
	];

	protected $codes = [
		'100' => 'Transaction was approved',
		'200' => 'Transaction was declined by processor',
		'201' => 'Do not honor',
		'202' => 'Insufficient funds',
		'203' => 'Over limit',
		'204' => 'Transaction not allowed',
		'220' => 'Incorrect payment information',
		'221' => 'No such card issuer',
		'222' => 'No card number on file with issuer',
		'223' => 'Expired card',
		'224' => 'Invalid expiration date',
		'225' => 'Invalid card security code',
		'240' => 'Call issuer for further information',
		'250' => 'Pick up card',
		'251' => 'Lost card',
		'252' => 'Stolen card',
		'253' => 'Fraudulent card',
		'260' => 'Declined with further instructions available. (See response text)',
		'261' => 'Declined-Stop all recurring payments',
		'262' => 'Declined-Stop this recurring program',
		'263' => 'Declined-Update cardholder data available',
		'264' => 'Declined-Retry in a few days',
		'300' => 'Transaction was rejected by gateway',
		'400' => 'Transaction error returned by processor',
		'410' => 'Invalid merchant configuration',
		'411' => 'Merchant account is inactive',
		'420' => 'Communication error',
		'421' => 'Communication error with issuer',
		'430' => 'Duplicate transaction at processor',
		'440' => 'Processor format error',
		'441' => 'Invalid transaction information',
		'460' => 'Processor feature not available',
		'461' => 'Unsupported card type'
	];

	// Initial Setting Functions
	public function __construct($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	}

	public function purchase(array $data)
	{
		$order = [
			'username' => $this->username,
			'password' => $this->password,

			'ccnumber' => $data['number'],
			'ccexp' => $data['exp_month'] . $data['exp_year'],
			'amount' => number_format($data['amount'], 2, ".", ""),
			'cvv' => array_key_exists('cvc', $data) ? $data['cvc'] : '',

			'ipaddress' => array_key_exists('ip_address', $data) ? $data['ip_address'] : '',
			'orderid' => $data['transaction_id'],
			'orderdescription' => array_key_exists('description', $data) ? $data['description'] : 'Purchase for ' . $data['first_name'] . ' ' . $data['last_name'],
			'tax' => 0,
			'shipping' => 0,
			'ponumber' => '',

			'firstname' => $data['first_name'],
			'lastname' => $data['last_name'],
			'email' => $data['email'],

			'address1' => $data['billing_address'],
			'address2' => array_key_exists('billing_address2', $data) ? $data['billing_address2'] : '',
			'country' => $data['billing_country'],
			'state' => $data['billing_state'],
			'city' => $data['billing_city'],
			'zip' => $data['billing_zip'],
			'phone' => array_key_exists('billing_phone', $data) ? $data['billing_phone'] : '',
			'type' => 'sale'
		];

		Log::info('Transactions gateway', $order);
		$values = array_map("urlencode", array_values($order));
		$this->data = array_combine(array_keys($order) , $values);
		return $this->send();
	}

	public function refund(array $data)
	{
		$order = [
			'username' => $this->username,
			'password' => $this->password,

			'ccnumber' => $data['number'],
			'ccexp' => $data['exp_month'] . $data['exp_year'],
			'amount' => number_format($data['amount'], 2, ".", ""),
			'cvv' => array_key_exists('cvv', $data) ? $data['cvv'] : '',

			'ipaddress' => array_key_exists('ip_address', $data) ? $data['ip_address'] : '',
			'orderid' => $data['transaction_id'],
			'orderdescription' => $data['description'],
			'tax' => 0,
			'shipping' => 0,
			'ponumber' => '',

			'firstname' => $data['first_name'],
			'lastname' => $data['last_name'],
			'email' => $data['email'],

			'address1' => $data['billing_address'],
			'address2' => array_key_exists('billing_address2', $data) ? $data['billing_address2'] : '',
			'country' => $data['billing_country'],
			'state' => $data['billing_state'],
			'city' => $data['billing_city'],
			'zip' => $data['billing_zip'],
			'phone' => array_key_exists('billing_phone', $data) ? $data['billing_phone'] : '',
			'type' => 'sale'
		];
		$values = array_map("urlencode", array_values($order));
		$this->data = array_combine(array_keys($order) , $values);
		return $this->send();
	}

	protected function send()
	{
		$response = null;
		$curl = cURL::post(self::URL, $this->data);
		$this->response = $curl->body;
		Log::info('Purchase gateway info: ' . $curl->body);
		parse_str($curl->body, $response);
		$response['gateway_message'] = $this->getMessage($response['response'], $response['response_code']);
		return $response;
	}

	protected function getMessage($response, $code)
	{
//		return $this->messages[$response] . '<br />' . $this->codes[$code];
		return $this->codes[$code];
	}
}