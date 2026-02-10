@echo off
title LicoPrint - Iniciador
echo ============================================
echo           LicoPrint - Servicio Local
echo ============================================
echo.

:: Verificar si LicoPrint está instalado
if exist "%PROGRAMFILES%\LicoPrint\LicoPrint.exe" (
    echo Iniciando LicoPrint desde Program Files...
    start "" "%PROGRAMFILES%\LicoPrint\LicoPrint.exe"
    goto :end
)

if exist "%PROGRAMFILES(x86)%\LicoPrint\LicoPrint.exe" (
    echo Iniciando LicoPrint desde Program Files (x86)...
    start "" "%PROGRAMFILES(x86)%\LicoPrint\LicoPrint.exe"
    goto :end
)

if exist "%LOCALAPPDATA%\LicoPrint\LicoPrint.exe" (
    echo Iniciando LicoPrint desde AppData Local...
    start "" "%LOCALAPPDATA%\LicoPrint\LicoPrint.exe"
    goto :end
)

if exist "%USERPROFILE%\LicoPrint\LicoPrint.exe" (
    echo Iniciando LicoPrint desde carpeta de usuario...
    start "" "%USERPROFILE%\LicoPrint\LicoPrint.exe"
    goto :end
)

:: Si no se encuentra, mostrar mensaje
echo.
echo [ERROR] LicoPrint no esta instalado.
echo.
echo Por favor:
echo 1. Descarga el instalador desde la pagina de configuracion
echo 2. Ejecuta el instalador
echo 3. Vuelve a ejecutar este archivo
echo.
pause
exit /b 1

:end
echo.
echo LicoPrint iniciado correctamente.
echo El servicio estara disponible en: http://localhost:2026
echo.
echo Puedes cerrar esta ventana.
timeout /t 5
