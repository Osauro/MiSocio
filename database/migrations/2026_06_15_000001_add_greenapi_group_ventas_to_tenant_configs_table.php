<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->string('greenapi_group_ventas')->nullable()->after('greenapi_notif_ventas');
            $table->string('greenapi_group_ventas_nombre')->nullable()->after('greenapi_group_ventas');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->dropColumn(['greenapi_group_ventas', 'greenapi_group_ventas_nombre']);
        });
    }
};
