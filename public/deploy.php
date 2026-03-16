<?php

header('Content-Type: text/plain; charset=utf-8');

// ── Diagnóstico rápido del entorno ────────────────────────────────────────────
echo "=== DIAGNÓSTICO DEL ENTORNO ===\n";
echo "PHP version : " . PHP_VERSION . "\n";
echo "Usuario     : " . trim(shell_exec('whoami') ?? 'desconocido') . "\n";
echo "exec()      : " . (function_exists('exec') ? 'disponible' : 'DESHABILITADO') . "\n";
echo "shell_exec(): " . (function_exists('shell_exec') ? 'disponible' : 'DESHABILITADO') . "\n";

// Buscar PHP 8.x primero (cPanel suele tener binarios versionados)
$phpBin = trim(shell_exec(
    'which php8.3 2>/dev/null || ' .
    'which php8.2 2>/dev/null || ' .
    'which php8.1 2>/dev/null || ' .
    'which php8.0 2>/dev/null || ' .
    'ls /usr/local/bin/php8* 2>/dev/null | sort -rV | head -1 || ' .
    'which php 2>/dev/null'
) ?? '');
$composerBin = trim(shell_exec('which composer 2>/dev/null') ?? '');
$gitBin      = trim(shell_exec('which git 2>/dev/null') ?? '');

echo "PHP path    : " . ($phpBin      ?: 'NO ENCONTRADO') . "\n";
echo "Composer    : " . ($composerBin ?: 'NO ENCONTRADO') . "\n";
echo "Git         : " . ($gitBin      ?: 'NO ENCONTRADO') . "\n";

$phpVersionReal = trim(shell_exec(($phpBin ?: 'php') . " -r 'echo PHP_VERSION;' 2>/dev/null") ?? '');
echo "PHP bin ver : " . ($phpVersionReal ?: 'no se pudo verificar') . "\n";
echo "\n";

if (!$phpBin)      $phpBin      = '/usr/local/bin/php';
if (!$composerBin) $composerBin = '/usr/local/bin/composer';
if (!$gitBin)      $gitBin      = '/usr/bin/git';

// COMPOSER_HOME es obligatorio cuando se corre desde web (sin HOME de shell)
$homeDir = '/home/misocio405';
putenv("HOME={$homeDir}");
putenv("COMPOSER_HOME={$homeDir}/.composer");

// ── Configuración ─────────────────────────────────────────────────────────────
$projectRoot = '/home/misocio405/MiSocio';
$publicHtml  = '/home/misocio405/public_html';

// ── Helpers ───────────────────────────────────────────────────────────────────
function run(string $cmd): bool
{
    echo "$ {$cmd}\n";
    $output = [];
    $code   = 0;
    exec($cmd . ' 2>&1', $output, $code);
    $out = implode("\n", $output);
    echo ($out ?: '(sin salida)') . "\n";
    if ($code !== 0) {
        echo "[ERROR] Código de salida: {$code}\n";
    }
    echo "\n";
    return $code === 0;
}

if (!function_exists('exec')) {
    echo "[FATAL] exec() está deshabilitado. Contactá al hosting para habilitarlo.\n";
    exit(1);
}

// ── Pipeline de despliegue ────────────────────────────────────────────────────
echo "=== Inicio del despliegue: " . date('Y-m-d H:i:s') . " ===\n";

// 1. Git pull
run("{$gitBin} -C {$projectRoot} pull");

// 2. Notificar a cPanel (Version Control)
run("/usr/bin/uapi VersionControlDeployment create repository_root='{$projectRoot}'");

// 3. Composer: instalar/actualizar dependencias (sin dev, optimizando autoloader)
run("{$composerBin} install --no-dev --optimize-autoloader --no-interaction --working-dir={$projectRoot}");

// 4. Crear directorios necesarios y asignar permisos
run("mkdir -p {$projectRoot}/storage/framework/{sessions,views,cache} {$projectRoot}/storage/logs {$projectRoot}/bootstrap/cache");
run("mkdir -p {$projectRoot}/storage/app/public {$projectRoot}/storage/app/private {$projectRoot}/storage/app/private/livewire-tmp");
run("chmod -R 775 {$projectRoot}/storage");
run("chmod -R 775 {$projectRoot}/bootstrap/cache");

// 5. Migraciones de base de datos
run("{$phpBin} {$projectRoot}/artisan migrate --force");

// 6. Sincronizar assets compilados a public_html
run("rsync -a --delete {$projectRoot}/public/build/ {$publicHtml}/build/");

// 6b. Copiar archivos de configuración PHP a public_html
run("cp {$projectRoot}/public/.htaccess {$publicHtml}/.htaccess");
run("cp {$projectRoot}/public/.user.ini {$publicHtml}/.user.ini");

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
