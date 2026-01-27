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
        $this->cantidadPendientes = Venta::where('estado', 'Pendiente')->count();
    }

    public function render()
    {
        return view('livewire.venta-cart');
    }
}
