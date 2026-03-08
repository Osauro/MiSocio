<?php

namespace App\Livewire;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\Kardex;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Compras extends Component
{
    use WithPagination, RequiresTenant, SweetAlertTrait;

    public $search = '';
    public $perPage = 12;
    public $fecha_inicio = null;
    public $fecha_fin = null;
    public $compraSeleccionada = null;
    public $mostrarModal = false;
    public $mostrarResumenEliminacion = false;
    public $resumenEliminacion = [];
    public $mostrarErrorStock = false;
    public $productosInsuficientes = [];
    public $mostrarModalFiltro = false;

    // Para el pago de crédito
    public $mostrarModalPago = false;
    public $compraAPagar = null;
    public $montoPago = 0;
    public $saldoCaja = 0;
    public $pasoPago = 0;
    public $montoAñadirCaja = 0;
    public $procesandoPago = false;

    public function mount()
    {
        $this->perPage = isset($_COOKIE['paginateCompras']) ? (int)$_COOKIE['paginateCompras'] : 12;
        $this->fecha_inicio = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin    = now()->endOfMonth()->format('Y-m-d');
    }

    public function verDetalles($compraId)
    {
        $this->compraSeleccionada = Compra::with(['proveedor', 'user', 'compraItems.producto' => function ($query) {
            $query->withTrashed();
        }])
            ->findOrFail($compraId);
        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->compraSeleccionada = null;
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

    public function abrirModalPago($compraId)
    {
        $compra = Compra::with('proveedor')->findOrFail($compraId);

        if ($compra->credito <= 0) {
            $this->toast('error', 'Esta compra no tiene deuda pendiente');
            return;
        }

        $this->compraAPagar = $compra;
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
        $this->compraAPagar = null;
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
                'detalle' => 'Añadir fondos para pago de crédito',
                'ingreso' => $this->montoAñadirCaja,
                'egreso' => 0
            ]);

            $this->obtenerSaldoCaja();
        }

        // Avanzar al paso 2 y determinar monto de pago
        if ($this->saldoCaja >= $this->compraAPagar->credito) {
            $this->montoPago = $this->compraAPagar->credito;
        } else {
            $this->montoPago = $this->saldoCaja;
        }

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

        if ($this->montoPago > $this->compraAPagar->credito) {
            $this->toast('error', 'El monto no puede ser mayor a la deuda pendiente');
            return;
        }

        if ($this->montoPago > $this->saldoCaja) {
            $this->toast('error', 'El monto no puede ser mayor al saldo en caja');
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

            // Actualizar la compra
            $nuevoCredito = $this->compraAPagar->credito - $this->montoPago;
            $nuevoEfectivo = $this->compraAPagar->efectivo + $this->montoPago;

            $this->compraAPagar->update([
                'credito' => $nuevoCredito,
                'efectivo' => $nuevoEfectivo
            ]);

            // Registrar el movimiento de egreso
            $nombreProveedor = $this->compraAPagar->proveedor ? $this->compraAPagar->proveedor->nombre : 'Sin proveedor';
            $detalle = 'Pago de crédito compra #' . $this->compraAPagar->numero_folio . ' - ' . $nombreProveedor;

            if ($nuevoCredito > 0) {
                $detalle .= ' (Pago parcial: Bs. ' . number_format($this->montoPago, 2) . ' / Saldo pendiente: Bs. ' . number_format($nuevoCredito, 2) . ')';
            } else {
                $detalle .= ' (Pago total)';
            }

            Movimiento::create([
                'tenant_id' => currentTenantId(),
                'user_id' => auth()->id(),
                'detalle' => $detalle,
                'ingreso' => 0,
                'egreso' => $this->montoPago
            ]);

            DB::commit();

            $mensaje = 'Pago registrado exitosamente';
            if ($nuevoCredito > 0) {
                $mensaje .= '.<br>Saldo pendiente: Bs. ' . number_format($nuevoCredito, 2);
            } else {
                $mensaje .= '.<br>Deuda saldada completamente';
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

    public function crearCompra()
    {
        Log::info('=== CREAR COMPRA INICIADO ===', [
            'user_id' => auth()->id(),
            'tenant_id' => currentTenantId()
        ]);

        // Verificar si el usuario ya tiene una compra pendiente en este tenant
        $compraPendiente = Compra::where('user_id', auth()->id())
            ->where('tenant_id', currentTenantId())
            ->where('estado', 'Pendiente')
            ->first();

        if ($compraPendiente) {
            Log::info('Compra pendiente encontrada, redirigiendo', [
                'compra_id' => $compraPendiente->id
            ]);
            // Redirigir a la compra pendiente
            return redirect()->route('compra', ['compraId' => $compraPendiente->id]);
        }

        // Si no hay pendiente, crear una nueva
        $nuevaCompra = Compra::create([
            'tenant_id' => currentTenantId(),
            'user_id' => auth()->id(),
            'estado' => 'Pendiente',
            'efectivo' => 0,
            'credito' => 0,
        ]);

        Log::info('Nueva compra creada', [
            'compra_id' => $nuevaCompra->id,
            'numero_folio' => $nuevaCompra->numero_folio,
            'estado' => $nuevaCompra->estado,
            'user_id' => $nuevaCompra->user_id,
            'tenant_id' => $nuevaCompra->tenant_id
        ]);

        // Redirigir a la nueva compra
        return redirect()->route('compra', ['compraId' => $nuevaCompra->id]);
    }

    public function eliminar($compraId)
    {
        try {
            DB::beginTransaction();

            $compra = Compra::with(['compraItems.producto' => function ($query) {
                $query->withTrashed();
            }])->findOrFail($compraId);

            // Si la compra está PENDIENTE, eliminar físicamente
            if ($compra->estado === 'Pendiente') {
                $compra->delete();
                DB::commit();

                $this->toast('success', 'Compra pendiente eliminada exitosamente');
                return;
            }

            // Si la compra está COMPLETA, marcar como Eliminado y devolver productos/dinero
            if ($compra->estado !== 'Completo') {
                $this->toast('error', 'Solo se pueden eliminar compras pendientes o completas');
                return;
            }

            // 1. Verificar que hay suficiente stock en todos los productos
            $productosConStockInsuficiente = [];

            foreach ($compra->compraItems as $item) {
                $producto = $item->producto;
                if ($producto->stock < $item->cantidad) {
                    // Calcular stock formateado
                    $cantidadPorMedida = $producto->cantidad ?? 1;
                    $medidaAbrev = strtolower(substr($producto->medida ?? 'u', 0, 1));

                    if ($cantidadPorMedida <= 1) {
                        $stockFormateado = intval($producto->stock) . $medidaAbrev;
                    } else {
                        $cajas = floor($producto->stock / $cantidadPorMedida);
                        $unidades = $producto->stock % $cantidadPorMedida;
                        if ($cajas > 0 && $unidades > 0) {
                            $stockFormateado = "{$cajas}{$medidaAbrev} - {$unidades}u";
                        } elseif ($cajas > 0) {
                            $stockFormateado = "{$cajas}{$medidaAbrev}";
                        } else {
                            $stockFormateado = "{$unidades}u";
                        }
                    }

                    // Calcular faltante y formatearlo
                    $faltante = $item->cantidad - $producto->stock;
                    if ($cantidadPorMedida <= 1) {
                        $faltanteFormateado = intval($faltante) . $medidaAbrev;
                    } else {
                        $cajasFaltantes = floor($faltante / $cantidadPorMedida);
                        $unidadesFaltantes = $faltante % $cantidadPorMedida;
                        if ($cajasFaltantes > 0 && $unidadesFaltantes > 0) {
                            $faltanteFormateado = "{$cajasFaltantes}{$medidaAbrev} - {$unidadesFaltantes}u";
                        } elseif ($cajasFaltantes > 0) {
                            $faltanteFormateado = "{$cajasFaltantes}{$medidaAbrev}";
                        } else {
                            $faltanteFormateado = "{$unidadesFaltantes}u";
                        }
                    }

                    $productosConStockInsuficiente[] = [
                        'nombre' => $producto->nombre,
                        'stock_actual' => $producto->stock,
                        'stock_formateado' => $stockFormateado,
                        'cantidad_requerida' => $item->cantidad,
                        'cantidad_formateada' => $item->cantidad_formateada,
                        'faltante' => $faltante,
                        'faltante_formateado' => $faltanteFormateado,
                    ];
                }
            }

            // Si hay productos sin stock suficiente, mostrar modal de error
            if (!empty($productosConStockInsuficiente)) {
                $this->productosInsuficientes = [
                    'compra_id' => $compra->id,
                    'total_productos' => count($productosConStockInsuficiente),
                    'productos' => $productosConStockInsuficiente
                ];
                $this->mostrarErrorStock = true;
                return;
            }

            // Preparar resumen
            $productosAfectados = [];
            $totalDevuelto = $compra->efectivo;
            $compraId = $compra->id;
            $totalCompra = $compra->efectivo + $compra->credito;

            // 2. Descontar stock de cada producto y registrar en Kardex
            foreach ($compra->compraItems as $item) {
                $producto = $item->producto;

                // Descontar el stock
                $stockAnterior = $producto->stock;
                $producto->stock -= $item->cantidad;
                $producto->save();

                // Formatear stocks usando la misma lógica que cantidad_formateada
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

                // Registrar salida en Kardex (reversión de la compra)
                Kardex::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => auth()->id(),
                    'producto_id' => $producto->id,
                    'entrada' => 0,
                    'salida' => $item->cantidad,
                    'anterior' => $stockAnterior,
                    'saldo' => $producto->stock,
                    'precio' => $item->precio,
                    'total' => $item->subtotal,
                    'obs' => "Eliminación de compra #{$compra->numero_folio}"
                ]);
            }

            // 3. Devolver solo el efectivo a caja (el crédito no se pagó aún)
            if ($compra->efectivo > 0) {
                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => auth()->id(),
                    'detalle' => "Devolución por eliminación de compra #{$compra->numero_folio}",
                    'ingreso' => $compra->efectivo,
                    'egreso' => 0
                ]);
            }

            // 4. Marcar la compra como eliminada (no borrar físicamente)
            $compra->estado = 'Eliminado';
            $compra->save();

            DB::commit();

            // 5. Mostrar resumen
            $this->resumenEliminacion = [
                'compra_id' => $compraId,
                'total_compra' => $totalCompra,
                'efectivo' => $compra->efectivo,
                'credito' => $compra->credito,
                'devuelto_caja' => $totalDevuelto,
                'productos' => $productosAfectados,
                'total_productos' => count($productosAfectados)
            ];

            $this->mostrarResumenEliminacion = true;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->toast('error', 'Error al eliminar la compra:<br>' . $e->getMessage());
        }
    }

    public function generarPDF($compraId)
    {
        $compra = Compra::with(['proveedor', 'user', 'compraItems.producto' => function ($query) {
            $query->withTrashed();
        }])
            ->findOrFail($compraId);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.compra', compact('compra'));

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'compra-' . $compra->id . '.pdf');
    }

    public function render()
    {
        $compras = Compra::with(['proveedor', 'user', 'compraItems.producto' => function ($query) {
            $query->withTrashed();
        }])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('proveedor', function ($subQ) {
                        $subQ->where('nombre', 'like', '%' . $this->search . '%');
                    })
                        ->orWhere('estado', 'like', '%' . $this->search . '%')
                        ->orWhere('id', 'like', '%' . $this->search . '%')
                        ->orWhereHas('compraItems.producto', function ($subQ) {
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

        return view('livewire.compras', [
            'compras' => $compras
        ]);
    }
}
