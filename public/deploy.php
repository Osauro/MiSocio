<?php

header('Content-Type: text/plain; charset=utf-8');

$phpBin      = '/usr/local/bin/php';
$projectRoot = '/home/misocio405/MiSocio';

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
run("{$phpBin} {$projectRoot}/artisan migrate --force");
run("{$phpBin} {$projectRoot}/artisan config:cache");
run("{$phpBin} {$projectRoot}/artisan route:cache");
run("{$phpBin} {$projectRoot}/artisan view:cache");

echo "=== Finalizado: " . date('Y-m-d H:i:s') . " ===\n";
