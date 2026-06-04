<div align="center">

# 🏢 MiSocio — Sistema de Gestión Empresarial

**Plataforma SaaS multi-tenant para la administración integral de negocios**

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-4.x-FB70A9?style=flat-square&logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Stripe](https://img.shields.io/badge/Stripe-Integrado-635BFF?style=flat-square&logo=stripe&logoColor=white)](https://stripe.com)
[![PWA](https://img.shields.io/badge/PWA-Habilitada-5A0FC8?style=flat-square&logo=pwa&logoColor=white)](https://web.dev/progressive-web-apps/)
[![License](https://img.shields.io/badge/Licencia-Privada-red?style=flat-square)](LICENSE)

</div>

---

## 📖 Descripción General

**MiSocio** es una plataforma SaaS multi-tenant de gestión empresarial diseñada para pequeñas y medianas empresas. Permite administrar ventas, compras, préstamos, inventario, hospedajes y mucho más desde una única interfaz web reactiva, instalable como aplicación en cualquier dispositivo (PWA).

Cada negocio registrado opera en un entorno completamente aislado (tenant), con sus propios productos, clientes, usuarios y configuraciones. Un único super-administrador (landlord) gestiona todos los negocios registrados en la plataforma.

### ¿Para quién está diseñado?

| Tipo de negocio | Caso de uso |
|---|---|
| Tiendas y minimarkets | Punto de venta con impresión térmica y control de inventario |
| Casas de empeño | Gestión de préstamos de artículos con alertas de vencimiento |
| Hostales y posadas | Control de habitaciones, check-in/out y cobros |
| Negocios con múltiples sucursales | Administración centralizada multi-tenant |

---

## ✨ Características Principales

### 🛒 Punto de Venta
- Creación de ventas con búsqueda de productos en tiempo real
- Soporte de pagos mixtos: **efectivo**, **pago online** y **crédito**
- Cálculo automático de cambio
- Numeración de folios por negocio (reutilización de huecos)
- Impresión automática de tickets al confirmar venta
- Exportación de tickets en **PDF**
- Gestión de ventas a crédito y registro de abonos

### 📦 Compras y Proveedores
- Registro de órdenes de compra por proveedor
- Actualización automática del kardex y stock al confirmar compra
- Importación de compras mediante **código QR**
- Impresión automática de comprobante de compra

### 🔖 Préstamos de Artículos
- Gestión completa del ciclo de préstamo a clientes
- Fechas de vencimiento configurables
- Notificación automática por **WhatsApp** al cliente y al propietario cuando un préstamo está próximo a vencer
- Tickets de préstamo y devolución en PDF

### 📋 Inventario Físico
- Creación de inventarios con conteo de unidades por producto
- Comparación entre stock real y stock del sistema
- Ajuste automático del kardex al cerrar el inventario
- Exportación del inventario en PDF

### 📊 Kardex y Movimientos
- Historial detallado de entradas y salidas por producto
- Registro de movimientos manuales con motivo
- Trazabilidad completa de cada unidad

### 🏨 Hospedajes
- Gestión de tipos de habitación con tarifas por modalidad (horas, noche, día)
- Registro de huéspedes con número de personas y acompañantes
- Control de check-in / check-out con fechas estimadas y reales
- Soporte de pagos mixtos (efectivo, online, crédito)

### 🗂️ Catálogo de Productos
- Gestión de productos con categorías, etiquetas y unidades de medida
- Control de stock y precio de compra/venta
- Imágenes de producto mediante galería integrada
- Control de fechas de vencimiento
- Soft delete (eliminación lógica)

### 👥 Gestión de Clientes
- Base de datos de clientes con nombre, teléfono, correo y CI
- Historial de ventas, préstamos y hospedajes por cliente

### 🔐 Usuarios y Roles
El sistema cuenta con tres niveles de acceso bien definidos:

**Landlord (Super-Administrador)**
- Gestión de todos los negocios registrados en la plataforma
- Administración de suscripciones y pagos
- Panel dedicado en `/admin`

**Admin (Administrador del Negocio)**
- Acceso total dentro de su tenant
- Gestión de usuarios, productos, clientes y configuración

**Operador**
- Acceso limitado a ventas, inventarios y módulos habilitados
- Sin acceso a configuración ni reportes avanzados

### 📱 Notificaciones por WhatsApp (Green API)
- Notificación al propietario por cada venta, crédito o cobro de abono
- Alerta de préstamos próximos a vencer (tarea programada diaria)
- Notificación de devolución de préstamos
- Configurable por tenant desde el panel de configuración

### 🖨️ Impresión Térmica (Print Agent)
- Conexión con impresoras ESC/POS a través de un agente local seguro
- Configuración independiente de impresora por módulo (ventas, compras, préstamos, inventario)
- Soporte de distintos tamaños de papel (58 mm, 80 mm, A4, etc.)
- Impresión automática al confirmar documentos
- Corte automático y apertura de cajón configurables

### 💳 Suscripciones y Facturación
- Planes de suscripción configurables con precio, duración y características
- Registro de membresías y fechas de vencimiento
- Bloqueo automático del tenant al vencer la suscripción
- Pasarela de pago integrada con **Stripe**
- Panel landlord para gestión de pagos y verificación manual

### ⚙️ Configuración del Negocio
- Nombre de la tienda, dirección, NIT, teléfono y logo
- Personalización de ticket con pie de página
- Múltiples temas de color por negocio
- Activación/desactivación de módulos (préstamos, hospedajes, compras, ventas)
- Parámetros de impresión granulares por módulo

---

## 🖥️ Módulos del Sistema

> El sistema cuenta con una interfaz moderna y responsiva basada en Tailwind CSS, accesible desde computadoras, tablets y teléfonos móviles, e instalable como PWA.

| Módulo | Descripción |
|---|---|
| **Dashboard** | Panel principal con métricas y resumen del negocio |
| **Punto de Venta** | Ventas con pagos mixtos e impresión automática |
| **Compras** | Órdenes de compra con actualización de kardex |
| **Préstamos** | Préstamo de artículos con alertas por WhatsApp |
| **Inventario** | Conteo físico con ajuste automático de stock |
| **Kardex** | Trazabilidad completa de movimientos de productos |
| **Hospedajes** | Check-in/out de habitaciones con control de tarifas |
| **Clientes** | CRUD completo con historial por módulo |
| **Productos** | Catálogo con galería, categorías y control de stock |
| **Usuarios** | Gestión de roles y accesos por negocio |
| **Suscripciones** | Planes y pagos con Stripe |
| **Configuración** | Personalización total del negocio y módulos |
| **Panel Landlord** | Administración global de todos los tenants |

---

## 🔄 Flujo de Trabajo Típico

```
1. REGISTRO DEL NEGOCIO
   └── El landlord crea un nuevo tenant con su plan de suscripción

2. CONFIGURACIÓN INICIAL
   ├── El admin configura nombre, logo y módulos habilitados
   ├── Registra productos, categorías y proveedores
   └── Crea usuarios operadores y asigna permisos

3. OPERACIÓN DIARIA
   ├── VENTAS: El operador busca productos → agrega al carrito → cobra → imprime ticket
   ├── COMPRAS: Registra compra → el stock y kardex se actualizan automáticamente
   └── PRÉSTAMOS: Crea préstamo → notificación por WhatsApp al vencer

4. CONTROL DE INVENTARIO
   ├── Crea inventario físico con conteo real
   ├── El sistema compara contra el stock registrado
   └── Al cerrar, ajusta automáticamente el kardex

5. GESTIÓN DE SUSCRIPCIÓN
   └── El tenant paga mediante Stripe → el landlord verifica → acceso renovado
```

---

## 🏗️ Arquitectura Técnica

El proyecto sigue la arquitectura **MVC** de Laravel con componentes **Livewire** para interactividad en tiempo real sin escribir JavaScript, sobre un modelo **single-database multi-tenant**:

```
app/
├── Http/
│   ├── Controllers/        # Controladores (tickets, perfil, auth)
│   ├── Middleware/         # tenant, tenant.active, tenant.manage, landlord
│   └── Requests/           # Validaciones de formulario
├── Livewire/
│   ├── Ventas.php / Venta.php / VentaCart.php
│   ├── Compras.php / Compra.php / CompraCart.php
│   ├── Prestamos.php / Prestamo.php / PrestamoCart.php
│   ├── Inventarios.php / Inventario.php / Kardex.php
│   ├── Hospedajes.php / Habitaciones.php
│   ├── Productos.php / Clientes.php / Usuarios.php
│   └── Landlord/           # Panel de administración global
├── Models/                 # Modelos Eloquent con Global Scopes por tenant_id
├── Services/               # GreenApiService, EscposPrinterService, TicketImageService
└── Helpers/                # helpers.php, TenantHelper.php
```

### Tecnologías Utilizadas

| Tecnología | Versión | Uso |
|---|---|---|
| **Laravel** | 12.x | Framework PHP principal |
| **Livewire** | 4.x | Componentes dinámicos sin JS |
| **Tailwind CSS** | 3.x | Diseño responsivo |
| **Vite** | Latest | Compilación de assets |
| **DomPDF** | Latest | Generación de PDF |
| **mike42/escpos-php** | Latest | Impresión térmica ESC/POS |
| **Green API** | — | Notificaciones por WhatsApp |
| **Stripe** | Latest | Pagos de suscripción |
| **Intervention Image** | Latest | Procesamiento de imágenes |
| **Laravel Breeze** | Latest | Autenticación |
| **MySQL / MariaDB** | 8.0+ | Base de datos |
| **PHPUnit** | 11 | Tests automatizados |

---

## 🏢 Arquitectura Multi-Tenant

La plataforma utiliza una arquitectura **single-database multi-tenant** basada en `tenant_id`:

- Cada modelo de negocio aplica un **Global Scope** automático que filtra registros por el tenant activo en sesión.
- Los middlewares `tenant`, `tenant.active` y `tenant.manage` controlan el acceso por rol y estado de suscripción.
- El **landlord** administra todos los negocios desde un panel dedicado en `/admin`.
- El cambio de tenant activo se realiza sin cerrar sesión desde el selector de tiendas.

---

## 📋 Requisitos del Sistema

| Componente | Versión mínima |
|---|---|
| PHP | 8.2 o superior |
| Composer | 2.x |
| MySQL / MariaDB | 8.0 o superior |
| Node.js | 18.0 o superior |
| NPM | 9.x o superior |
| Extensiones PHP | `mbstring`, `pdo_mysql`, `gd`, `zip`, `curl` |

---

## 🛠️ Instalación

### 1. Clonar el repositorio
```bash
git clone <url-del-repositorio> misocio
cd misocio
```

### 2. Instalar dependencias e inicializar el proyecto
```bash
composer install
```

### 3. Instalar dependencias de Node.js y compilar assets
```bash
npm install
npm run build
```

### 4. Configurar variables de entorno
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Crear la base de datos y ejecutar migraciones
```bash
php artisan migrate --seed
php artisan storage:link
```

### 6. Iniciar el servidor de desarrollo
```bash
composer run dev
```

Esto levanta en paralelo: servidor PHP, worker de colas, visor de logs y Vite.

---

## 🔑 Variables de Entorno Clave

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

## ⏰ Tareas Programadas

```php
// routes/console.php
Schedule::command('greenapi:notificar-por-vencer')->daily();
Schedule::command('prestamos:notificar-vencimiento')->dailyAt('08:00');
```

Agregar al crontab del servidor:

```cron
* * * * * php /ruta/al/proyecto/artisan schedule:run >> /dev/null 2>&1
```

---

## 🔒 Seguridad

- Autenticación mediante middleware personalizado por rol (`tenant`, `landlord`)
- Protección CSRF en todos los formularios Livewire
- Global Scopes por `tenant_id` para aislamiento total de datos entre negocios
- Validación de datos en backend (server-side) con Form Requests
- Rutas protegidas que redirigen al login si no hay sesión activa
- Bloqueo automático de tenants con suscripción vencida

---

## � Historial de Cambios (Changelog)

### v2.7 — 2026-06-03
**Stock Mínimo y Alertas Visuales en Productos**
- **Nueva columna `stock_minimo`** en la tabla `productos` (migración `2026_06_03_000001`). Permite definir el umbral bajo el cual un producto se considera en "stock bajo".
- **Accessor `stock_bajo`** en el modelo `Producto`: retorna `true` cuando el stock es mayor a 0 pero menor o igual al mínimo configurado.
- **Formulario de producto rediseñado**: los campos `Medida`, `Cantidad (unidades)` y `Stock Mínimo` ahora se muestran en la misma fila (`col-md-4` cada uno).
- **Ordenamiento inteligente en el listado**: los productos se muestran primero con `stock = 0`, luego los de stock bajo, y finalmente el resto alfabéticamente.
- **Alertas visuales en tarjetas**:
  - Fondo rojo (`#f8d7da`) + borde `danger` + badge **"Sin stock"** para productos con stock en 0.
  - Fondo amarillo (`#fff3cd`) + borde `warning` + badge **"Stock bajo"** para productos por debajo del mínimo.

---

### v2.6 — 2026-06-01
**Notificaciones WhatsApp para Ventas y Onboarding de Usuarios**
- Notificaciones WhatsApp al propietario al registrar una venta (configurable por tenant).
- Campo `onboarding_completado` en usuarios para controlar el flujo de primer acceso.

---

### v2.5 — 2026-05-31
**Notificaciones WhatsApp Ampliadas y Configuración de Prefijo**
- Prefijo de país separado para el celular del propietario en la configuración del tenant.
- Notificaciones WhatsApp para ventas a crédito y pagos de crédito.
- Notificaciones WhatsApp para préstamos: creación, devolución y vencimiento.
- Sistema de PIN automático en creación de usuario con botón para resetear y enviar por WhatsApp.

---

### v2.4 — 2026-05-30
**Impresoras por Módulo**
- Configuración de impresora térmica independiente por módulo (ventas, compras, préstamos, inventario).

---

### v2.3 — 2026-05-24
**Recalculo de Precio de Compra por Unidad**
- Migración para recalcular `precio_de_compra` a precio por unidad en todos los productos existentes.
- Nueva opción `ventas_iniciar_unidad` en configuración del tenant: permite iniciar la venta en modo unidades.

---

### v2.2 — 2026-04-26
**Módulos de Compras y Ventas Configurables**
- Activación/desactivación del módulo de compras por tenant.
- Campos configurables de ventas: solo unidad, mostrar logo, pie de ticket.
- Clave de Print Agent configurable por tenant.

---

### v2.1 — 2026-03-27
**Módulo de Hospedajes**
- Gestión de habitaciones por tipo con modalidades (horas, noche, día).
- Control de check-in/check-out con acompañantes.
- Tarifa por modalidad y tipo de habitación.
- Activación del módulo de hospedajes por tenant.

---

### v2.0 — 2026-03-16
**Galería de Imágenes**
- Galería centralizada de imágenes por tenant para asignar fotos a productos.
- Componente `GaleriaModal` integrado en el formulario de productos.

---

## �📜 Licencia

Proyecto privado. Todos los derechos reservados © 2026 MiSocio.

---

<div align="center">

**Desarrollado con ❤️ usando Laravel + Livewire**

*Para soporte técnico o consultas comerciales, contacte al equipo de desarrollo.*

</div>
