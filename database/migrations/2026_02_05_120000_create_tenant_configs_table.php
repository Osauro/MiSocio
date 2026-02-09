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
        Schema::create('tenant_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');

            // General
            $table->decimal('sueldo_base', 10, 2)->default(0);
            $table->string('ip_local')->nullable();

            // Impresión
            $table->string('impresora_nombre')->nullable();
            $table->string('impresora_tipo')->default('termica'); // termica, laser, inyeccion
            $table->string('papel_tamano')->default('80mm'); // 58mm, 80mm, carta, media-carta
            $table->integer('papel_copias')->default(1);

            // WhatsApp API (por desarrollar)
            $table->string('whatsapp_token')->nullable();
            $table->string('whatsapp_phone_id')->nullable();
            $table->boolean('whatsapp_enabled')->default(false);

            // Facebook API (por desarrollar)
            $table->string('facebook_page_id')->nullable();
            $table->string('facebook_access_token')->nullable();
            $table->boolean('facebook_enabled')->default(false);

            // Importación (por desarrollar)
            $table->timestamp('ultima_importacion')->nullable();
            $table->string('formato_importacion')->default('excel'); // excel, csv, json

            $table->timestamps();

            $table->unique('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_configs');
    }
};
