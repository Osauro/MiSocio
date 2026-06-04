<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->unsignedInteger('inventario_items')->default(0)->after('ventas_iniciar_unidad');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->dropColumn('inventario_items');
        });
    }
};
