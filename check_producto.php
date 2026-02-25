<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CONSULTA VENTA #9 ===\n\n";

// Buscar la venta #9
$venta = DB::table('ventas')->where('numero_folio', 9)->first(['id']);

if ($venta) {
    echo "Venta ID: {$venta->id}\n\n";

    // Obtener items de la venta
    $items = DB::table('venta_items')
        ->join('productos', 'venta_items.producto_id', '=', 'productos.id')
        ->where('venta_items.venta_id', $venta->id)
        ->select(
            'productos.id',
            'productos.nombre',
            'productos.cantidad',
            'productos.precio_por_menor',
            'productos.precio_por_mayor',
            'venta_items.cantidad as cantidad_vendida',
            'venta_items.precio as precio_item',
            'venta_items.subtotal'
        )
        ->get();

    foreach ($items as $item) {
        echo "=== PRODUCTO ===\n";
        echo "ID: {$item->id}\n";
        echo "Nombre: {$item->nombre}\n";
        echo "Cantidad por paquete: {$item->cantidad}\n";
        echo "Precio por menor: Bs. {$item->precio_por_menor}\n";
        echo "Precio por mayor: Bs. {$item->precio_por_mayor}\n";
        echo "\nEN LA VENTA:\n";
        echo "Cantidad vendida: {$item->cantidad_vendida}\n";
        echo "Precio guardado: Bs. {$item->precio_item}\n";
        echo "Subtotal: Bs. {$item->subtotal}\n";
        echo "\nPrecio por mayor DEBERÍA SER: Bs. " . ($item->cantidad * $item->precio_por_menor) . "\n\n";
    }
} else {
    echo "Venta #9 no encontrada\n";
}
