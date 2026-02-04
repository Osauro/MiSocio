<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Limpiando tablas de préstamos ===\n\n";

// Deshabilitar foreign keys
DB::statement('SET FOREIGN_KEY_CHECKS=0;');

// Truncar tablas
DB::table('prestamo_items')->truncate();
echo "✓ prestamo_items truncada\n";

DB::table('prestamos')->truncate();
echo "✓ prestamos truncada\n";

// Rehabilitar foreign keys
DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "\n=== Listo! Tablas limpiadas ===\n";
