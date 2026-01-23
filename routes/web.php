<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Categorias;
use App\Livewire\Clientes;
use App\Livewire\HomeLandlord;
use App\Livewire\HomeTenant;
use App\Livewire\Productos;
use App\Livewire\TestCliente;
use App\Livewire\Usuarios;
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
    Route::livewire('tenant/home', HomeTenant::class)->name('tenant.home');
    Route::livewire('tenant/productos', Productos::class)->name('tenant.productos');
    Route::livewire('tenant/categorias', Categorias::class)->name('tenant.categorias');
    Route::livewire('tenant/clientes', Clientes::class)->name('tenant.clientes');
    Route::livewire('tenant/usuarios', Usuarios::class)->name('tenant.usuarios');
    Route::livewire('tenant/test-cliente', TestCliente::class)->name('tenant.test-cliente');

    // Ruta de debug temporal
    Route::get('tenant/debug-user', function () {
        return view('debug-user');
    })->name('tenant.debug');
});

// Rutas para landlord (solo autenticación requerida)
Route::middleware('auth')->group(function () {
    Route::livewire('landlord/home', HomeLandlord::class)->name('landlord.home');
});

require __DIR__.'/auth.php';
