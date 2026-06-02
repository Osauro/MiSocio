<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Medida;
use App\Models\Membresia;
use App\Models\Producto;
use App\Models\Tenant;
use App\Models\TenantConfig;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ──────────────────────────────────────────────────────────
        // 1. LANDLORD ADMIN
        // ──────────────────────────────────────────────────────────
        $landlord = User::create([
            'name'           => 'Administrador',
            'celular'        => '70000000',
            'password'       => Hash::make('1234'),
            'is_super_admin' => true,
        ]);

        // ──────────────────────────────────────────────────────────
        // 2. USUARIOS ADMIN (dueños de tiendas)
        // ──────────────────────────────────────────────────────────
        $admin1 = User::create([
            'name'     => 'Carlos Mamani',
            'celular'  => '71000001',
            'password' => Hash::make('1234'),
        ]);

        $admin2 = User::create([
            'name'     => 'Rosa Quispe',
            'celular'  => '72000002',
            'password' => Hash::make('1234'),
        ]);

        $admin3 = User::create([
            'name'     => 'Jorge Condori',
            'celular'  => '73000003',
            'password' => Hash::make('1234'),
        ]);

        // ──────────────────────────────────────────────────────────
        // 3. TIENDAS (tenants)
        //    Admin 1 → 2 tiendas
        //    Admin 2 → 2 tiendas
        //    Admin 3 → 1 tienda
        // ──────────────────────────────────────────────────────────
        $tiendasConfig = [
            [
                'tenant' => [
                    'name'              => 'Tienda Don Carlos',
                    'domain'            => 'doncarlos',
                    'subscription_type' => 'anual',
                    'amount'            => 600.00,
                    'bill_date'         => Carbon::now()->addYear(),
                    'theme_number'      => 1,
                    'status'            => 1,
                ],
                'config' => ['nombre_tienda' => 'Tienda Don Carlos', 'direccion' => 'Av. Camacho #123, Centro'],
                'admin'  => $admin1,
            ],
            [
                'tenant' => [
                    'name'              => 'Minimarket El Valle',
                    'domain'            => 'elvalle',
                    'subscription_type' => 'anual',
                    'amount'            => 600.00,
                    'bill_date'         => Carbon::now()->addYear(),
                    'theme_number'      => 2,
                    'status'            => 1,
                ],
                'config' => ['nombre_tienda' => 'Minimarket El Valle', 'direccion' => 'Calle Potosí #456, Sopocachi'],
                'admin'  => $admin1,
            ],
            [
                'tenant' => [
                    'name'              => 'Abarrotes La Esperanza',
                    'domain'            => 'laesperanza',
                    'subscription_type' => 'mensual',
                    'amount'            => 120.00,
                    'bill_date'         => Carbon::now()->addMonths(3),
                    'theme_number'      => 3,
                    'status'            => 1,
                ],
                'config' => ['nombre_tienda' => 'Abarrotes La Esperanza', 'direccion' => 'Av. Buenos Aires #789, Miraflores'],
                'admin'  => $admin2,
            ],
            [
                'tenant' => [
                    'name'              => 'Distribuidora Norte',
                    'domain'            => 'distnorte',
                    'subscription_type' => 'mensual',
                    'amount'            => 120.00,
                    'bill_date'         => Carbon::now()->addMonths(6),
                    'theme_number'      => 4,
                    'status'            => 1,
                ],
                'config' => ['nombre_tienda' => 'Distribuidora Norte', 'direccion' => 'Calle Comercio #321, El Alto'],
                'admin'  => $admin2,
            ],
            [
                'tenant' => [
                    'name'              => 'Supermercado Central',
                    'domain'            => 'supercentral',
                    'subscription_type' => 'anual',
                    'amount'            => 600.00,
                    'bill_date'         => Carbon::now()->addYear(),
                    'theme_number'      => 5,
                    'status'            => 1,
                ],
                'config' => ['nombre_tienda' => 'Supermercado Central', 'direccion' => 'Av. 16 de Julio #654, Centro'],
                'admin'  => $admin3,
            ],
        ];

        $tenantsCreados = [];

        foreach ($tiendasConfig as $index => $data) {
            $tenant = Tenant::create($data['tenant']);
            $tenantsCreados[] = $tenant;

            // Config básica del negocio
            TenantConfig::create(array_merge(
                ['tenant_id' => $tenant->id],
                $data['config']
            ));

            // Membresía activa
            Membresia::withoutGlobalScopes()->create([
                'tenant_id'       => $tenant->id,
                'plan_nombre'     => $data['tenant']['subscription_type'] === 'anual' ? 'Plan Anual' : 'Plan Mensual',
                'duracion_meses'  => $data['tenant']['subscription_type'] === 'anual' ? 12 : 1,
                'fecha_inicio'    => Carbon::now(),
                'fecha_fin'       => $data['tenant']['bill_date'],
                'monto'           => $data['tenant']['amount'],
                'estado_pago'     => 'verificado',
            ]);

            // Vincular admin al tenant
            DB::table('tenant_user')->insert([
                'tenant_id'  => $tenant->id,
                'user_id'    => $data['admin']->id,
                'role'       => 'tenant',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ── 3 OPERADORES por tienda ────────────────────────────
            $base = ($index + 1) * 10;
            for ($op = 1; $op <= 3; $op++) {
                $operador = User::create([
                    'name'     => $this->nombreOperador($index, $op),
                    'celular'  => '8' . str_pad(($base * 1000000) + $op, 7, '0', STR_PAD_LEFT),
                    'password' => Hash::make('1234'),
                ]);

                DB::table('tenant_user')->insert([
                    'tenant_id'  => $tenant->id,
                    'user_id'    => $operador->id,
                    'role'       => 'user',
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ── 5 CATEGORÍAS ──────────────────────────────────────
            $categorias = $this->categoriasParaTienda($index);
            $categoriasCreadas = [];
            foreach ($categorias as $nombreCat) {
                $categoriasCreadas[] = Categoria::create([
                    'tenant_id' => $tenant->id,
                    'nombre'    => $nombreCat,
                ]);
            }

            // ── MEDIDAS ───────────────────────────────────────────
            $medidasNombres = ['unidad', 'kg', 'litro', 'caja', 'bolsa', 'paquete', 'botella'];
            foreach ($medidasNombres as $m) {
                Medida::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'nombre'    => $m,
                ]);
            }

            // ── 10-20 PRODUCTOS ───────────────────────────────────
            $productos = $this->productosParaTienda($index, $categoriasCreadas, $tenant->id);
            foreach ($productos as $prod) {
                Producto::withoutGlobalScopes()->create(array_merge(
                    $prod,
                    ['tenant_id' => $tenant->id]
                ));
            }

            // ── CLIENTES ──────────────────────────────────────────
            // Cliente SN (ventas rápidas)
            Cliente::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'nombre'    => 'SN',
                'celular'   => 'SN',
                'direccion' => 'SN',
                'nit'       => 'SN',
            ]);

            // 10-20 clientes regulares
            $cantidad = rand(10, 20);
            for ($c = 0; $c < $cantidad; $c++) {
                Cliente::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'nombre'    => $this->nombreCliente(),
                    'celular'   => '7' . rand(1000000, 9999999),
                    'ci'        => (string) rand(1000000, 9999999),
                    'direccion' => $this->direccionAleatoria(),
                    'nit'       => rand(0, 1) ? (string) rand(10000000, 99999999) : null,
                    'correo'    => rand(0, 1) ? strtolower($this->primerNombre()) . rand(10, 99) . '@gmail.com' : null,
                ]);
            }

            $this->command->info("✓ Tienda {$tenant->name} creada ({$cantidad} clientes, " . count($productos) . ' productos)');
        }

        // ──────────────────────────────────────────────────────────
        // VINCULAR LANDLORD A 2 TIENDAS
        // (para que aparezcan "Cambiar tienda" y "Cambiar modo")
        // ──────────────────────────────────────────────────────────
        $tiendasLandlord = [$tenantsCreados[0], $tenantsCreados[4]];
        foreach ($tiendasLandlord as $t) {
            DB::table('tenant_user')->insert([
                'tenant_id'  => $t->id,
                'user_id'    => $landlord->id,
                'role'       => 'tenant',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->command->info("✓ Landlord vinculado a: {$tenantsCreados[0]->name} y {$tenantsCreados[4]->name}");

        $this->command->info('');
        $this->command->info('────────────────────────────────────────────────');
        $this->command->info('Credenciales de acceso:');
        $this->command->info('  Landlord  → 70000000 / 1234  (2 tiendas + modo admin)');
        $this->command->info('  Admin 1   → 71000001 / 1234  (2 tiendas)');
        $this->command->info('  Admin 2   → 72000002 / 1234  (2 tiendas)');
        $this->command->info('  Admin 3   → 73000003 / 1234  (1 tienda)');
        $this->command->info('  Operadores→ 8x0000001 a 8x0000003 / 1234');
        $this->command->info('────────────────────────────────────────────────');
        $this->command->info('  Landlord ve: cambiar tienda (2 tiendas) + cambiar modo');
        $this->command->info('  Admin 1/2  ver: cambiar tienda (2 tiendas cada uno)');
        $this->command->info('────────────────────────────────────────────────');
    }

    // ──────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────

    private function nombreOperador(int $tienda, int $num): string
    {
        $nombres = [
            ['Ana López', 'Pedro Flores', 'Lucía Vargas'],
            ['Roberto Silva', 'Carmen Díaz', 'Miguel Torres'],
            ['Sofía Ríos', 'Antonio Cruz', 'Patricia Ruiz'],
            ['Diego Morales', 'Elena Soto', 'Andrés Reyes'],
            ['Natalia Vega', 'Ernesto Lima', 'Claudia Ponce'],
        ];
        return $nombres[$tienda][$num - 1];
    }

    private function categoriasParaTienda(int $index): array
    {
        $sets = [
            ['Abarrotes', 'Bebidas', 'Lácteos', 'Limpieza', 'Snacks'],
            ['Abarrotes', 'Bebidas', 'Congelados', 'Higiene Personal', 'Dulces'],
            ['Abarrotes', 'Bebidas', 'Panadería', 'Carnes', 'Limpieza'],
            ['Abarrotes', 'Bebidas', 'Lácteos', 'Embutidos', 'Aceites y Grasas'],
            ['Abarrotes', 'Bebidas', 'Frutas y Verduras', 'Lácteos', 'Limpieza'],
        ];
        return $sets[$index];
    }

    private function productosParaTienda(int $tienda, array $categorias, int $tenantId): array
    {
        // Índice: cat[0]=Abarrotes, cat[1]=Bebidas, cat[2]=..., cat[3]=..., cat[4]=...
        $catIds = array_map(fn($c) => $c->id, $categorias);

        $base = [
            // Abarrotes
            ['categoria_id' => $catIds[0], 'nombre' => 'Arroz 1kg',            'codigo' => 'ARR001', 'medida' => 'kg',      'cantidad' => 1,  'precio_de_compra' => 8.00,  'precio_por_mayor' => 10.00, 'precio_por_menor' => 12.00, 'stock' => 80],
            ['categoria_id' => $catIds[0], 'nombre' => 'Azúcar 1kg',           'codigo' => 'AZU001', 'medida' => 'kg',      'cantidad' => 1,  'precio_de_compra' => 7.00,  'precio_por_mayor' => 9.00,  'precio_por_menor' => 10.00, 'stock' => 60],
            ['categoria_id' => $catIds[0], 'nombre' => 'Aceite 1L',            'codigo' => 'ACE001', 'medida' => 'litro',   'cantidad' => 1,  'precio_de_compra' => 12.00, 'precio_por_mayor' => 15.00, 'precio_por_menor' => 18.00, 'stock' => 50],
            ['categoria_id' => $catIds[0], 'nombre' => 'Fideos 500g',          'codigo' => 'FID001', 'medida' => 'paquete', 'cantidad' => 1,  'precio_de_compra' => 5.00,  'precio_por_mayor' => 6.50,  'precio_por_menor' => 8.00,  'stock' => 100],
            ['categoria_id' => $catIds[0], 'nombre' => 'Harina 1kg',           'codigo' => 'HAR001', 'medida' => 'kg',      'cantidad' => 1,  'precio_de_compra' => 6.00,  'precio_por_mayor' => 8.00,  'precio_por_menor' => 10.00, 'stock' => 70],
            ['categoria_id' => $catIds[0], 'nombre' => 'Sal 1kg',              'codigo' => 'SAL001', 'medida' => 'kg',      'cantidad' => 1,  'precio_de_compra' => 3.00,  'precio_por_mayor' => 4.00,  'precio_por_menor' => 5.00,  'stock' => 90],
            // Bebidas
            ['categoria_id' => $catIds[1], 'nombre' => 'Agua Mineral 2L',      'codigo' => 'AGU001', 'medida' => 'botella', 'cantidad' => 1,  'precio_de_compra' => 5.00,  'precio_por_mayor' => 6.50,  'precio_por_menor' => 8.00,  'stock' => 120],
            ['categoria_id' => $catIds[1], 'nombre' => 'Coca Cola 2L',         'codigo' => 'COC001', 'medida' => 'botella', 'cantidad' => 1,  'precio_de_compra' => 9.00,  'precio_por_mayor' => 11.00, 'precio_por_menor' => 14.00, 'stock' => 80],
            ['categoria_id' => $catIds[1], 'nombre' => 'Jugo de Naranja 1L',   'codigo' => 'JUG001', 'medida' => 'botella', 'cantidad' => 1,  'precio_de_compra' => 7.00,  'precio_por_mayor' => 9.00,  'precio_por_menor' => 12.00, 'stock' => 60],
            ['categoria_id' => $catIds[1], 'nombre' => 'Té Helado 500ml',      'codigo' => 'TEH001', 'medida' => 'botella', 'cantidad' => 1,  'precio_de_compra' => 4.00,  'precio_por_mayor' => 5.50,  'precio_por_menor' => 7.00,  'stock' => 90],
            // Categoría 3
            ['categoria_id' => $catIds[2], 'nombre' => 'Leche 1L',             'codigo' => 'LEC001', 'medida' => 'litro',   'cantidad' => 1,  'precio_de_compra' => 7.00,  'precio_por_mayor' => 8.50,  'precio_por_menor' => 10.00, 'stock' => 50],
            ['categoria_id' => $catIds[2], 'nombre' => 'Yogur Frutado 500ml',  'codigo' => 'YOG001', 'medida' => 'unidad',  'cantidad' => 1,  'precio_de_compra' => 8.00,  'precio_por_mayor' => 10.00, 'precio_por_menor' => 12.00, 'stock' => 40],
            ['categoria_id' => $catIds[2], 'nombre' => 'Queso Fresco 250g',    'codigo' => 'QUE001', 'medida' => 'unidad',  'cantidad' => 1,  'precio_de_compra' => 10.00, 'precio_por_mayor' => 13.00, 'precio_por_menor' => 15.00, 'stock' => 30],
            // Categoría 4
            ['categoria_id' => $catIds[3], 'nombre' => 'Jabón Barra x3',       'codigo' => 'JAB001', 'medida' => 'paquete', 'cantidad' => 3,  'precio_de_compra' => 8.00,  'precio_por_mayor' => 10.00, 'precio_por_menor' => 13.00, 'stock' => 45],
            ['categoria_id' => $catIds[3], 'nombre' => 'Detergente 500g',      'codigo' => 'DET001', 'medida' => 'bolsa',   'cantidad' => 1,  'precio_de_compra' => 9.00,  'precio_por_mayor' => 12.00, 'precio_por_menor' => 15.00, 'stock' => 55],
            ['categoria_id' => $catIds[3], 'nombre' => 'Suavizante 1L',        'codigo' => 'SUA001', 'medida' => 'litro',   'cantidad' => 1,  'precio_de_compra' => 10.00, 'precio_por_mayor' => 13.00, 'precio_por_menor' => 16.00, 'stock' => 35],
            // Categoría 5
            ['categoria_id' => $catIds[4], 'nombre' => 'Galletas Oreo 120g',   'codigo' => 'GAL001', 'medida' => 'unidad',  'cantidad' => 1,  'precio_de_compra' => 5.00,  'precio_por_mayor' => 6.50,  'precio_por_menor' => 8.00,  'stock' => 70],
            ['categoria_id' => $catIds[4], 'nombre' => 'Papas Fritas 100g',    'codigo' => 'PAP001', 'medida' => 'unidad',  'cantidad' => 1,  'precio_de_compra' => 4.00,  'precio_por_mayor' => 5.00,  'precio_por_menor' => 7.00,  'stock' => 80],
            ['categoria_id' => $catIds[4], 'nombre' => 'Chocolatina 50g',      'codigo' => 'CHO001', 'medida' => 'unidad',  'cantidad' => 1,  'precio_de_compra' => 3.00,  'precio_por_mayor' => 4.00,  'precio_por_menor' => 6.00,  'stock' => 100],
            ['categoria_id' => $catIds[4], 'nombre' => 'Maíz Tostado 200g',    'codigo' => 'MAI001', 'medida' => 'bolsa',   'cantidad' => 1,  'precio_de_compra' => 4.00,  'precio_por_mayor' => 5.50,  'precio_por_menor' => 7.00,  'stock' => 60],
        ];

        // Agregar sufijo de tienda a código para evitar conflictos globales (aunque son por tenant, los códigos son únicos por tenant)
        // y variar el stock levemente por tienda
        $factor = 1 + ($tienda * 0.05);
        foreach ($base as &$p) {
            $p['codigo'] = $p['codigo'] . '_T' . ($tienda + 1);
            $p['stock']  = (int) round($p['stock'] * $factor);
        }
        unset($p);

        // Devolver entre 10 y 20 productos (mezcla aleatoria del pool de 20)
        shuffle($base);
        $cantidad = rand(10, 20);
        return array_slice($base, 0, min($cantidad, count($base)));
    }

    private array $nombresPool = [
        'Juan García', 'María López', 'Pedro Mamani', 'Ana Condori', 'Carlos Quispe',
        'Lucía Torres', 'José Flores', 'Carmen Rojas', 'Luis Pérez', 'Rosa Sánchez',
        'Miguel Vargas', 'Elena Cruz', 'Jorge Rivera', 'Patricia Morales', 'Fernando Díaz',
        'Isabel Reyes', 'Roberto Silva', 'Sofía Castillo', 'Ricardo Mendoza', 'Laura Vega',
        'Diego Ortiz', 'Claudia Pinto', 'Andrés Salazar', 'Gabriela Ramos', 'Raúl Aguilar',
        'Daniela Guerrero', 'Alberto Fuentes', 'Valentina Herrera', 'Javier Cabrera', 'Camila Espinoza',
    ];

    private int $nombreIndex = 0;

    private function nombreCliente(): string
    {
        $nombre = $this->nombresPool[$this->nombreIndex % count($this->nombresPool)];
        $this->nombreIndex++;
        return $nombre;
    }

    private function primerNombre(): string
    {
        $partes = explode(' ', $this->nombresPool[$this->nombreIndex % count($this->nombresPool)]);
        return strtolower($partes[0]);
    }

    private function direccionAleatoria(): string
    {
        $calles = ['Av. Camacho', 'Calle Potosí', 'Av. Buenos Aires', 'Calle Sagárnaga', 'Av. 6 de Agosto', 'Av. Arce', 'Calle Loayza', 'Av. Ballivián'];
        $zonas  = ['Centro', 'Sopocachi', 'Miraflores', 'San Pedro', 'Calacoto', 'Obrajes', 'El Alto', 'Zona Sur'];
        return $calles[array_rand($calles)] . ' #' . rand(100, 9999) . ', ' . $zonas[array_rand($zonas)];
    }
}
