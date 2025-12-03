<?php

return [
    // Token de acceso (Recomendado hoy en día)
    'app_access_token'  => env('MP_ACCESS_TOKEN', ''),
    
    // Credenciales Legacy (Client ID / Secret)
    'app_id'     => env('MP_APP_ID', ''),
    'app_secret' => env('MP_APP_SECRET', ''),
    
    // ID opcional para integradores
    'app_integrator_id' => env('MP_INTEGRATOR_ID', 'dev_a93e8427b23111ea8ff80242ac130004')
];