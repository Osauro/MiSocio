<?php

namespace App\Livewire;

use App\Models\Movimiento;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Livewire\Component;
use Livewire\WithPagination;

class Movimientos extends Component
{
    use WithPagination, SweetAlertTrait, RequiresTenant;

    public $search = '';
    public $perPage;
    public $fecha_inicio = null;
    public $fecha_fin = null;

    // Modal
    public $detalle = '';
    public $tipo = 'ingreso'; // ingreso o egreso
    public $monto = '';

    protected $rules = [
        'detalle' => 'nullable|string|max:255',
        'tipo' => 'required|in:ingreso,egreso',
        'monto' => 'required|numeric|min:0.01',
    ];

    protected $messages = [
        'monto.required' => 'El monto es obligatorio',
        'monto.min' => 'El monto debe ser mayor a 0',
    ];

    public function mount()
    {
        $this->perPage = $_COOKIE['paginateMovimientos'] ?? 15;
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
        $movimientosQuery = Movimiento::query()
            ->with('user');

        // Filtrar por búsqueda (usuario o detalle)
        if ($this->search) {
            $movimientosQuery->where(function ($query) {
                $query->where('detalle', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            });
        }

        // Filtrar por rango de fechas solo si ambas están definidas
        if ($this->fecha_inicio && $this->fecha_fin) {
            $movimientosQuery->whereDate('created_at', '>=', $this->fecha_inicio)
                            ->whereDate('created_at', '<=', $this->fecha_fin);
        }

        $movimientos = $movimientosQuery->orderBy('id', 'desc')->paginate($this->perPage);

        // Calcular totales (aplicar mismo filtro de fechas si existe)
        $totalesQuery = Movimiento::query();
        if ($this->fecha_inicio && $this->fecha_fin) {
            $totalesQuery->whereDate('created_at', '>=', $this->fecha_inicio)
                        ->whereDate('created_at', '<=', $this->fecha_fin);
        }

        $totalIngresos = $totalesQuery->sum('ingreso');
        $totalEgresos = (clone $totalesQuery)->sum('egreso');

        $saldoActual = Movimiento::orderBy('id', 'desc')->first()?->saldo ?? 0;

        return view('livewire.movimientos', [
            'movimientos' => $movimientos,
            'totalIngresos' => $totalIngresos,
            'totalEgresos' => $totalEgresos,
            'saldoActual' => $saldoActual,
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('showmodal');
    }

    public function save()
    {
        $this->validate();

        // Si no hay detalle, asignar "Retiro" o "Depósito" según el tipo
        $detalle = $this->detalle ?: ($this->tipo === 'ingreso' ? 'Depósito' : 'Retiro');

        Movimiento::create([
            'detalle' => $detalle,
            'ingreso' => $this->tipo === 'ingreso' ? $this->monto : 0,
            'egreso' => $this->tipo === 'egreso' ? $this->monto : 0,
        ]);

        $this->alertSuccess('Movimiento registrado exitosamente');
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->dispatch('closemodal');
    }

    private function resetForm()
    {
        $this->detalle = '';
        $this->tipo = 'ingreso';
        $this->monto = '';
        $this->resetValidation();
    }
}
