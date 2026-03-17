<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->boolean('prestamos_enabled')->default(true)->after('facebook_enabled');
            $table->unsignedBigInteger('prestamos_categoria_id')->nullable()->after('prestamos_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->dropColumn(['prestamos_enabled', 'prestamos_categoria_id']);
        });
    }
};
