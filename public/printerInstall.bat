@echo off
setlocal enabledelayedexpansion
title Instalador MiSocio Printer
color 0F
cls

:: ============================================
:: VERSION DEL INSTALADOR
:: ============================================
set "INSTALLER_VERSION=2.0.0"
set "INSTALLER_DATE=2026-03-09"

:: Configurar variables
set "PRINTER_PATH=C:\MiSocioPrinter"
set "PHP_PATH=C:\MiSocioPrinter_PHP"
set "TEMP_DIR=%TEMP%\MiSocioPrinter_Setup"
set "PORT=5421"
set "GIT_URL=https://github.com/git-for-windows/git/releases/download/v2.48.1.windows.1/Git-2.48.1-64-bit.exe"
set "COMPOSER_URL=https://getcomposer.org/Composer-Setup.exe"
set "PHP_URL=https://windows.php.net/downloads/releases/php-8.2.27-Win32-vs16-x64.zip"
set "REPO_URL=https://github.com/Osauro/MiSocioPrinter.git"

:: ============================================
:: PANTALLA DE BIENVENIDA
:: ============================================
echo ============================================
echo     MISOCIO PRINTER - Instalador
echo     Version: %INSTALLER_VERSION% (%INSTALLER_DATE%)
echo ============================================
echo.
echo [VERSION] Instalador MiSocio Printer v%INSTALLER_VERSION%
echo [INFO] Este instalador configurara MiSocio Printer completamente
echo [INFO] Instalara Git, Composer, PHP y MiSocio Printer
echo [INFO] Configurara el servidor para iniciar automaticamente
echo.
echo [WARNING] Este proceso puede tardar 15-30 minutos
echo [INFO] Presiona CTRL+C para cancelar en cualquier momento
echo.
pause
cls

echo ============================================
echo     INSTALACION EN PROGRESO...
echo ============================================
echo.

:: ============================================
:: PASO 1: ELIMINAR INSTALACION PREVIA
:: ============================================
echo [1/9] Eliminando instalacion previa...
echo [INFO] Verificando instalacion previa de MiSocio Printer...
if exist "%PRINTER_PATH%" (
    echo [WARNING] Encontrada instalacion previa en %PRINTER_PATH%
    echo [INFO] Deteniendo servicios relacionados...

    :: Detener procesos PHP del puerto 5421
    for /f "tokens=5" %%a in ('netstat -ano ^| findstr ":5421" ^| findstr "LISTENING"') do (
        taskkill /F /PID %%a 2>nul
    )

    taskkill /F /IM php.exe /T 2>nul
    timeout /t 3 /nobreak >nul

    echo [INFO] Eliminando directorio existente...
    rmdir /S /Q "%PRINTER_PATH%" 2>nul
    if exist "%PRINTER_PATH%" (
        echo [WARNING] Algunos archivos no se pudieron eliminar, continuando...
    ) else (
        echo [OK] Directorio eliminado correctamente.
    )
) else (
    echo [OK] No hay instalacion previa.
)
echo.
timeout /t 2 /nobreak >nul

:: ============================================
:: PASO 2: VERIFICAR E INSTALAR GIT
:: ============================================
echo [2/9] Verificando Git...
echo [INFO] Comprobando si Git esta instalado...
where git >nul 2>&1
if %errorlevel% equ 0 (
    echo [CHECK] Git encontrado en PATH.
    echo [INFO] Verificando si es ejecutable...

    for /f "tokens=*" %%i in ('where git 2^>nul') do (
        if exist "%%i" (
            echo [OK] Git ya esta instalado y es accesible.
            echo [INFO] Archivo encontrado en: %%i
            "%%i" --version 2>&1 | findstr /C:"git version"
            timeout /t 2 /nobreak >nul
            goto :git_complete
        )
    )

    echo [WARNING] Git en PATH pero archivo no accesible.
    echo [INFO] Procediendo con reinstalacion...
    goto :install_git
) else (
    echo [INFO] Git no encontrado en PATH.
    goto :install_git
)

:install_git
    echo [INFO] Git no encontrado. Descargando e instalando...
    if not exist "%TEMP_DIR%" mkdir "%TEMP_DIR%"
    cd /d "%TEMP_DIR%"

    echo [DOWNLOAD] Descargando Git desde GitHub...
    echo [INFO] Esto puede tardar varios minutos...
    powershell -ExecutionPolicy Bypass -Command "try { [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; $wc = New-Object System.Net.WebClient; $wc.DownloadTimeout = 300000; $wc.DownloadFile('%GIT_URL%', 'git-installer.exe'); Write-Host '[OK] Git descargado' } catch { Write-Host '[ERROR] Error descargando Git: ' + $_.Exception.Message; exit 1 }"

    if %errorlevel% neq 0 (
        echo [ERROR] No se pudo descargar Git.
        goto :error
    )

    if exist "git-installer.exe" (
        echo [INSTALL] Instalando Git silenciosamente...
        echo [INFO] Este proceso puede tardar 5-10 minutos...
        start /wait git-installer.exe /VERYSILENT /NORESTART /NOCANCEL /SP- /COMPONENTS="icons,ext\reg\shellhere,assoc,assoc_sh"

        :: Agregar Git al PATH
        set "PATH=%PATH%;C:\Program Files\Git\bin;C:\Program Files\Git\cmd"
        setx PATH "%PATH%;C:\Program Files\Git\bin;C:\Program Files\Git\cmd" /M >nul 2>&1

        :: Verificar instalacion
        timeout /t 5 /nobreak >nul
        where git >nul 2>&1
        if %errorlevel% equ 0 (
            echo [OK] Git instalado correctamente.
            git --version
        ) else (
            echo [ERROR] Error instalando Git.
            goto :error
        )
    ) else (
        echo [ERROR] No se pudo descargar el instalador de Git.
        goto :error
    )

:git_complete
echo.
timeout /t 2 /nobreak >nul

:: ============================================
:: PASO 3: VERIFICAR E INSTALAR COMPOSER
:: ============================================
echo [3/9] Verificando Composer...
echo [INFO] Comprobando si Composer esta instalado...

where composer >nul 2>&1
if %errorlevel% equ 0 (
    echo [CHECK] Composer encontrado en PATH.
    echo [INFO] Verificando si es ejecutable...

    for /f "tokens=*" %%i in ('where composer 2^>nul') do (
        if exist "%%i" (
            echo [OK] Composer ya esta instalado y es accesible.
            echo [INFO] Archivo encontrado en: %%i
            timeout /t 2 /nobreak >nul
            goto :composer_complete
        )
    )

    echo [WARNING] Composer en PATH pero archivo no accesible.
    echo [INFO] Procediendo con reinstalacion...
    goto :install_composer
) else (
    echo [INFO] Composer no encontrado en PATH.
    goto :install_composer
)

:install_composer
    echo [INFO] Composer no encontrado. Iniciando instalacion...
    if not exist "%TEMP_DIR%" mkdir "%TEMP_DIR%"
    cd /d "%TEMP_DIR%"

    echo [DOWNLOAD] Descargando Composer desde getcomposer.org...
    echo [INFO] Esto puede tardar unos minutos...
    powershell -ExecutionPolicy Bypass -Command "try { [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; $wc = New-Object System.Net.WebClient; $wc.Headers.Add('User-Agent', 'Mozilla/5.0'); $wc.DownloadTimeout = 300000; $wc.DownloadFile('%COMPOSER_URL%', 'composer-setup.exe'); Write-Host '[OK] Composer descargado' } catch { Write-Host '[ERROR] Error descargando Composer: ' + $_.Exception.Message; exit 1 }" 2>nul

    if %errorlevel% neq 0 (
        echo [RETRY] Intentando descarga alternativa de Composer...
        powershell -ExecutionPolicy Bypass -Command "try { Invoke-WebRequest -Uri '%COMPOSER_URL%' -OutFile 'composer-setup.exe' -UserAgent 'Mozilla/5.0' -TimeoutSec 300 } catch { Write-Host '[ERROR] Fallo descarga alternativa: ' + $_.Exception.Message; exit 1 }" 2>nul
    )

    if exist "composer-setup.exe" (
        echo [OK] Composer descargado correctamente.
        echo [INSTALL] Instalando Composer...
        echo [INFO] Puede aparecer ventana UAC, acepta para continuar

        start /wait composer-setup.exe /VERYSILENT /NORESTART /SUPPRESSMSGBOXES

        :: Verificar instalacion
        timeout /t 5 /nobreak >nul
        where composer >nul 2>&1
        if %errorlevel% equ 0 (
            echo [OK] Composer instalado correctamente.
        ) else (
            :: Agregar rutas comunes de Composer al PATH
            echo [PATH] Configurando variables de entorno...
            set "PATH=%PATH%;C:\Users\%USERNAME%\AppData\Roaming\Composer\vendor\bin"
            set "PATH=%PATH%;C:\ProgramData\ComposerSetup\bin"
            setx PATH "%PATH%;C:\Users\%USERNAME%\AppData\Roaming\Composer\vendor\bin;C:\ProgramData\ComposerSetup\bin" /M >nul 2>&1

            timeout /t 3 /nobreak >nul
            where composer >nul 2>&1
            if %errorlevel% equ 0 (
                echo [OK] Composer configurado correctamente.
            ) else (
                echo [WARNING] Composer instalado pero no detectado en PATH.
                echo [INFO] Continuando instalacion...
            )
        )
    ) else (
        echo [ERROR] No se pudo descargar Composer.
        echo [INFO] Verificando conexion a Internet...
        ping -n 1 getcomposer.org >nul 2>&1
        if %errorlevel% neq 0 (
            echo [ERROR] Sin conexion a Internet.
            goto :error
        ) else (
            echo [WARNING] Conexion OK pero descarga fallo.
            echo [INFO] Continuando instalacion...
        )
    )

:composer_complete
echo.
timeout /t 2 /nobreak >nul

:: ============================================
:: PASO 4: VERIFICAR E INSTALAR PHP
:: ============================================
echo [4/9] Verificando PHP...
echo [INFO] Comprobando si PHP esta instalado...

set "PHP_EXECUTABLE="
where php >nul 2>&1
if %errorlevel% equ 0 (
    echo [CHECK] PHP encontrado en PATH del sistema.

    for /f "tokens=*" %%i in ('where php 2^>nul') do (
        if exist "%%i" (
            set "PHP_EXECUTABLE=%%i"
            echo [OK] PHP del sistema encontrado y accesible.
            echo [INFO] Ruta: %%i

            echo [INFO] Verificando version de PHP...
            "%%i" --version 2>&1 | findstr /C:"PHP" >nul
            if !errorlevel! equ 0 (
                "%%i" --version 2>&1 | findstr /C:"PHP"
                echo [OK] Usaremos el PHP existente del sistema.
                echo [DECISION] No es necesario descargar PHP adicional.
                goto :php_complete
            )
        )
    )
)

echo [INFO] PHP no encontrado en el sistema.
echo [DECISION] Se descargara PHP independiente para MiSocio Printer.
goto :install_php

:install_php
    echo [INFO] Descargando e instalando PHP 8.2 independiente...
    if not exist "%TEMP_DIR%" mkdir "%TEMP_DIR%"
    cd /d "%TEMP_DIR%"

    echo [DOWNLOAD] Descargando PHP 8.2...
    echo [INFO] Esto puede tardar varios minutos...
    powershell -ExecutionPolicy Bypass -Command "try { [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; $wc = New-Object System.Net.WebClient; $wc.DownloadTimeout = 300000; $wc.DownloadFile('%PHP_URL%', 'php.zip'); Write-Host '[OK] PHP descargado' } catch { Write-Host '[ERROR] Error descargando PHP: ' + $_.Exception.Message; exit 1 }"

    if %errorlevel% neq 0 (
        echo [ERROR] No se pudo descargar PHP.
        goto :error
    )

    if exist "php.zip" (
        echo [INSTALL] Instalando PHP en %PHP_PATH%...
        if not exist "%PHP_PATH%" mkdir "%PHP_PATH%"
        powershell -ExecutionPolicy Bypass -Command "try { Expand-Archive -Path 'php.zip' -DestinationPath '%PHP_PATH%' -Force; Write-Host '[OK] PHP extraido' } catch { Write-Host '[ERROR] Error extrayendo PHP: ' + $_.Exception.Message; exit 1 }"

        if %errorlevel% neq 0 (
            echo [ERROR] No se pudo extraer PHP.
            goto :error
        )

        :: Configurar PHP
        echo [CONFIG] Configurando PHP...
        if exist "%PHP_PATH%\php.ini-development" (
            copy "%PHP_PATH%\php.ini-development" "%PHP_PATH%\php.ini" >nul 2>&1
        )

        :: Habilitar extensiones necesarias
        powershell -ExecutionPolicy Bypass -Command "(Get-Content '%PHP_PATH%\php.ini') -replace ';extension=mbstring','extension=mbstring' -replace ';extension=openssl','extension=openssl' -replace ';extension=pdo_mysql','extension=pdo_mysql' -replace ';extension=curl','extension=curl' -replace ';extension=fileinfo','extension=fileinfo' | Set-Content '%PHP_PATH%\php.ini'"

        set "PHP_EXECUTABLE=%PHP_PATH%\php.exe"

        :: Verificar instalacion
        timeout /t 3 /nobreak >nul
        "%PHP_EXECUTABLE%" --version >nul 2>&1
        if %errorlevel% equ 0 (
            echo [OK] PHP instalado correctamente en %PHP_PATH%.
            "%PHP_EXECUTABLE%" --version | findstr /C:"PHP"
            echo [INFO] PHP independiente configurado para MiSocio Printer.
        ) else (
            echo [ERROR] Error instalando PHP.
            goto :error
        )
    ) else (
        echo [ERROR] No se pudo descargar el archivo de PHP.
        goto :error
    )

:php_complete
echo.
timeout /t 2 /nobreak >nul

:: ============================================
:: PASO 5: CLONAR REPOSITORIO
:: ============================================
echo [5/9] Clonando repositorio MiSocio Printer...
echo [INFO] Clonando desde %REPO_URL%
cd /d "C:\"

echo [CLONE] Ejecutando git clone...
git clone "%REPO_URL%" "%PRINTER_PATH%"
if %errorlevel% equ 0 (
    echo [OK] Repositorio clonado correctamente.
) else (
    echo [ERROR] Error clonando repositorio. Verificando conexion...
    ping -n 1 github.com >nul 2>&1
    if %errorlevel% equ 0 (
        echo [RETRY] Reintentando clonado...
        git clone "%REPO_URL%" "%PRINTER_PATH%"
        if %errorlevel% neq 0 (
            echo [ERROR] Error persistente clonando repositorio.
            goto :error
        )
    ) else (
        echo [ERROR] Sin conexion a GitHub.
        goto :error
    )
)

if not exist "%PRINTER_PATH%" (
    echo [ERROR] El directorio MiSocio Printer no se creo correctamente.
    goto :error
)
echo [OK] Directorio MiSocio Printer creado en C:\
echo.
timeout /t 2 /nobreak >nul

:: ============================================
:: PASO 6: CONFIGURAR .ENV
:: ============================================
echo [6/9] Configurando archivo .env...
cd /d "%PRINTER_PATH%"

if exist ".env.example" (
    echo [INFO] Copiando .env.example a .env...
    copy ".env.example" ".env" >nul
    echo [OK] Archivo .env creado desde .env.example
) else (
    echo [INFO] Creando .env con valores por defecto...
    (
        echo APP_NAME=MiSocioPrinter
        echo APP_ENV=local
        echo APP_DEBUG=false
        echo APP_URL=http://localhost:%PORT%
    ) > ".env"
    echo [OK] Archivo .env creado con valores por defecto
)
echo.
timeout /t 2 /nobreak >nul

:: ============================================
:: PASO 7: INSTALAR DEPENDENCIAS
:: ============================================
echo [7/9] Instalando dependencias con Composer...
echo [INFO] Este proceso puede tardar 10-15 minutos...
cd /d "%PRINTER_PATH%"

set "COMPOSER_RESULT=1"

:: Intentar usar composer del sistema primero
echo [INFO] Buscando Composer en el sistema...
where composer >nul 2>&1
if %errorlevel% equ 0 (
    echo [INFO] Composer encontrado. Instalando dependencias...
    composer install --no-interaction --prefer-dist --optimize-autoloader
    set "COMPOSER_RESULT=!errorlevel!"
    echo [DEBUG] Composer result code: !COMPOSER_RESULT!
) else if exist "C:\ProgramData\ComposerSetup\bin\composer.bat" (
    echo [INFO] Usando Composer desde C:\ProgramData\ComposerSetup\bin...
    call "C:\ProgramData\ComposerSetup\bin\composer.bat" install --no-interaction --prefer-dist --optimize-autoloader
    set "COMPOSER_RESULT=!errorlevel!"
    echo [DEBUG] Composer result code: !COMPOSER_RESULT!
) else (
    echo [WARNING] Composer no encontrado en rutas comunes.
    echo [INFO] Intentando con composer.phar local...
    if exist "%PRINTER_PATH%\composer.phar" (
        php composer.phar install --no-interaction --prefer-dist --optimize-autoloader
        set "COMPOSER_RESULT=!errorlevel!"
        echo [DEBUG] Composer.phar result code: !COMPOSER_RESULT!
    ) else (
        echo [ERROR] No se encontro composer.phar en el proyecto
        set "COMPOSER_RESULT=1"
    )
)

echo [DEBUG] Final COMPOSER_RESULT: !COMPOSER_RESULT!
if !COMPOSER_RESULT! neq 0 (
    echo [ERROR] Fallo la instalacion de dependencias.
    echo [ERROR] Codigo de error: !COMPOSER_RESULT!
    echo [INFO] Revisa los mensajes anteriores para mas detalles.
    echo [INFO] Presiona cualquier tecla para ver mas informacion...
    pause >nul
    goto :error
)

echo [OK] Dependencias instaladas correctamente.
echo [INFO] Verificando directorio vendor...
if exist "%PRINTER_PATH%\vendor" (
    echo [OK] Directorio vendor creado correctamente.
) else (
    echo [WARNING] Directorio vendor no encontrado, pero continuando...
)
echo.
timeout /t 2 /nobreak >nul

:: ============================================
:: PASO 8: CREAR ARCHIVOS DE INICIO AUTOMATICO
:: ============================================
echo [8/9] Creando archivos de inicio automatico...

:: Crear printerStart.bat
(
echo @echo off
echo setlocal EnableDelayedExpansion
echo chcp 65001 ^>nul 2^>^&1
echo.
echo set "PRINTER_PATH=%PRINTER_PATH%"
echo set "LOG_FILE=%%PRINTER_PATH%%\startup.log"
echo set "PORT=%PORT%"
echo.
echo echo [%%date%% %%time%%] Iniciando MiSocio Printer... ^>^> "%%LOG_FILE%%"
echo.
echo if not exist "%%PRINTER_PATH%%" ^(
echo     echo [%%date%% %%time%%] ERROR: Directorio no encontrado ^>^> "%%LOG_FILE%%"
echo     exit /b 1
echo ^)
echo.
echo cd /d "%%PRINTER_PATH%%"
echo.
echo echo [%%date%% %%time%%] Verificando actualizaciones... ^>^> "%%LOG_FILE%%"
echo git --version ^>nul 2^>^&1
echo if ^^!errorlevel^^! equ 0 ^(
echo     git fetch origin ^>nul 2^>^&1
echo     if ^^!errorlevel^^! equ 0 ^(
echo         git rev-parse HEAD ^> "%%TEMP%%\current_commit.txt"
echo         git rev-parse origin/main ^> "%%TEMP%%\remote_commit.txt" 2^>nul ^|^| git rev-parse origin/master ^> "%%TEMP%%\remote_commit.txt" 2^>nul
echo         fc "%%TEMP%%\current_commit.txt" "%%TEMP%%\remote_commit.txt" ^>nul 2^>^&1
echo         if ^^!errorlevel^^! neq 0 ^(
echo             echo [%%date%% %%time%%] Actualizacion disponible. Aplicando... ^>^> "%%LOG_FILE%%"
echo             git pull ^>nul 2^>^&1
echo             if ^^!errorlevel^^! equ 0 ^(
echo                 echo [%%date%% %%time%%] Actualizacion aplicada ^>^> "%%LOG_FILE%%"
echo                 git diff HEAD@{1} HEAD --name-only ^| findstr "composer.json" ^>nul
echo                 if ^^!errorlevel^^! equ 0 ^(
echo                     composer install --no-interaction --prefer-dist --optimize-autoloader ^>nul 2^>^&1
echo                     echo [%%date%% %%time%%] Dependencias actualizadas ^>^> "%%LOG_FILE%%"
echo                 ^)
echo             ^)
echo         ^) else ^(
echo             echo [%%date%% %%time%%] Sistema actualizado ^>^> "%%LOG_FILE%%"
echo         ^)
echo         del "%%TEMP%%\current_commit.txt" "%%TEMP%%\remote_commit.txt" 2^>nul
echo     ^)
echo ^)
echo.
echo echo [%%date%% %%time%%] Verificando puerto %%PORT%%... ^>^> "%%LOG_FILE%%"
echo netstat -ano ^| findstr ":%%PORT%%" ^| findstr "LISTENING" ^>nul 2^>^&1
echo if ^^!errorlevel^^! equ 0 ^(
echo     echo [%%date%% %%time%%] Servidor ya en ejecucion ^>^> "%%LOG_FILE%%"
echo     exit /b 0
echo ^)
echo.
echo echo [%%date%% %%time%%] Iniciando servidor PHP... ^>^> "%%LOG_FILE%%"
echo set "WEBROOT=%%PRINTER_PATH%%"
echo if exist "%%PRINTER_PATH%%\public" set "WEBROOT=%%PRINTER_PATH%%\public"
echo.
echo powershell -WindowStyle Hidden -Command "Start-Process -FilePath 'php' -ArgumentList @^('-S','localhost:%%PORT%%','-t','%%WEBROOT%%'^) -WorkingDirectory '%%PRINTER_PATH%%' -WindowStyle Hidden"
echo timeout /t 3 /nobreak ^>nul
echo.
echo netstat -ano ^| findstr ":%%PORT%%" ^| findstr "LISTENING" ^>nul 2^>^&1
echo if ^^!errorlevel^^! equ 0 ^(
echo     echo [%%date%% %%time%%] Servidor iniciado en localhost:%%PORT%% ^>^> "%%LOG_FILE%%"
echo ^) else ^(
echo     echo [%%date%% %%time%%] ERROR: No se pudo iniciar servidor ^>^> "%%LOG_FILE%%"
echo     exit /b 1
echo ^)
echo exit /b 0
) > "%PRINTER_PATH%\printerStart.bat"

:: Crear printerStart.vbs
(
echo Set WshShell = CreateObject^("WScript.Shell"^)
echo WshShell.Run "%PRINTER_PATH%\printerStart.bat", 0, False
echo Set WshShell = Nothing
) > "%PRINTER_PATH%\printerStart.vbs"

:: Crear printerUninstall.bat
(
echo @echo off
echo cls
echo echo ============================================
echo echo     Desinstalar MiSocio Printer
echo echo ============================================
echo echo.
echo echo [INFO] Eliminando tarea programada...
echo schtasks /Query /TN "MiSocioPrinter" ^>nul 2^>^&1
echo if %%errorlevel%% equ 0 ^(
echo     schtasks /Delete /TN "MiSocioPrinter" /F
echo     echo [OK] Tarea eliminada.
echo ^) else ^(
echo     echo [INFO] No se encontro tarea programada.
echo ^)
echo.
echo echo [INFO] Deteniendo servidor PHP...
echo for /f "tokens=5" %%%%a in ^('netstat -ano ^^^| findstr ":%PORT%" ^^^| findstr "LISTENING"'^) do ^(
echo     taskkill /F /PID %%%%a ^>nul 2^>^&1
echo ^)
echo echo [OK] Servidor detenido.
echo.
echo echo ============================================
echo echo [OK] Desinstalacion completada
echo echo ============================================
echo echo.
echo echo El directorio %PRINTER_PATH% no fue eliminado.
echo echo Para eliminarlo manualmente:
echo echo   rmdir /s /q %PRINTER_PATH%
echo echo.
echo pause
) > "%PRINTER_PATH%\printerUninstall.bat"

echo [INFO] Verificando archivos creados...
set "FILES_OK=1"

if not exist "%PRINTER_PATH%\printerStart.bat" (
    echo [ERROR] No se pudo crear printerStart.bat
    set "FILES_OK=0"
) else (
    echo [OK] printerStart.bat creado
)

if not exist "%PRINTER_PATH%\printerStart.vbs" (
    echo [ERROR] No se pudo crear printerStart.vbs
    set "FILES_OK=0"
) else (
    echo [OK] printerStart.vbs creado
)

if not exist "%PRINTER_PATH%\printerUninstall.bat" (
    echo [ERROR] No se pudo crear printerUninstall.bat
    set "FILES_OK=0"
) else (
    echo [OK] printerUninstall.bat creado
)

if !FILES_OK! equ 0 (
    echo [ERROR] Algunos archivos no se pudieron crear
    echo [INFO] Presiona cualquier tecla para continuar de todos modos...
    pause >nul
) else (
    echo [OK] Todos los archivos de inicio creados correctamente.
)
echo.
timeout /t 2 /nobreak >nul

:: ============================================
:: PASO 9: CONFIGURAR TAREA PROGRAMADA
:: ============================================
echo [9/9] Configurando inicio automatico con Windows...

schtasks /Query /TN "MiSocioPrinter" >nul 2>&1
if %errorlevel% equ 0 (
    echo [INFO] Eliminando tarea programada existente...
    schtasks /Delete /TN "MiSocioPrinter" /F >nul 2>&1
)

echo [INFO] Creando tarea programada...
schtasks /Create /TN "MiSocioPrinter" /TR "wscript.exe \"%PRINTER_PATH%\printerStart.vbs\"" /SC ONLOGON /RL HIGHEST /F >nul 2>&1

if %errorlevel% equ 0 (
    echo [OK] Inicio automatico configurado correctamente.
) else (
    echo [WARNING] No se pudo configurar el inicio automatico.
    echo [INFO] Puedes ejecutar manualmente: %PRINTER_PATH%\printerStart.bat
)
echo.

:: ============================================
:: FINALIZAR INSTALACION
:: ============================================
echo ============================================
echo     INSTALACION COMPLETADA
echo ============================================
echo.
echo [OK] MiSocio Printer instalado correctamente en:
echo      %PRINTER_PATH%
echo.
echo [OK] Configuracion:
echo      - Puerto: %PORT%
echo      - URL: http://localhost:%PORT%
echo      - Inicio automatico: ACTIVADO
echo      - Actualizaciones automaticas: SI
echo.
echo [INFO] Iniciando servidor por primera vez...

:: Determinar que PHP usar
set "USE_PHP=php"
if defined PHP_EXECUTABLE (
    set "USE_PHP=%PHP_EXECUTABLE%"
    echo [INFO] Usando PHP instalado: %PHP_EXECUTABLE%
) else (
    echo [INFO] Usando PHP del sistema
)

:: Verificar que PHP funciona
echo [INFO] Verificando PHP...
"%USE_PHP%" --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP no es ejecutable
    echo [WARNING] El servidor no se pudo iniciar automaticamente
    echo [INFO] Intenta ejecutar manualmente: %PRINTER_PATH%\printerStart.bat
    goto :skip_server_start
)

echo [OK] PHP verificado correctamente
"%USE_PHP%" --version | findstr /C:"PHP"

cd /d "%PRINTER_PATH%"
set "WEBROOT=%PRINTER_PATH%"
if exist "%PRINTER_PATH%\public" (
    set "WEBROOT=%PRINTER_PATH%\public"
    echo [INFO] Usando webroot: public\
) else (
    echo [INFO] Usando webroot: raiz del proyecto
)

echo [START] Ejecutando servidor en puerto %PORT%...
echo [CMD] "%USE_PHP%" -S localhost:%PORT% -t "%WEBROOT%"
start "MiSocio Printer Server" /MIN "%USE_PHP%" -S localhost:%PORT% -t "%WEBROOT%"
echo [INFO] Esperando 5 segundos para que el servidor inicie...
timeout /t 5 /nobreak >nul

:: Verificar que el servidor inicio
echo [INFO] Verificando que el servidor esta escuchando en puerto %PORT%...
netstat -ano | findstr ":%PORT%" | findstr "LISTENING" >nul 2>&1
if %errorlevel% equ 0 (
    echo [OK] Servidor iniciado correctamente.
    echo.
    echo [INFO] Abriendo navegador en http://localhost:%PORT%
    timeout /t 2 /nobreak >nul
    start "" "http://localhost:%PORT%"
    echo [OK] Navegador abierto
) else (
    echo [WARNING] El servidor no inicio automaticamente.
    echo [INFO] Verifica el puerto %PORT% manualmente con: netstat -ano ^| findstr ":%PORT%"
    echo [INFO] O ejecuta manualmente: %PRINTER_PATH%\printerStart.bat
)

:skip_server_start

:: Limpiar archivos temporales
echo.
echo [CLEANUP] Limpiando archivos temporales...
if exist "%TEMP_DIR%" rmdir /S /Q "%TEMP_DIR%" 2>nul

echo.
echo ============================================
echo [SUCCESS] Instalacion finalizada exitosamente
echo ============================================
echo.
echo El servidor se iniciara automaticamente al iniciar Windows.
echo.
echo Para desinstalar: ejecuta %PRINTER_PATH%\printerUninstall.bat
echo.
echo Presiona cualquier tecla para cerrar...
pause >nul
exit /b 0

:: ============================================
:: MANEJO DE ERRORES
:: ============================================
:error
echo.
echo ============================================
echo                   ERROR
echo ============================================
echo.
echo [ERROR] La instalacion no se pudo completar.
echo [INFO] Revisa los mensajes anteriores para mas detalles.
echo.
echo POSIBLES SOLUCIONES:
echo 1. Ejecuta este instalador como Administrador
echo 2. Verifica tu conexion a Internet
echo 3. Desactiva temporalmente el antivirus
echo 4. Asegurate de tener suficiente espacio en disco
echo 5. Cierra otros programas que puedan interferir
echo.
echo Si el problema persiste, contacta a soporte tecnico.
echo.
echo [INFO] Presiona cualquier tecla para salir...
pause >nul

:: Limpiar en caso de error
if exist "%TEMP_DIR%" rmdir /S /Q "%TEMP_DIR%" 2>nul

exit /b 1
