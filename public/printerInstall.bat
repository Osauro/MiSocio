@echo off
:: ============================================================
:: Instalador MiSocio Printer
:: Sistema de impresion automatizado con actualizaciones
:: ============================================================

:: Verificar si ya se ejecuto con privilegios elevados
>nul 2>&1 "%SystemRoot%\system32\cacls.exe" "%SystemRoot%\system32\config\system"
if %errorlevel% neq 0 (
    echo.
    echo  ============================================
    echo    Se requieren permisos de Administrador
    echo  ============================================
    echo.
    echo  Este instalador necesita permisos elevados para:
    echo  - Instalar software del sistema (Git, PHP, Composer)
    echo  - Modificar variables de entorno
    echo  - Crear tareas programadas de inicio
    echo.
    echo  Se abrira una ventana solicitando permisos...
    echo.
    timeout /t 3 /nobreak
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit
)

setlocal EnableDelayedExpansion
title Instalador MiSocio Printer
color 0A
chcp 65001 >nul 2>&1

set "INSTALL_DIR=C:\MiSocioPrinter"
set "PHP_DIR=C:\php"
set "PORT=5421"
set "TEMP_DIR=%TEMP%\MiSocioInstaller"

mkdir "%TEMP_DIR%" 2>nul

cls
echo.
echo  ============================================
echo    Instalador MiSocio Printer v1.0
echo  ============================================
echo.
echo  Este instalador configurara automaticamente:
echo.
echo  [+] Git para control de versiones
echo  [+] PHP 8.2+ para el servidor web
echo  [+] Composer para gestionar dependencias
echo  [+] Servicio MiSocio Printer en C:\MiSocioPrinter
echo  [+] Inicio automatico con Windows
echo  [+] Actualizaciones automaticas
echo.
echo  El proceso puede tardar varios minutos...
echo.
echo  ============================================
echo.
pause

:: ============================================================
:: PASO 1: Verificar / Instalar GIT
:: ============================================================
echo  [1/7] Verificando GIT...
git --version >nul 2>&1
if !errorlevel! neq 0 (
    echo        GIT no encontrado. Descargando...
    powershell -Command "[Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12; (New-Object Net.WebClient).DownloadFile('https://github.com/git-for-windows/git/releases/download/v2.48.1.windows.1/Git-2.48.1-64-bit.exe','%TEMP_DIR%\git-setup.exe')"
    if !errorlevel! neq 0 (
        echo.
        echo  ============================================
        echo  ERROR: No se pudo descargar GIT
        echo  ============================================
        echo.
        echo  Verifica tu conexion a internet y vuelve a intentar.
        echo.
        pause
        exit /b 1
    )
    echo        Instalando GIT silenciosamente...
    "%TEMP_DIR%\git-setup.exe" /VERYSILENT /NORESTART /NOCANCEL /SP- /CLOSEAPPLICATIONS /COMPONENTS="icons,ext\reg\shellhere,assoc,assoc_sh"
    call :ADD_TO_PATH "C:\Program Files\Git\cmd"
    echo        GIT instalado correctamente.
) else (
    echo        GIT encontrado.
)

:: ============================================================
:: PASO 2: Verificar / Instalar PHP 8.2+
:: ============================================================
echo.
echo  [2/7] Verificando PHP 8.2+...
set "PHP_OK=0"
where php >nul 2>&1
if !errorlevel! equ 0 (
    powershell -Command "try { $v = (& php -r 'echo PHP_VERSION;' 2>$null); if ([version]$v -ge [version]'8.2.0') { exit 0 } else { exit 1 } } catch { exit 1 }" >nul 2>&1
    if !errorlevel! equ 0 set "PHP_OK=1"
)
if "!PHP_OK!"=="0" (
    echo        PHP 8.2+ no encontrado. Descargando...
    powershell -Command "[Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12; (New-Object Net.WebClient).DownloadFile('https://windows.php.net/downloads/releases/php-8.2.27-Win32-vs16-x64.zip','%TEMP_DIR%\php.zip')"
    if !errorlevel! neq 0 (
        echo.
        echo  ============================================
        echo  ERROR: No se pudo descargar PHP
        echo  ============================================
        echo.
        echo  Verifica tu conexion a internet y vuelve a intentar.
        echo.
        pause
        exit /b 1
    )
    echo        Extrayendo PHP en %PHP_DIR%...
    if not exist "%PHP_DIR%" mkdir "%PHP_DIR%"
    powershell -Command "Expand-Archive -Path '%TEMP_DIR%\php.zip' -DestinationPath '%PHP_DIR%' -Force"
    copy "%PHP_DIR%\php.ini-production" "%PHP_DIR%\php.ini" >nul 2>&1
    powershell -Command "(Get-Content '%PHP_DIR%\php.ini') -replace ';extension=mbstring','extension=mbstring' -replace ';extension=openssl','extension=openssl' -replace ';extension=pdo_mysql','extension=pdo_mysql' -replace ';extension=curl','extension=curl' -replace ';extension=fileinfo','extension=fileinfo' -replace ';extension=zip','extension=zip' | Set-Content '%PHP_DIR%\php.ini'"
    call :ADD_TO_PATH "%PHP_DIR%"
    echo        PHP 8.2 instalado correctamente.
) else (
    echo        PHP 8.2+ encontrado.
)

:: ============================================================
:: PASO 3: Verificar / Instalar Composer
:: ============================================================
echo.
echo  [3/7] Verificando Composer...
composer --version >nul 2>&1
if !errorlevel! neq 0 (
    echo        Composer no encontrado. Descargando...
    powershell -Command "[Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12; (New-Object Net.WebClient).DownloadFile('https://getcomposer.org/Composer-Setup.exe','%TEMP_DIR%\composer-setup.exe')"
    if !errorlevel! neq 0 (
        echo.
        echo  ============================================
        echo  ERROR: No se pudo descargar Composer
        echo  ============================================
        echo.
        echo  Verifica tu conexion a internet y vuelve a intentar.
        echo.
        pause
        exit /b 1
    )
    echo        Instalando Composer silenciosamente...
    "%TEMP_DIR%\composer-setup.exe" /VERYSILENT /NORESTART /PHP="%PHP_DIR%\php.exe"
    call :REFRESH_PATH
    echo        Composer instalado correctamente.
) else (
    echo        Composer encontrado.
)

:: ============================================================
:: PASO 4: Clonar repositorio
:: ============================================================
echo.
echo  [4/7] Clonando repositorio MiSocioPrinter...
if exist "%INSTALL_DIR%\.git" (
    echo        Repositorio existente. Actualizando...
    git -C "%INSTALL_DIR%" pull
) else (
    if exist "%INSTALL_DIR%" rmdir /s /q "%INSTALL_DIR%"
    git clone https://github.com/Osauro/MiSocioPrinter.git "%INSTALL_DIR%"
)
if !errorlevel! neq 0 (
    echo.
    echo  ============================================
    echo  ERROR: No se pudo clonar el repositorio
    echo  ============================================
    echo.
    echo  Verifica tu conexion a internet y que el repositorio exista.
    echo.
    pause
    exit /b 1
)
echo        Repositorio listo.

:: ============================================================
:: PASO 5: Generar .env
:: ============================================================
echo.
echo  [5/7] Configurando archivo .env...
cd /d "%INSTALL_DIR%"
if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo        .env creado desde .env.example
    ) else (
        (
            echo APP_NAME=MiSocioPrinter
            echo APP_ENV=local
            echo APP_DEBUG=false
            echo APP_URL=http://localhost:%PORT%
        ) > ".env"
        echo        .env creado con valores por defecto
    )
) else (
    echo        .env ya existe.
)

:: ============================================================
:: PASO 6: Instalar dependencias
:: ============================================================
echo.
echo  [6/7] Instalando dependencias con Composer...
cd /d "%INSTALL_DIR%"
composer install --no-interaction --prefer-dist --optimize-autoloader
if !errorlevel! neq 0 (
    echo.
    echo  ============================================
    echo  ERROR: Fallo la instalacion de dependencias
    echo  ============================================
    echo.
    echo  Revisa los mensajes de error anteriores.
    echo.
    pause
    exit /b 1
)
echo        Dependencias instaladas correctamente.

:: ============================================================
:: PASO 7: Crear archivos de inicio automatico y desinstalacion
:: ============================================================
echo.
echo  [7/9] Creando archivos de inicio automatico...

:: Crear printerStart.bat
(
echo @echo off
echo setlocal EnableDelayedExpansion
echo chcp 65001 ^>nul 2^>^&1
echo.
echo set "INSTALL_DIR=C:\MiSocioPrinter"
echo set "LOG_FILE=%%INSTALL_DIR%%\startup.log"
echo set "PORT=5421"
echo set "MAX_RETRIES=3"
echo.
echo echo [%%date%% %%time%%] Iniciando MiSocio Printer... ^>^> "%%LOG_FILE%%"
echo.
echo if not exist "%%INSTALL_DIR%%" ^(
echo     echo [%%date%% %%time%%] ERROR: Directorio %%INSTALL_DIR%% no encontrado ^>^> "%%LOG_FILE%%"
echo     exit /b 1
echo ^)
echo.
echo cd /d "%%INSTALL_DIR%%"
echo.
echo echo [%%date%% %%time%%] Verificando actualizaciones... ^>^> "%%LOG_FILE%%"
echo.
echo git --version ^>nul 2^>^&1
echo if ^^!errorlevel^^! equ 0 ^(
echo     for /L %%%%i in ^(1,1,%%MAX_RETRIES%%^) do ^(
echo         git fetch origin ^>nul 2^>^&1
echo         if ^^!errorlevel^^! equ 0 goto :git_fetch_ok
echo         timeout /t 2 /nobreak ^>nul
echo     ^)
echo     echo [%%date%% %%time%%] ADVERTENCIA: No se pudo conectar con el repositorio ^>^> "%%LOG_FILE%%"
echo     goto :skip_update
echo.
echo     :git_fetch_ok
echo     git rev-parse HEAD ^> "%%TEMP%%\current_commit.txt"
echo     git rev-parse origin/main ^> "%%TEMP%%\remote_commit.txt" 2^>nul ^|^| git rev-parse origin/master ^> "%%TEMP%%\remote_commit.txt" 2^>nul
echo.
echo     fc "%%TEMP%%\current_commit.txt" "%%TEMP%%\remote_commit.txt" ^>nul 2^>^&1
echo     if ^^!errorlevel^^! neq 0 ^(
echo         echo [%%date%% %%time%%] Actualizacion disponible. Aplicando... ^>^> "%%LOG_FILE%%"
echo         git pull ^>nul 2^>^&1
echo         if ^^!errorlevel^^! equ 0 ^(
echo             echo [%%date%% %%time%%] Actualizacion aplicada correctamente ^>^> "%%LOG_FILE%%"
echo.
echo             git diff HEAD@{1} HEAD --name-only ^| findstr "composer.json" ^>nul
echo             if ^^!errorlevel^^! equ 0 ^(
echo                 echo [%%date%% %%time%%] Actualizando dependencias... ^>^> "%%LOG_FILE%%"
echo                 composer install --no-interaction --prefer-dist --optimize-autoloader ^>nul 2^>^&1
echo                 echo [%%date%% %%time%%] Dependencias actualizadas ^>^> "%%LOG_FILE%%"
echo             ^)
echo         ^) else ^(
echo             echo [%%date%% %%time%%] ERROR: Fallo al aplicar actualizacion ^>^> "%%LOG_FILE%%"
echo         ^)
echo     ^) else ^(
echo         echo [%%date%% %%time%%] Sistema actualizado, sin cambios ^>^> "%%LOG_FILE%%"
echo     ^)
echo.
echo     del "%%TEMP%%\current_commit.txt" "%%TEMP%%\remote_commit.txt" 2^>nul
echo ^) else ^(
echo     echo [%%date%% %%time%%] ADVERTENCIA: Git no disponible ^>^> "%%LOG_FILE%%"
echo ^)
echo.
echo :skip_update
echo.
echo echo [%%date%% %%time%%] Verificando servicios en puerto %%PORT%%... ^>^> "%%LOG_FILE%%"
echo.
echo netstat -ano ^| findstr ":%%PORT%%" ^| findstr "LISTENING" ^>nul 2^>^&1
echo if ^^!errorlevel^^! equ 0 ^(
echo     echo [%%date%% %%time%%] Servidor ya en ejecucion en puerto %%PORT%% ^>^> "%%LOG_FILE%%"
echo     exit /b 0
echo ^)
echo.
echo echo [%%date%% %%time%%] Iniciando servidor PHP... ^>^> "%%LOG_FILE%%"
echo.
echo set "WEBROOT=%%INSTALL_DIR%%"
echo if exist "%%INSTALL_DIR%%\public" set "WEBROOT=%%INSTALL_DIR%%\public"
echo.
echo powershell -WindowStyle Hidden -Command "Start-Process -FilePath 'php' -ArgumentList @^('-S','localhost:%%PORT%%','-t','%%WEBROOT%%'^) -WorkingDirectory '%%INSTALL_DIR%%' -WindowStyle Hidden"
echo.
echo timeout /t 3 /nobreak ^>nul
echo.
echo netstat -ano ^| findstr ":%%PORT%%" ^| findstr "LISTENING" ^>nul 2^>^&1
echo if ^^!errorlevel^^! equ 0 ^(
echo     echo [%%date%% %%time%%] Servidor iniciado correctamente en localhost:%%PORT%% ^>^> "%%LOG_FILE%%"
echo ^) else ^(
echo     echo [%%date%% %%time%%] ERROR: No se pudo iniciar el servidor ^>^> "%%LOG_FILE%%"
echo     exit /b 1
echo ^)
echo.
echo echo [%%date%% %%time%%] MiSocio Printer operativo ^>^> "%%LOG_FILE%%"
echo exit /b 0
) > "%INSTALL_DIR%\printerStart.bat"

:: Crear printerStart.vbs
(
echo Set WshShell = CreateObject^("WScript.Shell"^)
echo WshShell.Run "C:\MiSocioPrinter\printerStart.bat", 0, False
echo Set WshShell = Nothing
) > "%INSTALL_DIR%\printerStart.vbs"

:: Crear printerUninstall.bat
(
echo @echo off
echo.
echo echo.
echo echo  ============================================
echo echo    Desinstalar MiSocio Printer
echo echo  ============================================
echo echo.
echo.
echo ^>nul 2^>^&1 "%%SystemRoot%%\system32\cacls.exe" "%%SystemRoot%%\system32\config\system"
echo if %%errorlevel%% neq 0 ^(
echo     echo Solicitando permisos de administrador...
echo     powershell -Command "Start-Process '%%~f0' -Verb RunAs"
echo     exit
echo ^)
echo.
echo echo  Eliminando tarea programada de inicio...
echo schtasks /Query /TN "MiSocioPrinter" ^>nul 2^>^&1
echo if %%errorlevel%% equ 0 ^(
echo     schtasks /Delete /TN "MiSocioPrinter" /F
echo     echo  Tarea eliminada correctamente.
echo ^) else ^(
echo     echo  No se encontro tarea programada.
echo ^)
echo.
echo echo.
echo echo  Deteniendo servidor PHP...
echo for /f "tokens=5" %%%%a in ^('netstat -ano ^^^| findstr ":5421" ^^^| findstr "LISTENING"'^) do ^(
echo     taskkill /F /PID %%%%a ^>nul 2^>^&1
echo     echo  Servidor detenido.
echo ^)
echo.
echo echo.
echo echo  ============================================
echo echo    Desinstalacion completada
echo echo  ============================================
echo echo.
echo echo  El directorio C:\MiSocioPrinter no fue eliminado.
echo echo  Si deseas eliminarlo manualmente, ejecuta:
echo echo  rmdir /s /q C:\MiSocioPrinter
echo echo.
echo pause
) > "%INSTALL_DIR%\printerUninstall.bat"

echo        Archivos creados correctamente.

:: ============================================================
:: PASO 8: Crear tarea programada en Inicio de Windows
:: ============================================================
echo.
echo  [8/9] Configurando inicio automatico con Windows...

schtasks /Query /TN "MiSocioPrinter" >nul 2>&1
if !errorlevel! equ 0 (
    schtasks /Delete /TN "MiSocioPrinter" /F >nul 2>&1
)

schtasks /Create /TN "MiSocioPrinter" /TR "wscript.exe \"%INSTALL_DIR%\printerStart.vbs\"" /SC ONLOGON /RL HIGHEST /F >nul 2>&1

if !errorlevel! equ 0 (
    echo        Inicio automatico configurado correctamente.
) else (
    echo        ADVERTENCIA: No se pudo configurar el inicio automatico.
)

:: ============================================================
:: PASO 9: Iniciar servidor PHP por primera vez y abrir navegador
:: ============================================================
echo.
echo  [9/9] Iniciando servidor PHP en localhost:%PORT%...
cd /d "%INSTALL_DIR%"

set "WEBROOT=%INSTALL_DIR%"
if exist "%INSTALL_DIR%\public" set "WEBROOT=%INSTALL_DIR%\public"

powershell -Command "Start-Process -FilePath 'php' -ArgumentList @('-S','localhost:%PORT%','-t','%WEBROOT%') -WorkingDirectory '%INSTALL_DIR%' -WindowStyle Hidden"

timeout /t 3 /nobreak >nul

start "" "http://localhost:%PORT%"

rmdir /s /q "%TEMP_DIR%" 2>nul

echo.
echo  ============================================
echo    MiSocio Printer instalado correctamente!
echo    - Servidor: http://localhost:%PORT%
echo    - Inicio automatico: ACTIVADO
echo    - Actualizaciones: AUTOMATICAS
echo  ============================================
echo.
echo  El servidor se iniciara automaticamente
echo  al iniciar Windows y buscara actualizaciones.
echo.
echo  Presiona cualquier tecla para cerrar...
pause >nul
exit

:: ============================================================
:: Subrutina: Agregar directorio al PATH del sistema
:: ============================================================
:ADD_TO_PATH
    powershell -Command "[Environment]::SetEnvironmentVariable('Path',[Environment]::GetEnvironmentVariable('Path','Machine')+';%~1','Machine')"
    set "PATH=%PATH%;%~1"
    exit /b 0

:: ============================================================
:: Subrutina: Refrescar PATH desde el registro del sistema
:: ============================================================
:REFRESH_PATH
    for /f "tokens=2*" %%a in ('reg query "HKLM\SYSTEM\CurrentControlSet\Control\Session Manager\Environment" /v PATH 2^>nul') do set "SYS_PATH=%%b"
    for /f "tokens=2*" %%a in ('reg query "HKCU\Environment" /v PATH 2^>nul') do set "USR_PATH=%%b"
    if defined USR_PATH (set "PATH=!SYS_PATH!;!USR_PATH!") else (set "PATH=!SYS_PATH!")
    exit /b 0
