<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige precio_de_compra para que siempre represente
 * el precio por paquete según la cantidad actual del producto.
 *
 * El cálculo: pdc = (suma_subtotales / suma_unidades) * cantidad_actual
 * Esto asegura que  stock * pdc / cantidad = stock * precio_por_unidad
 * sea el capital correcto sin importar cómo cambió "cantidad".
 */
return new class extends Migration
{
    public function up(): void
    {
        // Recalcular pdc para todos los productos que tienen al menos
        // una compra completada con unidades > 0.
        DB::statement("
            UPDATE productos p
            INNER JOIN (
                SELECT
                    ci.producto_id,
                    c.tenant_id,
                    SUM(ci.subtotal) / NULLIF(SUM(ci.cantidad), 0) AS precio_unitario_promedio
                FROM compra_items ci
                INNER JOIN compras c ON ci.compra_id = c.id
                WHERE c.estado = 'Completo'
                  AND ci.cantidad > 0
                GROUP BY ci.producto_id, c.tenant_id
            ) avg ON p.id = avg.producto_id
                   AND p.tenant_id = avg.tenant_id
            SET p.precio_de_compra = avg.precio_unitario_promedio * NULLIF(p.cantidad, 0)
            WHERE p.deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        // No se puede revertir automáticamente (los valores anteriores se pierden).
    }
};
