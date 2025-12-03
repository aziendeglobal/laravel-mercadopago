# Laravel MercadoPago Integration

[![Latest Stable Version](https://poser.pugx.org/aziendeglobal/laravel-mercadopago/v/stable)](https://packagist.org/packages/aziendeglobal/laravel-mercadopago)
[![License](https://poser.pugx.org/aziendeglobal/laravel-mercadopago/license)](https://packagist.org/packages/aziendeglobal/laravel-mercadopago)

Un paquete optimizado para integrar la API de Mercado Pago en aplicaciones Laravel. Soporta Laravel 8, 9, 10 y 11. Utiliza el cliente HTTP nativo de Laravel para un mejor rendimiento y seguridad.

## Características

-   Integración simple con **Service Provider** y **Facade**.
-   Cliente HTTP optimizado (reemplaza cURL nativo por Laravel Http Client).
-   Manejo de excepciones claro.
-   Soporte para **Sandbox**.
-   Gestión de Preferencias, Pagos, Devoluciones y Suscripciones.

## Instalación

Instala el paquete vía Composer:

```bash
composer require aziendeglobal/laravel-mercadopago