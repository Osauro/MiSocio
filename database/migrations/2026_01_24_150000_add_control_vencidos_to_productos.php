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
        Schema::table('productos', function (Blueprint $table) {
            $table->boolean('control')->default(true)->after('stock')->comment('Si se controla el stock del producto');
            $table->integer('vencidos')->default(0)->after('control')->comment('Cantidad de stock vencido o pinchado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['control', 'vencidos']);
        });
    }
};
