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
use Illuminate\Support\Facades\DB;

class Prestamos extends Component
{
    use WithPagination, RequiresTenant, SweetAlertTrait;

    public $search = '';
    public $perPage = 12;
    public $fecha_inicio = null;
    public $fecha_fin = null;
    public $prestamoSeleccionado = null;
    public $mostrarModal = false;
    public $mostrarResumenEliminacion = false;
    public $resumenEliminacion = [];
    public $mostrarModalFiltro = false;

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
    }

    public function cerrarResumen()
    {
        $this->mostrarResumenEliminacion = false;
        $this->resumenEliminacion = [];
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
        // Verificar si el usuario ya tiene un préstamo pendiente
        $prestamoPendiente = Prestamo::where('user_id', auth()->id())
            ->where('estado', 'Pendiente')
            ->first();

        if ($prestamoPendiente) {
            // Redirigir al préstamo pendiente
            return redirect()->route('prestamo', ['prestamoId' => $prestamoPendiente->id]);
        }

        // Si no hay pendiente, crear uno nuevo
        $nuevoPrestamo = Prestamo::create([
            'tenant_id' => currentTenantId(),
            'user_id' => auth()->id(),
            'estado' => 'Pendiente',
            'deposito' => 0,
            'fecha_prestamo' => now(),
        ]);

        // Redirigir al nuevo préstamo
        return redirect()->route('prestamo', ['prestamoId' => $nuevoPrestamo->id]);
    }

    public function eliminar($prestamoId)
    {
        try {
            DB::beginTransaction();

            $prestamo = Prestamo::with(['prestamoItems.producto' => function ($query) {
                $query->withTrashed();
            }])->findOrFail($prestamoId);

            // Si el préstamo está PENDIENTE, eliminar físicamente
            if ($prestamo->estado === 'Pendiente') {
                $prestamo->delete();
                DB::commit();

                $this->toast('success', 'Préstamo pendiente eliminado exitosamente');
                return;
            }

            // Si el préstamo está COMPLETO, marcar como Eliminado y devolver productos/dinero
            if ($prestamo->estado !== 'Completo') {
                $this->toast('error', 'Solo se pueden eliminar préstamos pendientes o completos');
                return;
            }

            // Preparar resumen
            $productosAfectados = [];
            $totalDevuelto = $prestamo->deposito;
            $prestamoId = $prestamo->id;

            // Devolver stock de cada producto y registrar en Kardex
            foreach ($prestamo->prestamoItems as $item) {
                $producto = $item->producto;

                // Devolver el stock
                $stockAnterior = $producto->stock;
                $producto->stock += $item->cantidad;
                $producto->save();

                // Formatear stocks
                $cantidadPorMedida = $producto->cantidad ?? 1;
                $medidaAbrev = strtolower(substr($producto->medida ?? 'u', 0, 1));

                // Formatear stock anterior
                if ($cantidadPorMedida <= 1) {
                    $stockAnteriorFormateado = intval($stockAnterior) . $medidaAbrev;
                } else {
                    $cajasAnt = floor($stockAnterior / $cantidadPorMedida);
                    $unidadesAnt = $stockAnterior % $cantidadPorMedida;
                    if ($cajasAnt > 0 && $unidadesAnt > 0) {
                        $stockAnteriorFormateado = "{$cajasAnt}{$medidaAbrev} - {$unidadesAnt}u";
                    } elseif ($cajasAnt > 0) {
                        $stockAnteriorFormateado = "{$cajasAnt}{$medidaAbrev}";
                    } else {
                        $stockAnteriorFormateado = "{$unidadesAnt}u";
                    }
                }

                // Formatear stock nuevo
                if ($cantidadPorMedida <= 1) {
                    $stockNuevoFormateado = intval($producto->stock) . $medidaAbrev;
                } else {
                    $cajasNue = floor($producto->stock / $cantidadPorMedida);
                    $unidadesNue = $producto->stock % $cantidadPorMedida;
                    if ($cajasNue > 0 && $unidadesNue > 0) {
                        $stockNuevoFormateado = "{$cajasNue}{$medidaAbrev} - {$unidadesNue}u";
                    } elseif ($cajasNue > 0) {
                        $stockNuevoFormateado = "{$cajasNue}{$medidaAbrev}";
                    } else {
                        $stockNuevoFormateado = "{$unidadesNue}u";
                    }
                }

                // Guardar para el resumen
                $productosAfectados[] = [
                    'nombre' => $producto->nombre,
                    'cantidad' => $item->cantidad,
                    'cantidad_formateada' => $item->cantidad_formateada,
                    'stock_anterior' => $stockAnterior,
                    'stock_anterior_formateado' => $stockAnteriorFormateado,
                    'stock_nuevo' => $producto->stock,
                    'stock_nuevo_formateado' => $stockNuevoFormateado
                ];

                // Registrar entrada en Kardex (reversión del préstamo)
                Kardex::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => auth()->id(),
                    'producto_id' => $producto->id,
                    'entrada' => $item->cantidad,
                    'salida' => 0,
                    'anterior' => $stockAnterior,
                    'saldo' => $producto->stock,
                    'precio' => $item->precio_deposito,
                    'total' => $item->subtotal_deposito,
                    'obs' => "Eliminación de préstamo #{$prestamo->numero_folio}"
                ]);
            }

            // Devolver el depósito a caja
            if ($prestamo->deposito > 0) {
                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => auth()->id(),
                    'detalle' => "Devolución de depósito por eliminación de préstamo #{$prestamo->numero_folio}",
                    'ingreso' => 0,
                    'egreso' => $prestamo->deposito
                ]);
            }

            // Marcar el préstamo como eliminado (no borrar físicamente)
            $prestamo->estado = 'Eliminado';
            $prestamo->save();

            DB::commit();

            // Mostrar resumen
            $this->resumenEliminacion = [
                'prestamo_id' => $prestamoId,
                'deposito' => $prestamo->deposito,
                'devuelto_caja' => $totalDevuelto,
                'productos' => $productosAfectados,
                'total_productos' => count($productosAfectados)
            ];

            $this->mostrarResumenEliminacion = true;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->toast('error', 'Error al eliminar el préstamo:<br>' . $e->getMessage());
        }
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

    public function render()
    {
        $prestamos = Prestamo::with(['cliente', 'user', 'prestamoItems.producto' => function ($query) {
            $query->withTrashed();
        }])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('cliente', function ($subQ) {
                        $subQ->where('nombre', 'like', '%' . $this->search . '%');
                    })
                        ->orWhere('estado', 'like', '%' . $this->search . '%')
                        ->orWhere('id', 'like', '%' . $this->search . '%')
                        ->orWhereHas('prestamoItems.producto', function ($subQ) {
                            $subQ->withTrashed()
                                ->where('nombre', 'like', '%' . $this->search . '%')
                                ->orWhere('codigo', 'like', '%' . $this->search . '%')
                                ->orWhereHas('tags', function ($tagQuery) {
                                    $tagQuery->where('nombre', 'like', '%' . $this->search . '%');
                                });
                        });
                });
            })
            ->when($this->fecha_inicio && $this->fecha_fin, function ($query) {
                $query->whereDate('created_at', '>=', $this->fecha_inicio)
                      ->whereDate('created_at', '<=', $this->fecha_fin);
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.prestamos', [
            'prestamos' => $prestamos
        ]);
    }
}
