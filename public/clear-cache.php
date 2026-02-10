<?php
/**
 * Script temporal para limpiar caché en producción.
 * Acceder: https://licos.fadi.com.bo/clear-cache.php
 * ELIMINAR DESPUÉS DE USAR.
 */

// Borrar archivos de caché de rutas
$cacheDir = __DIR__ . '/../bootstrap/cache';
$deleted = [];

foreach (glob($cacheDir . '/routes-v7*.php') as $file) {
    unlink($file);
    $deleted[] = basename($file);
}

// También borrar config cache y views cache
foreach (['config.php', 'services.php', 'packages.php'] as $f) {
    $path = $cacheDir . '/' . $f;
    if (file_exists($path)) {
        unlink($path);
        $deleted[] = $f;
    }
}

// Borrar vistas compiladas
$viewsDir = __DIR__ . '/../storage/framework/views';
$viewCount = 0;
foreach (glob($viewsDir . '/*.php') as $file) {
    unlink($file);
    $viewCount++;
}

echo "✅ Cache limpiada correctamente!\n\n";
echo "Archivos de cache eliminados: " . implode(', ', $deleted) . "\n";
echo "Vistas compiladas eliminadas: " . $viewCount . "\n\n";
echo "⚠️ ELIMINA este archivo (public/clear-cache.php) por seguridad.";
