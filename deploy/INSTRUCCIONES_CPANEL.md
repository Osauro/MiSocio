# Despliegue en cPanel Compartido - LicoPOS

## Estructura de archivos

```
/home/tuusuario/
├── licos/              ← Archivos del proyecto (fuera de public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   └── ...
│
└── public_html/        ← Solo archivos públicos
    ├── .htaccess
    ├── index.php       ← Modificado para apuntar a /licos
    ├── build/
    ├── assets/
    └── storage → ../licos/storage/app/public (enlace simbólico)
```

## Paso 1: Preparar archivos locales

1. Ejecutar en tu proyecto local:
```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Paso 2: Crear base de datos en cPanel

1. Ir a **cPanel → MySQL Databases**
2. Crear una nueva base de datos (ej: `tuuser_licos`)
3. Crear un usuario de base de datos
4. Asignar el usuario a la base de datos con **TODOS los privilegios**
5. Anotar:
   - Nombre de la base de datos
   - Usuario de la base de datos
   - Contraseña

## Paso 3: Subir archivos

### Opción A: Usando File Manager

1. Ir a **cPanel → File Manager**
2. Crear carpeta `licos` en `/home/tuusuario/` (fuera de public_html)
3. Subir un ZIP con todo el proyecto (excepto node_modules y .git)
4. Extraer el ZIP en `/home/tuusuario/licos/`
5. Copiar el contenido de `licos/public/` a `public_html/`

### Opción B: Usando Terminal SSH (recomendado)

```bash
cd ~
git clone https://github.com/Osauro/LicoPOS.git licos
cd licos
composer install --optimize-autoloader --no-dev
```

## Paso 4: Configurar .env

1. Editar `/home/tuusuario/licos/.env`:

```env
APP_NAME=LicoPOS
APP_ENV=production
APP_KEY=base64:GENERA_UNA_NUEVA_KEY
APP_DEBUG=false
APP_URL=https://tudominio.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=tuuser_licos
DB_USERNAME=tuuser_licos
DB_PASSWORD=tu_password_seguro

SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

2. Generar APP_KEY (si tienes SSH):
```bash
cd ~/licos
php artisan key:generate
```

## Paso 5: Configurar index.php en public_html

Reemplazar el contenido de `public_html/index.php` con el archivo `index.php` de la carpeta `deploy/`.

## Paso 6: Configurar permisos

En Terminal SSH o usando File Manager:

```bash
cd ~/licos
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs storage/framework
```

## Paso 7: Crear enlace simbólico para storage

En Terminal SSH:
```bash
cd ~/public_html
rm -rf storage
ln -s ../licos/storage/app/public storage
```

O ejecutar:
```bash
cd ~/licos
php artisan storage:link
```

## Paso 8: Ejecutar migraciones

En Terminal SSH:
```bash
cd ~/licos
php artisan migrate --force
```

## Paso 9: Importar datos (opcional)

Si quieres importar datos de paybol_fadi:
```bash
php artisan import:paybol
```

## Solución de problemas

### Error 500
- Verificar permisos de storage/
- Revisar storage/logs/laravel.log
- Asegurar que APP_DEBUG=true temporalmente para ver errores

### Página en blanco
- Verificar que el index.php apunta correctamente a /licos
- Verificar .htaccess

### Imágenes no cargan
- Verificar enlace simbólico de storage
- Ejecutar `php artisan storage:link`

### Error de base de datos
- Verificar credenciales en .env
- En cPanel el host suele ser `localhost`

## Configurar dominio/subdominio (opcional)

Si usas un subdominio:
1. Ir a **cPanel → Subdomains**
2. Crear subdominio (ej: app.tudominio.com)
3. Document Root: `/home/tuusuario/licos/public`

Esto evita tener que modificar index.php ya que el subdominio apunta directamente a /public.
