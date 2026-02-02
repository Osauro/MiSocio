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
    public $mostrarModalFiltro = false;

    // Para el pago de crédito
    public $mostrarModalPago = false;
    public $ventaAPagar = null;
    public $montoPagoEfectivo = 0;
    public $montoPagoOnline = 0;
    public $procesandoPago = false;

    public function updatedMontoPagoEfectivo($value)
    {
        if (!$this->ventaAPagar) return;

        $efectivo = round((float) $value, 2);
        $creditoTotal = round($this->ventaAPagar->credito, 2);

        // Si el efectivo es mayor o igual al crédito total, poner online en 0
        if ($efectivo >= $creditoTotal) {
            $this->montoPagoEfectivo = $creditoTotal;
            $this->montoPagoOnline = 0;
        }
        // Si efectivo + online excede el crédito, ajustar online
        else {
            $online = round((float) $this->montoPagoOnline, 2);
            if ($efectivo + $online > $creditoTotal) {
                $this->montoPagoOnline = round($creditoTotal - $efectivo, 2);
            }
        }
    }

    public function updatedMontoPagoOnline($value)
    {
        if (!$this->ventaAPagar) return;

        $online = round((float) $value, 2);
        $creditoTotal = round($this->ventaAPagar->credito, 2);

        // Si el online es mayor o igual al crédito total, poner efectivo en 0
        if ($online >= $creditoTotal) {
            $this->montoPagoOnline = $creditoTotal;
            $this->montoPagoEfectivo = 0;
        }
        // Si online + efectivo excede el crédito, ajustar efectivo
        else {
            $efectivo = round((float) $this->montoPagoEfectivo, 2);
            if ($online + $efectivo > $creditoTotal) {
                $this->montoPagoEfectivo = round($creditoTotal - $online, 2);
            }
        }
    }

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
        $this->montoPagoEfectivo = $venta->credito; // Por defecto el monto total en efectivo
        $this->montoPagoOnline = 0;
        $this->procesandoPago = false;
        $this->mostrarModalPago = true;
    }

    public function cerrarModalPago()
    {
        $this->mostrarModalPago = false;
        $this->ventaAPagar = null;
        $this->montoPagoEfectivo = 0;
        $this->montoPagoOnline = 0;
        $this->procesandoPago = false;
    }

    public function pagarCredito()
    {
        // Validaciones
        $efectivo = round((float) $this->montoPagoEfectivo, 2);
        $online = round((float) $this->montoPagoOnline, 2);
        $totalPago = $efectivo + $online;

        if ($totalPago <= 0) {
            $this->toast('error', 'Debe ingresar un monto mayor a 0');
            return;
        }

        if ($totalPago > $this->ventaAPagar->credito) {
            $this->toast('error', 'El monto total no puede ser mayor a la deuda pendiente');
            return;
        }

        try {
            $this->procesandoPago = true;
            DB::beginTransaction();

            // Calcular nuevo crédito y actualizar efectivo/online
            $nuevoCredito = round($this->ventaAPagar->credito - $totalPago, 2);
            $nuevoEfectivo = round($this->ventaAPagar->efectivo + $efectivo, 2);
            $nuevoOnline = round($this->ventaAPagar->online + $online, 2);

            $this->ventaAPagar->update([
                'credito' => $nuevoCredito,
                'efectivo' => $nuevoEfectivo,
                'online' => $nuevoOnline
            ]);

            // Registrar movimientos
            $nombreCliente = $this->ventaAPagar->cliente ? $this->ventaAPagar->cliente->nombre : 'Sin cliente';
            $detalleBase = 'Cobro de crédito venta #' . $this->ventaAPagar->numero_folio . ' - ' . $nombreCliente;

            if ($nuevoCredito > 0) {
                $detalleBase .= ' (Pago parcial: Bs. ' . number_format($totalPago, 2) . ' / Saldo pendiente: Bs. ' . number_format($nuevoCredito, 2) . ')';
            } else {
                $detalleBase .= ' (Pago total)';
            }

            // Registrar movimiento de efectivo si hay
            if ($efectivo > 0) {
                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => auth()->id(),
                    'detalle' => $detalleBase,
                    'ingreso' => $efectivo,
                    'egreso' => 0
                ]);
            }

            // Registrar movimiento de online si hay
            if ($online > 0) {
                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => auth()->id(),
                    'detalle' => $detalleBase . ' (Online)',
                    'ingreso' => $online,
                    'egreso' => 0
                ]);
            }

            DB::commit();

            $mensaje = 'Pago registrado exitosamente';
            if ($nuevoCredito > 0) {
                $mensaje .= '.<br>Saldo pendiente: Bs. ' . number_format($nuevoCredito, 2);
            } else {
                $mensaje .= '.<br>Deuda cobrada completamente';
            }

            $this->toast('success', $mensaje);
            $this->procesandoPago = false;
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
                    'entrada' => $item->cantidad,
                    'salida' => 0,
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
