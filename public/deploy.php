<?php

header('Content-Type: text/plain; charset=utf-8');

$phpBin      = '/usr/local/bin/php';
$projectRoot = '/home/misocio405/MiSocio';
$publicHtml  = '/home/misocio405/public_html';

function run(string $cmd): void
{
    echo "$ {$cmd}\n";
    $output = [];
    $code   = 0;
    exec($cmd . ' 2>&1', $output, $code);
    echo (implode("\n", $output) ?: '(sin salida)') . "\n";
    if ($code !== 0) echo "[ERROR] Código de salida: {$code}\n";
    echo "\n";
}

echo "=== Despliegue: " . date('Y-m-d H:i:s') . " ===\n\n";

run("git -C {$projectRoot} fetch origin");
run("git -C {$projectRoot} reset --hard origin/master");
run("/usr/bin/uapi VersionControlDeployment create repository_root='{$projectRoot}'");
run("{$phpBin} {$projectRoot}/artisan migrate --force");
run("{$phpBin} {$projectRoot}/artisan config:cache");
run("{$phpBin} {$projectRoot}/artisan route:cache");

// Symlink storage → public_html/storage (recrear siempre)
run("rm -f {$publicHtml}/storage");
run("ln -s {$projectRoot}/storage/app/public {$publicHtml}/storage");
echo "Symlink: " . (is_link("{$publicHtml}/storage") ? readlink("{$publicHtml}/storage") : 'FALLIDO') . "\n\n";

// Copiar .htaccess a public_html (contiene SymLinksIfOwnerMatch)
run("cp {$projectRoot}/public/.htaccess {$publicHtml}/.htaccess");

echo "=== Finalizado: " . date('Y-m-d H:i:s') . " ===\n";
