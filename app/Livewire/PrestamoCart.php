<?php

namespace App\Livewire;

use App\Models\Prestamo;
use App\Models\PrestamoItem;
use Livewire\Component;
use App\Traits\RequiresTenant;

class PrestamoCart extends Component
{
    use RequiresTenant;

    public $cantidadItems = 0;

    protected $listeners = ['prestamoActualizado' => 'actualizarContador'];

    public function mount()
    {
        $this->actualizarContador();
    }

    public function actualizarContador()
    {
        // Verificar que exista un tenant activo
        if (!currentTenantId()) {
            $this->cantidadItems = 0;
            return;
        }

        // Buscar préstamo pendiente del usuario actual
        $prestamoPendiente = Prestamo::where('tenant_id', currentTenantId())
            ->where('estado', 'Pendiente')
            ->where('user_id', auth()->id())
            ->first();

        if ($prestamoPendiente) {
            // Contar items en el préstamo pendiente
            $this->cantidadItems = PrestamoItem::where('prestamo_id', $prestamoPendiente->id)->count();
        } else {
            $this->cantidadItems = 0;
        }
    }

    public function render()
    {
        return view('livewire.prestamo-cart');
    }
}
