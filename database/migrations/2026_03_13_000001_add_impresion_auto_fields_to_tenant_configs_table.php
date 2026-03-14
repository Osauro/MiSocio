<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->boolean('impresion_auto_venta')->default(false)->after('ancho_caracteres');
            $table->boolean('impresion_auto_prestamo')->default(false)->after('impresion_auto_venta');
            $table->boolean('impresion_auto_inventario')->default(false)->after('impresion_auto_prestamo');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->dropColumn(['impresion_auto_venta', 'impresion_auto_prestamo', 'impresion_auto_inventario']);
        });
    }
};
