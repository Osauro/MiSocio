<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Categorias;
use App\Livewire\Clientes;
use App\Livewire\Compra;
use App\Livewire\Compras;
use App\Livewire\HomeLandlord;
use App\Livewire\HomeTenant;
use App\Livewire\Kardex;
use App\Livewire\Movimientos;
use App\Livewire\Productos;
use App\Livewire\Usuarios;
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
    Route::livewire('tenant/ventas', Ventas::class)->name('tenant.ventas');

    // Kardex - Todos los usuarios pueden ver
    Route::livewire('tenant/kardex', Kardex::class)->name('tenant.kardex');

    // Ruta de debug temporal - Solo para desarrollo
    Route::get('tenant/debug-user', function () {
        return view('debug-user');
    })->name('tenant.debug');
});

// Rutas de administración del tenant - Solo Landlord y Tenant Admin
Route::middleware(['auth', 'tenant', 'tenant.manage'])->group(function () {
    // Dashboard - Solo administradores
    Route::livewire('tenant/home', HomeTenant::class)->name('tenant.home');

    // Gestión de recursos
    Route::livewire('tenant/productos', Productos::class)->name('tenant.productos');
    Route::livewire('tenant/categorias', Categorias::class)->name('tenant.categorias');
    Route::livewire('tenant/clientes', Clientes::class)->name('tenant.clientes');
    Route::livewire('tenant/usuarios', Usuarios::class)->name('tenant.usuarios');
    Route::livewire('tenant/movimientos', Movimientos::class)->name('tenant.movimientos');

    // Compras - Solo administradores
    Route::livewire('tenant/compras', Compras::class)->name('tenant.compras');

    // Crear/Editar Compra - Solo administradores
    Route::livewire('tenant/compra/{compraId}', Compra::class)->name('tenant.compra');
});

// Rutas para landlord - Solo Landlords
Route::middleware(['auth', 'landlord'])->group(function () {
    Route::livewire('landlord/home', HomeLandlord::class)->name('landlord.home');
    // Aquí irán futuras rutas de gestión de tenants, suscripciones, pagos
});

require __DIR__.'/auth.php';
