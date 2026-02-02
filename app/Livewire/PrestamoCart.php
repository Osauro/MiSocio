<?php

namespace App\Livewire;

use App\Models\Prestamo;
use Livewire\Component;
use App\Traits\RequiresTenant;

class PrestamoCart extends Component
{
    use RequiresTenant;

    public $cantidadPendientes = 0;

    protected $listeners = ['prestamoActualizado' => 'actualizarContador'];

    public function mount()
    {
        $this->actualizarContador();
    }

    public function actualizarContador()
    {
        // Verificar que exista un tenant activo
        if (!currentTenantId()) {
            $this->cantidadPendientes = 0;
            return;
        }

        // Solo contar préstamos pendientes del usuario actual
        $this->cantidadPendientes = Prestamo::where('tenant_id', currentTenantId())
            ->where('estado', 'Pendiente')
            ->where('user_id', auth()->id())
            ->count();
    }

    public function render()
    {
        return view('livewire.prestamo-cart');
    }
}
