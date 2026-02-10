@echo off
setlocal enabledelayedexpansion

set "LICOPRINT_DIR=%LOCALAPPDATA%\LicoPrint"

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
    echo LicoPrint ya esta corriendo en http://localhost:2026
    start "" "http://localhost:2026"
    exit /b 0
)

:: Usar el script silencioso si existe
if exist "%LICOPRINT_DIR%\start-silent.vbs" (
    start "" wscript.exe "%LICOPRINT_DIR%\start-silent.vbs"
    timeout /t 2 /nobreak >nul
    start "" "http://localhost:2026"
    exit /b 0
)

:: Fallback: buscar PHP e iniciar silenciosamente
set "PHP_EXE="

if exist "%LICOPRINT_DIR%\php_path.txt" (
    set /p PHP_EXE=<"%LICOPRINT_DIR%\php_path.txt"
    if exist "!PHP_EXE!" goto :START_SILENT
)

where php >nul 2>nul
if %ERRORLEVEL% equ 0 (
    for /f "tokens=*" %%i in ('where php') do (
        set "PHP_EXE=%%i"
        goto :START_SILENT
    )
)

for /d %%i in (C:\laragon\bin\php\php-8.*) do (
    if exist "%%i\php.exe" (
        set "PHP_EXE=%%i\php.exe"
        goto :START_SILENT
    )
)

if exist "C:\xampp\php\php.exe" (
    set "PHP_EXE=C:\xampp\php\php.exe"
    goto :START_SILENT
)

if exist "%LICOPRINT_DIR%\php\php.exe" (
    set "PHP_EXE=%LICOPRINT_DIR%\php\php.exe"
    goto :START_SILENT
)

echo [ERROR] PHP no encontrado.
pause
exit /b 1

:START_SILENT
:: Crear VBS temporal para inicio silencioso
(
echo Set WshShell = CreateObject^("WScript.Shell"^)
echo WshShell.Run """%PHP_EXE%"" -S localhost:2026 -t ""%LICOPRINT_DIR%"" ""%LICOPRINT_DIR%\server.php""", 0, False
echo Set WshShell = Nothing
) > "%TEMP%\licoprint_start.vbs"

start "" wscript.exe "%TEMP%\licoprint_start.vbs"
timeout /t 2 /nobreak >nul
start "" "http://localhost:2026"
exit /b 0
