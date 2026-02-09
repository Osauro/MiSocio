#!/bin/bash
# Script de despliegue para LicoPOS en cPanel
# Dominio: licos.fadi.com.bo

echo "==================================="
echo "  Despliegue LicoPOS - FADI"
echo "==================================="

# Variables - AJUSTAR SEGÚN TU HOSTING
DB_NAME="fadi_licos"
DB_USER="fadi_licos"
DB_PASS="TU_PASSWORD_AQUI"
DOMAIN="licos.fadi.com.bo"

# Ir al home
cd ~

# Clonar repositorio (si no existe)
if [ ! -d "licos" ]; then
    echo "Clonando repositorio..."
    git clone https://github.com/Osauro/LicoPOS.git licos
else
    echo "Actualizando repositorio..."
    cd licos
    git pull origin master
    cd ~
fi

cd ~/licos

# Instalar dependencias
echo "Instalando dependencias de Composer..."
composer install --optimize-autoloader --no-dev

# Crear .env si no existe
if [ ! -f ".env" ]; then
    echo "Creando archivo .env..."
    cp .env.example .env

    # Generar APP_KEY
    php artisan key:generate
fi

# Configurar .env
echo "Configurando .env..."
sed -i "s|APP_ENV=local|APP_ENV=production|g" .env
sed -i "s|APP_DEBUG=true|APP_DEBUG=false|g" .env
sed -i "s|APP_URL=.*|APP_URL=https://${DOMAIN}|g" .env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|g" .env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|g" .env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|g" .env

# Permisos
echo "Configurando permisos..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs storage/framework storage/framework/sessions storage/framework/views storage/framework/cache

# Crear enlace simbólico de storage
echo "Creando enlace simbólico de storage..."
php artisan storage:link

# Cachear configuración
echo "Optimizando para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar migraciones
echo "Ejecutando migraciones..."
php artisan migrate --force

echo ""
echo "==================================="
echo "  ¡Despliegue completado!"
echo "==================================="
echo ""
echo "SIGUIENTE PASO:"
echo "1. En cPanel → Subdomains, crea el subdominio 'licos'"
echo "2. Apunta el Document Root a: /home/TU_USUARIO/licos/public"
echo ""
echo "Tu sitio estará en: https://${DOMAIN}"
echo ""
