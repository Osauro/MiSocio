<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar columna solo si no existe
        if (!Schema::hasColumn('prestamos', 'fecha_vencimiento')) {
            Schema::table('prestamos', function (Blueprint $table) {
                $table->date('fecha_vencimiento')->nullable()->after('fecha_prestamo');
            });
        }

        // Primero actualizar estados existentes antes de cambiar el enum
        // Completo -> Prestado, Eliminado -> Devuelto
        DB::table('prestamos')->where('estado', 'Completo')->update(['estado' => 'Pendiente']);
        DB::table('prestamos')->where('estado', 'Eliminado')->update(['estado' => 'Devuelto']);

        // Cambiar el enum de estado
        DB::statement("ALTER TABLE prestamos MODIFY COLUMN estado ENUM('Pendiente', 'Prestado', 'Devuelto', 'Vencido') DEFAULT 'Pendiente'");

        // Calcular fecha_vencimiento para préstamos existentes (fecha_prestamo + 7 días)
        DB::statement("UPDATE prestamos SET fecha_vencimiento = DATE_ADD(fecha_prestamo, INTERVAL 7 DAY) WHERE fecha_prestamo IS NOT NULL AND fecha_vencimiento IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropColumn('fecha_vencimiento');
        });

        DB::statement("ALTER TABLE prestamos MODIFY COLUMN estado ENUM('Pendiente', 'Completo', 'Devuelto', 'Eliminado') DEFAULT 'Pendiente'");
        DB::table('prestamos')->where('estado', 'Prestado')->update(['estado' => 'Completo']);
    }
};
