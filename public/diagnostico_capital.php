<?php
/**
 * Diagnóstico de Capital - Script temporal de solo lectura
 * Acceder con: /diagnostico_capital.php?token=misocio_diag_2026
 * ELIMINAR después de diagnosticar
 */

if (!isset($_GET['token']) || $_GET['token'] !== 'misocio_diag_2026') {
    http_response_code(403);
    die('Acceso denegado');
}

header('Content-Type: text/plain; charset=utf-8');

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DIAGNÓSTICO DE CAPITAL - " . date('Y-m-d H:i:s') . " ===\n\n";

// Obtener tenants
$tenants = DB::table('tenants')->get();
foreach ($tenants as $tenant) {
    $tid = $tenant->id;
    echo "========================================\n";
    echo "TENANT: {$tenant->name} (ID: {$tid})\n";
    echo "========================================\n\n";

    // --- COMPRAS ---
    $compras = DB::table('compras')
        ->where('tenant_id', $tid)
        ->where('estado', 'Completo')
        ->orderBy('created_at')
        ->get();

    $totalCompras = 0;
    echo "COMPRAS COMPLETADAS:\n";
    foreach ($compras as $c) {
        $total = $c->efectivo + $c->credito;
        $totalCompras += $total;
        echo "  Folio #{$c->numero_folio} | Total: Bs." . number_format($total, 2) . " | Fecha: {$c->created_at}\n";
    }
    echo "TOTAL COMPRAS: Bs." . number_format($totalCompras, 2) . "\n\n";

    // --- SUBTOTALES DE COMPRA ITEMS (valor real pagado) ---
    $subtotalItems = DB::table('compra_items')
        ->join('compras', 'compra_items.compra_id', '=', 'compras.id')
        ->where('compras.tenant_id', $tid)
        ->where('compras.estado', 'Completo')
        ->sum('compra_items.subtotal');
    echo "SUMA SUBTOTALES compra_items: Bs." . number_format($subtotalItems, 2) . "\n\n";

    // --- CAPITAL ACTUAL (formula corregida) ---
    $capitalFix = DB::table('productos')
        ->where('tenant_id', $tid)
        ->whereNull('deleted_at')
        ->selectRaw('SUM(stock * precio_de_compra / NULLIF(cantidad, 0)) as cap')
        ->value('cap');
    echo "CAPITAL (stock * pdc / cantidad): Bs." . number_format($capitalFix, 2) . "\n";

    // --- CAPITAL SIN FIX (formula original) ---
    $capitalOriginal = DB::table('productos')
        ->where('tenant_id', $tid)
        ->whereNull('deleted_at')
        ->selectRaw('SUM(stock * precio_de_compra) as cap')
        ->value('cap');
    echo "CAPITAL (stock * pdc - original): Bs." . number_format($capitalOriginal, 2) . "\n\n";

    // --- PRODUCTOS CON STOCK > 0 ---
    $productos = DB::table('productos')
        ->where('tenant_id', $tid)
        ->whereNull('deleted_at')
        ->where('stock', '>', 0)
        ->orderByRaw('(stock * precio_de_compra / NULLIF(cantidad, 0)) DESC')
        ->get(['nombre', 'stock', 'precio_de_compra', 'cantidad', 'control',
               DB::raw('(stock * precio_de_compra / NULLIF(cantidad, 0)) as cap_fix'),
               DB::raw('(stock * precio_de_compra) as cap_orig')]);

    echo "PRODUCTOS CON STOCK > 0 (ordenados por mayor aporte al capital):\n";
    echo str_pad("Nombre", 32) . " | stock | pdc    | cant | control | cap_fix   | cap_orig\n";
    echo str_repeat("-", 95) . "\n";

    foreach ($productos as $p) {
        echo str_pad(substr($p->nombre, 0, 31), 32)
            . " | " . str_pad($p->stock, 5)
            . " | " . str_pad(number_format($p->precio_de_compra, 2), 6)
            . " | " . str_pad($p->cantidad, 4)
            . " | " . str_pad($p->control ? 'SI' : 'NO', 7)
            . " | Bs." . str_pad(number_format($p->cap_fix, 2), 9)
            . " | Bs." . number_format($p->cap_orig, 2)
            . "\n";
    }

    // --- COMPRA ITEMS DETALLE (para comparar con productos) ---
    echo "\nDETALLE COMPRA ITEMS (precio y subtotal por producto):\n";
    echo str_pad("Producto", 32) . " | cant_total | precio  | cant_med | subtotal\n";
    echo str_repeat("-", 80) . "\n";

    $items = DB::table('compra_items')
        ->join('compras', 'compra_items.compra_id', '=', 'compras.id')
        ->join('productos', 'compra_items.producto_id', '=', 'productos.id')
        ->where('compras.tenant_id', $tid)
        ->where('compras.estado', 'Completo')
        ->select(
            'productos.nombre',
            'productos.cantidad as cantidad_medida',
            'compra_items.cantidad as cant_total',
            'compra_items.precio',
            'compra_items.subtotal'
        )
        ->orderBy('compra_items.subtotal', 'DESC')
        ->get();

    foreach ($items as $item) {
        echo str_pad(substr($item->nombre, 0, 31), 32)
            . " | " . str_pad($item->cant_total, 10)
            . " | " . str_pad(number_format($item->precio, 2), 7)
            . " | " . str_pad($item->cantidad_medida, 8)
            . " | Bs." . number_format($item->subtotal, 2)
            . "\n";
    }

    echo "\n\n";
}

echo "=== FIN DEL DIAGNÓSTICO ===\n";
echo "RECUERDA: Eliminar este archivo después de usarlo.\n";
