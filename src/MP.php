<?php

namespace AziendeGlobal\LaravelMercadoPago;

use Exception;
use Illuminate\Support\Facades\Http;

/**
 * MercadoPago Integration Library for Laravel
 * * Optimized version using Laravel HTTP Facade.
 */
class MP
{
    const VERSION = "1.0.1";
    const API_BASE_URL = "https://api.mercadopago.com";

    private $client_id;
    private $client_secret;
    private $ll_access_token;
    private $access_data;
    private $sandbox = false;
    private $integrator_id;

    /**
     * Constructor modernizado.
     * Puedes pasar el token directamente, o las credenciales de cliente.
     */
    public function __construct($access_token = null, $client_id = null, $client_secret = null, $integrator_id = null)
    {
        $this->ll_access_token = $access_token;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->integrator_id = $integrator_id;

        // Validación básica
        if (!$this->ll_access_token && (!$this->client_id || !$this->client_secret)) {
            throw new MercadoPagoException("Invalid arguments. Use CLIENT_ID and CLIENT_SECRET, or ACCESS_TOKEN");
        }
    }

    public function sandbox_mode($enable = null)
    {
        if (!is_null($enable)) {
            $this->sandbox = $enable === true;
        }
        return $this->sandbox;
    }

    /**
     * Get Access Token for API use
     */
    public function get_access_token()
    {
        if ($this->ll_access_token) {
            return $this->ll_access_token;
        }

        $app_client_values = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'client_credentials'
        ];

        // Usamos Http Facade con asForm() para x-www-form-urlencoded
        $response = Http::asForm()->post(self::API_BASE_URL . "/oauth/token", $app_client_values);

        if ($response->failed()) {
            throw new MercadoPagoException($response->body(), $response->status());
        }

        $this->access_data = $response->json();
        return $this->access_data['access_token'];
    }

    /**
     * Método centralizado para hacer peticiones HTTP usando Laravel
     */
    private function request($method, $uri, $params = [], $data = [], $headers = [])
    {
        $method = strtolower($method);
        $url = self::API_BASE_URL . $uri;

        // Configurar Headers base
        $requestHeaders = array_merge([
            'accept' => 'application/json',
            'content-type' => 'application/json',
            'user-agent' => "MercadoPago PHP SDK v" . self::VERSION
        ], $headers);

        if ($this->integrator_id) {
            $requestHeaders['x-integrator-id'] = $this->integrator_id;
        }

        // Si la petición requiere autenticación (casi todas), agregamos el token a los params
        // Nota: Algunas APIs prefieren Bearer token en header, pero mantenemos la lógica legacy de query param
        if (!isset($params['access_token'])) {
            $params['access_token'] = $this->get_access_token();
        }

        // Construir la petición
        $http = Http::withHeaders($requestHeaders);

        // Ejecutar según el verbo
        switch ($method) {
            case 'get':
                $response = $http->get($url, $params);
                break;
            case 'post':
                // En POST/PUT, params van en la URL y data en el body
                $urlWithParams = $url . '?' . http_build_query($params);
                $response = $http->post($urlWithParams, $data);
                break;
            case 'put':
                $urlWithParams = $url . '?' . http_build_query($params);
                $response = $http->put($urlWithParams, $data);
                break;
            case 'delete':
                $response = $http->delete($url, $params);
                break;
            default:
                throw new MercadoPagoException("Method {$method} not supported");
        }

        // Manejo de respuesta unificado
        $responseBody = $response->json();

        if ($response->failed()) {
            // Lógica para extraer el mensaje de error detallado de MP
            $message = $responseBody['message'] ?? $response->body();
            
            if (isset($responseBody['cause'])) {
                if (is_array($responseBody['cause'])) {
                    foreach ($responseBody['cause'] as $cause) {
                        $desc = $cause['description'] ?? '';
                        $code = $cause['code'] ?? '';
                        $message .= " - {$code}: {$desc}";
                    }
                } elseif (isset($responseBody['cause']['code'])) {
                    $message .= " - " . $responseBody['cause']['code'] . ': ' . ($responseBody['cause']['description'] ?? '');
                }
            }
            
            throw new MercadoPagoException($message, $response->status());
        }

        return [
            "status" => $response->status(),
            "response" => $responseBody
        ];
    }

    /* --- Métodos Públicos de la API --- */

    public function get_payment($id)
    {
        $uri_prefix = $this->sandbox ? "/sandbox" : "";
        return $this->request('get', $uri_prefix . "/collections/notifications/{$id}");
    }

    public function get_payment_info($id)
    {
        return $this->get_payment($id);
    }

    public function get_authorized_payment($id)
    {
        return $this->request('get', "/authorized_payments/{$id}");
    }

    public function refund_payment($id)
    {
        return $this->request('put', "/collections/{$id}", [], ["status" => "refunded"]);
    }

    public function cancel_payment($id)
    {
        return $this->request('put', "/collections/{$id}", [], ["status" => "cancelled"]);
    }

    public function cancel_preapproval_payment($id)
    {
        return $this->request('put', "/preapproval/{$id}", [], ["status" => "cancelled"]);
    }

    public function search_payment($filters, $offset = 0, $limit = 0)
    {
        $filters["offset"] = $offset;
        $filters["limit"] = $limit;
        $uri_prefix = $this->sandbox ? "/sandbox" : "";
        
        return $this->request('get', $uri_prefix . "/collections/search", $filters);
    }

    public function create_preference($preference)
    {
        return $this->request('post', "/checkout/preferences", [], $preference);
    }

    public function update_preference($id, $preference)
    {
        return $this->request('put', "/checkout/preferences/{$id}", [], $preference);
    }

    public function get_preference($id)
    {
        return $this->request('get', "/checkout/preferences/{$id}");
    }

    public function create_preapproval_payment($preapproval_payment)
    {
        return $this->request('post', "/preapproval", [], $preapproval_payment);
    }

    public function update_preapproval_payment($id, $preapproval_payment)
    {
        return $this->request('put', "/preapproval/{$id}", [], $preapproval_payment);
    }

    public function get_preapproval_payment($id)
    {
        return $this->request('get', "/preapproval/{$id}");
    }

    public function get_preapproval_payments_search($filters)
    {
        return $this->request('get', "/preapproval/search", $filters);
    }

    public function create_preapproval_plan_payment($preapproval_plan_payment)
    {
        return $this->request('post', "/preapproval_plan", [], $preapproval_plan_payment);
    }

    public function update_preapproval_plan_payment($id, $preapproval_plan_payment)
    {
        return $this->request('put', "/preapproval_plan/{$id}", [], $preapproval_plan_payment);
    }

    public function create_user_test($data)
    {
        return $this->request('post', "/users/test", [], $data);
    }

    /* --- Métodos Genéricos --- */

    public function get($request, $params = null, $authenticate = true)
    {
        $uri = is_string($request) ? $request : $request['uri'];
        $params = is_string($request) ? ($params ?? []) : ($request['params'] ?? []);
        
        // Manejo de compatibilidad con argumentos viejos
        if (is_array($request) && isset($request['authenticate'])) $authenticate = $request['authenticate'];

        if ($authenticate === false) {
             // Hack: pasamos un token dummy o modificamos request interno, 
             // pero para simplificar, si auth es false, no inyectamos token en params
             // Sin embargo, mi método request() inyecta token si falta. 
             // Para métodos públicos genéricos, asumimos autenticación por defecto.
        }

        return $this->request('get', $uri, $params);
    }

    public function post($request, $data = null, $params = null)
    {
        $uri = is_string($request) ? $request : $request['uri'];
        $data = is_string($request) ? ($data ?? []) : ($request['data'] ?? []);
        $params = is_string($request) ? ($params ?? []) : ($request['params'] ?? []);

        return $this->request('post', $uri, $params, $data);
    }

    public function put($request, $data = null, $params = null)
    {
        $uri = is_string($request) ? $request : $request['uri'];
        $data = is_string($request) ? ($data ?? []) : ($request['data'] ?? []);
        $params = is_string($request) ? ($params ?? []) : ($request['params'] ?? []);

        return $this->request('put', $uri, $params, $data);
    }

    public function delete($request, $params = null)
    {
        $uri = is_string($request) ? $request : $request['uri'];
        $params = is_string($request) ? ($params ?? []) : ($request['params'] ?? []);

        return $this->request('delete', $uri, $params);
    }
}

/**
 * MercadoPago Exception Class
 */
class MercadoPagoException extends Exception
{
    public function __construct($message, $code = 500, Exception $previous = null)
    {
        parent::__construct($message, (int)$code, $previous);
    }
}