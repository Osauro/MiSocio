# LicoPrint - Servicio de Impresión Local

Servicio que permite a LicoPOS comunicarse con impresoras térmicas locales.

## Archivos

| Archivo | Descripción |
|---------|-------------|
| `instalar-licoprint.bat` | Instalador automático. Descarga PHP si es necesario, configura todo y crea accesos directos |
| `iniciar-licoprint.bat` | Inicia el servicio si ya está instalado |

## Instalación

1. Descarga `instalar-licoprint.bat`
2. Ejecuta como administrador (clic derecho → Ejecutar como administrador)
3. Sigue las instrucciones en pantalla
4. Al finalizar, se creará un acceso directo en el Escritorio

## El instalador realiza:

- ✅ Detecta si PHP está instalado (Laragon, XAMPP, o en PATH)
- ✅ Si no hay PHP, lo descarga automáticamente
- ✅ Crea la carpeta de instalación en `%LOCALAPPDATA%\LicoPrint`
- ✅ Genera el servidor PHP con la API de impresión
- ✅ Crea la interfaz web de configuración
- ✅ Genera scripts de inicio
- ✅ Crea acceso directo en el Escritorio

## Uso

1. Ejecuta `LicoPrint.bat` desde el Escritorio
2. Se abre automáticamente `http://localhost:2026`
3. Selecciona tu impresora de la lista
4. Guarda la configuración
5. Haz una prueba de impresión

## API Endpoints

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/` | GET | Interfaz web de configuración |
| `/api/config` | GET/POST | Obtener/guardar configuración |
| `/api/printers` | GET | Lista de impresoras detectadas |
| `/api/print` | POST | Enviar impresión |
| `/api/test` | GET | Impresión de prueba |

## Requisitos

- Windows 10/11
- PowerShell (incluido en Windows)
- Conexión a internet (solo para instalación si no tienes PHP)

## Desinstalación

Elimina la carpeta: `%LOCALAPPDATA%\LicoPrint`
