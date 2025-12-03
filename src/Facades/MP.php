<?php

namespace AziendeGlobal\LaravelMercadoPago\Facades;

use Illuminate\Support\Facades\Facade;

class MP extends Facade
{
    protected static function getFacadeAccessor()
    {
        // Este string debe coincidir con lo que pusimos en $this->app->singleton('MP', ...)
        return 'MP'; 
    }
}