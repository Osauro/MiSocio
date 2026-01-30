<?php

namespace App\Livewire;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Movimiento;
use App\Models\Kardex;
use App\Traits\RequiresTenant;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Compras extends Component
{
    use WithPagination, RequiresTenant;

    public $search = '';
    public $perPage = 12;
    public $compraSeleccionada = null;
    public $mostrarModal = false;
    public $mostrarResumenEliminacion = false;
    public $resumenEliminacion = [];
    public $mostrarErrorStock = false;
    public $productosInsuficientes = [];

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

    public function crearCompra()
    {
        // Verificar si el usuario ya tiene una compra pendiente
        $compraPendiente = Compra::where('user_id', auth()->id())
            ->where('estado', 'Pendiente')
            ->first();

        if ($compraPendiente) {
            // Redirigir a la compra pendiente
            return redirect()->route('tenant.compra', ['compraId' => $compraPendiente->id]);
        }

        // Si no hay pendiente, crear una nueva
        $nuevaCompra = Compra::create([
            'tenant_id' => currentTenantId(),
            'user_id' => auth()->id(),
            'estado' => 'Pendiente',
            'efectivo' => 0,
            'credito' => 0,
        ]);

        // Redirigir a la nueva compra
        return redirect()->route('tenant.compra', ['compraId' => $nuevaCompra->id]);
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

                $this->dispatch('alert', [
                    'type' => 'success',
                    'message' => 'Compra pendiente eliminada exitosamente'
                ]);
                return;
            }

            // Si la compra está COMPLETA, marcar como Eliminado y devolver productos/dinero
            if ($compra->estado !== 'Completo') {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Solo se pueden eliminar compras pendientes o completas'
                ]);
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

            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Error al eliminar la compra: ' . $e->getMessage()
            ]);
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
                                ->orWhere('codigo', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.compras', [
            'compras' => $compras
        ]);
    }
}
