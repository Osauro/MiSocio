@echo off
setlocal enabledelayedexpansion

set "LICOPRINT_DIR=C:\LicoPrint"

:: Verificar si LicoPrint esta instalado
if not exist "%LICOPRINT_DIR%\server.php" (
    echo [ERROR] LicoPrint no esta instalado.
    echo Por favor ejecuta el instalador primero.
    pause
    exit /b 1
)

:: Verificar si ya esta corriendo
netstat -an | findstr ":2026" | findstr "LISTENING" >nul 2>nul
if %ERRORLEVEL% equ 0 (
    start "" "http://localhost:2026"
    exit /b 0
)

:: Usar el script silencioso si existe
if exist "%LICOPRINT_DIR%\start-silent.vbs" (
    start "" wscript.exe "%LICOPRINT_DIR%\start-silent.vbs"
    timeout /t 3 /nobreak >nul
    start "" "http://localhost:2026"
    exit /b 0
)

:: Fallback: ejecutar start.bat directamente (oculto)
if exist "%LICOPRINT_DIR%\start.bat" (
    start /min "" "%LICOPRINT_DIR%\start.bat"
    timeout /t 3 /nobreak >nul
    start "" "http://localhost:2026"
    exit /b 0
)

echo [ERROR] Archivos de inicio no encontrados.
echo Reinstala LicoPrint.
pause
exit /b 1
