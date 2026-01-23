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
        // Migrar datos existentes de users a tenant_user
        DB::table('users')
            ->whereNotNull('tenant_id')
            ->get()
            ->each(function ($user) {
                DB::table('tenant_user')->insert([
                    'tenant_id' => $user->tenant_id,
                    'user_id' => $user->id,
                    'role' => $user->role ?? 'user',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        // Eliminar columnas antiguas de la tabla users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar columnas en users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
            $table->enum('role', ['landlord', 'tenant', 'user'])->default('user')->after('name');
        });

        // Restaurar datos desde tenant_user a users (solo la primera relación)
        DB::table('tenant_user')
            ->get()
            ->groupBy('user_id')
            ->each(function ($tenantUsers, $userId) {
                $firstRelation = $tenantUsers->first();
                DB::table('users')
                    ->where('id', $userId)
                    ->update([
                        'tenant_id' => $firstRelation->tenant_id,
                        'role' => $firstRelation->role,
                    ]);
            });
    }
};
