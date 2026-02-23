<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\PlanSuscripcion;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Características comunes para TODOS los planes (ilimitado)
        $caracteristicas = [
            'Productos ilimitados',
            'Ventas ilimitadas',
            'Usuarios ilimitados',
            'Gestión completa de inventario',
            'Control de préstamos',
            'Reportes y estadísticas',
            'Múltiples formas de pago',
            'Impresión de tickets y facturas',
            'Soporte técnico por email',
        ];

        // Actualizar todos los planes con las mismas características
        PlanSuscripcion::query()->update(['caracteristicas' => $caracteristicas]);

        // Actualizar descripciones
        PlanSuscripcion::where('slug', 'demo')->update([
            'descripcion' => 'Plan de prueba gratuito por 15 días - Acceso completo'
        ]);

        PlanSuscripcion::where('slug', 'mensual')->update([
            'descripcion' => 'Suscripción mensual - Pago cada mes'
        ]);

        PlanSuscripcion::where('slug', 'trimestral')->update([
            'descripcion' => 'Suscripción trimestral - Ahorra 8% (Bs. 30)'
        ]);

        PlanSuscripcion::where('slug', 'semestral')->update([
            'descripcion' => 'Suscripción semestral - Ahorra 12% (Bs. 90)'
        ]);

        PlanSuscripcion::where('slug', 'anual')->update([
            'descripcion' => 'Suscripción anual - Ahorra 17% (Bs. 240)'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir
    }
};
