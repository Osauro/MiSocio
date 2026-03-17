<?php

namespace App\Livewire;

use App\Models\Prestamo;
use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\Kardex;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Prestamos extends Component
{
    use WithPagination, RequiresTenant, SweetAlertTrait;

    public $search = '';
    public $perPage = 12;
    public $fecha_inicio = null;
    public $fecha_fin = null;
    public $mostrarModalFiltro = false;

    // Modal de detalles
    public $mostrarModal = false;
    public $prestamoSeleccionado = null;
    public $procesandoDevolucion = false;

    public function mount()
    {
        if (!prestamosHabilitados()) {
            abort(403, 'El módulo de préstamos no está habilitado.');
        }
        // Cargar perPage desde cookie
        $this->perPage = isset($_COOKIE['paginatePrestamos']) ? (int)$_COOKIE['paginatePrestamos'] : 12;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function verDetalles($prestamoId)
    {
        $this->prestamoSeleccionado = Prestamo::with(['cliente', 'user', 'prestamoItems.producto' => function ($query) {
            $query->withTrashed();
        }])
            ->findOrFail($prestamoId);
        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->prestamoSeleccionado = null;
        $this->procesandoDevolucion = false;
    }

    public function procesarDevolucion()
    {
        if ($this->procesandoDevolucion || !$this->prestamoSeleccionado) return;

        if ($this->prestamoSeleccionado->estado !== 'Prestado') {
            $this->toast('error', 'Este préstamo no puede ser devuelto');
            return;
        }

        try {
            $this->procesandoDevolucion = true;
            DB::beginTransaction();

            $depositoDevolver = $this->prestamoSeleccionado->total;
            $nombreCliente = $this->prestamoSeleccionado->cliente->nombre ?? 'Sin cliente';
            $numeroFolio = $this->prestamoSeleccionado->numero_folio;

            // Devolver todos los items
            foreach ($this->prestamoSeleccionado->prestamoItems as $item) {
                $producto = Producto::find($item->producto_id);

                if ($producto && $producto->control) {
                    $stockAnterior = $producto->stock;
                    $producto->stock += $item->cantidad;
                    $producto->save();

                    // Registrar ENTRADA en Kardex
                    Kardex::create([
                        'tenant_id' => currentTenantId(),
                        'user_id' => Auth::id(),
                        'producto_id' => $producto->id,
                        'entrada' => $item->cantidad,
                        'salida' => 0,
                        'anterior' => $stockAnterior,
                        'saldo' => $producto->stock,
                        'precio' => $item->precio,
                        'total' => round(($item->precio / ($producto->cantidad ?: 1)) * $item->cantidad, 2),
                        'obs' => 'Devolución préstamo #' . $numeroFolio . ' - ' . $nombreCliente,
                    ]);
                } elseif ($producto) {
                    // Producto sin control - solo registrar en Kardex
                    Kardex::create([
                        'tenant_id' => currentTenantId(),
                        'user_id' => Auth::id(),
                        'producto_id' => $producto->id,
                        'entrada' => $item->cantidad,
                        'salida' => 0,
                        'anterior' => 0,
                        'saldo' => 0,
                        'precio' => $item->precio,
                        'total' => round(($item->precio / ($producto->cantidad ?: 1)) * $item->cantidad, 2),
                        'obs' => 'Devolución préstamo #' . $numeroFolio . ' - ' . $nombreCliente,
                    ]);
                }
                // Los items NO se eliminan, se conservan como histórico
            }

            // Registrar devolución de depósito en movimientos (egreso de caja)
            if ($depositoDevolver > 0) {
                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => Auth::id(),
                    'detalle' => 'Devolución depósito préstamo #' . $numeroFolio . ' - ' . $nombreCliente,
                    'ingreso' => 0,
                    'egreso' => $depositoDevolver,
                ]);
            }

            // Actualizar estado del préstamo (conservar depósito como histórico)
            $this->prestamoSeleccionado->estado = 'Devuelto';
            $this->prestamoSeleccionado->save();

            DB::commit();

            $this->cerrarModal();
            $this->toast('success', 'Préstamo devuelto. En garantía: Bs. ' . number_format($depositoDevolver, 2));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->procesandoDevolucion = false;
            $this->toast('error', 'Error: ' . $e->getMessage());
        }
    }

    public function limpiarFiltroFechas()
    {
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
        $this->resetPage();
    }

    public function abrirModalFiltro()
    {
        $this->mostrarModalFiltro = true;
    }

    public function cerrarModalFiltro()
    {
        $this->mostrarModalFiltro = false;
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

    public function crearPrestamo()
    {
        if (!prestamosHabilitados()) {
            $this->toast('error', 'El módulo de préstamos no está habilitado.');
            return;
        }

        // Verificar si el usuario ya tiene un préstamo pendiente
        $prestamoPendiente = Prestamo::where('user_id', Auth::id())
            ->where('tenant_id', currentTenantId())
            ->where('estado', 'Pendiente')
            ->first();

        if ($prestamoPendiente) {
            return redirect()->route('prestamo', ['prestamoId' => $prestamoPendiente->id]);
        }

        // Crear nuevo préstamo
        $ultimoFolio = Prestamo::where('tenant_id', currentTenantId())->max('numero_folio') ?? 0;

        $prestamo = Prestamo::create([
            'tenant_id' => currentTenantId(),
            'user_id' => Auth::id(),
            'numero_folio' => $ultimoFolio + 1,
            'estado' => 'Pendiente',
            'total' => 0,
        ]);

        return redirect()->route('prestamo', ['prestamoId' => $prestamo->id]);
    }

    public function generarPDF($prestamoId)
    {
        $prestamo = Prestamo::with(['cliente', 'user', 'prestamoItems.producto' => function ($query) {
            $query->withTrashed();
        }])
            ->findOrFail($prestamoId);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.prestamo', compact('prestamo'));

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'prestamo-' . $prestamo->id . '.pdf');
    }

    public function imprimirTicket($prestamoId)
    {
        $this->dispatch('abrir-ticket-prestamo', [
            'prestamoId' => $prestamoId,
        ]);
    }

    public function render()
    {
        $query = Prestamo::with(['cliente', 'user', 'prestamoItems.producto'])
            ->where('tenant_id', currentTenantId());

        // Filtro por búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero_folio', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cliente', function ($q2) {
                        $q2->where('nombre', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Filtro por fechas
        if ($this->fecha_inicio && $this->fecha_fin) {
            $query->whereDate('created_at', '>=', $this->fecha_inicio)
                ->whereDate('created_at', '<=', $this->fecha_fin);
        }

        $prestamos = $query->orderBy('id', 'desc')->paginate($this->perPage);

        return view('livewire.prestamos', [
            'prestamos' => $prestamos
        ]);
    }
}
