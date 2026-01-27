<?php

namespace App\Livewire;

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
        // TODO: Implementar cuando exista el modelo Prestamo
        // $this->cantidadPendientes = Prestamo::where('estado', 'Pendiente')->count();
        $this->cantidadPendientes = 0;
    }

    public function render()
    {
        return view('livewire.prestamo-cart');
    }
}
