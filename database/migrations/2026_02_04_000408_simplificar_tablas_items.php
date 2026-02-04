<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Simplificar prestamo_items
        Schema::table('prestamo_items', function (Blueprint $table) {
            // Eliminar columnas innecesarias
            $table->dropColumn(['cantidad_devuelta', 'precio_deposito', 'subtotal_deposito']);

            // Agregar columnas faltantes
            $table->decimal('precio', 10, 2)->after('cantidad');
            $table->decimal('subtotal', 10, 2)->after('precio');
        });

        // Eliminar timestamps de prestamo_items
        Schema::table('prestamo_items', function (Blueprint $table) {
            $table->dropTimestamps();
        });

        // Eliminar timestamps de venta_items
        Schema::table('venta_items', function (Blueprint $table) {
            $table->dropTimestamps();
        });

        // Eliminar timestamps de compra_items
        Schema::table('compra_items', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar prestamo_items
        Schema::table('prestamo_items', function (Blueprint $table) {
            $table->integer('cantidad_devuelta')->default(0);
            $table->decimal('precio_deposito', 10, 2)->default(0);
            $table->decimal('subtotal_deposito', 10, 2)->default(0);
            $table->dropColumn(['precio', 'subtotal']);
        });

        // Restaurar timestamps
        Schema::table('prestamo_items', function (Blueprint $table) {
            $table->timestamps();
        });

        Schema::table('venta_items', function (Blueprint $table) {
            $table->timestamps();
        });

        Schema::table('compra_items', function (Blueprint $table) {
            $table->timestamps();
        });
    }
};
