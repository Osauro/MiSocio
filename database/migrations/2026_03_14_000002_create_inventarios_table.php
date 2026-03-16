<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('numero_folio')->default(1);
            $table->enum('estado', ['Pendiente', 'Completo', 'Eliminado'])->default('Pendiente');
            $table->text('obs')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'numero_folio']);
        });

        Schema::create('inventario_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventario_id')->constrained('inventarios')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->integer('stock_sistema');   // stock que tenía el sistema al momento de agregar
            $table->integer('stock_contado');   // lo que el usuario contó físicamente
            $table->integer('diferencia');      // stock_contado - stock_sistema (positivo=sobrante, negativo=faltante)

            $table->unique(['inventario_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_items');
        Schema::dropIfExists('inventarios');
    }
};
