@echo off
title Compilar LicoPrint
echo ============================================
echo         Compilando LicoPrint
echo ============================================
echo.

:: Verificar si Go está instalado
where go >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERROR] Go no esta instalado.
    echo.
    echo Por favor instala Go desde: https://go.dev/dl/
    echo.
    pause
    exit /b 1
)

echo Versión de Go:
go version
echo.

:: Compilar para Windows
echo Compilando para Windows (64-bit)...
set GOOS=windows
set GOARCH=amd64
go build -ldflags="-s -w -H windowsgui" -o ..\public\downloads\LicoPrint.exe .

if %ERRORLEVEL% equ 0 (
    echo.
    echo [OK] Compilacion exitosa!
    echo.
    echo Archivo generado: ..\public\downloads\LicoPrint.exe
    echo.
) else (
    echo.
    echo [ERROR] La compilacion fallo.
    echo.
)

pause
