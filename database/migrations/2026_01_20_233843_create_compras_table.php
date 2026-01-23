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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('proveedor_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->decimal('efectivo', 10, 2)->default(0);
            $table->decimal('online', 10, 2)->default(0);
            $table->decimal('credito', 10, 2)->default(0);
            $table->enum('estado', ['Pendiente', 'Completo'])->default('Pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
