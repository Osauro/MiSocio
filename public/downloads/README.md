# Carpeta de Descargas - LicoPrint

Esta carpeta contiene los archivos descargables para el servicio de impresora local.

## Archivos necesarios:

1. **LicoPrint-Installer.exe** - Instalador del servicio de impresión local
   - Debe ser creado/compilado por separado
   - Es un servidor HTTP local que corre en el puerto 2026
   - Permite configurar y enviar comandos a impresoras térmicas

2. **iniciar-licoprint.bat** - Script para iniciar el servicio
   - Se genera automáticamente
   - Busca LicoPrint en las ubicaciones comunes e inicia el servicio

## Estructura del servicio LicoPrint:

El servicio debe:
- Correr un servidor HTTP en `localhost:2026`
- Proveer una interfaz web para configurar impresoras
- Recibir comandos de impresión vía API REST
- Soportar impresoras térmicas (ESC/POS) de 58mm y 80mm
