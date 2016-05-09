<?php  namespace Faiverson\Merchant;

use Illuminate\Foundation\Application;
use InvalidArgumentException;

class MerchantManager
{
    /**
     * Create a new connection factory instance.
     *
     * @param  \Illuminate\Contracts\Container\Container  $container
     * @return void
     */
    public function __construct(Application $app)
    {
		$handler = $app['config']->get('merchants.handler');
        $this->handler = $handler;
		$this->config = $app['config']->get("merchants.{$handler}");
    }

	public function merchant()
	{
		switch ($this->handler) {
			case 'nmi':
				return new NMIHandler($this->config['username'], $this->config['username']);

			case 'stripe':
				return new StripeHandler($this->config['key']);
		}

		throw new InvalidArgumentException("Unsupported merchant [$this->handler]");
	}
}
