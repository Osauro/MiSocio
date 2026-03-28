<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_habitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->tinyInteger('capacidad_maxima')->default(2);
            $table->string('color', 20)->default('#6c757d'); // color HEX del card
            $table->string('imagen')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'nombre']);
        });

        Schema::create('habitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('tipo_habitacion_id')->constrained('tipo_habitaciones')->onDelete('cascade');
            $table->string('numero', 20);
            $table->tinyInteger('piso')->nullable();
            $table->enum('estado', ['disponible', 'ocupada', 'limpieza', 'mantenimiento'])->default('disponible');
            $table->text('descripcion')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['tenant_id', 'numero']);
        });

        Schema::create('tarifas_habitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('tipo_habitacion_id')->constrained('tipo_habitaciones')->onDelete('cascade');
            $table->enum('modalidad', ['hora', 'noche', 'semana']);
            $table->decimal('precio', 10, 2)->default(0);
            $table->boolean('precio_por_persona')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['tipo_habitacion_id', 'modalidad']);
        });

        Schema::create('hospedajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->unsignedInteger('numero_folio')->nullable();
            $table->enum('estado', ['activo', 'finalizado', 'cancelado'])->default('activo');
            $table->datetime('fecha_entrada');
            $table->datetime('fecha_salida_estimada')->nullable();
            $table->datetime('fecha_salida_real')->nullable();
            $table->tinyInteger('numero_personas')->default(1);
            $table->text('observaciones')->nullable();
            $table->decimal('efectivo', 10, 2)->default(0);
            $table->decimal('online', 10, 2)->default(0);
            $table->decimal('credito', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('hospedaje_habitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospedaje_id')->constrained('hospedajes')->onDelete('cascade');
            $table->foreignId('habitacion_id')->constrained('habitaciones')->onDelete('cascade');
            $table->foreignId('tarifa_id')->nullable()->constrained('tarifas_habitacion')->onDelete('set null');
            $table->enum('modalidad', ['hora', 'noche', 'semana'])->default('noche');
            $table->decimal('unidades', 8, 2)->default(1); // horas, noches o semanas
            $table->tinyInteger('numero_personas')->default(1);
            $table->decimal('precio_unitario', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospedaje_habitaciones');
        Schema::dropIfExists('hospedajes');
        Schema::dropIfExists('tarifas_habitacion');
        Schema::dropIfExists('habitaciones');
        Schema::dropIfExists('tipo_habitaciones');
    }
};
