<?php

namespace AziendeGlobal\LaravelMercadoPago\Providers;

use Illuminate\Support\ServiceProvider;
use AziendeGlobal\LaravelMercadoPago\MP;

class MercadoPagoServiceProvider extends ServiceProvider 
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Publicar el archivo de configuración
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/mercadopago.php' => config_path('mercadopago.php')
            ], 'mercadopago-config');
        }
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        // Fusionar configuración por defecto (buena práctica en paquetes)
        $this->mergeConfigFrom(
            __DIR__.'/../config/mercadopago.php', 'mercadopago'
        );

        // Registrar el Singleton
        // Usamos el string 'MP' para que coincida con el getFacadeAccessor del Facade
        $this->app->singleton('MP', function($app) {
            // Leemos la configuración AQUÍ, en el momento que se necesita
            $config = $app['config']->get('mercadopago');

            return new MP(
                $config['app_access_token'] ?? null,
                $config['app_id'] ?? null,
                $config['app_secret'] ?? null,
                $config['app_integrator_id'] ?? null
            );
        });
    }
}