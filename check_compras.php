<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "=== ÚLTIMAS 10 COMPRAS ===\n\n";

$compras = DB::table('compras')
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get(['id', 'numero_folio', 'estado', 'user_id', 'tenant_id', 'created_at']);

foreach ($compras as $compra) {
    echo sprintf(
        "ID: %d | Folio: %s | Estado: %s | User: %s | Tenant: %s | Fecha: %s\n",
        $compra->id,
        $compra->numero_folio,
        $compra->estado,
        $compra->user_id ?? 'NULL',
        $compra->tenant_id ?? 'NULL',
        $compra->created_at
    );
}

echo "\n=== COMPRAS PENDIENTES ===\n\n";

$pendientes = DB::table('compras')
    ->where('estado', 'Pendiente')
    ->get(['id', 'numero_folio', 'user_id', 'tenant_id']);

foreach ($pendientes as $compra) {
    echo sprintf(
        "ID: %d | Folio: %s | User: %s | Tenant: %s\n",
        $compra->id,
        $compra->numero_folio,
        $compra->user_id ?? 'NULL',
        $compra->tenant_id ?? 'NULL'
    );
}

echo "\n=== USUARIO Y TENANT ACTUAL (si hay sesión) ===\n\n";
echo "Auth check: " . (Auth::check() ? 'YES' : 'NO') . "\n";
if (Auth::check()) {
    echo "User ID: " . Auth::id() . "\n";
}
echo "Tenant ID (sesión): " . (session('current_tenant_id') ?? 'NULL') . "\n";
