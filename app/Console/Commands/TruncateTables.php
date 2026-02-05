<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:truncate
                            {tables?* : Tablas específicas a truncar (separadas por espacio)}
                            {--all : Truncar todas las tablas disponibles}
                            {--ventas : Truncar ventas y venta_items}
                            {--compras : Truncar compras y compra_items}
                            {--prestamos : Truncar prestamos y prestamo_items}
                            {--kardex : Truncar kardex}
                            {--movimientos : Truncar movimientos}
                            {--force : Forzar sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncar tablas de la base de datos (útil para desarrollo)';

    /**
     * Grupos de tablas predefinidos
     */
    protected $tableGroups = [
        'ventas' => ['venta_items', 'ventas'],
        'compras' => ['compra_items', 'compras'],
        'prestamos' => ['prestamo_items', 'prestamos'],
        'kardex' => ['kardex'],
        'movimientos' => ['movimientos'],
        'clientes' => ['clientes'],
    ];

    /**
     * Todas las tablas que se pueden truncar
     */
    protected $allowedTables = [
        'ventas',
        'venta_items',
        'compras',
        'compra_items',
        'prestamos',
        'prestamo_items',
        'kardex',
        'movimientos',
        'productos',
        'categorias',
        'clientes',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tablesToTruncate = $this->getTablesList();

        if (empty($tablesToTruncate)) {
            $this->error('No se especificaron tablas para truncar.');
            $this->line('');
            $this->info('Uso:');
            $this->line('  php artisan db:truncate --ventas      # Truncar ventas y venta_items');
            $this->line('  php artisan db:truncate --compras     # Truncar compras y compra_items');
            $this->line('  php artisan db:truncate --prestamos   # Truncar prestamos y prestamo_items');
            $this->line('  php artisan db:truncate --kardex      # Truncar kardex');
            $this->line('  php artisan db:truncate --movimientos # Truncar movimientos');
            $this->line('  php artisan db:truncate --all         # Truncar todas las anteriores');
            $this->line('  php artisan db:truncate productos clientes  # Tablas específicas');
            return 1;
        }

        // Verificar que las tablas existen
        $validTables = [];
        foreach ($tablesToTruncate as $table) {
            if (!in_array($table, $this->allowedTables)) {
                $this->warn("⚠ Tabla '{$table}' no está en la lista permitida, ignorando.");
                continue;
            }
            if (!Schema::hasTable($table)) {
                $this->warn("⚠ Tabla '{$table}' no existe, ignorando.");
                continue;
            }
            $validTables[] = $table;
        }

        if (empty($validTables)) {
            $this->error('No hay tablas válidas para truncar.');
            return 1;
        }

        // Mostrar tablas a truncar
        $this->info('Se truncarán las siguientes tablas:');
        foreach ($validTables as $table) {
            $count = DB::table($table)->count();
            $this->line("  • {$table} ({$count} registros)");
        }

        // Si es --all, mostrar aviso de reset de productos
        if ($this->option('all')) {
            $productosCount = DB::table('productos')->count();
            $this->line("  • productos: se reseteará stock y vencidos a 0 ({$productosCount} productos)");
        }

        $this->line('');

        // Confirmar si no se usa --force
        if (!$this->option('force')) {
            if (!$this->confirm('¿Estás seguro? Esta acción no se puede deshacer.', false)) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        // Ejecutar truncate
        $this->info('');
        $this->info('Truncando tablas...');

        $isAllOption = $this->option('all');

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($validTables as $table) {
                DB::table($table)->truncate();
                $this->line("  ✓ {$table} truncada");
            }

            // Si es --all, resetear stock y vencidos de productos
            if ($isAllOption) {
                DB::table('productos')->update([
                    'stock' => 0,
                    'vencidos' => 0,
                ]);
                $this->line("  ✓ productos: stock y vencidos reseteados a 0");
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('');
            $this->info('✅ ¡Tablas truncadas exitosamente!');
            return 0;

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Obtener lista de tablas a truncar según opciones
     */
    protected function getTablesList(): array
    {
        $tables = [];

        // Si se especificaron tablas como argumentos
        $argTables = $this->argument('tables');
        if (!empty($argTables)) {
            return $argTables;
        }

        // Si se usa --all
        if ($this->option('all')) {
            foreach ($this->tableGroups as $group) {
                $tables = array_merge($tables, $group);
            }
            return array_unique($tables);
        }

        // Opciones individuales
        foreach ($this->tableGroups as $option => $groupTables) {
            if ($this->option($option)) {
                $tables = array_merge($tables, $groupTables);
            }
        }

        return array_unique($tables);
    }
}
