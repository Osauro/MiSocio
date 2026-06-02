<h1 align="center">MiSocio</h1>
<p align="center"><em>Sistema de Gestión Empresarial Multi-Tenant</em></p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Livewire-4-FB70A9?style=flat&logo=livewire&logoColor=white" alt="Livewire 4">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?style=flat&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/PWA-Habilitada-5A0FC8?style=flat&logo=pwa&logoColor=white" alt="PWA">
</p>

---

## Descripción General

**MiSocio** es una plataforma SaaS multi-tenant de gestión empresarial diseñada para pequeñas y medianas empresas. Permite administrar ventas, compras, préstamos, inventario, hospedajes y mucho más desde una única interfaz web reactiva, instalable como aplicación en cualquier dispositivo (PWA).

Cada negocio registrado opera en un entorno completamente aislado (tenant), con sus propios productos, clientes, usuarios y configuraciones. Un único super-administrador (landlord) gestiona todos los negocios registrados en la plataforma.

---

## Características Principales

### Punto de Venta
- Creación de ventas con búsqueda de productos en tiempo real
- Soporte de pagos mixtos: **efectivo**, **pago online** y **crédito**
- Cálculo automático de cambio
- Numeración de folios por negocio (reutilización de huecos)
- Impresión automática de tickets al confirmar venta
- Exportación de tickets en **PDF**
- Gestión de ventas a crédito y registro de abonos

### Compras y Proveedores
- Registro de órdenes de compra por proveedor
- Actualización automática del kardex y stock al confirmar compra
- Importación de compras mediante **código QR**
- Impresión automática de comprobante de compra

### Préstamos
- Gestión completa del ciclo de préstamo de artículos a clientes
- Fechas de vencimiento configurables
- Notificación automática por **WhatsApp** al cliente y al propietario cuando un préstamo está próximo a vencer
- Tickets de préstamo y devolución en PDF

### Inventario Físico
- Creación de inventarios con conteo de unidades por producto
- Comparación entre stock real y stock del sistema
- Ajuste automático del kardex al cerrar el inventario
- Exportación del inventario en PDF

### Kardex y Movimientos
- Historial detallado de entradas y salidas por producto
- Registro de movimientos manuales con motivo
- Trazabilidad completa de cada unidad

### Hospedajes
- Gestión de tipos de habitación con tarifas por modalidad (horas, noche, día)
- Registro de huéspedes con número de personas y acompañantes
- Control de check-in / check-out con fechas estimadas y reales
- Soporte de pagos mixtos (efectivo, online, crédito)

### Catálogo de Productos
- Gestión de productos con categorías, etiquetas y unidades de medida
- Control de stock y precio de compra/venta
- Imágenes de producto mediante galería integrada
- Control de fechas de vencimiento
- Soft delete (eliminación lógica)

### Gestión de Clientes
- Base de datos de clientes con nombre, teléfono, correo y CI
- Historial de ventas, préstamos y hospedajes por cliente

### Usuarios y Roles
- Tres niveles de acceso:
  - **Landlord** — super-administrador de la plataforma
  - **Admin** — administrador del negocio (acceso total dentro de su tenant)
  - **Operador** — acceso limitado a ventas, inventarios y módulos habilitados
- Control de usuarios activos/inactivos por negocio

### Notificaciones por WhatsApp (Green API)
- Notificación al propietario por cada venta, crédito o cobro de abono
- Alerta de préstamos próximos a vencer (tarea programada diaria)
- Notificación de devolución de préstamos
- Configurable por tenant desde el panel de configuración

### Impresión Térmica (Print Agent)
- Conexión con impresoras ESC/POS a través de un agente local seguro
- Configuración independiente de impresora por módulo (ventas, compras, préstamos, inventario)
- Soporte de distintos tamaños de papel (58 mm, 80 mm, A4, etc.)
- Impresión automática al confirmar documentos
- Corte automático y apertura de cajón configurables

### Suscripciones y Facturación
- Planes de suscripción configurables con precio, duración y características
- Registro de membresías y fechas de vencimiento
- Bloqueo automático del tenant al vencer la suscripción
- Pasarela de pago integrada con **Stripe**
- Panel landlord para gestión de pagos y verificación manual

### Configuración del Negocio
- Nombre de la tienda, dirección, NIT, teléfono y logo
- Personalización de ticket con pie de página
- Múltiples temas de color por negocio
- Activación/desactivación de módulos (préstamos, hospedajes, compras, ventas)
- Parámetros de impresión granulares por módulo

---

## Tecnología

| Capa | Tecnología |
|------|-----------|
| Backend | PHP 8.2 + Laravel 12 |
| Frontend reactivo | Livewire 4 |
| Estilos | Tailwind CSS 3 + Vite |
| Base de datos | MySQL / MariaDB (Eloquent ORM) |
| Generación de PDF | DomPDF |
| Impresión térmica | mike42/escpos-php + Print Agent |
| WhatsApp | Green API |
| Pagos | Stripe |
| Imágenes | Intervention Image |
| PWA | Manifest dinámico + Service Worker |
| Autenticación | Laravel Breeze |
| Tests | PHPUnit 11 |

---

## Arquitectura Multi-Tenant

La plataforma utiliza una arquitectura **single-database multi-tenant** basada en `tenant_id`:

- Cada modelo de negocio aplica un **Global Scope** automático que filtra registros por el tenant activo en sesión.
- Los middlewares `tenant`, `tenant.active` y `tenant.manage` controlan el acceso por rol y estado de suscripción.
- El **landlord** administra todos los negocios desde un panel dedicado (`/admin`).
- El cambio de tenant activo se realiza sin cerrar sesión desde el selector de tiendas.

---

## Instalación

### Requisitos previos
- PHP >= 8.2 con extensiones: `mbstring`, `pdo_mysql`, `gd`, `zip`, `curl`
- Composer 2
- Node.js 18+ y npm
- MySQL / MariaDB

### Pasos

```bash
# 1. Clonar el repositorio
git clone <url-del-repositorio> misocio
cd misocio

# 2. Instalar dependencias e inicializar el proyecto
composer run setup

# 3. Configurar variables de entorno
cp .env.example .env
# Editar .env con los datos de base de datos, correo, Stripe, Green API, etc.

# 4. Ejecutar migraciones y seeders
php artisan migrate --seed

# 5. Enlazar almacenamiento
php artisan storage:link
```

### Servidor de desarrollo

```bash
composer run dev
```

Esto levanta en paralelo: servidor PHP, worker de colas, visor de logs y Vite.

---

## Variables de Entorno Clave

```env
APP_NAME=MiSocio
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_DATABASE=misocio

# Stripe (pagos de suscripción)
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...

# Green API (notificaciones WhatsApp)
GREENAPI_INSTANCE_ID=
GREENAPI_API_TOKEN=
GREENAPI_LANDLORD_PHONE=591...

# Print Agent (impresión térmica local)
PRINT_AGENT_URL=http://localhost:9876
PRINT_AGENT_SECRET_KEY=
```

---

## Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/     # Controladores (tickets, perfil, auth)
│   ├── Middleware/      # tenant, tenant.active, tenant.manage, landlord
│   └── Requests/        # Validaciones de formulario
├── Livewire/            # Componentes reactivos (Ventas, Compras, Préstamos, etc.)
│   └── Landlord/        # Panel de administración global
├── Models/              # Modelos Eloquent con Global Scopes por tenant
├── Services/            # GreenApiService, EscposPrinterService, TicketImageService
├── Helpers/             # helpers.php, TenantHelper.php
database/
├── migrations/          # +70 migraciones versionadas
resources/
├── views/               # Blade + componentes Livewire
routes/
├── web.php              # Rutas agrupadas por middleware
└── auth.php             # Rutas de autenticación (Breeze)
```

---

## Tareas Programadas

```php
// routes/console.php
Schedule::command('greenapi:notificar-por-vencer')->daily();
Schedule::command('prestamos:notificar-vencimiento')->dailyAt('08:00');
```

Configurar en crontab del servidor:

```cron
* * * * * php /ruta/al/proyecto/artisan schedule:run >> /dev/null 2>&1
```

---

## Licencia

Proyecto privado. Todos los derechos reservados © 2026 MiSocio.
