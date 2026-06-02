<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->boolean('greenapi_notif_credito')->default(false);
            $table->boolean('greenapi_notif_pago_credito')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->dropColumn(['greenapi_notif_credito', 'greenapi_notif_pago_credito']);
        });
    }
};
