<?php

/**
 * LicoPOS - Index para cPanel
 *
 * Este archivo va en public_html/ cuando el proyecto
 * está instalado en /home/usuario/licos/
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determinar la ruta al proyecto Laravel
// Ajusta esta ruta según donde instalaste el proyecto
$laravelPath = dirname(__DIR__) . '/licos';

// Verificar si existe el directorio
if (!is_dir($laravelPath)) {
    die('Error: No se encuentra el directorio del proyecto en: ' . $laravelPath);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelPath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelPath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once $laravelPath . '/bootstrap/app.php')
    ->handleRequest(Request::capture());
