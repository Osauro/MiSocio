<?php

namespace App\Livewire;

use App\Models\Compra;
use Livewire\Component;
use App\Traits\RequiresTenant;

class CompraCart extends Component
{
    use RequiresTenant;

    public $cantidadPendientes = 0;
    public $compraPendienteId = null;

    public function mount()
    {
        $this->actualizarContador();
    }

    public function actualizarContador()
    {
        // Verificar que exista un tenant activo
        if (!currentTenantId()) {
            $this->cantidadPendientes = 0;
            $this->compraPendienteId = null;
            return;
        }

        $compraPendiente = Compra::where('tenant_id', currentTenantId())
            ->where('user_id', auth()->id())
            ->where('estado', 'Pendiente')
            ->first();

        if ($compraPendiente) {
            $this->cantidadPendientes = $compraPendiente->compraItems()->count();
            $this->compraPendienteId = $compraPendiente->id;
        } else {
            $this->cantidadPendientes = 0;
            $this->compraPendienteId = null;
        }
    }

    public function render()
    {
        return view('livewire.compra-cart');
    }
}
