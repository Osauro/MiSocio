<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\VentaItem;
use App\Models\Producto;

// Buscar venta #9
$items = VentaItem::where('venta_id', 9)->with('producto')->get();

echo "Items de venta #9:\n";
foreach ($items as $item) {
    if ($item->producto) {
        echo "Producto: {$item->producto->nombre}\n";
        echo "  Cantidad (paquete): {$item->producto->cantidad}\n";
        echo "  Precio menor: {$item->producto->precio_por_menor}\n";
        echo "  Precio mayor (BD): {$item->producto->precio_por_mayor}\n";
        echo "  Calculado: " . ($item->producto->precio_por_menor * $item->producto->cantidad) . "\n";
        echo "  Item precio: {$item->precio}\n";
        echo "  Item cantidad: {$item->cantidad}\n";
        echo "  Item subtotal: {$item->subtotal}\n";
        echo "---\n";
    }
}


