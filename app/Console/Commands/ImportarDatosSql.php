<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportarDatosSql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:importar-datos {--archivo=paybol_fadi.sql} {--force : Forzar importación sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Borra y recarga las tablas de productos, categorias y medidas desde el archivo SQL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $archivo = $this->option('archivo');
        $rutaArchivo = base_path($archivo);

        // Verificar que el archivo existe
        if (!File::exists($rutaArchivo)) {
            $this->error("El archivo {$archivo} no existe en la ruta: {$rutaArchivo}");
            return 1;
        }

        $this->info("Iniciando proceso de importación...");

        try {
            // Confirmar con el usuario
            if (!$this->option('force') && !$this->confirm('¿Estás seguro de que quieres borrar y recargar las tablas? Esta acción no se puede deshacer.')) {
                $this->info('Operación cancelada.');
                return 0;
            }

            // Obtener todos los tenants
            $tenants = DB::table('tenants')->get();

            if ($tenants->isEmpty()) {
                $this->error('No hay tenants en la base de datos.');
                return 1;
            }

            $this->info("Se encontraron {$tenants->count()} tenant(s)");

            // 1. Borrar datos de las tablas (en orden por las relaciones de claves foráneas)
            $this->info('Limpiando tablas...');

            // Desactivar verificación de claves foráneas temporalmente
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $this->line('  - Vaciando items de ventas...');
            DB::table('venta_items')->truncate();

            $this->line('  - Vaciando ventas...');
            DB::table('ventas')->truncate();

            $this->line('  - Vaciando items de compras...');
            DB::table('compra_items')->truncate();

            $this->line('  - Vaciando compras...');
            DB::table('compras')->truncate();

            $this->line('  - Vaciando movimientos...');
            DB::table('movimientos')->truncate();

            $this->line('  - Vaciando productos...');
            DB::table('productos')->truncate();

            $this->line('  - Vaciando categorías...');
            DB::table('categorias')->truncate();

            $this->line('  - Vaciando medidas...');
            DB::table('medidas')->truncate();

            // Reactivar verificación de claves foráneas
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // 2. Leer el archivo SQL
            $this->info('Leyendo archivo SQL...');
            $contenidoSql = File::get($rutaArchivo);

            // 3. Extraer las inserciones para cada tabla y cada tenant
            $this->info('Importando datos para cada tenant...');

            $totalMedidas = 0;
            $totalCategorias = 0;
            $totalProductos = 0;

            foreach ($tenants as $tenant) {
                $this->line("  - Importando para tenant: {$tenant->name}");
                // Extraer inserciones de medidas
                $medidas = $this->extraerInserts($contenidoSql, 'medidas', $tenant->id);
                $totalMedidas += $medidas;

                // Extraer inserciones de categorías
                $categorias = $this->extraerInserts($contenidoSql, 'categorias', $tenant->id);
                $totalCategorias += $categorias;

                // Extraer inserciones de productos
                $productos = $this->extraerInserts($contenidoSql, 'productos', $tenant->id);
                $totalProductos += $productos;
            }

            $this->newLine();
            $this->info('✓ Importación completada exitosamente!');
            $this->table(
                ['Tabla', 'Registros importados'],
                [
                    ['Medidas', $totalMedidas],
                    ['Categorías', $totalCategorias],
                    ['Productos', $totalProductos],
                ]
            );

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error durante la importación: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Extrae y ejecuta las inserciones de una tabla específica
     */
    private function extraerInserts($contenidoSql, $tabla, $tenantId)
    {
        // Mapeo de columnas del SQL a las columnas reales de la base de datos
        $mapeoColumnas = [
            'medidas' => [
                'sql' => ['id', 'nombre'],
                'db' => ['id', 'nombre']
            ],
            'categorias' => [
                'sql' => ['id', 'nombre', 'slug', 'image', 'created_at', 'updated_at', 'deleted_at'],
                'db' => ['id', 'nombre', 'descripcion', 'imagen']
            ],
            'productos' => [
                'sql' => ['id', 'categoria_id', 'nombre', 'codigo_barra', 'medida', 'image', 'cantidad',
                         'precio_de_compra', 'precio_por_mayor', 'precio_por_menor', 'precio_de_oferta',
                         'fecha_fin_oferta', 'vencidos', 'fecha_vencimiento', 'stock_minimo', 'stock_control',
                         'control', 'created_at', 'updated_at', 'deleted_at', 'tags_busqueda'],
                'db' => ['id', 'categoria_id', 'nombre', 'codigo_barra', 'medida', 'imagen', 'cantidad',
                        'precio_de_compra', 'precio_por_mayor', 'precio_por_menor']
            ]
        ];

        // Buscar el INSERT de la tabla
        $patron = "/INSERT INTO `{$tabla}` \(`id`,\s*([^)]+)\) VALUES\s*\(([^;]+)\);/is";

        if (preg_match($patron, $contenidoSql, $matches)) {
            $valores = $matches[2];
            $columnasDb = $mapeoColumnas[$tabla]['db'];

            // Procesar los valores línea por línea
            $lineasValores = preg_split('/\),\s*\(/s', trim($valores));
            $registrosImportados = 0;

            foreach ($lineasValores as $linea) {
                $linea = trim($linea, '()');
                $campos = $this->parsearCampos($linea);

                // Construir el array de datos según la tabla
                $datos = ['tenant_id' => $tenantId];

                if ($tabla === 'medidas') {
                    $datos['id'] = $campos[0];
                    $datos['nombre'] = $campos[1];
                } elseif ($tabla === 'categorias') {
                    $datos['id'] = $campos[0];
                    $datos['nombre'] = $campos[1];
                    $datos['descripcion'] = null;
                    $datos['imagen'] = $campos[3] !== 'NULL' ? $campos[3] : null;
                } elseif ($tabla === 'productos') {
                    $datos['id'] = $campos[0];
                    $datos['categoria_id'] = $campos[1];
                    $datos['nombre'] = $campos[2];
                    $datos['codigo'] = $campos[3] !== 'NULL' ? $campos[3] : null;
                    $datos['medida'] = $campos[4];
                    $datos['imagen'] = $campos[5] !== 'NULL' ? $campos[5] : null;
                    $datos['cantidad'] = $campos[6]; // Cantidad del SQL
                    $datos['precio_de_compra'] = $campos[7];
                    $datos['precio_por_mayor'] = $campos[8];
                    $datos['precio_por_menor'] = $campos[9];
                    $datos['stock'] = 0; // Stock inicial en 0
                }

                try {
                    DB::table($tabla)->insert($datos);
                    $registrosImportados++;
                } catch (\Exception $e) {
                    // Si falla por duplicado, ignorar y continuar
                    if (!str_contains($e->getMessage(), 'Duplicate entry')) {
                        throw $e;
                    }
                }
            }

            return $registrosImportados;
        }

        return 0;
    }

    /**
     * Parsea una línea de valores SQL manejando strings entre comillas
     */
    private function parsearCampos($linea)
    {
        $campos = [];
        $buffer = '';
        $dentroComillas = false;
        $caracterComilla = '';

        for ($i = 0; $i < strlen($linea); $i++) {
            $char = $linea[$i];

            if (($char === "'" || $char === '"') && ($i === 0 || $linea[$i - 1] !== '\\')) {
                if (!$dentroComillas) {
                    $dentroComillas = true;
                    $caracterComilla = $char;
                } elseif ($char === $caracterComilla) {
                    $dentroComillas = false;
                }
                $buffer .= $char;
            } elseif ($char === ',' && !$dentroComillas) {
                $campos[] = trim($buffer);
                $buffer = '';
            } else {
                $buffer .= $char;
            }
        }

        if ($buffer !== '') {
            $campos[] = trim($buffer);
        }

        // Limpiar comillas de los valores
        return array_map(function ($campo) {
            $campo = trim($campo);
            if ($campo === 'NULL') {
                return 'NULL';
            }
            if ((str_starts_with($campo, "'") && str_ends_with($campo, "'")) ||
                (str_starts_with($campo, '"') && str_ends_with($campo, '"'))) {
                return substr($campo, 1, -1);
            }
            return $campo;
        }, $campos);
    }
}
