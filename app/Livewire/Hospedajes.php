<?php

namespace App\Livewire;

use App\Models\Hospedaje;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Livewire\Component;
use Livewire\WithPagination;

class Hospedajes extends Component
{
    use WithPagination, RequiresTenant, SweetAlertTrait;

    public $search = '';
    public $filtroEstado = '';
    public $perPage = 15;

    // Modal detalles
    public $mostrarModal = false;
    public $hospedajeSeleccionado = null;

    public function mount()
    {
        if (!hospedajesHabilitados()) {
            abort(403, 'El módulo de hospedajes no está habilitado.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function verDetalles(int $id): void
    {
        $this->hospedajeSeleccionado = Hospedaje::with([
            'cliente',
            'user',
            'habitaciones.habitacion.tipoHabitacion',
            'habitaciones.tarifa',
        ])->findOrFail($id);
        $this->mostrarModal = true;
    }

    public function cerrarModal(): void
    {
        $this->mostrarModal            = false;
        $this->hospedajeSeleccionado   = null;
    }

    public function render()
    {
        $hospedajes = Hospedaje::with(['cliente', 'user', 'habitaciones.habitacion'])
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->search, function ($q) {
                $q->whereHas('cliente', fn($c) => $c->where('nombre', 'like', '%' . $this->search . '%'))
                  ->orWhere('numero_folio', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.hospedajes', compact('hospedajes'));
    }
}
