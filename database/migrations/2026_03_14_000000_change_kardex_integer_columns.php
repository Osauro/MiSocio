<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kardex', function (Blueprint $table) {
            $table->integer('entrada')->default(0)->change();
            $table->integer('salida')->default(0)->change();
            $table->integer('anterior')->default(0)->change();
            $table->integer('saldo')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('kardex', function (Blueprint $table) {
            $table->decimal('entrada', 10, 2)->default(0)->change();
            $table->decimal('salida', 10, 2)->default(0)->change();
            $table->decimal('anterior', 10, 2)->default(0)->change();
            $table->decimal('saldo', 10, 2)->default(0)->change();
        });
    }
};
