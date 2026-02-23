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
        Schema::create('planes_suscripcion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // mensual, trimestral, semestral, anual
            $table->string('slug')->unique(); // mensual, trimestral, semestral, anual
            $table->integer('duracion_meses'); // 1, 3, 6, 12
            $table->decimal('precio', 10, 2); // Precio en Bs.
            $table->text('descripcion')->nullable();
            $table->json('caracteristicas')->nullable(); // Array de características
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0); // Para ordenar la visualización
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes_suscripcion');
    }
};
