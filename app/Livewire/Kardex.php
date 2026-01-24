<?php

namespace App\Livewire;

use App\Models\Kardex as KardexModel;
use App\Models\Producto;
use App\Traits\RequiresTenant;
use Livewire\Component;
use Livewire\WithPagination;

class Kardex extends Component
{
    use WithPagination, RequiresTenant;

    public $search = '';
    public $perPage;
    public $fecha_inicio = null;
    public $fecha_fin = null;

    public function mount()
    {
        $this->perPage = $_COOKIE['paginateKardex'] ?? 7;
    }

    public function limpiarFiltroFechas()
    {
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFechaInicio()
    {
        if ($this->fecha_inicio && !$this->fecha_fin) {
            $this->fecha_fin = now()->format('Y-m-d');
        }
        $this->resetPage();
    }

    public function updatingFechaFin()
    {
        $this->resetPage();
    }

    public function render()
    {
        // El GlobalScope de Kardex filtra automáticamente por tenant
        $kardexQuery = KardexModel::query()
            ->with(['producto', 'user']);

        // Filtrar por búsqueda (nombre de producto o código)
        if ($this->search) {
            $kardexQuery->whereHas('producto', function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('codigo', 'like', '%' . $this->search . '%');
            });
        }

        // Filtrar por rango de fechas solo si ambas están definidas
        if ($this->fecha_inicio && $this->fecha_fin) {
            $kardexQuery->whereDate('created_at', '>=', $this->fecha_inicio)
                ->whereDate('created_at', '<=', $this->fecha_fin);
        }

        $kardex = $kardexQuery->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.kardex', [
            'kardex' => $kardex
        ]);
    }

    public function filtrarPorProducto($nombreProducto)
    {
        // Toggle: si ya está filtrado por este producto, limpiar el search
        if ($this->search === $nombreProducto) {
            $this->search = '';
        } else {
            $this->search = $nombreProducto;
        }
        $this->resetPage();
    }
}
