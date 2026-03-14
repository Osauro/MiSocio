<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Livewire\Categorias;
use App\Livewire\Clientes;
use App\Livewire\Compra;
use App\Livewire\Compras;
use App\Livewire\Config;
use App\Livewire\CrearTenant;
use App\Livewire\HomeLandlord;
use App\Livewire\HomeTenant;
use App\Livewire\Kardex;
use App\Livewire\Landlord\PagosManager;
use App\Livewire\Landlord\TenantsManager;
use App\Livewire\Movimientos;
use App\Livewire\Prestamo;
use App\Livewire\Prestamos;
use App\Livewire\Productos;
use App\Livewire\Suscripcion;
use App\Livewire\Usuarios;
use App\Livewire\Venta;
use App\Livewire\Ventas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Manifest PWA dinámico (color según tenant activo)
Route::get('/manifest.json', function () {
    $color = getThemeColor();
    return response()->json([
        'name'            => 'MiSocio',
        'short_name'      => 'MiSocio',
        'description'     => 'Sistema de gestión para tu negocio',
        'start_url'       => '/',
        'scope'           => '/',
        'display'         => 'standalone',
        'orientation'     => 'portrait-primary',
        'background_color'=> '#ffffff',
        'theme_color'     => $color,
        'handle_links'    => 'preferred',
        'prefer_related_applications' => false,
        'icons'           => [
            ['src' => '/assets/images/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/assets/images/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/assets/images/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
        ],
        'shortcuts' => [
            ['name' => 'Nueva Venta', 'url' => '/venta', 'description' => 'Ir a nueva venta'],
            ['name' => 'Dashboard',   'url' => '/dashboard', 'description' => 'Ir al dashboard'],
        ],
    ])->header('Content-Type', 'application/manifest+json');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Crear tienda - Accesible sin tener un tenant (para crear el primero)
    Route::livewire('crear-tienda', CrearTenant::class)->name('suscripcion.create');
});

// Rutas que requieren autenticación Y tenant activo (no vencido)
Route::middleware(['auth', 'tenant', 'tenant.active'])->group(function () {
    // Pantalla de bloqueo para operadores cuando el tenant está vencido
    Route::view('tenant-expirado', 'tenant-expirado')->name('tenant.expirado');
    // Ventas - Todos los usuarios pueden acceder
    Route::livewire('ventas', Ventas::class)->name('ventas');

    // Crear/Editar Venta - Todos los usuarios pueden acceder
    Route::livewire('venta/{ventaId}', Venta::class)->name('venta');

    // Crear/Editar Préstamo - Todos los usuarios pueden acceder
    Route::livewire('prestamo/{prestamoId}', Prestamo::class)->name('prestamo');

    // Kardex - Todos los usuarios pueden ver
    Route::livewire('kardex', Kardex::class)->name('kardex');

    // Tickets
    Route::get('ticket/venta/{ventaId}', [TicketController::class, 'ventaHtml'])->name('ticket.venta');
    Route::get('ticket/venta/{ventaId}/pdf', [TicketController::class, 'venta'])->name('ticket.venta.pdf');
    Route::get('ticket/prestamo/{prestamoId}', [TicketController::class, 'prestamoHtml'])->name('ticket.prestamo');
    Route::get('ticket/prestamo/{prestamoId}/pdf', [TicketController::class, 'prestamo'])->name('ticket.prestamo.pdf');

    // Ruta de debug temporal - Solo para desarrollo
    Route::get('debug-user', function () {
        return view('debug-user');
    })->name('debug');
});

// Rutas de administración del tenant - Solo Landlord y Tenant Admin
Route::middleware(['auth', 'tenant', 'tenant.active', 'tenant.manage'])->group(function () {
    // Dashboard - Solo administradores
    Route::livewire('dashboard', HomeTenant::class)->name('dashboard');

    // Gestión de recursos
    Route::livewire('productos', Productos::class)->name('productos');
    Route::livewire('categorias', Categorias::class)->name('categorias');
    Route::livewire('clientes', Clientes::class)->name('clientes');
    Route::livewire('usuarios', Usuarios::class)->name('usuarios');
    Route::livewire('movimientos', Movimientos::class)->name('movimientos');

    // Compras - Solo administradores
    Route::livewire('compras', Compras::class)->name('compras');

    // Crear/Editar Compra - Solo administradores
    Route::livewire('compra/{compraId}', Compra::class)->name('compra');

    // Préstamos - Solo administradores
    Route::livewire('prestamos', Prestamos::class)->name('prestamos');

    // Configuración del sistema - Solo administradores
    Route::livewire('config', Config::class)->name('config');

    // Descargar instalador de impresora - DESHABILITADO: Ahora se descarga desde https://fadi.com.bo
    // Route::get('descargar/printer-install', function () {
    //     $filePath = public_path('printerInstall.bat');
    //     if (!file_exists($filePath)) {
    //         abort(404, 'Archivo de instalación no encontrado');
    //     }
    //     return response()->download($filePath, 'printerInstall.bat', [
    //         'Content-Type' => 'application/octet-stream',
    //     ]);
    // })->name('printer.install.download');

    // Suscripción - Solo administradores
    Route::livewire('suscripcion', Suscripcion::class)->name('suscripcion');
});

// Rutas para landlord - Solo Landlords
Route::middleware(['auth', 'landlord'])->prefix('admin')->group(function () {
    Route::livewire('dashboard', HomeLandlord::class)->name('admin.dashboard');
    Route::livewire('tenants', TenantsManager::class)->name('admin.tenants');
    Route::livewire('planes', \App\Livewire\Landlord\PlanesSuscripcion::class)->name('admin.planes');
    Route::livewire('pagos', PagosManager::class)->name('admin.pagos');
});

require __DIR__.'/auth.php';
