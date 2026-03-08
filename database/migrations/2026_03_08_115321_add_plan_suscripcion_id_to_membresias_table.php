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
            $table->foreignId('plan_suscripcion_id')->nullable()->constrained('planes_suscripcion')->nullOnDelete()->after('plan_nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membresias', function (Blueprint $table) {
            $table->dropForeign(['plan_suscripcion_id']);
            $table->dropColumn('plan_suscripcion_id');
        });
    }
};
