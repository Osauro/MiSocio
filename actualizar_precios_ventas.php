<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ACTUALIZAR PRECIOS DE VENTAS PENDIENTES ===\n\n";

// Obtener todos los items de ventas pendientes
$ventaItems = DB::table('venta_items')
    ->join('ventas', 'venta_items.venta_id', '=', 'ventas.id')
    ->join('productos', 'venta_items.producto_id', '=', 'productos.id')
    ->where('ventas.estado', 'Pendiente')
    ->select(
        'venta_items.id as item_id',
        'venta_items.producto_id',
        'venta_items.precio as precio_actual',
        'productos.precio_por_mayor',
        'productos.nombre as producto_nombre',
        'ventas.numero_folio'
    )
    ->get();

$actualizados = 0;

foreach ($ventaItems as $item) {
    if ($item->precio_actual != $item->precio_por_mayor) {
        DB::table('venta_items')
            ->where('id', $item->item_id)
            ->update(['precio' => $item->precio_por_mayor]);
        
        echo "✓ Venta #{$item->numero_folio} - {$item->producto_nombre}: ";
        echo "Precio actualizado de Bs. {$item->precio_actual} a Bs. {$item->precio_por_mayor}\n";
        $actualizados++;
    }
}

echo "\n=== RESULTADO ===\n";
echo "Total de items actualizados: {$actualizados}\n";
