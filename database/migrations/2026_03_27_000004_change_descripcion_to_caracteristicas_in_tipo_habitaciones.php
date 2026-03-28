<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_habitaciones', function (Blueprint $table) {
            $table->dropColumn('descripcion');
            $table->json('caracteristicas')->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_habitaciones', function (Blueprint $table) {
            $table->dropColumn('caracteristicas');
            $table->text('descripcion')->nullable()->after('nombre');
        });
    }
};
