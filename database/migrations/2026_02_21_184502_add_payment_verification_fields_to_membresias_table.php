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
        Schema::table('membresias', function (Blueprint $table) {
            $table->string('plan_nombre')->default('mensual')->after('tenant_id');
            $table->integer('duracion_meses')->default(1)->after('plan_nombre');
            $table->date('fecha_inicio')->nullable()->after('duracion_meses');
            $table->date('fecha_fin')->nullable()->after('fecha_inicio');
            $table->enum('estado_pago', ['pendiente', 'verificado', 'rechazado'])->default('pendiente')->after('monto');
            $table->string('comprobante_url')->nullable()->after('estado_pago');
            $table->foreignId('verificado_por')->nullable()->constrained('users')->nullOnDelete()->after('comprobante_url');
            $table->timestamp('verificado_at')->nullable()->after('verificado_por');
            $table->text('notas_verificacion')->nullable()->after('verificado_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membresias', function (Blueprint $table) {
            $table->dropForeign(['verificado_por']);
            $table->dropColumn([
                'plan_nombre',
                'duracion_meses',
                'fecha_inicio',
                'fecha_fin',
                'estado_pago',
                'comprobante_url',
                'verificado_por',
                'verificado_at',
                'notas_verificacion'
            ]);
        });
    }
};
