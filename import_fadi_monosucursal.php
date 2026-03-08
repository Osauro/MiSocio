<?php

/**
 * Script de importación de datos desde paybol_fadi (monosucursal)
 * Consolida stock de todas las tiendas en un solo valor
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Configurar el tenant (ajustar según necesidad)
$tenantId = 1; // ID del tenant FADI

echo "=== Importación de datos de paybol_fadi (Monosucursal) ===\n\n";

try {
    // Verificar el tenant
    $tenant = DB::table('tenants')->where('id', $tenantId)->first();

    if (!$tenant) {
        echo "Error: Tenant ID '$tenantId' no encontrado.\n";
        echo "Tenants disponibles:\n";
        $tenants = DB::table('tenants')->get();
        foreach ($tenants as $t) {
            echo "  - ID: {$t->id}, Name: {$t->name}\n";
        }
        exit(1);
    }

    echo "Tenant encontrado: {$tenant->name} (ID: {$tenant->id})\n\n";
    // Conectar a la base de datos de origen (paybol_fadi)
    config(['database.connections.fadi' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'paybol_fadi',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => false,
    ]]);

    DB::purge('fadi');

    // ===== IMPORTAR CATEGORÍAS =====
    echo "1. Importando categorías...\n";
    $categoriasFadi = DB::connection('fadi')->table('categorias')->get();
    $mapCategorias = [];

    foreach ($categoriasFadi as $cat) {
        $existente = DB::table('categorias')
            ->where('tenant_id', $tenantId)
            ->where('nombre', $cat->nombre)
            ->first();

        if ($existente) {
            $mapCategorias[$cat->id] = $existente->id;
            echo "  - Categoría '{$cat->nombre}' ya existe (ID: {$existente->id})\n";
        } else {
            $newId = DB::table('categorias')->insertGetId([
                'tenant_id' => $tenantId,
                'nombre' => $cat->nombre,
                'descripcion' => $cat->descripcion ?? null,
                'imagen' => $cat->image ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $mapCategorias[$cat->id] = $newId;
            echo "  - Categoría '{$cat->nombre}' importada (ID: {$newId})\n";
        }
    }

    echo "Total categorías: " . count($categoriasFadi) . "\n\n";

    // ===== IMPORTAR CLIENTES =====
    echo "2. Importando clientes...\n";
    $clientesFadi = DB::connection('fadi')->table('clientes')->get();
    $mapClientes = [];

    foreach ($clientesFadi as $cliente) {
        $existente = DB::table('clientes')
            ->where('tenant_id', $tenantId)
            ->where('nombre', $cliente->nombre)
            ->first();

        if ($existente) {
            $mapClientes[$cliente->id] = $existente->id;
        } else {
            $newId = DB::table('clientes')->insertGetId([
                'tenant_id' => $tenantId,
                'nombre' => $cliente->nombre,
                'celular' => $cliente->celular ?? null,
                'created_at' => $cliente->created_at,
                'updated_at' => $cliente->updated_at,
            ]);
            $mapClientes[$cliente->id] = $newId;
        }
    }

    echo "Total clientes: " . count($clientesFadi) . "\n\n";

    // ===== IMPORTAR PRODUCTOS (CONSOLIDANDO STOCK) =====
    echo "3. Importando productos con stock consolidado...\n";
    $productosFadi = DB::connection('fadi')->table('productos')
        ->whereNull('deleted_at')
        ->get();
    $mapProductos = [];

    foreach ($productosFadi as $producto) {
        // En la versión monosucursal, el stock está en el campo "stock" del producto
        $stockConsolidado = $producto->stock ?? 0;

        $existente = DB::table('productos')
            ->where('tenant_id', $tenantId)
            ->where('nombre', $producto->nombre)
            ->first();

        if ($existente) {
            // Actualizar stock
            DB::table('productos')
                ->where('id', $existente->id)
                ->update([
                    'stock' => $stockConsolidado,
                    'precio_de_compra' => $producto->precio_de_compra,
                    'precio_por_mayor' => $producto->precio_por_mayor,
                    'precio_por_menor' => $producto->precio_por_menor,
                    'updated_at' => now(),
                ]);
            $mapProductos[$producto->id] = $existente->id;
            echo "  - Producto '{$producto->nombre}' actualizado (Stock: {$stockConsolidado})\n";
        } else {
            $categoriaId = $mapCategorias[$producto->categoria_id] ?? null;

            $newId = DB::table('productos')->insertGetId([
                'tenant_id' => $tenantId,
                'categoria_id' => $categoriaId,
                'nombre' => $producto->nombre,
                'codigo' => $producto->codigo_barra,
                'imagen' => $producto->image,
                'medida' => $producto->medida,
                'cantidad' => $producto->cantidad, // Cantidad por empaque
                'precio_de_compra' => $producto->precio_de_compra,
                'precio_por_mayor' => $producto->precio_por_mayor,
                'precio_por_menor' => $producto->precio_por_menor,
                'stock' => $stockConsolidado, // Stock monosucursal
                'created_at' => $producto->created_at,
                'updated_at' => $producto->updated_at,
            ]);
            $mapProductos[$producto->id] = $newId;
            echo "  - Producto '{$producto->nombre}' importado (Stock: {$stockConsolidado})\n";
        }
    }

    echo "Total productos: " . count($productosFadi) . "\n\n";

    // ===== IMPORTAR VENTAS =====
    echo "4. Importando ventas...\n";
    $ventasFadi = DB::connection('fadi')->table('ventas')
        ->whereNull('deleted_at')
        ->orderBy('id')
        ->get();

    $ventasImportadas = 0;

    foreach ($ventasFadi as $venta) {
        $clienteId = $mapClientes[$venta->cliente_id] ?? null;

        if (!$clienteId) {
            echo "  - Venta ID {$venta->id}: Cliente no encontrado\n";
            continue;
        }

        // Verificar si la venta ya existe
        $existente = DB::table('ventas')
            ->where('tenant_id', $tenantId)
            ->where('created_at', $venta->created_at)
            ->where('cliente_id', $clienteId)
            ->where('total', $venta->total)
            ->first();

        if ($existente) {
            continue;
        }

        // Insertar venta
        $newVentaId = DB::table('ventas')->insertGetId([
            'tenant_id' => $tenantId,
            'cliente_id' => $clienteId,
            'total' => $venta->total,
            'descuento' => $venta->descuento ?? 0,
            'created_at' => $venta->created_at,
            'updated_at' => $venta->updated_at,
        ]);

        // Importar items de la venta
        $items = DB::connection('fadi')->table('venta_items')
            ->where('venta_id', $venta->id)
            ->get();

        foreach ($items as $item) {
            $productoId = $mapProductos[$item->producto_id] ?? null;

            if (!$productoId) {
                continue;
            }

            DB::table('venta_items')->insert([
                'venta_id' => $newVentaId,
                'producto_id' => $productoId,
                'cantidad' => $item->cantidad,
                'precio' => $item->precio,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
        }

        $ventasImportadas++;

        if ($ventasImportadas % 100 == 0) {
            echo "  - Importadas {$ventasImportadas} ventas...\n";
        }
    }

    echo "Total ventas importadas: {$ventasImportadas}\n\n";

    // ===== IMPORTAR COMPRAS =====
    echo "5. Importando compras...\n";
    $comprasFadi = DB::connection('fadi')->table('compras')
        ->whereNull('deleted_at')
        ->orderBy('id')
        ->get();

    $comprasImportadas = 0;

    foreach ($comprasFadi as $compra) {
        // Verificar si la compra ya existe
        $existente = DB::table('compras')
            ->where('tenant_id', $tenantId)
            ->where('created_at', $compra->created_at)
            ->where('total', $compra->total)
            ->first();

        if ($existente) {
            continue;
        }

        // Insertar compra
        $newCompraId = DB::table('compras')->insertGetId([
            'tenant_id' => $tenantId,
            'proveedor' => $compra->proveedor ?? 'Proveedor General',
            'total' => $compra->total,
            'created_at' => $compra->created_at,
            'updated_at' => $compra->updated_at,
        ]);

        // Importar items de la compra
        $items = DB::connection('fadi')->table('compra_items')
            ->where('compra_id', $compra->id)
            ->get();

        foreach ($items as $item) {
            $productoId = $mapProductos[$item->producto_id] ?? null;

            if (!$productoId) {
                continue;
            }

            DB::table('compra_items')->insert([
                'compra_id' => $newCompraId,
                'producto_id' => $productoId,
                'cantidad' => $item->cantidad,
                'precio_de_compra' => $item->precio_de_compra,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ]);
        }

        $comprasImportadas++;

        if ($comprasImportadas % 50 == 0) {
            echo "  - Importadas {$comprasImportadas} compras...\n";
        }
    }

    echo "Total compras importadas: {$comprasImportadas}\n\n";

    echo "=== Importación completada exitosamente ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
