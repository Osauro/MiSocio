<?php

namespace App\Livewire;

use App\Models\Venta;
use Livewire\Component;
use App\Traits\RequiresTenant;

class VentaCart extends Component
{
    use RequiresTenant;

    public $cantidadPendientes = 0;

    protected $listeners = ['ventaActualizada' => 'actualizarContador'];

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

        // Contar items de la venta pendiente actual del usuario
        $ventaPendiente = Venta::where('tenant_id', currentTenantId())
            ->where('estado', 'Pendiente')
            ->where('user_id', auth()->id())
            ->first();

        $this->cantidadPendientes = $ventaPendiente
            ? $ventaPendiente->ventaItems()->count()
            : 0;
    }

    public function render()
    {
        return view('livewire.venta-cart');
    }
}
