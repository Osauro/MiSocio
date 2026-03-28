<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de modalidades personalizables por tenant
        Schema::create('modalidades_habitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('nombre', 80);           // Ej: "Temporal", "Por día"
            $table->string('unidad_label', 40)->default('unidades'); // Label para el campo cantidad
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'nombre']);
        });

        // Cambiar ENUM a VARCHAR en tarifas_habitacion
        DB::statement("ALTER TABLE tarifas_habitacion MODIFY modalidad VARCHAR(80) NOT NULL");

        // Cambiar ENUM a VARCHAR en hospedaje_habitaciones
        DB::statement("ALTER TABLE hospedaje_habitaciones MODIFY modalidad VARCHAR(80) NOT NULL DEFAULT 'dia'");
    }

    public function down(): void
    {
        Schema::dropIfExists('modalidades_habitacion');
    }
};