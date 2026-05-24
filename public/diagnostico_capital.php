<?php
/**
 * Diagnóstico de Capital - Script temporal de solo lectura
 * Acceder con: /diagnostico_capital.php?token=misocio_diag_2026
 * ELIMINAR después de diagnosticar
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['token']) || $_GET['token'] !== 'misocio_diag_2026') {
    http_response_code(403);
    die('Acceso denegado');
}

header('Content-Type: text/plain; charset=utf-8');

// Leer .env sin Laravel — busca en varias rutas posibles
function readEnv(): array {
    $posibles = [
        __DIR__ . '/../.env',                          // local: public/ dentro del proyecto
        '/home/misocio405/MiSocio/.env',               // producción cPanel
        dirname(__DIR__) . '/.env',                    // genérico un nivel arriba
    ];
    foreach ($posibles as $path) {
        if (file_exists($path)) {
            echo "# .env encontrado en: {$path}\n";
            $vars = [];
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
                [$key, $val] = explode('=', $line, 2);
                $vars[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
            }
            return $vars;
        }
        echo "# No encontrado: {$path}\n";
    }
    die("ERROR: No se encontró el archivo .env\n");
}

echo "# __DIR__ = " . __DIR__ . "\n";
$env  = readEnv();
$host = $env['DB_HOST']     ?? '127.0.0.1';
$port = $env['DB_PORT']     ?? '3306';
$db   = $env['DB_DATABASE'] ?? '';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

echo "# Conectando a: {$host}:{$port}/{$db}\n\n";

$pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);


echo "=== DIAGNÓSTICO DE CAPITAL - " . date('Y-m-d H:i:s') . " ===\n\n";

$tenants = $pdo->query('SELECT id, name FROM tenants')->fetchAll();
foreach ($tenants as $tenant) {
    $tid = $tenant->id;
    echo "========================================\n";
    echo "TENANT: {$tenant->name} (ID: {$tid})\n";
    echo "========================================\n\n";

    // --- COMPRAS ---
    $stmt = $pdo->prepare("SELECT numero_folio, efectivo, credito, created_at FROM compras WHERE tenant_id=? AND estado='Completo' ORDER BY created_at");
    $stmt->execute([$tid]);
    $compras = $stmt->fetchAll();
    $totalCompras = 0;
    echo "COMPRAS COMPLETADAS:\n";
    foreach ($compras as $c) {
        $total = $c->efectivo + $c->credito;
        $totalCompras += $total;
        echo "  Folio #{$c->numero_folio} | Total: Bs." . number_format($total, 2) . " | Fecha: {$c->created_at}\n";
    }
    echo "TOTAL COMPRAS: Bs." . number_format($totalCompras, 2) . "\n\n";

    // --- SUBTOTALES DE COMPRA ITEMS ---
    $stmt = $pdo->prepare("SELECT SUM(ci.subtotal) as total FROM compra_items ci JOIN compras c ON ci.compra_id=c.id WHERE c.tenant_id=? AND c.estado='Completo'");
    $stmt->execute([$tid]);
    $subtotalItems = $stmt->fetchColumn();
    echo "SUMA SUBTOTALES compra_items: Bs." . number_format($subtotalItems, 2) . "\n\n";

    // --- CAPITAL FORMULA CORREGIDA ---
    $stmt = $pdo->prepare("SELECT SUM(stock * precio_de_compra / NULLIF(cantidad,0)) FROM productos WHERE tenant_id=? AND deleted_at IS NULL");
    $stmt->execute([$tid]);
    $capitalFix = $stmt->fetchColumn();
    echo "CAPITAL (stock * pdc / cantidad): Bs." . number_format($capitalFix, 2) . "\n";

    // --- CAPITAL FORMULA ORIGINAL ---
    $stmt = $pdo->prepare("SELECT SUM(stock * precio_de_compra) FROM productos WHERE tenant_id=? AND deleted_at IS NULL");
    $stmt->execute([$tid]);
    $capitalOriginal = $stmt->fetchColumn();
    echo "CAPITAL (stock * pdc - original): Bs." . number_format($capitalOriginal, 2) . "\n\n";

    // --- PRODUCTOS CON STOCK > 0 ---
    $stmt = $pdo->prepare("
        SELECT nombre, stock, precio_de_compra, cantidad, control,
               (stock * precio_de_compra / NULLIF(cantidad,0)) as cap_fix,
               (stock * precio_de_compra) as cap_orig
        FROM productos
        WHERE tenant_id=? AND deleted_at IS NULL AND stock > 0
        ORDER BY cap_fix DESC
    ");
    $stmt->execute([$tid]);
    $productos = $stmt->fetchAll();

    echo "PRODUCTOS CON STOCK > 0 (ordenados por mayor aporte):\n";
    echo str_pad("Nombre", 32) . " | stock | pdc      | cant | cap_fix     | cap_orig\n";
    echo str_repeat("-", 90) . "\n";
    foreach ($productos as $p) {
        echo str_pad(substr($p->nombre, 0, 31), 32)
            . " | " . str_pad($p->stock, 5)
            . " | " . str_pad(number_format($p->precio_de_compra, 2), 8)
            . " | " . str_pad($p->cantidad, 4)
            . " | Bs." . str_pad(number_format($p->cap_fix, 2), 9)
            . " | Bs." . number_format($p->cap_orig, 2)
            . "\n";
    }

    // --- COMPRA ITEMS DETALLE ---
    echo "\nDETALLE COMPRA ITEMS (precio y subtotal por producto):\n";
    echo str_pad("Producto", 32) . " | cant_total | precio   | cant_med | subtotal\n";
    echo str_repeat("-", 82) . "\n";
    $stmt = $pdo->prepare("
        SELECT p.nombre, p.cantidad as cantidad_medida,
               ci.cantidad as cant_total, ci.precio, ci.subtotal
        FROM compra_items ci
        JOIN compras c  ON ci.compra_id  = c.id
        JOIN productos p ON ci.producto_id = p.id
        WHERE c.tenant_id=? AND c.estado='Completo'
        ORDER BY ci.subtotal DESC
    ");
    $stmt->execute([$tid]);
    $items = $stmt->fetchAll();
    foreach ($items as $item) {
        echo str_pad(substr($item->nombre, 0, 31), 32)
            . " | " . str_pad($item->cant_total, 10)
            . " | " . str_pad(number_format($item->precio, 2), 8)
            . " | " . str_pad($item->cantidad_medida, 8)
            . " | Bs." . number_format($item->subtotal, 2)
            . "\n";
    }

    echo "\n\n";
}

echo "=== FIN DEL DIAGNÓSTICO ===\n";
echo "RECUERDA: Eliminar este archivo después de usarlo.\n";
