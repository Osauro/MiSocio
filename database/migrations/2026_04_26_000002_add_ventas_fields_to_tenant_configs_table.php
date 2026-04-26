<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->boolean('ventas_enabled')->default(true)->after('compras_enabled');
            $table->boolean('ventas_solo_unidad')->default(false)->after('ventas_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->dropColumn(['ventas_enabled', 'ventas_solo_unidad']);
        });
    }
};
