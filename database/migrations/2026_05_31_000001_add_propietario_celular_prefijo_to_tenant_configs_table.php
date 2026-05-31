<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tenant_configs', 'propietario_celular_prefijo')) {
            Schema::table('tenant_configs', function (Blueprint $table) {
                $table->string('propietario_celular_prefijo', 10)->nullable()->default('591')
                    ->after('propietario_nombre');
            });
        }
    }

    public function down(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->dropColumn('propietario_celular_prefijo');
        });
    }
};
