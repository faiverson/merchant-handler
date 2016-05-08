<?php

namespace Faiverson\Merchant;

use Illuminate\Support\ServiceProvider;

class MerchantServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/merchants.php' => config_path('merchants.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
		$this->app->singleton('Faiverson\Merchant\MerchantManager', function ($app) {
			return new MerchantManager($app);
		});

		$this->app->singleton('Faiverson\Merchant\contract\Merchant', function ($app) {
			return $app->make('Faiverson\Merchant\MerchantManager')->merchant();
		});
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
		return [
			'Faiverson\Merchant\MerchantManager',
			'Faiverson\Merchant\contract\Merchant',
		];
    }

}