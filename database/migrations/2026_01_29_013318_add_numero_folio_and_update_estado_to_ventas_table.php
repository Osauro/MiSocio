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
        Schema::table('ventas', function (Blueprint $table) {
            // Agregar número de folio correlativo por tenant
            $table->integer('numero_folio')->after('id')->nullable();

            // Actualizar el campo estado para incluir 'Eliminado'
            $table->dropColumn('estado');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->enum('estado', ['Pendiente', 'Completo', 'Eliminado'])->default('Pendiente')->after('cambio');

            // Índice único compuesto para asegurar que el folio sea único por tenant
            $table->unique(['tenant_id', 'numero_folio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'numero_folio']);
            $table->dropColumn('numero_folio');
            $table->dropColumn('estado');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->enum('estado', ['Pendiente', 'Completo'])->default('Pendiente');
        });
    }
};
