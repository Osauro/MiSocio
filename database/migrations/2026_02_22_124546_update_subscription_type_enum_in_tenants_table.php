<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el ENUM para incluir trimestral y semestral
        DB::statement("ALTER TABLE tenants MODIFY COLUMN subscription_type ENUM('demo', 'mensual', 'trimestral', 'semestral', 'anual') DEFAULT 'demo'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al ENUM original
        DB::statement("ALTER TABLE tenants MODIFY COLUMN subscription_type ENUM('demo', 'mensual', 'anual') DEFAULT 'demo'");
    }
};
