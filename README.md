# FORK DEL POYECTO ORIGINAL DE: 

* santigraviano/laravel-mercadopago

<a href="https://github.com/santigraviano/laravel-mercadopago">santigraviano/laravel-mercadopago</a>

Mejoras 05/2024:

-se corrige el error por certificados caducados

-se agrega la variable de Access Token (MP_ACCESS_TOKEN) para configurar desde el .env

-se agrega la variable de Integrator Id (MP_INTEGRATOR_ID) para configurar desde el .env









# DESCRIPCIÓN 

# Laravel Facade para MercadoPago 

* [Instalación](#install)
* [Configuración](#configuration)
* [Como utilizar](#how-to)

* Compatibilidad en revisión !!

<a name="install"></a>
### Instalación

`composer require aziendeglobal/laravel-mercadopago`

Dentro de `config/app.php` agregar los siguientes Provider y Alias

Provider

```php
'providers' => [
  // Otros Providers...
  AziendeGlobal\LaravelMercadoPago\Providers\MercadoPagoServiceProvider::class,
  /*
   * Application Service Providers...
   */
],
```

Alias

```php
'aliases' => [
  // Otros Aliases
  'MP' => AziendeGlobal\LaravelMercadoPago\Facades\MP::class,
],
```

<a name="configuration"></a>
### Configuración

Antes de configurar el APP ID y APP SECRET, ejecutar el siguiente comando: 

`php artisan vendor:publish`

Despues de haber ejecutado el comando, ir al archivo `.env` y agregar los campos `MP_APP_ID` y `MP_APP_SECRET` con los correspondientes valores de los `CLIENT_ID` y `CLIENT_SECRET` de tu aplicacion de MercadoPago.

Para saber cuales son tus datos `CLIENT_ID` y `CLIENT_SECRET` podes ingresar aqui: 

* [Credenciales](https://www.mercadopago.com/mla/account/credentials?type=basic)

Si no deseas usar el archivo `.env`, ir a `config/mercadopago.php` y agregar tus datos de aplicación correspondientes.

```php
return [
	'app_id'     => env('MP_APP_ID', 'TU CLIENT ID AQUI'),
	'app_secret' => env('MP_APP_SECRET', 'TU CLIENT SECRET AQUI')
];
```

<a name="how-to"></a>
### Como utilizar

En este ejemplo vamos a crear una preferencia de pago, usando la Facade `MP` 

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use MP;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class MercadoPagoController extends Controller
{
  public function getCreatePreference()
  {
  	$preferenceData = [
  		'items' => [
  			[
  				'id' => 12,
  				'category_id' => 'phones',
  				'title' => 'iPhone 6',
  				'description' => 'iPhone 6 de 64gb nuevo',
  				'picture_url' => 'http://d243u7pon29hni.cloudfront.net/images/products/iphone-6-dorado-128-gb-red-4g-8-mpx-1256254%20(1)_m.png',
  				'quantity' => 1,
  				'currency_id' => 'ARS',
  				'unit_price' => 14999
  			]
  		],
  	];

  	$preference = MP::create_preference($preferenceData);

  	return dd($preference);

  }
```

En este ejemplo vamos a crear una subscripción (débito automático), usando la Facade `MP` 

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use MP;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class MercadoPagoController extends Controller
{
  public function getCreatePreapproval()
  {
    $preapproval_data = [
      'payer_email' => 'agariobadcell@gmail.com',
      'back_url' => 'http://labhor.com.ar/laravel/public/preapproval',
      'reason' => 'Subscripción a paquete premium',
      'external_reference' => $subscription->id,
      'auto_recurring' => [
        'frequency' => 1,
        'frequency_type' => 'months',
        'transaction_amount' => 99,
        'currency_id' => 'ARS',
        'start_date' => Carbon::now()->addHour()->format('Y-m-d\TH:i:s.BP'),
        'end_date' => Carbon::now()->addMonth()->format('Y-m-d\TH:i:s.BP'),
      ],
    ];

    MP::create_preapproval_payment($preapproval_data);

    return dd($preapproval);
  }
```


