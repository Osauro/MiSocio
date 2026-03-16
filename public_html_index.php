<?php

/**
 * Este archivo va en ~/public_html/index.php (una sola vez).
 * Apunta al app Laravel que vive en ~/MiSocio/ (fuera del web root).
 */

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Mantenimiento
if (file_exists($maintenance = __DIR__ . '/../MiSocio/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__ . '/../MiSocio/vendor/autoload.php';

$app = require_once __DIR__ . '/../MiSocio/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
