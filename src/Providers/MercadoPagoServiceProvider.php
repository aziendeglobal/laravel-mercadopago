<?php

namespace AziendeGlobal\LaravelMercadoPago\Providers;

use Illuminate\Support\ServiceProvider;
use AziendeGlobal\LaravelMercadoPago\MP;

class MercadoPagoServiceProvider extends ServiceProvider 
{

	protected $mp_access_token;
	protected $mp_app_id;
	protected $mp_app_secret;
	protected $mp_integrator_id;

	public function boot()
	{
		
		$this->publishes([__DIR__.'/../config/mercadopago.php' => config_path('mercadopago.php')]);

		$this->mp_access_token     = config('mercadopago.app_access_token');
		$this->mp_app_id     = config('mercadopago.app_id');
		$this->mp_app_secret = config('mercadopago.app_secret');
		$this->mp_integrator_id = config('mercadopago.app_integrator_id');
	}

	public function register()
	{
		$this->app->singleton('MP', function(){
			return new MP($this->mp_access_token, $this->mp_app_id, $this->mp_app_secret, $this->mp_integrator_id);
		});
	}
}