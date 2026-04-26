<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clave secreta AES-256-GCM (hex, 64 caracteres = 32 bytes)
    |--------------------------------------------------------------------------
    | Debe coincidir exactamente con la configurada en el Print Agent local.
    */
    'secret_key' => env('PRINT_AGENT_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | URL base del Print Agent
    |--------------------------------------------------------------------------
    | Por defecto el agente corre en localhost:9876.
    */
    'base_url' => env('PRINT_AGENT_URL', 'http://localhost:9876'),

    /*
    |--------------------------------------------------------------------------
    | Timeout HTTP (segundos)
    |--------------------------------------------------------------------------
    */
    'timeout' => env('PRINT_AGENT_TIMEOUT', 5),

    /*
    |--------------------------------------------------------------------------
    | Impresoras disponibles en el agente
    |--------------------------------------------------------------------------
    | Nombre lógico => nombre exacto del dispositivo Windows / nombre en el agente.
    | Estos nombres deben estar registrados en el Print Agent.
    |
    | Estructura:
    |   'nombre_logico' => [
    |       'name'  => 'NombreEnAgente',   // nombre exacto que el agente conoce
    |       'paper' => '80mm',             // 58mm | 80mm
    |       'cols'  => 48,                 // caracteres por línea
    |   ]
    */
    'printers' => [
        'fadi' => [
            'name'  => 'Fadi',
            'paper' => '80mm',
            'cols'  => 48,
        ],
        'inventarios' => [
            'name'  => 'Inventarios',
            'paper' => '58mm',
            'cols'  => 32,
        ],
        'misociopos' => [
            'name'  => 'MiSocioPOS',
            'paper' => '80mm',
            'cols'  => 48,
        ],
    ],

];
