<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportPaybolData extends Command
{
    protected $signature = 'import:paybol {--tenant=1 : ID del tenant destino}';
    protected $description = 'Importa datos desde la base de datos paybol_fadi al tenant especificado';

    protected $tenantId;
    protected $categoryMap = [];
    protected $clienteMap = [];
    protected $productoMap = [];
    protected $ventaMap = [];
    protected $compraMap = [];
    protected $prestamoMap = [];
    protected $userMap = [];

    public function handle()
    {
        $this->tenantId = $this->option('tenant');

        $this->info("===========================================");
        $this->info("  Importación de datos de PayBol FADI");
        $this->info("  Tenant destino: {$this->tenantId}");
        $this->info("===========================================\n");

        // Verificar conexión a paybol_fadi
        try {
            $testConnection = DB::connection('paybol')->select('SELECT 1');
            $this->info("✓ Conexión a paybol_fadi establecida");
        } catch (\Exception $e) {
            $this->error("✗ No se puede conectar a paybol_fadi. Asegúrate de configurar la conexión en config/database.php");
            $this->info("\nAgrega esto en config/database.php en el array 'connections':");
            $this->line("'paybol' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => 'paybol_fadi',
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
],");
            return 1;
        }

        // Truncar TODO y crear tenant FADI
        $this->limpiarTodo();
        $defaultUserId = $this->crearTenantFadi();

        DB::beginTransaction();

        try {
            $this->importarCategorias();
            $this->importarClientes();
            $this->importarProductos();
            $this->importarVentas($defaultUserId);
            $this->importarVentaItems();
            $this->importarCompras($defaultUserId);
            $this->importarCompraItems();
            $this->importarMovimientos($defaultUserId);
            $this->importarPrestamos($defaultUserId);
            $this->importarPrestamoItems();
            $this->importarKardex($defaultUserId);

            DB::commit();

            $this->newLine();
            $this->info("===========================================");
            $this->info("  ¡Importación completada exitosamente!");
            $this->info("===========================================");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error durante la importación: " . $e->getMessage());
            $this->error("Línea: " . $e->getLine());
            $this->error("Archivo: " . $e->getFile());
            return 1;
        }
    }

    protected function limpiarTodo()
    {
        $this->info("Truncando TODAS las tablas...");

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('venta_items')->truncate();
        DB::table('compra_items')->truncate();
        DB::table('prestamo_items')->truncate();
        DB::table('kardex')->truncate();
        DB::table('movimientos')->truncate();
        DB::table('ventas')->truncate();
        DB::table('compras')->truncate();
        DB::table('prestamos')->truncate();
        DB::table('productos')->truncate();
        DB::table('clientes')->truncate();
        DB::table('categorias')->truncate();
        DB::table('tenant_user')->truncate();
        DB::table('tenants')->truncate();
        DB::table('users')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info("✓ Todas las tablas truncadas\n");
    }

    protected function crearTenantFadi()
    {
        $this->info("Creando tenant FADI...");

        // Crear usuario admin
        $userId = DB::table('users')->insertGetId([
            'name' => 'Admin FADI',
            'celular' => '73010688',
            'password' => bcrypt('5421'),
            'is_super_admin' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear tenant FADI
        $this->tenantId = DB::table('tenants')->insertGetId([
            'name' => 'FADI',
            'subscription_type' => 'anual',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Asociar usuario al tenant
        DB::table('tenant_user')->insert([
            'tenant_id' => $this->tenantId,
            'user_id' => $userId,
            'role' => 'tenant',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info("✓ Tenant FADI creado (ID: {$this->tenantId})");
        $this->info("✓ Usuario admin creado (ID: {$userId})\n");

        return $userId;
    }

    protected function importarCategorias()
    {
        $this->info("Importando categorías...");

        $categorias = DB::connection('paybol')
            ->table('categorias')
            ->whereNull('deleted_at')
            ->get();

        $bar = $this->output->createProgressBar(count($categorias));
        $bar->start();

        foreach ($categorias as $cat) {
            $newId = DB::table('categorias')->insertGetId([
                'tenant_id' => $this->tenantId,
                'nombre' => $cat->nombre,
                'descripcion' => $cat->slug,
                'imagen' => $cat->image,
                'created_at' => $cat->created_at,
                'updated_at' => $cat->updated_at,
            ]);

            $this->categoryMap[$cat->id] = $newId;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ " . count($categorias) . " categorías importadas\n");
    }

    protected function importarClientes()
    {
        $this->info("Importando clientes...");

        $clientes = DB::connection('paybol')
            ->table('clientes')
            ->get();

        $bar = $this->output->createProgressBar(count($clientes));
        $bar->start();

        foreach ($clientes as $cliente) {
            $newId = DB::table('clientes')->insertGetId([
                'tenant_id' => $this->tenantId,
                'nombre' => $cliente->nombre,
                'celular' => $cliente->celular ? (string) $cliente->celular : null,
                'created_at' => $cliente->created_at,
                'updated_at' => $cliente->updated_at,
            ]);

            $this->clienteMap[$cliente->id] = $newId;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ " . count($clientes) . " clientes importados\n");
    }

    protected function importarProductos()
    {
        $this->info("Importando productos...");

        $productos = DB::connection('paybol')
            ->table('productos')
            ->whereNull('deleted_at')
            ->get();

        $bar = $this->output->createProgressBar(count($productos));
        $bar->start();

        foreach ($productos as $prod) {
            $categoriaId = isset($this->categoryMap[$prod->categoria_id])
                ? $this->categoryMap[$prod->categoria_id]
                : null;

            // Mapear medida a formato corto
            $medida = $this->mapearMedida($prod->medida);

            $newId = DB::table('productos')->insertGetId([
                'tenant_id' => $this->tenantId,
                'categoria_id' => $categoriaId,
                'nombre' => $prod->nombre,
                'codigo' => $prod->codigo_barra,
                'imagen' => $prod->image,
                'medida' => $medida,
                'cantidad' => $prod->cantidad ?? 1,
                'precio_de_compra' => $prod->precio_de_compra ?? 0,
                'precio_por_mayor' => $prod->precio_por_mayor ?? 0,
                'precio_por_menor' => $prod->precio_por_menor ?? 0,
                'stock' => $prod->cantidad ?? 0,
                'created_at' => $prod->created_at,
                'updated_at' => $prod->updated_at,
            ]);

            $this->productoMap[$prod->id] = $newId;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ " . count($productos) . " productos importados\n");
    }

    protected function mapearMedida($medida)
    {
        $mapeo = [
            'Caja' => 'CJ',
            'Paquete' => 'PQ',
            'Unidad' => 'UN',
            'Botella' => 'BT',
            'Litro' => 'LT',
            'Kilogramo' => 'KG',
        ];

        return $mapeo[$medida] ?? 'UN';
    }

    protected function importarVentas($defaultUserId)
    {
        $this->info("Importando ventas...");

        // Solo importar ventas de tienda_id = 1 (la tienda principal)
        $ventas = DB::connection('paybol')
            ->table('ventas')
            ->where('tienda_id', 1)
            ->get();

        $bar = $this->output->createProgressBar(count($ventas));
        $bar->start();

        foreach ($ventas as $venta) {
            $clienteId = isset($this->clienteMap[$venta->cliente_id])
                ? $this->clienteMap[$venta->cliente_id]
                : null;

            // Mapear estado
            $estado = $venta->estado === 'Completado' ? 'Completo' : 'Pendiente';

            $newId = DB::table('ventas')->insertGetId([
                'numero_folio' => $venta->id,
                'tenant_id' => $this->tenantId,
                'user_id' => $defaultUserId,
                'cliente_id' => $clienteId,
                'efectivo' => $venta->efectivo ?? 0,
                'online' => $venta->online ?? 0,
                'credito' => $venta->credito ?? 0,
                'cambio' => $venta->cambio ?? 0,
                'estado' => $estado,
                'created_at' => $venta->created_at,
                'updated_at' => $venta->updated_at,
            ]);

            $this->ventaMap[$venta->id] = $newId;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ " . count($ventas) . " ventas importadas\n");
    }

    protected function importarVentaItems()
    {
        $this->info("Importando items de ventas...");

        // JOIN con productos para obtener la cantidad por paquete
        $items = DB::connection('paybol')
            ->table('venta_items')
            ->join('productos', 'venta_items.producto_id', '=', 'productos.id')
            ->where('venta_items.tienda_id', 1)
            ->select('venta_items.*', 'productos.cantidad as cantidad_paquete')
            ->get();

        $bar = $this->output->createProgressBar(count($items));
        $bar->start();

        $importados = 0;
        foreach ($items as $item) {
            // Solo importar si tenemos la venta y el producto mapeados
            if (!isset($this->ventaMap[$item->venta_id]) || !isset($this->productoMap[$item->producto_id])) {
                $bar->advance();
                continue;
            }

            // Calcular cantidad: (enteros * productos.cantidad) + unidades
            $enteros = $item->enteros ?? 0;
            $unidades = $item->unidades ?? 0;
            $cantidadPaquete = $item->cantidad_paquete ?? 1;
            $cantidad = ($enteros * $cantidadPaquete) + $unidades;
            if ($cantidad <= 0) $cantidad = 1;

            DB::table('venta_items')->insert([
                'venta_id' => $this->ventaMap[$item->venta_id],
                'producto_id' => $this->productoMap[$item->producto_id],
                'cantidad' => $cantidad,
                'precio_compra' => $item->precio_compra ?? 0,
                'precio' => $item->precio ?? 0,
                'beneficio' => $item->beneficio ?? 0,
                'subtotal' => $item->subtotal ?? 0,
            ]);

            $importados++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ {$importados} items de venta importados\n");
    }

    protected function importarCompras($defaultUserId)
    {
        $this->info("Importando compras...");

        // Solo importar compras de tienda_id = 1
        $compras = DB::connection('paybol')
            ->table('compras')
            ->where('tienda_id', 1)
            ->orWhereNull('tienda_id')
            ->get();

        $bar = $this->output->createProgressBar(count($compras));
        $bar->start();

        foreach ($compras as $compra) {
            // Mapear estado
            $estado = $compra->estado === 'Completado' ? 'Completo' : 'Pendiente';

            $newId = DB::table('compras')->insertGetId([
                'numero_folio' => $compra->id,
                'tenant_id' => $this->tenantId,
                'user_id' => $defaultUserId,
                'proveedor_id' => null,
                'efectivo' => $compra->efectivo ?? 0,
                'credito' => $compra->credito ?? 0,
                'estado' => $estado,
                'created_at' => $compra->created_at,
                'updated_at' => $compra->updated_at,
            ]);

            $this->compraMap[$compra->id] = $newId;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ " . count($compras) . " compras importadas\n");
    }

    protected function importarCompraItems()
    {
        $this->info("Importando items de compras...");

        // JOIN con productos para obtener la cantidad por paquete
        $items = DB::connection('paybol')
            ->table('compra_items')
            ->join('productos', 'compra_items.producto_id', '=', 'productos.id')
            ->where(function($query) {
                $query->where('compra_items.tienda_id', 1)
                      ->orWhereNull('compra_items.tienda_id');
            })
            ->select('compra_items.*', 'productos.cantidad as cantidad_paquete')
            ->get();

        $bar = $this->output->createProgressBar(count($items));
        $bar->start();

        $importados = 0;
        foreach ($items as $item) {
            // Solo importar si tenemos la compra y el producto mapeados
            if (!isset($this->compraMap[$item->compra_id]) || !isset($this->productoMap[$item->producto_id])) {
                $bar->advance();
                continue;
            }

            // Calcular cantidad: (enteros * productos.cantidad) + unidades
            $enteros = $item->enteros ?? 0;
            $unidades = $item->unidades ?? 0;
            $cantidadPaquete = $item->cantidad_paquete ?? 1;
            $cantidad = ($enteros * $cantidadPaquete) + $unidades;
            if ($cantidad <= 0) $cantidad = 1;

            DB::table('compra_items')->insert([
                'compra_id' => $this->compraMap[$item->compra_id],
                'producto_id' => $this->productoMap[$item->producto_id],
                'cantidad' => $cantidad,
                'precio' => $item->precio ?? 0,
                'subtotal' => $item->subtotal ?? 0,
            ]);

            $importados++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ {$importados} items de compra importados\n");
    }

    protected function importarMovimientos($defaultUserId)
    {
        $this->info("Importando movimientos...");

        $movimientos = DB::connection('paybol')
            ->table('movimientos')
            ->where('tienda_id', 1)
            ->get();

        $bar = $this->output->createProgressBar(count($movimientos));
        $bar->start();

        foreach ($movimientos as $mov) {
            DB::table('movimientos')->insert([
                'tenant_id' => $this->tenantId,
                'user_id' => $defaultUserId,
                'detalle' => $mov->detalle ?? 'Sin detalle',
                'ingreso' => $mov->ingreso ?? 0,
                'egreso' => $mov->egreso ?? 0,
                'saldo' => $mov->saldo ?? 0,
                'created_at' => $mov->created_at,
                'updated_at' => $mov->updated_at,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ " . count($movimientos) . " movimientos importados\n");
    }

    protected function importarPrestamos($defaultUserId)
    {
        $this->info("Importando préstamos...");

        $prestamos = DB::connection('paybol')
            ->table('prestamos')
            ->where('tienda_id', 1)
            ->orWhereNull('tienda_id')
            ->get();

        $bar = $this->output->createProgressBar(count($prestamos));
        $bar->start();

        foreach ($prestamos as $prestamo) {
            $clienteId = isset($this->clienteMap[$prestamo->cliente_id])
                ? $this->clienteMap[$prestamo->cliente_id]
                : null;

            // Mapear estado (Caducado -> Vencido)
            $estado = $prestamo->estado;
            if ($estado === 'Caducado') {
                $estado = 'Vencido';
            }

            $newId = DB::table('prestamos')->insertGetId([
                'numero_folio' => $prestamo->id,
                'tenant_id' => $this->tenantId,
                'user_id' => $defaultUserId,
                'cliente_id' => $clienteId,
                'deposito' => $prestamo->efectivo ?? 0,
                'estado' => $estado,
                'fecha_prestamo' => $prestamo->created_at ? date('Y-m-d', strtotime($prestamo->created_at)) : null,
                'fecha_vencimiento' => $prestamo->expired_at ? date('Y-m-d', strtotime($prestamo->expired_at)) : null,
                'fecha_devolucion' => $prestamo->estado === 'Devuelto' ? date('Y-m-d', strtotime($prestamo->updated_at)) : null,
                'created_at' => $prestamo->created_at,
                'updated_at' => $prestamo->updated_at,
            ]);

            $this->prestamoMap[$prestamo->id] = $newId;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ " . count($prestamos) . " préstamos importados\n");
    }

    protected function importarPrestamoItems()
    {
        $this->info("Importando items de préstamos...");

        $items = DB::connection('paybol')
            ->table('prestamo_items')
            ->get();

        $bar = $this->output->createProgressBar(count($items));
        $bar->start();

        $importados = 0;
        foreach ($items as $item) {
            // Solo importar si tenemos el préstamo y el producto mapeados
            if (!isset($this->prestamoMap[$item->prestamo_id]) || !isset($this->productoMap[$item->producto_id])) {
                $bar->advance();
                continue;
            }

            DB::table('prestamo_items')->insert([
                'prestamo_id' => $this->prestamoMap[$item->prestamo_id],
                'producto_id' => $this->productoMap[$item->producto_id],
                'cantidad' => $item->cantidad ?? 1,
                'precio' => $item->precio ?? 0,
                'subtotal' => $item->subtotal ?? 0,
            ]);

            $importados++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ {$importados} items de préstamo importados\n");
    }

    protected function importarKardex($defaultUserId)
    {
        $this->info("Importando kardex...");

        // En paybol_fadi la tabla se llama 'kardexes'
        $kardexes = DB::connection('paybol')
            ->table('kardexes')
            ->where('tienda_id', 1)
            ->get();

        $bar = $this->output->createProgressBar(count($kardexes));
        $bar->start();

        $importados = 0;
        foreach ($kardexes as $k) {
            // Solo importar si tenemos el producto mapeado
            if (!isset($this->productoMap[$k->producto_id])) {
                $bar->advance();
                continue;
            }

            DB::table('kardex')->insert([
                'tenant_id' => $this->tenantId,
                'user_id' => $defaultUserId,
                'producto_id' => $this->productoMap[$k->producto_id],
                'entrada' => $k->entrada ?? 0,
                'salida' => $k->salida ?? 0,
                'anterior' => $k->anterior ?? 0,
                'saldo' => $k->saldo ?? 0,
                'precio' => $k->precio ?? 0,
                'total' => $k->total ?? 0,
                'obs' => $k->obs,
                'created_at' => $k->created_at,
                'updated_at' => $k->updated_at,
            ]);

            $importados++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ {$importados} registros de kardex importados\n");
    }
}
