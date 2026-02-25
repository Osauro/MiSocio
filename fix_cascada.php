<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Producto;

echo "=== LISTAR PRODUCTOS ===\n\n";

// Usar el modelo que ya tiene el tenant configurado
$productos = Producto::orderBy('id', 'desc')
    ->limit(20)
    ->get(['id', 'nombre', 'cantidad', 'precio_por_menor', 'precio_por_mayor']);

if ($productos->isEmpty()) {
    echo "No se encontraron productos\n";
} else {
    foreach ($productos as $producto) {
        echo "ID: {$producto->id} | {$producto->nombre}\n";
        echo "  Cantidad: {$producto->cantidad} | Menor: Bs.{$producto->precio_por_menor} | Mayor: Bs.{$producto->precio_por_mayor}\n\n";
    }
}


