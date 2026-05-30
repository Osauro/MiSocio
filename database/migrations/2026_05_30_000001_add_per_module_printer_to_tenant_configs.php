<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            // Ventas
            $table->string('impresora_ventas')->nullable()->after('impresora_nombre');
            $table->string('papel_tamano_ventas', 20)->default('80mm')->after('impresora_ventas');
            // Préstamos
            $table->string('impresora_prestamos')->nullable()->after('papel_tamano_ventas');
            $table->string('papel_tamano_prestamos', 20)->default('80mm')->after('impresora_prestamos');
            // Inventario
            $table->string('impresora_inventario')->nullable()->after('papel_tamano_prestamos');
            $table->string('papel_tamano_inventario', 20)->default('80mm')->after('impresora_inventario');
            // Compras
            $table->string('impresora_compras')->nullable()->after('papel_tamano_inventario');
            $table->string('papel_tamano_compras', 20)->default('80mm')->after('impresora_compras');
            $table->boolean('impresion_auto_compra')->default(false)->after('impresion_auto_inventario');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->dropColumn([
                'impresora_ventas', 'papel_tamano_ventas',
                'impresora_prestamos', 'papel_tamano_prestamos',
                'impresora_inventario', 'papel_tamano_inventario',
                'impresora_compras', 'papel_tamano_compras',
                'impresion_auto_compra',
            ]);
        });
    }
};
