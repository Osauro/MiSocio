<?php
// Script diagnóstico - buscar Paceña en productos
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Producto;
use Illuminate\Support\Facades\DB;

// Simular el currentTenantId() = 1 (tenant FADI local)
session()->put('current_tenant_id', 1);

// Autenticar un usuario para activar el global scope
$user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::login($user);

$search = 'paceña';

echo "=== Tenant actual: " . currentTenantId() . " ===\n";

echo "=== Query COMPLETO (igual que getProductos()) ===\n";
$query = Producto::query()->withTrashed();
$result = $query->with(['categoria', 'tags'])
    ->where(function ($q) use ($search) {
        $q->where('nombre', 'like', '%' . $search . '%')
            ->orWhere('codigo', 'like', '%' . $search . '%')
            ->orWhereHas('categoria', function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%');
            })
            ->orWhereHas('tags', function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%');
            });
    })
    ->orderBy('nombre');

echo "SQL: " . $result->toSql() . "\n";
echo "Bindings: " . implode(', ', $result->getBindings()) . "\n";
echo "Count: " . $result->count() . "\n\n";

echo "=== Búsqueda sin orWhereHas ===\n";
echo "Count: " . Producto::withTrashed()->where('nombre', 'like', '%' . $search . '%')->count() . "\n\n";

echo "=== Búsqueda 'ace' sin global scope ===\n";
echo "Count: " . Producto::withoutGlobalScopes()->where('nombre', 'like', '%ace%')->count() . "\n";
