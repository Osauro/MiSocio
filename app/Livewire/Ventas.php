<?php

namespace App\Livewire;

use App\Models\Venta;
use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\Kardex;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Ventas extends Component
{
    use WithPagination, RequiresTenant, SweetAlertTrait;

    public $search = '';
    public $perPage = 12;
    public $fecha_inicio = null;
    public $fecha_fin = null;
    public $ventaSeleccionada = null;
    public $mostrarModal = false;
    public $mostrarResumenEliminacion = false;
    public $resumenEliminacion = [];
    public $mostrarErrorStock = false;
    public $productosInsuficientes = [];

    // Para el pago de crédito
    public $mostrarModalPago = false;
    public $ventaAPagar = null;
    public $montoPago = 0;
    public $saldoCaja = 0;
    public $pasoPago = 0;
    public $montoAñadirCaja = 0;
    public $procesandoPago = false;

    public function verDetalles($ventaId)
    {
        $this->ventaSeleccionada = Venta::with(['cliente', 'user', 'ventaItems.producto' => function ($query) {
            $query->withTrashed();
        }])
            ->findOrFail($ventaId);
        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->ventaSeleccionada = null;
    }

    public function cerrarResumen()
    {
        $this->mostrarResumenEliminacion = false;
        $this->resumenEliminacion = [];
    }

    public function cerrarErrorStock()
    {
        $this->mostrarErrorStock = false;
        $this->productosInsuficientes = [];
    }

    public function abrirModalPago($ventaId)
    {
        $venta = Venta::with('cliente')->findOrFail($ventaId);

        if ($venta->credito <= 0) {
            $this->toast('error', 'Esta venta no tiene deuda pendiente');
            return;
        }

        $this->ventaAPagar = $venta;
        $this->obtenerSaldoCaja();
        $this->pasoPago = 1;
        $this->montoAñadirCaja = 0;
        $this->montoPago = 0;
        $this->procesandoPago = false;
        $this->mostrarModalPago = true;
    }

    public function cerrarModalPago()
    {
        $this->mostrarModalPago = false;
        $this->ventaAPagar = null;
        $this->montoPago = 0;
        $this->pasoPago = 0;
        $this->montoAñadirCaja = 0;
        $this->procesandoPago = false;
    }

    private function obtenerSaldoCaja()
    {
        $movimientos = Movimiento::where('tenant_id', currentTenantId())->get();
        $this->saldoCaja = $movimientos->sum('ingreso') - $movimientos->sum('egreso');
    }

    public function avanzarPasoPago1()
    {
        // Si hay monto a añadir, agregarlo a la caja
        if ($this->montoAñadirCaja > 0) {
            Movimiento::create([
                'tenant_id' => currentTenantId(),
                'user_id' => auth()->id(),
                'detalle' => 'Añadir fondos para cobro de crédito',
                'ingreso' => $this->montoAñadirCaja,
                'egreso' => 0
            ]);

            $this->obtenerSaldoCaja();
        }

        // Avanzar al paso 2 y determinar monto de pago
        $this->montoPago = $this->ventaAPagar->credito;
        $this->pasoPago = 2;
        $this->dispatch('paso-changed');
    }

    public function avanzarPasoPago2()
    {
        // Validaciones
        if ($this->montoPago <= 0) {
            $this->toast('error', 'El monto debe ser mayor a 0');
            return;
        }

        if ($this->montoPago > $this->ventaAPagar->credito) {
            $this->toast('error', 'El monto no puede ser mayor a la deuda pendiente');
            return;
        }

        // Avanzar al paso 3 y procesar
        $this->pasoPago = 3;
        $this->procesarPagoCredito();
    }

    public function retrocederPasoPago()
    {
        if ($this->pasoPago > 1) {
            $this->pasoPago--;

            // Limpiar datos según el paso
            if ($this->pasoPago === 1) {
                $this->montoAñadirCaja = 0;
                $this->montoPago = 0;
            }

            $this->dispatch('paso-changed');
        }
    }

    private function procesarPagoCredito()
    {
        try {
            $this->procesandoPago = true;
            DB::beginTransaction();

            // Actualizar la venta
            $nuevoCredito = $this->ventaAPagar->credito - $this->montoPago;
            $nuevoEfectivo = $this->ventaAPagar->efectivo + $this->montoPago;

            $this->ventaAPagar->update([
                'credito' => $nuevoCredito,
                'efectivo' => $nuevoEfectivo
            ]);

            // Registrar el movimiento de ingreso (cobro de crédito)
            $nombreCliente = $this->ventaAPagar->cliente ? $this->ventaAPagar->cliente->nombre : 'Sin cliente';
            $detalle = 'Cobro de crédito venta #' . $this->ventaAPagar->numero_folio . ' - ' . $nombreCliente;

            if ($nuevoCredito > 0) {
                $detalle .= ' (Pago parcial: Bs. ' . number_format($this->montoPago, 2) . ' / Saldo pendiente: Bs. ' . number_format($nuevoCredito, 2) . ')';
            } else {
                $detalle .= ' (Pago total)';
            }

            Movimiento::create([
                'tenant_id' => currentTenantId(),
                'user_id' => auth()->id(),
                'detalle' => $detalle,
                'ingreso' => $this->montoPago,
                'egreso' => 0
            ]);

            DB::commit();

            $mensaje = 'Pago registrado exitosamente';
            if ($nuevoCredito > 0) {
                $mensaje .= '.<br>Saldo pendiente: Bs. ' . number_format($nuevoCredito, 2);
            } else {
                $mensaje .= '.<br>Deuda cobrada completamente';
            }

            $this->toast('success', $mensaje);

            $this->cerrarModalPago();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->procesandoPago = false;
            $this->toast('error', 'Error al procesar el pago:<br>' . $e->getMessage());
        }
    }

    public function limpiarFiltroFechas()
    {
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
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

    public function crearVenta()
    {
        // Verificar si el usuario ya tiene una venta pendiente
        $ventaPendiente = Venta::where('user_id', auth()->id())
            ->where('estado', 'Pendiente')
            ->first();

        if ($ventaPendiente) {
            // Redirigir a la venta pendiente
            return redirect()->route('venta', ['ventaId' => $ventaPendiente->id]);
        }

        // Si no hay pendiente, crear una nueva
        $nuevaVenta = Venta::create([
            'tenant_id' => currentTenantId(),
            'user_id' => auth()->id(),
            'estado' => 'Pendiente',
            'efectivo' => 0,
            'online' => 0,
            'credito' => 0,
            'cambio' => 0,
        ]);

        // Redirigir a la nueva venta
        return redirect()->route('venta', ['ventaId' => $nuevaVenta->id]);
    }

    public function eliminar($ventaId)
    {
        try {
            DB::beginTransaction();

            $venta = Venta::with(['ventaItems.producto' => function ($query) {
                $query->withTrashed();
            }])->findOrFail($ventaId);

            // Si la venta está PENDIENTE, eliminar físicamente
            if ($venta->estado === 'Pendiente') {
                $venta->delete();
                DB::commit();

                $this->toast('success', 'Venta pendiente eliminada exitosamente');
                return;
            }

            // Si la venta está COMPLETA, marcar como Eliminado y devolver productos/dinero
            if ($venta->estado !== 'Completo') {
                $this->toast('error', 'Solo se pueden eliminar ventas pendientes o completas');
                return;
            }

            // Preparar resumen
            $productosAfectados = [];
            $totalDevuelto = $venta->efectivo;
            $ventaId = $venta->id;
            $totalVenta = $venta->efectivo + $venta->online + $venta->credito;

            // Devolver stock de cada producto y registrar en Kardex
            foreach ($venta->ventaItems as $item) {
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

                // Registrar entrada en Kardex (reversión de la venta)
                Kardex::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => auth()->id(),
                    'producto_id' => $producto->id,
                    'entradas' => $item->cantidad,
                    'salidas' => 0,
                    'anterior' => $stockAnterior,
                    'saldo' => $producto->stock,
                    'precio' => $item->precio,
                    'total' => $item->subtotal,
                    'obs' => "Eliminación de venta #{$venta->numero_folio}"
                ]);
            }

            // Devolver solo el efectivo a caja (el crédito no se cobró aún, el online no estaba en caja)
            if ($venta->efectivo > 0) {
                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => auth()->id(),
                    'detalle' => "Devolución por eliminación de venta #{$venta->numero_folio}",
                    'ingreso' => 0,
                    'egreso' => $venta->efectivo
                ]);
            }

            // Marcar la venta como eliminada (no borrar físicamente)
            $venta->estado = 'Eliminado';
            $venta->save();

            DB::commit();

            // Mostrar resumen
            $this->resumenEliminacion = [
                'venta_id' => $ventaId,
                'total_venta' => $totalVenta,
                'efectivo' => $venta->efectivo,
                'online' => $venta->online,
                'credito' => $venta->credito,
                'devuelto_caja' => $totalDevuelto,
                'productos' => $productosAfectados,
                'total_productos' => count($productosAfectados)
            ];

            $this->mostrarResumenEliminacion = true;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->toast('error', 'Error al eliminar la venta:<br>' . $e->getMessage());
        }
    }

    public function generarPDF($ventaId)
    {
        $venta = Venta::with(['cliente', 'user', 'ventaItems.producto' => function ($query) {
            $query->withTrashed();
        }])
            ->findOrFail($ventaId);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.venta', compact('venta'));

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'venta-' . $venta->id . '.pdf');
    }

    public function render()
    {
        $ventas = Venta::with(['cliente', 'user', 'ventaItems.producto' => function ($query) {
            $query->withTrashed();
        }])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('cliente', function ($subQ) {
                        $subQ->where('nombre', 'like', '%' . $this->search . '%');
                    })
                        ->orWhere('estado', 'like', '%' . $this->search . '%')
                        ->orWhere('id', 'like', '%' . $this->search . '%')
                        ->orWhereHas('ventaItems.producto', function ($subQ) {
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

        return view('livewire.ventas', [
            'ventas' => $ventas
        ]);
    }
}
