<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// ============================================================
// Conexión de producción (DB1_ del .env) y tenant FADI id=7
// ============================================================
$conexion = 'db1';
$tenantId = 7;

echo "\n=== LIMPIEZA DE VENTA_ITEMS DUPLICADOS ===\n";
echo "    Conexión : {$conexion} (" . env('DB1_HOST') . "/" . env('DB1_DATABASE') . ")\n";
echo "    Tenant ID: {$tenantId}\n\n";

// ============================================================
// PASO 1: Detectar duplicados
// ============================================================
echo "--- PASO 1: Detectando duplicados ---\n";

$duplicados = DB::connection($conexion)->select("
    SELECT
        vi.venta_id,
        v.numero_folio,
        vi.producto_id,
        p.nombre,
        COUNT(*) AS total,
        GROUP_CONCAT(vi.id ORDER BY vi.id SEPARATOR ', ') AS item_ids,
        GROUP_CONCAT(vi.cantidad ORDER BY vi.id SEPARATOR ', ') AS cantidades,
        GROUP_CONCAT(vi.subtotal ORDER BY vi.id SEPARATOR ', ') AS subtotales
    FROM venta_items vi
    JOIN ventas v ON v.id = vi.venta_id
    JOIN productos p ON p.id = vi.producto_id
    WHERE v.tenant_id = ?
    GROUP BY vi.venta_id, vi.producto_id
    HAVING COUNT(*) > 1
    ORDER BY vi.venta_id
", [$tenantId]);

if (empty($duplicados)) {
    echo "✓ No se encontraron items duplicados. La base de datos está limpia.\n\n";
    exit(0);
}

echo "Se encontraron " . count($duplicados) . " producto(s) duplicado(s):\n\n";

foreach ($duplicados as $dup) {
    echo "  Venta #{$dup->numero_folio} (id={$dup->venta_id}) | Producto: {$dup->nombre} (id={$dup->producto_id})\n";
    echo "    Item IDs    : {$dup->item_ids}\n";
    echo "    Cantidades  : {$dup->cantidades}\n";
    echo "    Subtotales  : {$dup->subtotales}\n\n";
}

// ============================================================
// PASO 2: Confirmar antes de eliminar
// ============================================================
echo "--- PASO 2: ¿Eliminar duplicados? Se conservará el PRIMERO (id menor) ---\n";
echo "Escribe 'si' para continuar, cualquier otra cosa para cancelar: ";

$respuesta = strtolower(trim(fgets(STDIN)));

if ($respuesta !== 'si') {
    echo "\nOperación cancelada. No se eliminó nada.\n\n";
    exit(0);
}

// ============================================================
// PASO 3: Eliminar duplicados dentro de una transacción
// ============================================================
echo "\n--- PASO 3: Eliminando duplicados ---\n";

$db = DB::connection($conexion);
$db->beginTransaction();

try {
    $eliminados = $db->statement("
        DELETE vi
        FROM venta_items vi
        JOIN ventas v ON v.id = vi.venta_id
        WHERE v.tenant_id = ?
          AND vi.id NOT IN (
            SELECT min_id FROM (
                SELECT MIN(vi2.id) AS min_id
                FROM venta_items vi2
                JOIN ventas v2 ON v2.id = vi2.venta_id
                WHERE v2.tenant_id = ?
                GROUP BY vi2.venta_id, vi2.producto_id
            ) t
          )
          AND vi.venta_id IN (
            SELECT venta_id FROM (
                SELECT vi3.venta_id
                FROM venta_items vi3
                JOIN ventas v3 ON v3.id = vi3.venta_id
                WHERE v3.tenant_id = ?
                GROUP BY vi3.venta_id, vi3.producto_id
                HAVING COUNT(*) > 1
            ) dup
          )
    ", [$tenantId, $tenantId, $tenantId]);

    $filasEliminadas = $db->select("SELECT ROW_COUNT() as n")[0]->n;

    // ============================================================
    // PASO 4: Verificar que quedó limpio
    // ============================================================
    $restantes = $db->select("
        SELECT COUNT(*) AS total
        FROM (
            SELECT vi.venta_id, vi.producto_id
            FROM venta_items vi
            JOIN ventas v ON v.id = vi.venta_id
            WHERE v.tenant_id = ?
            GROUP BY vi.venta_id, vi.producto_id
            HAVING COUNT(*) > 1
        ) t
    ", [$tenantId]);

    $totalRestantes = $restantes[0]->total;

    if ($totalRestantes > 0) {
        $db->rollBack();
        echo "✗ Error: aún quedan {$totalRestantes} duplicados después de la limpieza. Se revirtió el cambio.\n\n";
        exit(1);
    }

    $db->commit();
    echo "✓ Se eliminaron {$filasEliminadas} item(s) duplicado(s) correctamente.\n";
    echo "✓ Verificación OK: no quedan duplicados.\n\n";

} catch (\Exception $e) {
    $db->rollBack();
    echo "✗ Error inesperado: " . $e->getMessage() . "\n";
    echo "  Se revirtieron todos los cambios.\n\n";
    exit(1);
}

echo "=== LISTO ===\n\n";
