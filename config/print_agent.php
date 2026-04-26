<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clave secreta AES-256-GCM (hex, 64 caracteres = 32 bytes)
    |--------------------------------------------------------------------------
    | Debe coincidir exactamente con la configurada en el Print Agent local.
    | Esta clave es de infraestructura y es compartida por todos los tenants
    | que usen el mismo Print Agent.
    */
    'secret_key' => env('PRINT_AGENT_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | URL base del Print Agent
    |--------------------------------------------------------------------------
    | El agente corre localmente (mismo servidor o equipo del cliente).
    | No varía por tenant; es una configuración de infraestructura.
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
    | NOTA: Configuración de impresora por tenant
    |--------------------------------------------------------------------------
    | El nombre de la impresora, tamaño de papel, corte automático, apertura
    | de cajón y ancho en caracteres se almacenan en la tabla tenant_configs
    | (campos: impresora_nombre, papel_tamano, corte_automatico, abrir_cajon,
    | ancho_caracteres) y se leen desde el modelo TenantConfig.
    |
    | No definas impresoras aquí — cada tenant gestiona la suya desde la
    | sección "Impresión" del panel de configuración.
    */

];

