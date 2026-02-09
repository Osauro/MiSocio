# Despliegue LicoPOS en licos.fadi.com.bo

## Paso 1: Crear base de datos en cPanel

1. Ir a **cPanel → MySQL Databases**
2. Crear base de datos: `fadi_licos` (o el prefijo que te asigne cPanel)
3. Crear usuario: `fadi_licos` 
4. Asignar usuario a la base de datos con **TODOS los privilegios**
5. **Anotar** los nombres exactos que cPanel genera (ej: `fadi_licos` o `fadicom_licos`)

## Paso 2: Crear subdominio

1. Ir a **cPanel → Subdomains** (o Domains en cPanel nuevo)
2. Crear subdominio: `licos`
3. **Document Root**: `/home/TU_USUARIO/licos/public`
   - IMPORTANTE: apunta a la carpeta `public` del proyecto
4. Esperar propagación DNS (puede tomar unos minutos)

## Paso 3: Conectar por SSH

```bash
ssh tu_usuario@fadi.com.bo -p 22
# o el puerto que te indique tu hosting
```

## Paso 4: Clonar e instalar

```bash
cd ~
git clone https://github.com/Osauro/LicoPOS.git licos
cd licos
composer install --optimize-autoloader --no-dev
```

## Paso 5: Configurar .env

```bash
cp .env.example .env
nano .env
```

Editar con estos valores:

```env
APP_NAME=LicoPOS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://licos.fadi.com.bo

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=fadi_licos
DB_USERNAME=fadi_licos
DB_PASSWORD=TU_PASSWORD_AQUI

SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

Guardar: `Ctrl+O`, salir: `Ctrl+X`

## Paso 6: Generar key y optimizar

```bash
php artisan key:generate
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Paso 7: Permisos

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs storage/framework
```

## Paso 8: Migraciones

```bash
php artisan migrate --force
```

## Paso 9: Importar datos de FADI (opcional)

Si quieres importar los datos de paybol_fadi, primero necesitas:

1. Exportar paybol_fadi desde tu servidor local
2. Importar en el servidor de producción
3. Configurar la conexión en .env:

```env
# Agregar al final de .env
PAYBOL_DB_DATABASE=fadi_paybol
PAYBOL_DB_USERNAME=fadi_paybol
PAYBOL_DB_PASSWORD=password_paybol
```

4. Ejecutar importación:
```bash
php artisan import:paybol
```

---

## Comandos útiles posteriores

### Actualizar código
```bash
cd ~/licos
git pull origin master
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

### Limpiar caché
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Ver logs de errores
```bash
tail -f ~/licos/storage/logs/laravel.log
```

---

## Credenciales de acceso

Una vez importados los datos:
- **Celular**: 73010688
- **Password**: 5421

---

## SSL/HTTPS

En cPanel:
1. Ir a **SSL/TLS** o **Let's Encrypt**
2. Generar certificado para `licos.fadi.com.bo`
3. Forzar HTTPS en .htaccess (ya incluido)
