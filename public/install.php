<?php
/**
 * LicoPOS - Instalador Web
 *
 * Este archivo realiza la instalación completa del sistema.
 * ELIMINAR DESPUÉS DE LA INSTALACIÓN
 */

// Mostrar errores durante instalación
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión al principio
session_start();

// Configuración
$basePath = dirname(__DIR__);
$step = $_GET['step'] ?? 'check';
$errors = [];
$success = [];

// Funciones auxiliares
function checkRequirement($name, $condition, $required = true) {
    return [
        'name' => $name,
        'status' => $condition,
        'required' => $required
    ];
}

function generateRandomKey($length = 32) {
    return 'base64:' . base64_encode(random_bytes($length));
}

function runArtisanCommand($basePath, $command) {
    $output = [];
    $returnVar = 0;
    exec("cd {$basePath} && php artisan {$command} 2>&1", $output, $returnVar);
    return ['output' => implode("\n", $output), 'success' => $returnVar === 0];
}

// Manejar POST del formulario de BD ANTES de cualquier output
if ($step === 'database' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbPort = $_POST['db_port'] ?? '3306';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    $appUrl = $_POST['app_url'] ?? '';

    try {
        $pdo = new PDO(
            "mysql:host={$dbHost};port={$dbPort}",
            $dbUser,
            $dbPass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $_SESSION['install'] = [
            'db_host' => $dbHost,
            'db_port' => $dbPort,
            'db_name' => $dbName,
            'db_user' => $dbUser,
            'db_pass' => $dbPass,
            'app_url' => $appUrl,
        ];

        header('Location: ?step=install');
        exit;

    } catch (PDOException $e) {
        $errors[] = 'Error de conexión: ' . $e->getMessage();
    }
}

// Redirect si install step pero no hay sesión
if ($step === 'install' && !isset($_SESSION['install'])) {
    header('Location: ?step=database');
    exit;
}

// Estilos CSS
$css = '
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background: linear-gradient(135deg, #1a472a 0%, #2d5a3d 100%);
        min-height: 100vh;
        padding: 40px 20px;
    }
    .container {
        max-width: 700px;
        margin: 0 auto;
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        overflow: hidden;
    }
    .header {
        background: #1a472a;
        color: white;
        padding: 30px;
        text-align: center;
    }
    .header h1 { font-size: 28px; margin-bottom: 5px; }
    .header p { opacity: 0.8; }
    .content { padding: 30px; }
    .step-indicator {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-bottom: 30px;
    }
    .step-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #ddd;
    }
    .step-dot.active { background: #1a472a; }
    .step-dot.completed { background: #4CAF50; }
    .check-list { list-style: none; }
    .check-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }
    .check-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 14px;
    }
    .check-icon.success { background: #4CAF50; color: white; }
    .check-icon.error { background: #f44336; color: white; }
    .check-icon.warning { background: #ff9800; color: white; }
    .form-group { margin-bottom: 20px; }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    .form-group input, .form-group select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 15px;
        transition: border-color 0.3s;
    }
    .form-group input:focus, .form-group select:focus {
        outline: none;
        border-color: #1a472a;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    .btn {
        display: inline-block;
        padding: 14px 30px;
        background: #1a472a;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.3s;
    }
    .btn:hover { background: #2d5a3d; }
    .btn:disabled { background: #ccc; cursor: not-allowed; }
    .btn-block { width: 100%; text-align: center; }
    .btn-danger { background: #f44336; }
    .btn-danger:hover { background: #d32f2f; }
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
    .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
    .alert-warning { background: #fff3e0; color: #e65100; border: 1px solid #ffcc80; }
    .code-block {
        background: #263238;
        color: #aed581;
        padding: 15px;
        border-radius: 8px;
        font-family: monospace;
        font-size: 13px;
        overflow-x: auto;
        margin: 15px 0;
    }
    .log-output {
        background: #1e1e1e;
        color: #d4d4d4;
        padding: 15px;
        border-radius: 8px;
        font-family: monospace;
        font-size: 12px;
        max-height: 300px;
        overflow-y: auto;
        margin: 15px 0;
    }
    .log-output .success { color: #4CAF50; }
    .log-output .error { color: #f44336; }
    .credentials-box {
        background: #e3f2fd;
        border: 2px solid #2196F3;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        margin: 20px 0;
    }
    .credentials-box h3 { color: #1565c0; margin-bottom: 15px; }
    .credentials-box .cred {
        font-size: 24px;
        font-weight: bold;
        color: #0d47a1;
        margin: 5px 0;
    }
    .text-center { text-align: center; }
    .mt-20 { margin-top: 20px; }
    .small { font-size: 13px; color: #666; }
</style>
';

// HTML Header
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - LicoPOS</title>
    ' . $css . '
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🍷 LicoPOS</h1>
        <p>Asistente de Instalación</p>
    </div>
    <div class="content">';

// Steps indicator
$steps = ['check' => 1, 'database' => 2, 'install' => 3, 'complete' => 4];
$currentStep = $steps[$step] ?? 1;

echo '<div class="step-indicator">';
for ($i = 1; $i <= 4; $i++) {
    $class = $i < $currentStep ? 'completed' : ($i == $currentStep ? 'active' : '');
    echo "<div class='step-dot {$class}'></div>";
}
echo '</div>';

// STEP 1: Check Requirements
if ($step === 'check') {
    $requirements = [
        checkRequirement('PHP >= 8.2', version_compare(PHP_VERSION, '8.2.0', '>=')),
        checkRequirement('PDO Extension', extension_loaded('pdo')),
        checkRequirement('PDO MySQL', extension_loaded('pdo_mysql')),
        checkRequirement('Mbstring Extension', extension_loaded('mbstring')),
        checkRequirement('OpenSSL Extension', extension_loaded('openssl')),
        checkRequirement('Tokenizer Extension', extension_loaded('tokenizer')),
        checkRequirement('XML Extension', extension_loaded('xml')),
        checkRequirement('Ctype Extension', extension_loaded('ctype')),
        checkRequirement('JSON Extension', extension_loaded('json')),
        checkRequirement('BCMath Extension', extension_loaded('bcmath'), false),
        checkRequirement('Fileinfo Extension', extension_loaded('fileinfo')),
    ];

    // Check directories
    $directories = [
        checkRequirement('storage/framework writable', is_writable($basePath . '/storage/framework') || @mkdir($basePath . '/storage/framework', 0775, true)),
        checkRequirement('storage/logs writable', is_writable($basePath . '/storage/logs') || @mkdir($basePath . '/storage/logs', 0775, true)),
        checkRequirement('bootstrap/cache writable', is_writable($basePath . '/bootstrap/cache') || @mkdir($basePath . '/bootstrap/cache', 0775, true)),
    ];

    // Create necessary directories
    @mkdir($basePath . '/storage/framework/sessions', 0775, true);
    @mkdir($basePath . '/storage/framework/views', 0775, true);
    @mkdir($basePath . '/storage/framework/cache', 0775, true);

    $allPassed = true;

    echo '<h2 style="margin-bottom: 20px;">📋 Verificación de Requisitos</h2>';

    echo '<h4 style="margin: 15px 0 10px;">Extensiones PHP</h4>';
    echo '<ul class="check-list">';
    foreach ($requirements as $req) {
        $icon = $req['status'] ? '✓' : ($req['required'] ? '✗' : '!');
        $class = $req['status'] ? 'success' : ($req['required'] ? 'error' : 'warning');
        if (!$req['status'] && $req['required']) $allPassed = false;
        echo "<li class='check-item'>
            <span class='check-icon {$class}'>{$icon}</span>
            <span>{$req['name']}</span>
        </li>";
    }
    echo '</ul>';

    echo '<h4 style="margin: 15px 0 10px;">Permisos de Directorios</h4>';
    echo '<ul class="check-list">';
    foreach ($directories as $dir) {
        $icon = $dir['status'] ? '✓' : '✗';
        $class = $dir['status'] ? 'success' : 'error';
        if (!$dir['status']) $allPassed = false;
        echo "<li class='check-item'>
            <span class='check-icon {$class}'>{$icon}</span>
            <span>{$dir['name']}</span>
        </li>";
    }
    echo '</ul>';

    echo '<div class="mt-20 text-center">';
    if ($allPassed) {
        echo '<a href="?step=database" class="btn">Continuar →</a>';
    } else {
        echo '<div class="alert alert-error">Corrige los errores antes de continuar</div>';
        echo '<a href="?step=check" class="btn">Verificar de nuevo</a>';
    }
    echo '</div>';
}

// STEP 2: Database Configuration
elseif ($step === 'database') {
    // Detectar URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $detectedUrl = $protocol . '://' . $host;

    echo '<h2 style="margin-bottom: 20px;">🗄️ Configuración de Base de Datos</h2>';

    if (!empty($errors)) {
        echo '<div class="alert alert-error">' . implode('<br>', $errors) . '</div>';
    }

    echo '<form method="POST">
        <div class="form-group">
            <label>URL de la Aplicación</label>
            <input type="url" name="app_url" value="' . $detectedUrl . '" required placeholder="https://licos.fadi.com.bo">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Host de Base de Datos</label>
                <input type="text" name="db_host" value="localhost" required>
            </div>
            <div class="form-group">
                <label>Puerto</label>
                <input type="text" name="db_port" value="3306" required>
            </div>
        </div>

        <div class="form-group">
            <label>Nombre de la Base de Datos</label>
            <input type="text" name="db_name" required placeholder="paybol_licos">
            <p class="small">Se creará automáticamente si no existe</p>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Usuario de BD</label>
                <input type="text" name="db_user" required placeholder="paybol_licos">
            </div>
            <div class="form-group">
                <label>Contraseña de BD</label>
                <input type="password" name="db_pass" placeholder="••••••••">
            </div>
        </div>

        <div class="mt-20">
            <button type="submit" class="btn btn-block">Probar Conexión e Instalar →</button>
        </div>
    </form>';
}

// STEP 3: Install
elseif ($step === 'install') {
    $config = $_SESSION['install'];
    $logs = [];
    $hasError = false;

    echo '<h2 style="margin-bottom: 20px;">⚙️ Instalando LicoPOS...</h2>';
    echo '<div class="log-output" id="install-log">';

    // 1. Create .env file
    $logs[] = '<span class="success">→ Creando archivo .env...</span>';
    $envContent = "APP_NAME=LicoPOS
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=America/La_Paz
APP_URL={$config['app_url']}

APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_ES

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST={$config['db_host']}
DB_PORT={$config['db_port']}
DB_DATABASE={$config['db_name']}
DB_USERNAME={$config['db_user']}
DB_PASSWORD={$config['db_pass']}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=file
CACHE_PREFIX=licos_
";

    if (file_put_contents($basePath . '/.env', $envContent)) {
        $logs[] = '<span class="success">✓ Archivo .env creado</span>';
    } else {
        $logs[] = '<span class="error">✗ Error creando .env</span>';
        $hasError = true;
    }

    // 2. Generate APP_KEY
    $logs[] = '<span class="success">→ Generando APP_KEY...</span>';
    $result = runArtisanCommand($basePath, 'key:generate --force');
    if ($result['success']) {
        $logs[] = '<span class="success">✓ APP_KEY generada</span>';
    } else {
        $logs[] = '<span class="error">✗ Error: ' . $result['output'] . '</span>';
        $hasError = true;
    }

    // 3. Clear config cache
    $logs[] = '<span class="success">→ Limpiando caché...</span>';
    runArtisanCommand($basePath, 'config:clear');
    runArtisanCommand($basePath, 'cache:clear');
    $logs[] = '<span class="success">✓ Caché limpiada</span>';

    // 4. Run migrations
    $logs[] = '<span class="success">→ Ejecutando migraciones...</span>';
    $result = runArtisanCommand($basePath, 'migrate --force');
    if ($result['success']) {
        $logs[] = '<span class="success">✓ Migraciones ejecutadas</span>';
    } else {
        $logs[] = '<span class="error">✗ Error en migraciones: ' . $result['output'] . '</span>';
        $hasError = true;
    }

    // 5. Create storage link
    $logs[] = '<span class="success">→ Creando enlace de storage...</span>';
    $result = runArtisanCommand($basePath, 'storage:link');
    $logs[] = '<span class="success">✓ Enlace de storage creado</span>';

    // 6. Create default tenant and user
    $logs[] = '<span class="success">→ Creando tenant y usuario por defecto...</span>';
    try {
        $pdo = new PDO(
            "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']}",
            $config['db_user'],
            $config['db_pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Check if tenant exists
        $stmt = $pdo->query("SELECT COUNT(*) FROM tenants");
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            // Create tenant
            $pdo->exec("INSERT INTO tenants (name, subscription_type, status, created_at, updated_at)
                        VALUES ('Mi Empresa', 'demo', 1, NOW(), NOW())");
            $tenantId = $pdo->lastInsertId();

            // Create user
            $password = password_hash('5421', PASSWORD_BCRYPT);
            $pdo->exec("INSERT INTO users (name, celular, password, is_super_admin, created_at, updated_at)
                        VALUES ('Administrador', '73010688', '{$password}', 1, NOW(), NOW())");
            $userId = $pdo->lastInsertId();

            // Link user to tenant
            $pdo->exec("INSERT INTO tenant_user (tenant_id, user_id, role, is_active, created_at, updated_at)
                        VALUES ({$tenantId}, {$userId}, 'tenant', 1, NOW(), NOW())");

            $logs[] = '<span class="success">✓ Tenant y usuario creados</span>';
            $_SESSION['install']['new_user'] = true;
        } else {
            $logs[] = '<span class="success">✓ Ya existen datos, omitiendo creación de usuario</span>';
            $_SESSION['install']['new_user'] = false;
        }

    } catch (Exception $e) {
        $logs[] = '<span class="error">✗ Error: ' . $e->getMessage() . '</span>';
        $hasError = true;
    }

    // 7. Optimize
    $logs[] = '<span class="success">→ Optimizando para producción...</span>';
    runArtisanCommand($basePath, 'config:cache');
    runArtisanCommand($basePath, 'route:cache');
    runArtisanCommand($basePath, 'view:cache');
    $logs[] = '<span class="success">✓ Optimización completada</span>';

    // Output logs
    echo implode("\n", $logs);
    echo '</div>';

    if ($hasError) {
        echo '<div class="alert alert-error mt-20">
            Hubo errores durante la instalación. Revisa los logs arriba.
        </div>';
        echo '<a href="?step=database" class="btn btn-danger">← Volver</a>';
    } else {
        echo '<div class="alert alert-success mt-20">
            ¡Instalación completada exitosamente!
        </div>';
        echo '<div class="text-center">
            <a href="?step=complete" class="btn">Ver Credenciales →</a>
        </div>';
    }
}

// STEP 4: Complete
elseif ($step === 'complete') {
    $newUser = $_SESSION['install']['new_user'] ?? true;

    echo '<h2 style="margin-bottom: 20px; text-align: center;">🎉 ¡Instalación Completada!</h2>';

    if ($newUser) {
        echo '<div class="credentials-box">
            <h3>Credenciales de Acceso</h3>
            <p>Celular:</p>
            <p class="cred">73010688</p>
            <p style="margin-top: 10px;">Contraseña:</p>
            <p class="cred">5421</p>
        </div>';
    }

    echo '<div class="alert alert-warning">
        <strong>⚠️ IMPORTANTE:</strong> Por seguridad, elimina este archivo después de la instalación:
        <div class="code-block">rm public/install.php</div>
    </div>';

    $appUrl = $_SESSION['install']['app_url'] ?? '';

    echo '<div class="text-center mt-20">
        <a href="' . $appUrl . '" class="btn" style="font-size: 18px; padding: 18px 40px;">
            Ir a LicoPOS →
        </a>
    </div>';

    echo '<p class="small text-center mt-20">
        Recuerda cambiar la contraseña después del primer inicio de sesión.
    </p>';

    // Clear session
    unset($_SESSION['install']);
}

echo '</div></div></body></html>';
