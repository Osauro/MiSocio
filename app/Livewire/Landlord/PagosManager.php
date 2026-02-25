<?php

namespace App\Livewire\Landlord;

use App\Models\Membresia;
use App\Models\Tenant;
use App\Traits\SweetAlertTrait;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Carbon\Carbon;

class PagosManager extends Component
{
    use WithPagination, SweetAlertTrait, WithFileUploads;

    public $search = '';
    public $soloPendientes = true;
    public $perPage = 15;

    // Modal de verificación
    public $modalOpen = false;
    public $pagoId;
    public $pago;
    public $accion; // 'verificar' o 'rechazar'
    public $notas;

    protected $queryString = ['search', 'soloPendientes'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSoloPendientes()
    {
        $this->resetPage();
    }

    public function verComprobante($id)
    {
        $this->pago = Membresia::withoutGlobalScope('tenant')
            ->with(['tenant', 'verificadoPor'])
            ->findOrFail($id);
        $this->pagoId = $id;
        $this->modalOpen = true;
        $this->accion = null;
        $this->notas = $this->pago->notas_verificacion ?? '';
    }

    public function verificarPago()
    {
        $this->accion = 'verificar';
    }

    public function rechazarPago()
    {
        $this->accion = 'rechazar';
    }

    public function confirmarAccion()
    {
        $this->validate([
            'notas' => 'nullable|string|max:500'
        ]);

        try {
            $pago = Membresia::withoutGlobalScope('tenant')->findOrFail($this->pagoId);

            if ($this->accion === 'verificar') {
                $pago->update([
                    'estado_pago' => 'verificado',
                    'verificado_por' => auth()->id(),
                    'verificado_at' => now(),
                    'notas_verificacion' => $this->notas
                ]);

                // Actualizar fecha de próximo pago del tenant
                $tenant = $pago->tenant;
                $fechaInicio = $pago->fecha_inicio ?? now();
                $fechaFin = $fechaInicio->copy()->addMonths((int) $pago->duracion_meses);

                $pago->update([
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin
                ]);

                $tenant->update([
                    'bill_date' => $fechaFin,
                    'status' => 1
                ]);

                // Activar el acceso de todos los usuarios al tenant
                foreach ($tenant->users as $user) {
                    $tenant->users()->updateExistingPivot($user->id, ['is_active' => true]);
                }

                $this->alertSuccess('Pago verificado exitosamente');
            } else {
                $pago->update([
                    'estado_pago' => 'rechazado',
                    'verificado_por' => auth()->id(),
                    'verificado_at' => now(),
                    'notas_verificacion' => $this->notas
                ]);

                $this->alertSuccess('Pago rechazado');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            $this->alertError('Error: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->modalOpen = false;
        $this->pagoId = null;
        $this->pago = null;
        $this->accion = null;
        $this->notas = '';
    }

    #[Layout('layouts.landlord.theme')]
    public function render()
    {
        $pagos = Membresia::withoutGlobalScope('tenant')
            ->with(['tenant', 'verificadoPor'])
            ->when($this->search, function ($query) {
                $query->whereHas('tenant', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('plan_nombre', 'like', '%' . $this->search . '%')
                ->orWhere('monto', 'like', '%' . $this->search . '%');
            })
            ->when($this->soloPendientes, function ($query) {
                $query->where('estado_pago', 'pendiente');
            })
            ->latest()
            ->paginate($this->perPage);

        $estadisticas = [
            'pendientes' => Membresia::withoutGlobalScope('tenant')->where('estado_pago', 'pendiente')->count(),
            'verificados' => Membresia::withoutGlobalScope('tenant')->where('estado_pago', 'verificado')->count(),
            'rechazados' => Membresia::withoutGlobalScope('tenant')->where('estado_pago', 'rechazado')->count(),
            'total_mes' => Membresia::withoutGlobalScope('tenant')
                ->where('estado_pago', 'verificado')
                ->whereMonth('verificado_at', now()->month)
                ->sum('monto'),
        ];

        return view('livewire.landlord.pagos-manager', [
            'pagos' => $pagos,
            'estadisticas' => $estadisticas
        ]);
    }
}
