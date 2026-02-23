<?php

namespace Database\Seeders;

use App\Models\PlanSuscripcion;
use Illuminate\Database\Seeder;

class PlanesSuscripcionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Características comunes para TODOS los planes (ilimitado)
        $caracteristicasComunes = [
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

        $planes = [
            [
                'nombre' => 'Plan Demo',
                'slug' => 'demo',
                'duracion_meses' => 0,
                'precio' => 0.00,
                'descripcion' => 'Plan de prueba gratuito por 15 días - Acceso completo',
                'caracteristicas' => $caracteristicasComunes,
                'activo' => true,
                'orden' => 1,
            ],
            [
                'nombre' => 'Plan Mensual',
                'slug' => 'mensual',
                'duracion_meses' => 1,
                'precio' => 120.00,
                'descripcion' => 'Suscripción mensual - Pago cada mes',
                'caracteristicas' => $caracteristicasComunes,
                'activo' => true,
                'orden' => 2,
            ],
            [
                'nombre' => 'Plan Trimestral',
                'slug' => 'trimestral',
                'duracion_meses' => 3,
                'precio' => 330.00,
                'descripcion' => 'Suscripción trimestral - Ahorra 8% (Bs. 30)',
                'caracteristicas' => $caracteristicasComunes,
                'activo' => true,
                'orden' => 3,
            ],
            [
                'nombre' => 'Plan Semestral',
                'slug' => 'semestral',
                'duracion_meses' => 6,
                'precio' => 630.00,
                'descripcion' => 'Suscripción semestral - Ahorra 12% (Bs. 90)',
                'caracteristicas' => $caracteristicasComunes,
                'activo' => true,
                'orden' => 4,
            ],
            [
                'nombre' => 'Plan Anual',
                'slug' => 'anual',
                'duracion_meses' => 12,
                'precio' => 1200.00,
                'descripcion' => 'Suscripción anual - Ahorra 17% (Bs. 240)',
                'caracteristicas' => $caracteristicasComunes,
                'activo' => true,
                'orden' => 5,
            ],
        ];

        foreach ($planes as $plan) {
            PlanSuscripcion::create($plan);
        }
    }
}
