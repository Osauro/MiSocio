<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * - fecha_prestamo  → eliminada (se usa created_at)
     * - fecha_vencimiento → renombrada a expired_at
     * - fecha_devolucion  → eliminada (se usa updated_at cuando estado = 'Devuelto')
     */
    public function up(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            // Renombrar fecha_vencimiento → expired_at
            if (Schema::hasColumn('prestamos', 'fecha_vencimiento')) {
                $table->renameColumn('fecha_vencimiento', 'expired_at');
            }

            // Eliminar fecha_prestamo (se usa created_at)
            if (Schema::hasColumn('prestamos', 'fecha_prestamo')) {
                $table->dropColumn('fecha_prestamo');
            }

            // Eliminar fecha_devolucion (se usa updated_at cuando estado = 'Devuelto')
            if (Schema::hasColumn('prestamos', 'fecha_devolucion')) {
                $table->dropColumn('fecha_devolucion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            // Revertir: renombrar expired_at → fecha_vencimiento
            if (Schema::hasColumn('prestamos', 'expired_at')) {
                $table->renameColumn('expired_at', 'fecha_vencimiento');
            }

            // Restaurar columnas eliminadas
            if (!Schema::hasColumn('prestamos', 'fecha_prestamo')) {
                $table->date('fecha_prestamo')->nullable()->after('deposito');
            }

            if (!Schema::hasColumn('prestamos', 'fecha_devolucion')) {
                $table->date('fecha_devolucion')->nullable()->after('expired_at');
            }
        });
    }
};
