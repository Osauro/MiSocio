<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->boolean('greenapi_notif_prestamo')->default(false)->after('greenapi_notif_pago_credito');
            $table->boolean('greenapi_notif_devolucion_prestamo')->default(false)->after('greenapi_notif_prestamo');
            $table->boolean('greenapi_notif_vencimiento_prestamo')->default(false)->after('greenapi_notif_devolucion_prestamo');
            $table->string('latitud', 30)->nullable()->after('direccion');
            $table->string('longitud', 30)->nullable()->after('latitud');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_configs', function (Blueprint $table) {
            $table->dropColumn([
                'greenapi_notif_prestamo',
                'greenapi_notif_devolucion_prestamo',
                'greenapi_notif_vencimiento_prestamo',
                'latitud',
                'longitud',
            ]);
        });
    }
};
