<?php namespace Hardywen\Wxpay;

use Illuminate\Support\ServiceProvider;

class WxpayServiceProvider extends ServiceProvider {

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
		$this->package('hardywen/wxpay');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$app = $this->app;
		$app['wxpay'] = $app->share(function ($app) {
			return new Wxpay($app['config']->get('wxpay::config'));
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('wxpay');
	}

}
