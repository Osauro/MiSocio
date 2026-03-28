<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospedajes', function (Blueprint $table) {
            $table->json('acompanantes')->nullable()->after('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::table('hospedajes', function (Blueprint $table) {
            $table->dropColumn('acompanantes');
        });
    }
};
