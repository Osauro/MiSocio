# LicoPrint

Servicio local de impresión para LicoPOS.

## Requisitos

- Go 1.21 o superior

## Compilar

```bash
# Windows
go build -ldflags="-s -w -H windowsgui" -o LicoPrint.exe .

# Linux
go build -ldflags="-s -w" -o licoprint .

# Mac
GOOS=darwin go build -ldflags="-s -w" -o licoprint .
```

O usar el script:
```
build.bat
```

## Uso

1. Ejecutar `LicoPrint.exe`
2. Se abrirá automáticamente en `http://localhost:2026`
3. Seleccionar impresora y configurar
4. Guardar configuración

## API Endpoints

- `GET /` - Interfaz web
- `GET /api/config` - Obtener configuración
- `POST /api/config` - Guardar configuración
- `GET /api/printers` - Listar impresoras
- `POST /api/print` - Enviar impresión
- `GET /api/test` - Impresión de prueba

## Estructura

```
licoprint/
├── main.go           # Servidor principal
├── go.mod            # Módulo Go
├── build.bat         # Script de compilación
├── templates/
│   └── index.html    # Interfaz web
└── static/
    └── placeholder.css
```
