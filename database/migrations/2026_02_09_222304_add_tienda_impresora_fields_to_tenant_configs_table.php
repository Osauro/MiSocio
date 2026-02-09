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
        Schema::table('tenant_configs', function (Blueprint $table) {
            // Datos de la tienda
            $table->string('nombre_tienda')->nullable()->after('tenant_id');
            $table->string('direccion')->nullable()->after('nombre_tienda');
            $table->string('telefono', 20)->nullable()->after('direccion');
            $table->string('email')->nullable()->after('telefono');
            $table->string('nit', 50)->nullable()->after('email');

            // Datos del propietario
            $table->string('propietario_nombre')->nullable()->after('nit');
            $table->string('propietario_celular', 20)->nullable()->after('propietario_nombre');

            // Logo
            $table->string('logo')->nullable()->after('propietario_celular');

            // Opciones de impresora térmica
            $table->boolean('corte_automatico')->default(true)->after('papel_copias');
            $table->boolean('abrir_cajon')->default(false)->after('corte_automatico');
            $table->boolean('sonido_apertura')->default(true)->after('abrir_cajon');
            $table->integer('ancho_caracteres')->default(48)->after('sonido_apertura'); // 32 para 58mm, 48 para 80mm
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_tienda',
                'direccion',
                'telefono',
                'email',
                'nit',
                'propietario_nombre',
                'propietario_celular',
                'logo',
                'corte_automatico',
                'abrir_cajon',
                'sonido_apertura',
                'ancho_caracteres',
            ]);
        });
    }
};
