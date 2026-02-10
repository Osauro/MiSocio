<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Livewire\Categorias;
use App\Livewire\Clientes;
use App\Livewire\Compra;
use App\Livewire\Compras;
use App\Livewire\Config;
use App\Livewire\HomeLandlord;
use App\Livewire\HomeTenant;
use App\Livewire\Kardex;
use App\Livewire\Movimientos;
use App\Livewire\Prestamo;
use App\Livewire\Prestamos;
use App\Livewire\Productos;
use App\Livewire\Usuarios;
use App\Livewire\Venta;
use App\Livewire\Ventas;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rutas que requieren autenticación Y tenant activo
Route::middleware(['auth', 'tenant'])->group(function () {
    // Ventas - Todos los usuarios pueden acceder
    Route::livewire('ventas', Ventas::class)->name('ventas');

    // Crear/Editar Venta - Todos los usuarios pueden acceder
    Route::livewire('venta/{ventaId}', Venta::class)->name('venta');

    // Crear/Editar Préstamo - Todos los usuarios pueden acceder
    Route::livewire('prestamo/{prestamoId}', Prestamo::class)->name('prestamo');

    // Kardex - Todos los usuarios pueden ver
    Route::livewire('kardex', Kardex::class)->name('kardex');

    // Tickets PDF
    Route::get('ticket/venta/{ventaId}', [TicketController::class, 'venta'])->name('ticket.venta');

    // Ruta de debug temporal - Solo para desarrollo
    Route::get('debug-user', function () {
        return view('debug-user');
    })->name('debug');
});

// Rutas de administración del tenant - Solo Landlord y Tenant Admin
Route::middleware(['auth', 'tenant', 'tenant.manage'])->group(function () {
    // Dashboard - Solo administradores
    Route::livewire('home', HomeTenant::class)->name('home');

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
});

// Rutas para landlord - Solo Landlords
Route::middleware(['auth', 'landlord'])->prefix('admin')->group(function () {
    Route::livewire('home', HomeLandlord::class)->name('admin.home');
    // Aquí irán futuras rutas de gestión de tenants, suscripciones, pagos
});

require __DIR__.'/auth.php';
