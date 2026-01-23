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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
            $table->string('nombre');
            $table->string('codigo')->nullable();
            $table->string('imagen')->nullable();
            $table->string('medida', 10);
            $table->integer('cantidad');
            $table->decimal('precio_de_compra', 10, 2)->default(0);
            $table->decimal('precio_por_mayor', 10, 2)->default(0);
            $table->decimal('precio_por_menor', 10, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->timestamps();

            // Índice único compuesto por tenant_id y nombre
            $table->unique(['tenant_id', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
