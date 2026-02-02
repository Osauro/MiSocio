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

    // Para devoluciÃ³n parcial de envases
    public $mostrarModalDevolucion = false;
    public $prestamoADevolver = null;
    public $itemsDevolucion = [];
    public $montoDevolucionTotal = 0;

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

    public function abrirModalDevolucion($prestamoId)
    {
        $this->prestamoADevolver = Prestamo::with(['cliente', 'prestamoItems.producto' => function ($query) {
            $query->withTrashed();
        }])
            ->findOrFail($prestamoId);

        // Solo se puede devolver si estÃ¡ Completo
        if ($this->prestamoADevolver->estado !== 'Completo') {
            $this->toast('warning', 'Solo se pueden registrar devoluciones de prÃ©stamos completos');
            return;
        }

        // Preparar los items con sus cantidades pendientes
        $this->itemsDevolucion = [];
        foreach ($this->prestamoADevolver->prestamoItems as $item) {
            $cantidadPendiente = $item->cantidad - $item->cantidad_devuelta;

            if ($cantidadPendiente > 0) {
                $this->itemsDevolucion[] = [
                    'prestamo_item_id' => $item->id,
                    'producto_id' => $item->producto_id,
                    'producto_nombre' => $item->producto->nombre ?? 'Producto eliminado',
                    'cantidad_total' => $item->cantidad,
                    'cantidad_devuelta' => $item->cantidad_devuelta,
                    'cantidad_pendiente' => $cantidadPendiente,
                    'cantidad_a_devolver' => 0, // Lo ingresa el usuario
                    'precio_deposito' => $item->precio_deposito,
                    'medida' => $item->producto->medida ?? 'u',
                    'cantidad_por_medida' => $item->producto->cantidad ?? 1,
                ];
            }
        }

        if (empty($this->itemsDevolucion)) {
            $this->toast('info', 'Este prÃ©stamo ya estÃ¡ totalmente devuelto');
            return;
        }

        $this->mostrarModalDevolucion = true;
        $this->montoDevolucionTotal = 0;
    }

    public function cerrarModalDevolucion()
    {
        $this->mostrarModalDevolucion = false;
        $this->prestamoADevolver = null;
        $this->itemsDevolucion = [];
        $this->montoDevolucionTotal = 0;
    }

    public function updatedItemsDevolucion()
    {
        // Calcular el monto total a devolver basado en las cantidades
        $this->montoDevolucionTotal = 0;

        foreach ($this->itemsDevolucion as $item) {
            $cantidadADevolver = $item['cantidad_a_devolver'] ?? 0;

            // Validar que no exceda la cantidad pendiente
            if ($cantidadADevolver > $item['cantidad_pendiente']) {
                $cantidadADevolver = $item['cantidad_pendiente'];
            }

            $this->montoDevolucionTotal += $cantidadADevolver * $item['precio_deposito'];
        }
    }

    public function registrarDevolucion()
    {
        try {
            DB::beginTransaction();

            $prestamo = Prestamo::with(['prestamoItems.producto' => function ($query) {
                $query->withTrashed();
            }])->findOrFail($this->prestamoADevolver->id);

            $totalDevuelto = 0;
            $montoDevuelto = 0;
            $todosItemsCompletos = true;

            foreach ($this->itemsDevolucion as $itemData) {
                $cantidadADevolver = floatval($itemData['cantidad_a_devolver'] ?? 0);

                if ($cantidadADevolver <= 0) {
                    continue; // No devuelve nada de este item
                }

                // Buscar el PrestamoItem
                $prestamoItem = $prestamo->prestamoItems->firstWhere('id', $itemData['prestamo_item_id']);

                if (!$prestamoItem) {
                    continue;
                }

                $cantidadPendiente = $prestamoItem->cantidad - $prestamoItem->cantidad_devuelta;

                // Validar que no exceda la cantidad pendiente
                if ($cantidadADevolver > $cantidadPendiente) {
                    $cantidadADevolver = $cantidadPendiente;
                }

                // Actualizar cantidad devuelta
                $prestamoItem->cantidad_devuelta += $cantidadADevolver;
                $prestamoItem->save();

                // Actualizar stock del producto (devuelven envases)
                $producto = $prestamoItem->producto;
                if ($producto) {
                    $stockAnterior = $producto->stock;
                    $producto->stock += $cantidadADevolver;
                    $producto->save();

                    // Registrar en Kardex (ENTRADA - devuelven envases)
                    Kardex::create([
                        'tenant_id' => currentTenantId(),
                        'user_id' => auth()->id(),
                        'producto_id' => $producto->id,
                        'entrada' => $cantidadADevolver,
                        'salida' => 0,
                        'anterior' => $stockAnterior,
                        'saldo' => $producto->stock,
                        'precio' => $prestamoItem->precio_deposito,
                        'total' => $cantidadADevolver * $prestamoItem->precio_deposito,
                        'obs' => "DevoluciÃ³n de prÃ©stamo #{$prestamo->numero_folio}"
                    ]);
                }

                // Calcular monto a devolver
                $montoItem = $cantidadADevolver * $prestamoItem->precio_deposito;
                $montoDevuelto += $montoItem;
                $totalDevuelto += $cantidadADevolver;

                // Verificar si este item estÃ¡ completo
                if ($prestamoItem->cantidad_devuelta < $prestamoItem->cantidad) {
                    $todosItemsCompletos = false;
                }
            }

            if ($totalDevuelto <= 0) {
                DB::rollBack();
                $this->toast('warning', 'Debe ingresar al menos una cantidad a devolver');
                return;
            }

            // Registrar en Movimientos (EGRESO - devolvemos depÃ³sito)
            if ($montoDevuelto > 0) {
                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => auth()->id(),
                    'detalle' => "DevoluciÃ³n de depÃ³sito prÃ©stamo #{$prestamo->numero_folio} ({$totalDevuelto} unidades)",
                    'ingreso' => 0,
                    'egreso' => $montoDevuelto
                ]);
            }

            // Si todos los items estÃ¡n completamente devueltos, cambiar estado a Devuelto
            if ($todosItemsCompletos) {
                $prestamo->estado = 'Devuelto';
                $prestamo->fecha_devolucion = now();
                $prestamo->save();
            }

            DB::commit();

            $this->cerrarModalDevolucion();

            if ($todosItemsCompletos) {
                $this->toast('success', 'DevoluciÃ³n completa registrada. DepÃ³sito devuelto: $' . number_format($montoDevuelto, 2));
            } else {
                $this->toast('success', 'DevoluciÃ³n parcial registrada. DepÃ³sito devuelto: $' . number_format($montoDevuelto, 2));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->toast('error', 'Error al registrar la devoluciÃ³n:<br>' . $e->getMessage());
        }
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
        // Verificar si el usuario ya tiene un prÃ©stamo pendiente
        $prestamoPendiente = Prestamo::where('user_id', auth()->id())
            ->where('estado', 'Pendiente')
            ->first();

        if ($prestamoPendiente) {
            // Redirigir al prÃ©stamo pendiente
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

        // Redirigir al nuevo prÃ©stamo
        return redirect()->route('prestamo', ['prestamoId' => $nuevoPrestamo->id]);
    }

    public function eliminar($prestamoId)
    {
        try {
            DB::beginTransaction();

            $prestamo = Prestamo::with(['prestamoItems.producto' => function ($query) {
                $query->withTrashed();
            }])->findOrFail($prestamoId);

            // Si el prÃ©stamo estÃ¡ PENDIENTE, eliminar fÃ­sicamente
            if ($prestamo->estado === 'Pendiente') {
                $prestamo->delete();
                DB::commit();

                $this->toast('success', 'PrÃ©stamo pendiente eliminado exitosamente');
                return;
            }

            // Si el prÃ©stamo estÃ¡ COMPLETO, marcar como Eliminado y devolver productos/dinero
            if ($prestamo->estado !== 'Completo') {
                $this->toast('error', 'Solo se pueden eliminar prÃ©stamos pendientes o completos');
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

                // Registrar entrada en Kardex (reversiÃ³n del prÃ©stamo)
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
                    'obs' => "EliminaciÃ³n de prÃ©stamo #{$prestamo->numero_folio}"
                ]);
            }

            // Devolver el depÃ³sito a caja
            if ($prestamo->deposito > 0) {
                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => auth()->id(),
                    'detalle' => "DevoluciÃ³n de depÃ³sito por eliminaciÃ³n de prÃ©stamo #{$prestamo->numero_folio}",
                    'ingreso' => 0,
                    'egreso' => $prestamo->deposito
                ]);
            }

            // Marcar el prÃ©stamo como eliminado (no borrar fÃ­sicamente)
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

            $this->toast('error', 'Error al eliminar el prÃ©stamo:<br>' . $e->getMessage());
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
