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

:: Script para iniciar el servidor
(
echo @echo off
echo title LicoPrint - Servicio de Impresion [Puerto 2026]
echo color 0A
echo.
echo  ╔═════════════════════════════════════════╗
echo  ║     LicoPrint - Servicio Activo         ║
echo  ║     http://localhost:2026               ║
echo  ╠═════════════════════════════════════════╣
echo  ║  Presiona Ctrl+C para detener           ║
echo  ╚═════════════════════════════════════════╝
echo.
echo "%PHP_EXE%" -S localhost:2026 -t "%LICOPRINT_DIR%" "%LICOPRINT_DIR%\server.php"
) > "%LICOPRINT_DIR%\start.bat"

:: Script para abrir navegador
(
echo @echo off
echo start "" "http://localhost:2026"
) > "%LICOPRINT_DIR%\open-browser.bat"

:: Crear acceso directo en Escritorio
set "DESKTOP=%USERPROFILE%\Desktop"
(
echo @echo off
echo cd /d "%LICOPRINT_DIR%"
echo start "" "%LICOPRINT_DIR%\start.bat"
echo timeout /t 2 /nobreak ^>nul
echo start "" "http://localhost:2026"
) > "%DESKTOP%\LicoPrint.bat"

echo       Scripts creados.
echo       Acceso directo en Escritorio: LicoPrint.bat
echo.

:: Actualizar PATH (opcional)
echo [7/7] Finalizando instalacion...

:: Guardar la ruta de PHP para el script de inicio
echo %PHP_EXE%> "%LICOPRINT_DIR%\php_path.txt"

:: Actualizar el script de inicio con la ruta correcta
(
echo @echo off
echo title LicoPrint - Servicio de Impresion [Puerto 2026]
echo color 0A
echo.
echo  ╔═════════════════════════════════════════╗
echo  ║     LicoPrint - Servicio Activo         ║
echo  ║     http://localhost:2026               ║
echo  ╠═════════════════════════════════════════╣
echo  ║  Presiona Ctrl+C para detener           ║
echo  ╚═════════════════════════════════════════╝
echo.
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
echo         body { background: linear-gradient^(135deg, #1a1a2e 0%%, #16213e 100%%^); min-height: 100vh; color: #fff; }
echo         .card { background: rgba^(255,255,255,0.1^); backdrop-filter: blur^(10px^); border: 1px solid rgba^(255,255,255,0.2^); border-radius: 15px; }
echo         .card-header { background: rgba^(0,0,0,0.2^); border-bottom: 1px solid rgba^(255,255,255,0.1^); border-radius: 15px 15px 0 0 !important; }
echo         .form-control, .form-select { background: rgba^(255,255,255,0.1^); border: 1px solid rgba^(255,255,255,0.2^); color: #fff; }
echo         .form-control:focus, .form-select:focus { background: rgba^(255,255,255,0.15^); border-color: #4CAF50; color: #fff; }
echo         .form-select option { background: #1a1a2e; color: #fff; }
echo         .btn-success { background: linear-gradient^(45deg, #4CAF50, #45a049^); border: none; }
echo         .btn-primary { background: linear-gradient^(45deg, #2196F3, #1976D2^); border: none; }
echo         .form-check-input:checked { background-color: #4CAF50; border-color: #4CAF50; }
echo         .printer-item { background: rgba^(255,255,255,0.05^); border: 1px solid rgba^(255,255,255,0.1^); border-radius: 10px; padding: 12px 15px; margin-bottom: 8px; cursor: pointer; transition: all 0.3s; }
echo         .printer-item:hover { background: rgba^(255,255,255,0.1^); transform: translateX^(5px^); }
echo         .printer-item.active { background: rgba^(76, 175, 80, 0.3^); border-color: #4CAF50; }
echo         .logo { font-size: 2rem; font-weight: bold; text-align: center; margin-bottom: 20px; }
echo         .logo i { color: #4CAF50; }
echo         .status-toast { position: fixed; bottom: 20px; right: 20px; padding: 12px 24px; border-radius: 8px; z-index: 1000; display: none; }
echo     ^</style^>
echo ^</head^>
echo ^<body^>
echo     ^<div class="container py-4"^>
echo         ^<div class="logo"^>^<i class="fa-solid fa-print"^>^</i^> LicoPrint^</div^>
echo         ^<div class="row"^>
echo             ^<div class="col-md-5 mb-4"^>
echo                 ^<div class="card h-100"^>
echo                     ^<div class="card-header d-flex justify-content-between align-items-center"^>
echo                         ^<h5 class="mb-0"^>^<i class="fa-solid fa-list me-2"^>^</i^>Impresoras^</h5^>
echo                         ^<button class="btn btn-sm btn-outline-light" onclick="refreshPrinters^(^)"^>
echo                             ^<i class="fa-solid fa-sync" id="refresh-icon"^>^</i^>
echo                         ^</button^>
echo                     ^</div^>
echo                     ^<div class="card-body"^>
echo                         ^<div id="printers-list"^>^<div class="text-center py-3"^>Cargando...^</div^>^</div^>
echo                         ^<div class="mt-3"^>
echo                             ^<label class="form-label small"^>Impresora seleccionada:^</label^>
echo                             ^<input type="text" class="form-control" id="printer_name" placeholder="Nombre de impresora"^>
echo                         ^</div^>
echo                     ^</div^>
echo                 ^</div^>
echo             ^</div^>
echo             ^<div class="col-md-7 mb-4"^>
echo                 ^<div class="card"^>
echo                     ^<div class="card-header"^>^<h5 class="mb-0"^>^<i class="fa-solid fa-cog me-2"^>^</i^>Configuracion^</h5^>^</div^>
echo                     ^<div class="card-body"^>
echo                         ^<div class="row"^>
echo                             ^<div class="col-md-6 mb-3"^>
echo                                 ^<label class="form-label"^>Tipo de Impresora^</label^>
echo                                 ^<select class="form-select" id="printer_type"^>
echo                                     ^<option value="thermal"^>Termica ^(POS^)^</option^>
echo                                     ^<option value="laser"^>Laser^</option^>
echo                                     ^<option value="inkjet"^>Inyeccion^</option^>
echo                                 ^</select^>
echo                             ^</div^>
echo                             ^<div class="col-md-6 mb-3"^>
echo                                 ^<label class="form-label"^>Tamano de Papel^</label^>
echo                                 ^<select class="form-select" id="paper_size"^>
echo                                     ^<option value="58mm"^>58mm^</option^>
echo                                     ^<option value="80mm" selected^>80mm^</option^>
echo                                     ^<option value="letter"^>Carta^</option^>
echo                                 ^</select^>
echo                             ^</div^>
echo                         ^</div^>
echo                         ^<div class="mb-3"^>
echo                             ^<label class="form-label"^>Ancho de caracteres^</label^>
echo                             ^<input type="number" class="form-control" id="char_width" value="48" min="32" max="80"^>
echo                         ^</div^>
echo                         ^<div class="row mb-3"^>
echo                             ^<div class="col-6"^>
echo                                 ^<div class="form-check form-switch"^>
echo                                     ^<input class="form-check-input" type="checkbox" id="auto_cut" checked^>
echo                                     ^<label class="form-check-label" for="auto_cut"^>^<i class="fa-solid fa-scissors me-1"^>^</i^> Corte automatico^</label^>
echo                                 ^</div^>
echo                             ^</div^>
echo                             ^<div class="col-6"^>
echo                                 ^<div class="form-check form-switch"^>
echo                                     ^<input class="form-check-input" type="checkbox" id="open_drawer"^>
echo                                     ^<label class="form-check-label" for="open_drawer"^>^<i class="fa-solid fa-cash-register me-1"^>^</i^> Abrir cajon^</label^>
echo                                 ^</div^>
echo                             ^</div^>
echo                         ^</div^>
echo                         ^<hr class="border-secondary"^>
echo                         ^<div class="d-flex gap-2 flex-wrap"^>
echo                             ^<button class="btn btn-success" onclick="saveConfig^(^)"^>^<i class="fa-solid fa-save me-1"^>^</i^> Guardar^</button^>
echo                             ^<button class="btn btn-primary" onclick="testPrint^(^)"^>^<i class="fa-solid fa-print me-1"^>^</i^> Imprimir Prueba^</button^>
echo                         ^</div^>
echo                     ^</div^>
echo                 ^</div^>
echo                 ^<div class="card mt-3"^>
echo                     ^<div class="card-body py-2"^>
echo                         ^<small^>^<span class="badge bg-success"^>Activo^</span^> Puerto 2026 - LicoPrint v1.0^</small^>
echo                     ^</div^>
echo                 ^</div^>
echo             ^</div^>
echo         ^</div^>
echo     ^</div^>
echo     ^<div id="toast" class="status-toast bg-success text-white"^>^</div^>
echo     ^<script^>
echo         function toast^(msg, type^) { const t = document.getElementById^('toast'^); t.textContent = msg; t.className = 'status-toast bg-' + type + ' text-white'; t.style.display = 'block'; setTimeout^(^(^) =^> t.style.display = 'none', 3000^); }
echo         function selectPrinter^(name^) { document.getElementById^('printer_name'^).value = name; document.querySelectorAll^('.printer-item'^).forEach^(i =^> i.classList.toggle^('active', i.dataset.name === name^)^); }
echo         async function refreshPrinters^(^) { document.getElementById^('refresh-icon'^).classList.add^('fa-spin'^); try { const res = await fetch^('/api/printers'^); const printers = await res.json^(^); const current = document.getElementById^('printer_name'^).value; document.getElementById^('printers-list'^).innerHTML = printers.length ? printers.map^(p =^> `^<div class="printer-item ${p === current ? 'active' : ''}" data-name="${p}" onclick="selectPrinter^('${p}'^)"^>^<i class="fa-solid fa-print me-2"^>^</i^>${p}^</div^>`^).join^(''^) : '^<div class="text-center text-muted py-3"^>No se detectaron impresoras^</div^>'; toast^('Actualizado', 'success'^); } catch^(e^) { toast^('Error', 'danger'^); } document.getElementById^('refresh-icon'^).classList.remove^('fa-spin'^); }
echo         async function loadConfig^(^) { try { const res = await fetch^('/api/config'^); const c = await res.json^(^); document.getElementById^('printer_name'^).value = c.printer_name ^|^| ''; document.getElementById^('printer_type'^).value = c.printer_type ^|^| 'thermal'; document.getElementById^('paper_size'^).value = c.paper_size ^|^| '80mm'; document.getElementById^('char_width'^).value = c.char_width ^|^| 48; document.getElementById^('auto_cut'^).checked = c.auto_cut !== false; document.getElementById^('open_drawer'^).checked = c.open_drawer === true; } catch^(e^) {} }
echo         async function saveConfig^(^) { const config = { printer_name: document.getElementById^('printer_name'^).value, printer_type: document.getElementById^('printer_type'^).value, paper_size: document.getElementById^('paper_size'^).value, char_width: parseInt^(document.getElementById^('char_width'^).value^) ^|^| 48, auto_cut: document.getElementById^('auto_cut'^).checked, open_drawer: document.getElementById^('open_drawer'^).checked }; try { await fetch^('/api/config', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify^(config^) }^); toast^('Guardado', 'success'^); } catch^(e^) { toast^('Error al guardar', 'danger'^); } }
echo         async function testPrint^(^) { try { const res = await fetch^('/api/test'^); const data = await res.json^(^); toast^(data.success ? 'Impresion enviada' : ^(data.error ^|^| 'Error'^), data.success ? 'success' : 'danger'^); } catch^(e^) { toast^('Error de conexion', 'danger'^); } }
echo         document.getElementById^('paper_size'^).addEventListener^('change', function^(^) { document.getElementById^('char_width'^).value = this.value === '58mm' ? 32 : 48; }^);
echo         loadConfig^(^); refreshPrinters^(^);
echo     ^</script^>
echo ^</body^>
echo ^</html^>
) > "%LICOPRINT_DIR%\index.html"
goto :eof
