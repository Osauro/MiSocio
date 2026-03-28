<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modalidades_habitacion', function (Blueprint $table) {
            $table->dropColumn('unidad_label');
            $table->decimal('horas', 6, 2)->default(1.00)->after('nombre')
                  ->comment('Duración del alquiler en horas (ej: Noche=12, Hora=1, Semana=168)');
        });
    }

    public function down(): void
    {
        Schema::table('modalidades_habitacion', function (Blueprint $table) {
            $table->dropColumn('horas');
            $table->string('unidad_label', 40)->default('unidades')->after('nombre');
        });
    }
};
