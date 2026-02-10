@echo off
setlocal enabledelayedexpansion
title LicoPrint - Iniciador
color 0A

echo.
echo  ╔═════════════════════════════════════════╗
echo  ║     LicoPrint - Servicio Local          ║
echo  ║     http://localhost:2026               ║
echo  ╚═════════════════════════════════════════╝
echo.

set "LICOPRINT_DIR=%LOCALAPPDATA%\LicoPrint"

:: Verificar si LicoPrint esta instalado
if not exist "%LICOPRINT_DIR%\server.php" (
    echo [ERROR] LicoPrint no esta instalado.
    echo.
    echo Por favor:
    echo 1. Descarga el instalador desde la pagina de configuracion
    echo 2. Ejecuta instalar-licoprint.bat
    echo 3. Vuelve a ejecutar este archivo
    echo.
    pause
    exit /b 1
)

:: Verificar si existe el script de inicio
if exist "%LICOPRINT_DIR%\start.bat" (
    echo Iniciando LicoPrint...
    start "" "%LICOPRINT_DIR%\start.bat"
    timeout /t 2 /nobreak >nul
    start "" "http://localhost:2026"
    echo.
    echo LicoPrint iniciado en http://localhost:2026
    timeout /t 3
    exit /b 0
)

:: Buscar PHP e iniciar manualmente
set "PHP_EXE="

:: Leer ruta de PHP guardada
if exist "%LICOPRINT_DIR%\php_path.txt" (
    set /p PHP_EXE=<"%LICOPRINT_DIR%\php_path.txt"
    if exist "!PHP_EXE!" goto :START_PHP
)

:: Buscar PHP en ubicaciones comunes
where php >nul 2>nul
if %ERRORLEVEL% equ 0 (
    for /f "tokens=*" %%i in ('where php') do (
        set "PHP_EXE=%%i"
        goto :START_PHP
    )
)

for /d %%i in (C:\laragon\bin\php\php-8.*) do (
    if exist "%%i\php.exe" (
        set "PHP_EXE=%%i\php.exe"
        goto :START_PHP
    )
)

if exist "C:\xampp\php\php.exe" (
    set "PHP_EXE=C:\xampp\php\php.exe"
    goto :START_PHP
)

if exist "%LICOPRINT_DIR%\php\php.exe" (
    set "PHP_EXE=%LICOPRINT_DIR%\php\php.exe"
    goto :START_PHP
)

echo [ERROR] PHP no encontrado.
echo Por favor ejecuta el instalador nuevamente.
pause
exit /b 1

:START_PHP
echo Iniciando servidor PHP...
echo PHP: %PHP_EXE%
echo.
start "" "%PHP_EXE%" -S localhost:2026 -t "%LICOPRINT_DIR%" "%LICOPRINT_DIR%\server.php"
timeout /t 2 /nobreak >nul
start "" "http://localhost:2026"
echo.
echo LicoPrint iniciado en http://localhost:2026
timeout /t 3
