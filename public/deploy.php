<?php

// ── Configuración ─────────────────────────────────────────────────────────────
$projectRoot  = '/home/misocio405/MiSocio';
$publicHtml   = '/home/misocio405/public_html';
$composerBin  = '/usr/bin/composer';
$phpBin       = '/usr/bin/php';
$gitBin       = '/usr/bin/git';

header('Content-Type: text/plain; charset=utf-8');

// ── Helpers ───────────────────────────────────────────────────────────────────
function run(string $cmd): void
{
    echo "\n$ {$cmd}\n";
    $output = [];
    $code   = 0;
    exec($cmd . ' 2>&1', $output, $code);
    echo implode("\n", $output) . "\n";
    if ($code !== 0) {
        echo "[ERROR] El comando terminó con código {$code}.\n";
    }
}

// ── Pipeline de despliegue ────────────────────────────────────────────────────
echo "=== Inicio del despliegue: " . date('Y-m-d H:i:s') . " ===\n";

// 1. Git pull
run("{$gitBin} -C {$projectRoot} pull");

// 2. Notificar a cPanel (Version Control)
run("/usr/bin/uapi VersionControlDeployment create repository_root='{$projectRoot}'");

// 3. Composer: instalar/actualizar dependencias (sin dev, optimizando autoloader)
run("{$composerBin} install --no-dev --optimize-autoloader --no-interaction --working-dir={$projectRoot}");

// 4. Permisos de escritura en directorios que Laravel necesita
run("chmod -R 775 {$projectRoot}/storage");
run("chmod -R 775 {$projectRoot}/bootstrap/cache");

// 5. Migraciones de base de datos
run("{$phpBin} {$projectRoot}/artisan migrate --force");

// 6. Sincronizar .htaccess y assets compilados a public_html
run("cp {$projectRoot}/public/.htaccess {$publicHtml}/.htaccess");
run("rsync -a --delete {$projectRoot}/public/build/ {$publicHtml}/build/");

// 7. Symlink storage dentro de public_html (dentro del mismo usuario, suele funcionar)
if (!file_exists("{$publicHtml}/storage")) {
    run("ln -s {$projectRoot}/storage/app/public {$publicHtml}/storage");
} else {
    echo "\n$ Symlink public_html/storage ya existe, se omite.\n";
}

// 8. Limpiar y reconstruir cachés de Laravel
run("{$phpBin} {$projectRoot}/artisan config:cache");
run("{$phpBin} {$projectRoot}/artisan route:cache");
run("{$phpBin} {$projectRoot}/artisan view:cache");
run("{$phpBin} {$projectRoot}/artisan event:cache");

echo "\n=== Despliegue finalizado: " . date('Y-m-d H:i:s') . " ===\n";
