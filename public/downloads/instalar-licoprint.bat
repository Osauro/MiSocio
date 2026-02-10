@echo off
setlocal enabledelayedexpansion
title LicoPrint - Instalador Automatico
color 0A

echo.
echo  ╔═══════════════════════════════════════════════════════════════╗
echo  ║                                                               ║
echo  ║     ██╗     ██╗ ██████╗ ██████╗ ██████╗ ██████╗ ██╗███╗   ██╗╔══╗
echo  ║     ██║     ██║██╔════╝██╔═══██╗██╔══██╗██╔══██╗██║████╗  ██║║  ║
echo  ║     ██║     ██║██║     ██║   ██║██████╔╝██████╔╝██║██╔██╗ ██║║  ║
echo  ║     ██║     ██║██║     ██║   ██║██╔═══╝ ██╔══██╗██║██║╚██╗██║║  ║
echo  ║     ███████╗██║╚██████╗╚██████╔╝██║     ██║  ██║██║██║ ╚████║║  ║
echo  ║     ╚══════╝╚═╝ ╚═════╝ ╚═════╝ ╚═╝     ╚═╝  ╚═╝╚═╝╚═╝  ╚═══╝╚══╝
echo  ║                                                               ║
echo  ║           Servicio de Impresion Local - Instalador            ║
echo  ╚═══════════════════════════════════════════════════════════════╝
echo.

:: Configuracion
set "LICOPRINT_DIR=%LOCALAPPDATA%\LicoPrint"
set "PHP_VERSION=8.3.4"
set "PHP_URL=https://windows.php.net/downloads/releases/php-%PHP_VERSION%-nts-Win32-vs16-x64.zip"
set "COMPOSER_URL=https://getcomposer.org/download/latest-stable/composer.phar"
set "PORT=2026"

echo [1/7] Verificando requisitos del sistema...
echo.

:: Verificar si ya esta instalado
if exist "%LICOPRINT_DIR%\server.php" (
    echo [INFO] LicoPrint ya esta instalado en: %LICOPRINT_DIR%
    echo.
    set /p REINSTALL="Deseas reinstalar? (S/N): "
    if /i "!REINSTALL!" neq "S" (
        echo.
        echo Iniciando LicoPrint existente...
        goto :START_SERVER
    )
    echo.
    echo Reinstalando...
    rmdir /s /q "%LICOPRINT_DIR%" 2>nul
)

:: Crear directorio
echo [2/7] Creando directorio de instalacion...
mkdir "%LICOPRINT_DIR%" 2>nul
mkdir "%LICOPRINT_DIR%\php" 2>nul
mkdir "%LICOPRINT_DIR%\logs" 2>nul
mkdir "%LICOPRINT_DIR%\temp" 2>nul
echo       Directorio: %LICOPRINT_DIR%
echo.

:: Verificar PHP
echo [3/7] Verificando PHP...
set "PHP_EXE="

:: Buscar PHP en PATH
where php >nul 2>nul
if %ERRORLEVEL% equ 0 (
    for /f "tokens=*" %%i in ('where php') do (
        set "PHP_EXE=%%i"
        goto :PHP_FOUND
    )
)

:: Buscar en ubicaciones comunes
if exist "C:\laragon\bin\php\php-8.3.*\php.exe" (
    for /d %%i in (C:\laragon\bin\php\php-8.3.*) do set "PHP_EXE=%%i\php.exe"
    goto :PHP_FOUND
)
if exist "C:\laragon\bin\php\php-8.2.*\php.exe" (
    for /d %%i in (C:\laragon\bin\php\php-8.2.*) do set "PHP_EXE=%%i\php.exe"
    goto :PHP_FOUND
)
if exist "C:\laragon\bin\php\php-8.1.*\php.exe" (
    for /d %%i in (C:\laragon\bin\php\php-8.1.*) do set "PHP_EXE=%%i\php.exe"
    goto :PHP_FOUND
)
if exist "C:\xampp\php\php.exe" (
    set "PHP_EXE=C:\xampp\php\php.exe"
    goto :PHP_FOUND
)
if exist "%LICOPRINT_DIR%\php\php.exe" (
    set "PHP_EXE=%LICOPRINT_DIR%\php\php.exe"
    goto :PHP_FOUND
)

:: PHP no encontrado - descargar
echo       PHP no encontrado. Descargando PHP %PHP_VERSION%...
echo.

:: Verificar PowerShell para descargas
powershell -Command "exit" >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] PowerShell no disponible. Por favor instala PHP manualmente.
    pause
    exit /b 1
)

:: Descargar PHP
echo       Descargando desde windows.php.net...
powershell -Command "& {[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri '%PHP_URL%' -OutFile '%LICOPRINT_DIR%\php.zip' -UseBasicParsing}" 2>nul

if not exist "%LICOPRINT_DIR%\php.zip" (
    echo [ERROR] No se pudo descargar PHP.
    echo       Por favor descarga PHP manualmente desde: https://windows.php.net
    pause
    exit /b 1
)

:: Extraer PHP
echo       Extrayendo PHP...
powershell -Command "Expand-Archive -Path '%LICOPRINT_DIR%\php.zip' -DestinationPath '%LICOPRINT_DIR%\php' -Force"
del "%LICOPRINT_DIR%\php.zip" 2>nul

:: Configurar php.ini
echo       Configurando PHP...
if exist "%LICOPRINT_DIR%\php\php.ini-development" (
    copy "%LICOPRINT_DIR%\php\php.ini-development" "%LICOPRINT_DIR%\php\php.ini" >nul
)

:: Habilitar extensiones necesarias
powershell -Command "(Get-Content '%LICOPRINT_DIR%\php\php.ini') -replace ';extension=curl', 'extension=curl' -replace ';extension=mbstring', 'extension=mbstring' -replace ';extension=openssl', 'extension=openssl' | Set-Content '%LICOPRINT_DIR%\php\php.ini'"

set "PHP_EXE=%LICOPRINT_DIR%\php\php.exe"
echo       PHP instalado correctamente.
echo.

:PHP_FOUND
echo       PHP encontrado: %PHP_EXE%
echo.

:: Crear archivos del servidor
echo [4/7] Creando servidor de impresion...

:: Crear server.php
(
echo ^<?php
echo /**
echo  * LicoPrint - Servidor de Impresion Local
echo  * Puerto: 2026
echo  */
echo.
echo error_reporting^(E_ALL^);
echo ini_set^('display_errors', '0'^);
echo ini_set^('log_errors', '1'^);
echo.
echo $configFile = __DIR__ . '/config.json';
echo $logFile = __DIR__ . '/logs/print.log';
echo.
echo // Cargar o crear configuracion
echo function loadConfig^(^) {
echo     global $configFile;
echo     $default = [
echo         'printer_name' =^> '',
echo         'printer_type' =^> 'thermal',
echo         'paper_size' =^> '80mm',
echo         'auto_cut' =^> true,
echo         'open_drawer' =^> false,
echo         'char_width' =^> 48
echo     ];
echo     if ^(file_exists^($configFile^)^) {
echo         $config = json_decode^(file_get_contents^($configFile^), true^);
echo         return array_merge^($default, $config ?: []^);
echo     }
echo     return $default;
echo }
echo.
echo function saveConfig^($config^) {
echo     global $configFile;
echo     file_put_contents^($configFile, json_encode^($config, JSON_PRETTY_PRINT^)^);
echo }
echo.
echo function logPrint^($message^) {
echo     global $logFile;
echo     $date = date^('Y-m-d H:i:s'^);
echo     @file_put_contents^($logFile, "[$date] $message\n", FILE_APPEND^);
echo }
echo.
echo // Detectar impresoras ^(Windows^)
echo function getPrinters^(^) {
echo     $printers = [];
echo     if ^(strtoupper^(substr^(PHP_OS, 0, 3^)^) === 'WIN'^) {
echo         exec^('powershell -Command "Get-Printer | Select-Object -ExpandProperty Name"', $output^);
echo         foreach ^($output as $line^) {
echo             $line = trim^($line^);
echo             if ^(!empty^($line^)^) $printers[] = $line;
echo         }
echo     }
echo     return $printers;
echo }
echo.
echo // Imprimir texto
echo function printText^($printerName, $content, $copies = 1^) {
echo     if ^(empty^($printerName^)^) return ['success' =^> false, 'error' =^> 'No hay impresora configurada'];
echo.
echo     $tempFile = sys_get_temp_dir^(^) . '/licoprint_' . uniqid^(^) . '.txt';
echo     file_put_contents^($tempFile, $content^);
echo.
echo     for ^($i = 0; $i ^< max^(1, $copies^); $i++^) {
echo         $cmd = 'powershell -Command "Get-Content \"' . $tempFile . '\" | Out-Printer -Name \"' . $printerName . '\""';
echo         exec^($cmd, $output, $returnCode^);
echo         if ^($returnCode !== 0^) {
echo             @unlink^($tempFile^);
echo             return ['success' =^> false, 'error' =^> 'Error al imprimir: codigo ' . $returnCode];
echo         }
echo     }
echo.
echo     @unlink^($tempFile^);
echo     logPrint^("Impreso en $printerName: " . strlen^($content^) . " bytes"^);
echo     return ['success' =^> true];
echo }
echo.
echo // Router
echo $uri = parse_url^($_SERVER['REQUEST_URI'], PHP_URL_PATH^);
echo $method = $_SERVER['REQUEST_METHOD'];
echo.
echo // CORS
echo header^('Access-Control-Allow-Origin: *'^);
echo header^('Access-Control-Allow-Methods: GET, POST, OPTIONS'^);
echo header^('Access-Control-Allow-Headers: Content-Type'^);
echo if ^($method === 'OPTIONS'^) exit;
echo.
echo // API Routes
echo if ^(strpos^($uri, '/api/'^) === 0^) {
echo     header^('Content-Type: application/json'^);
echo.
echo     switch ^($uri^) {
echo         case '/api/config':
echo             if ^($method === 'GET'^) {
echo                 echo json_encode^(loadConfig^(^)^);
echo             } else {
echo                 $data = json_decode^(file_get_contents^('php://input'^), true^);
echo                 saveConfig^($data^);
echo                 echo json_encode^(['status' =^> 'ok']^);
echo             }
echo             break;
echo.
echo         case '/api/printers':
echo             echo json_encode^(getPrinters^(^)^);
echo             break;
echo.
echo         case '/api/print':
echo             $data = json_decode^(file_get_contents^('php://input'^), true^);
echo             $config = loadConfig^(^);
echo             $result = printText^($config['printer_name'], $data['content'] ?? '', $data['copies'] ?? 1^);
echo             echo json_encode^($result^);
echo             break;
echo.
echo         case '/api/test':
echo             $config = loadConfig^(^);
echo             $width = $config['char_width'] ?: 48;
echo             if ^($config['paper_size'] === '58mm' ^&^& $width ^> 32^) $width = 32;
echo             $line = str_repeat^('=', $width^);
echo             $content = "$line\n";
echo             $content .= str_pad^('PRUEBA DE IMPRESION', $width, ' ', STR_PAD_BOTH^) . "\n";
echo             $content .= "$line\n\n";
echo             $content .= "Impresora: " . $config['printer_name'] . "\n";
echo             $content .= "Papel: " . $config['paper_size'] . "\n";
echo             $content .= "Ancho: " . $width . " caracteres\n\n";
echo             $content .= "$line\n";
echo             $content .= str_pad^('IMPRESION CORRECTA!', $width, ' ', STR_PAD_BOTH^) . "\n";
echo             $content .= "$line\n\n";
echo             $content .= "LicoPrint v1.0\n";
echo             $content .= date^('d/m/Y H:i:s'^) . "\n\n\n";
echo             $result = printText^($config['printer_name'], $content^);
echo             echo json_encode^($result^);
echo             break;
echo.
echo         default:
echo             http_response_code^(404^);
echo             echo json_encode^(['error' =^> 'Not found']^);
echo     }
echo     exit;
echo }
echo.
echo // Servir index.html
echo $indexFile = __DIR__ . '/index.html';
echo if ^(file_exists^($indexFile^)^) {
echo     header^('Content-Type: text/html; charset=utf-8'^);
echo     readfile^($indexFile^);
echo } else {
echo     echo 'LicoPrint Server Running';
echo }
) > "%LICOPRINT_DIR%\server.php"

echo       server.php creado.

:: Crear index.html
echo [5/7] Creando interfaz web...
call :CREATE_INDEX_HTML

echo       index.html creado.
echo.

:: Crear script de inicio
echo [6/7] Creando scripts de inicio...

:: Script para iniciar el servidor (visible)
(
echo @echo off
echo "%PHP_EXE%" -S localhost:2026 -t "%LICOPRINT_DIR%" "%LICOPRINT_DIR%\server.php"
) > "%LICOPRINT_DIR%\start.bat"

:: Script VBS para inicio SILENCIOSO (oculta la ventana)
(
echo Set WshShell = CreateObject^("WScript.Shell"^)
echo WshShell.Run chr^(34^) ^& "%LICOPRINT_DIR%\start.bat" ^& chr^(34^), 0, False
echo Set WshShell = Nothing
) > "%LICOPRINT_DIR%\start-silent.vbs"

:: Script para detener el servidor
(
echo @echo off
echo taskkill /F /IM php.exe /FI "WINDOWTITLE eq *localhost:2026*" 2^>nul
echo for /f "tokens=5" %%%%a in ^('netstat -aon ^| findstr ":2026"'^) do taskkill /F /PID %%%%a 2^>nul
echo echo LicoPrint detenido.
) > "%LICOPRINT_DIR%\stop.bat"

:: Crear acceso directo en Escritorio (silencioso)
set "DESKTOP=%USERPROFILE%\Desktop"
copy "%LICOPRINT_DIR%\start-silent.vbs" "%DESKTOP%\LicoPrint.vbs" >nul

:: Agregar al inicio de Windows
set "STARTUP=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup"
copy "%LICOPRINT_DIR%\start-silent.vbs" "%STARTUP%\LicoPrint.vbs" >nul

echo       Scripts creados.
echo       Acceso directo en Escritorio: LicoPrint.vbs
echo       Inicio automatico con Windows: Activado
echo.

:: Actualizar PATH (opcional)
echo [7/7] Finalizando instalacion...

:: Guardar la ruta de PHP para el script de inicio
echo %PHP_EXE%> "%LICOPRINT_DIR%\php_path.txt"

:: Recrear el script de inicio con la ruta correcta
(
echo @echo off
echo "%PHP_EXE%" -S localhost:2026 -t "%LICOPRINT_DIR%" "%LICOPRINT_DIR%\server.php"
) > "%LICOPRINT_DIR%\start.bat"

echo.
echo  ╔═══════════════════════════════════════════════════════════════╗
echo  ║                                                               ║
echo  ║              INSTALACION COMPLETADA                           ║
echo  ║                                                               ║
echo  ╠═══════════════════════════════════════════════════════════════╣
echo  ║                                                               ║
echo  ║   Directorio: %LICOPRINT_DIR%
echo  ║   PHP: %PHP_EXE%
echo  ║   Puerto: 2026                                                ║
echo  ║                                                               ║
echo  ║   Para iniciar: Ejecuta LicoPrint.bat en tu Escritorio        ║
echo  ║   O abre: %LICOPRINT_DIR%\start.bat
echo  ║                                                               ║
echo  ╚═══════════════════════════════════════════════════════════════╝
echo.

:START_SERVER
set /p INICIAR="Deseas iniciar LicoPrint ahora? (S/N): "
if /i "%INICIAR%" neq "S" (
    echo.
    echo Instalacion completada. Ejecuta LicoPrint.bat cuando quieras iniciar.
    pause
    exit /b 0
)

echo.
echo Iniciando LicoPrint...
start "" "%LICOPRINT_DIR%\start.bat"
timeout /t 2 /nobreak >nul
start "" "http://localhost:2026"

echo.
echo LicoPrint iniciado en http://localhost:2026
echo.
pause
exit /b 0

:: ============================================================================
:: FUNCION: Crear index.html
:: ============================================================================
:CREATE_INDEX_HTML
(
echo ^<!DOCTYPE html^>
echo ^<html lang="es"^>
echo ^<head^>
echo     ^<meta charset="UTF-8"^>
echo     ^<meta name="viewport" content="width=device-width, initial-scale=1.0"^>
echo     ^<title^>LicoPrint - Configuracion^</title^>
echo     ^<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"^>
echo     ^<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"^>
echo     ^<style^>
echo         :root { --primary: #10b981; --primary-hover: #059669; --bg: #f8fafc; --card-bg: #ffffff; --text: #1e293b; --text-muted: #64748b; --border: #e2e8f0; }
echo         body { background: var^(--bg^); color: var^(--text^); font-family: 'Segoe UI', system-ui, sans-serif; }
echo         .card { background: var^(--card-bg^); border: 1px solid var^(--border^); border-radius: 12px; box-shadow: 0 1px 3px rgba^(0,0,0,0.1^); }
echo         .card-header { background: var^(--card-bg^); border-bottom: 1px solid var^(--border^); font-weight: 600; }
echo         .form-control, .form-select { border: 1px solid var^(--border^); border-radius: 8px; }
echo         .form-control:focus, .form-select:focus { border-color: var^(--primary^); box-shadow: 0 0 0 3px rgba^(16,185,129,0.15^); }
echo         .btn-success { background: var^(--primary^); border-color: var^(--primary^); }
echo         .btn-success:hover { background: var^(--primary-hover^); border-color: var^(--primary-hover^); }
echo         .btn-primary { background: #3b82f6; border-color: #3b82f6; }
echo         .btn-primary:hover { background: #2563eb; border-color: #2563eb; }
echo         .form-check-input:checked { background-color: var^(--primary^); border-color: var^(--primary^); }
echo         .printer-item { background: var^(--bg^); border: 1px solid var^(--border^); border-radius: 8px; padding: 12px 15px; margin-bottom: 8px; cursor: pointer; transition: all 0.2s; }
echo         .printer-item:hover { background: #f1f5f9; border-color: #cbd5e1; }
echo         .printer-item.active { background: #ecfdf5; border-color: var^(--primary^); color: #065f46; }
echo         .printer-item.active i { color: var^(--primary^); }
echo         .logo { font-size: 1.5rem; font-weight: 700; color: var^(--text^); display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 24px; }
echo         .logo i { color: var^(--primary^); font-size: 1.75rem; }
echo         .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
echo         .status-badge.online { background: #dcfce7; color: #166534; }
echo         .status-badge.online::before { content: ''; width: 8px; height: 8px; background: #22c55e; border-radius: 50%%; animation: pulse 2s infinite; }
echo         @keyframes pulse { 0%%, 100%% { opacity: 1; } 50%% { opacity: 0.5; } }
echo         .toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 1000; }
echo         .custom-toast { padding: 12px 20px; border-radius: 8px; color: #fff; display: none; box-shadow: 0 4px 12px rgba^(0,0,0,0.15^); }
echo     ^</style^>
echo ^</head^>
echo ^<body^>
echo     ^<div class="container py-4" style="max-width: 900px;"^>
echo         ^<div class="logo"^>^<i class="fa-solid fa-print"^>^</i^> LicoPrint^</div^>
echo         ^<div class="row g-4"^>
echo             ^<div class="col-md-5"^>
echo                 ^<div class="card h-100"^>
echo                     ^<div class="card-header d-flex justify-content-between align-items-center py-3"^>
echo                         ^<span^>^<i class="fa-solid fa-list me-2 text-muted"^>^</i^>Impresoras^</span^>
echo                         ^<button class="btn btn-sm btn-outline-secondary" onclick="refreshPrinters^(^)" title="Actualizar"^>
echo                             ^<i class="fa-solid fa-sync" id="refresh-icon"^>^</i^>
echo                         ^</button^>
echo                     ^</div^>
echo                     ^<div class="card-body"^>
echo                         ^<div id="printers-list"^>^<div class="text-center py-4 text-muted"^>^<i class="fa-solid fa-spinner fa-spin me-2"^>^</i^>Cargando...^</div^>^</div^>
echo                         ^<div class="mt-3"^>
echo                             ^<label class="form-label small text-muted"^>Impresora seleccionada^</label^>
echo                             ^<input type="text" class="form-control" id="printer_name" placeholder="Selecciona una impresora"^>
echo                         ^</div^>
echo                     ^</div^>
echo                 ^</div^>
echo             ^</div^>
echo             ^<div class="col-md-7"^>
echo                 ^<div class="card"^>
echo                     ^<div class="card-header py-3"^>^<i class="fa-solid fa-sliders me-2 text-muted"^>^</i^>Configuracion^</div^>
echo                     ^<div class="card-body"^>
echo                         ^<div class="row g-3"^>
echo                             ^<div class="col-6"^>
echo                                 ^<label class="form-label small text-muted"^>Tipo de Impresora^</label^>
echo                                 ^<select class="form-select" id="printer_type"^>
echo                                     ^<option value="thermal"^>Termica ^(POS^)^</option^>
echo                                     ^<option value="laser"^>Laser^</option^>
echo                                     ^<option value="inkjet"^>Inyeccion^</option^>
echo                                 ^</select^>
echo                             ^</div^>
echo                             ^<div class="col-6"^>
echo                                 ^<label class="form-label small text-muted"^>Tamano de Papel^</label^>
echo                                 ^<select class="form-select" id="paper_size"^>
echo                                     ^<option value="58mm"^>58mm^</option^>
echo                                     ^<option value="80mm" selected^>80mm^</option^>
echo                                 ^</select^>
echo                             ^</div^>
echo                             ^<div class="col-12"^>
echo                                 ^<label class="form-label small text-muted"^>Ancho de caracteres^</label^>
echo                                 ^<input type="number" class="form-control" id="char_width" value="48" min="32" max="80"^>
echo                             ^</div^>
echo                             ^<div class="col-6"^>
echo                                 ^<div class="form-check form-switch"^>
echo                                     ^<input class="form-check-input" type="checkbox" id="auto_cut" checked^>
echo                                     ^<label class="form-check-label" for="auto_cut"^>Corte automatico^</label^>
echo                                 ^</div^>
echo                             ^</div^>
echo                             ^<div class="col-6"^>
echo                                 ^<div class="form-check form-switch"^>
echo                                     ^<input class="form-check-input" type="checkbox" id="open_drawer"^>
echo                                     ^<label class="form-check-label" for="open_drawer"^>Abrir cajon^</label^>
echo                                 ^</div^>
echo                             ^</div^>
echo                         ^</div^>
echo                         ^<hr class="my-3"^>
echo                         ^<div class="d-flex gap-2"^>
echo                             ^<button class="btn btn-success" onclick="saveConfig^(^)"^>^<i class="fa-solid fa-check me-1"^>^</i^> Guardar^</button^>
echo                             ^<button class="btn btn-primary" onclick="testPrint^(^)"^>^<i class="fa-solid fa-print me-1"^>^</i^> Prueba^</button^>
echo                         ^</div^>
echo                     ^</div^>
echo                 ^</div^>
echo                 ^<div class="d-flex justify-content-between align-items-center mt-3 px-1"^>
echo                     ^<span class="status-badge online"^>Servicio activo^</span^>
echo                     ^<small class="text-muted"^>Puerto 2026 • LicoPrint v1.0^</small^>
echo                 ^</div^>
echo             ^</div^>
echo         ^</div^>
echo     ^</div^>
echo     ^<div class="toast-container"^>^<div id="toast" class="custom-toast"^>^</div^>^</div^>
echo     ^<script^>
echo         function toast^(msg, ok^) { const t = document.getElementById^('toast'^); t.textContent = msg; t.style.background = ok ? '#10b981' : '#ef4444'; t.style.display = 'block'; setTimeout^(^(^) =^> t.style.display = 'none', 3000^); }
echo         function selectPrinter^(name^) { document.getElementById^('printer_name'^).value = name; document.querySelectorAll^('.printer-item'^).forEach^(i =^> i.classList.toggle^('active', i.dataset.name === name^)^); }
echo         async function refreshPrinters^(^) { document.getElementById^('refresh-icon'^).classList.add^('fa-spin'^); try { const res = await fetch^('/api/printers'^); const printers = await res.json^(^); const current = document.getElementById^('printer_name'^).value; document.getElementById^('printers-list'^).innerHTML = printers.length ? printers.map^(p =^> `^<div class="printer-item ${p === current ? 'active' : ''}" data-name="${p}" onclick="selectPrinter^('${p}'^)"^>^<i class="fa-solid fa-print me-2"^>^</i^>${p}^</div^>`^).join^(''^) : '^<div class="text-center text-muted py-4"^>^<i class="fa-solid fa-exclamation-circle me-2"^>^</i^>No se detectaron impresoras^</div^>'; } catch^(e^) { toast^('Error al cargar impresoras', false^); } document.getElementById^('refresh-icon'^).classList.remove^('fa-spin'^); }
echo         async function loadConfig^(^) { try { const res = await fetch^('/api/config'^); const c = await res.json^(^); document.getElementById^('printer_name'^).value = c.printer_name ^|^| ''; document.getElementById^('printer_type'^).value = c.printer_type ^|^| 'thermal'; document.getElementById^('paper_size'^).value = c.paper_size ^|^| '80mm'; document.getElementById^('char_width'^).value = c.char_width ^|^| 48; document.getElementById^('auto_cut'^).checked = c.auto_cut !== false; document.getElementById^('open_drawer'^).checked = c.open_drawer === true; } catch^(e^) {} }
echo         async function saveConfig^(^) { const config = { printer_name: document.getElementById^('printer_name'^).value, printer_type: document.getElementById^('printer_type'^).value, paper_size: document.getElementById^('paper_size'^).value, char_width: parseInt^(document.getElementById^('char_width'^).value^) ^|^| 48, auto_cut: document.getElementById^('auto_cut'^).checked, open_drawer: document.getElementById^('open_drawer'^).checked }; try { await fetch^('/api/config', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify^(config^) }^); toast^('Configuracion guardada', true^); } catch^(e^) { toast^('Error al guardar', false^); } }
echo         async function testPrint^(^) { try { const res = await fetch^('/api/test'^); const data = await res.json^(^); toast^(data.success ? 'Impresion enviada' : ^(data.error ^|^| 'Error'^), data.success^); } catch^(e^) { toast^('Error de conexion', false^); } }
echo         document.getElementById^('paper_size'^).addEventListener^('change', function^(^) { document.getElementById^('char_width'^).value = this.value === '58mm' ? 32 : 48; }^);
echo         loadConfig^(^); refreshPrinters^(^);
echo     ^</script^>
echo ^</body^>
echo ^</html^>
) > "%LICOPRINT_DIR%\index.html"
goto :eof
