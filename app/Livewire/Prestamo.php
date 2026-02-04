<?php

namespace App\Livewire;

use App\Models\Prestamo as PrestamoModel;
use App\Models\PrestamoItem;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Kardex;
use App\Models\Movimiento;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Prestamo extends Component
{
    use RequiresTenant, SweetAlertTrait;

    public $prestamoId;
    public $prestamo;
    public $buscar = '';
    public $productosEncontrados = [];
    public $items = [];
    public $mostrarBuscador = true;

    // Variables para el flujo de pago
    public $pasoActual = 0; // 0: no iniciado, 1: fecha, 2: cliente, 3: pago
    public $fechaPrestamo;
    public $buscarCliente = '';
    public $clientesEncontrados = [];
    public $clienteSeleccionado = null;
    public $mostrarFormNuevoCliente = false;
    public $nuevoCliente = [
        'nombre' => '',
        'celular' => '',
        'direccion' => '',
        'nit' => '',
    ];
    public $montoPagoEfectivo = 0;
    public $montoPagoOnline = 0;
    public $saldoCaja = 0;
    public $procesandoPago = false;

    public function mount($prestamoId = null)
    {
        if (!$prestamoId) {
            // Si no viene ID, redirigir a prestamos
            return redirect()->route('prestamos');
        }

        // Cargar el préstamo
        $this->prestamo = PrestamoModel::findOrFail($prestamoId);

        // Verificar que sea del usuario actual y esté pendiente
        if ($this->prestamo->user_id !== Auth::id() || $this->prestamo->estado !== 'Pendiente') {
            return redirect()->route('prestamos');
        }

        $this->prestamoId = $this->prestamo->id;
        $this->fechaPrestamo = now()->format('Y-m-d');
        $this->cargarItems();
    }

    public function cargarItems()
    {
        $prestamoItems = PrestamoItem::where('prestamo_id', $this->prestamoId)
            ->with('producto')
            ->get();

        $this->items = $prestamoItems->map(function ($item) {
            // En préstamos siempre usamos solo unidades (sin enteros/cajas)
            return [
                'id' => $item->id,
                'producto_id' => $item->producto_id,
                'nombre' => $item->producto->nombre ?? 'Producto',
                'imagen' => $item->producto->photo_url ?? null,
                'medida' => $item->producto->medida ?? 'u',
                'cantidad_por_medida' => 1,
                'enteros' => 0,
                'unidades' => $item->cantidad,
                'precio' => $item->precio,
                'subtotal' => $item->subtotal,
            ];
        })->toArray();
    }

    /**
     * Redondeo personalizado para subtotales:
     * - Decimales < 0.50 se redondean a .50
     * - Decimales >= 0.50 se redondean al siguiente entero
     * Solo permite valores con .00 o .50
     */
    private function redondearSubtotal($valor)
    {
        $entero = floor($valor);
        $decimal = $valor - $entero;

        if ($decimal == 0) {
            return $valor; // Ya es .00
        } elseif ($decimal <= 0.50) {
            return $entero + 0.50; // Redondear a .50
        } else {
            return $entero + 1.00; // Redondear al siguiente entero
        }
    }

    /**
     * Calcular stock comprometido en otras ventas pendientes
     */
    private function calcularStockComprometido($productoId)
    {
        // Sumar cantidades del producto en todos los préstamos pendientes excepto el actual
        return PrestamoItem::whereHas('prestamo', function($query) {
            $query->where('estado', 'Pendiente')
                  ->where('id', '!=', $this->prestamoId);
        })
        ->where('producto_id', $productoId)
        ->sum('cantidad');
    }

    /**
     * Formatear cantidad a formato amigable (cajas y unidades)
     */
    private function formatearCantidad($cantidad, $producto)
    {
        if ($cantidad == 0) {
            return '0';
        }

        $cantidadPorMedida = $producto->cantidad ?? 1;
        $medida = $producto->medida ?? 'u';

        if ($cantidadPorMedida <= 1) {
            return intval($cantidad) . strtolower(substr($medida, 0, 1));
        }

        $cajas = floor($cantidad / $cantidadPorMedida);
        $unidades = $cantidad % $cantidadPorMedida;
        $medidaAbrev = strtolower(substr($medida, 0, 1));

        if ($cajas > 0 && $unidades > 0) {
            return "{$cajas}{$medidaAbrev} - {$unidades}u";
        } elseif ($cajas > 0) {
            return "{$cajas}{$medidaAbrev}";
        } else {
            return "{$unidades}u";
        }
    }

    public function updatedBuscar()
    {
        // Solo buscar si hay al menos 2 caracteres
        if (strlen($this->buscar) < 2) {
            $this->productosEncontrados = [];
            return;
        }

        $query = Producto::where('tenant_id', currentTenantId())
            ->whereHas('categoria', function ($q) {
                $q->where('nombre', 'like', '%Envase%');
            })
            ->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->buscar . '%')
                    ->orWhere('codigo', 'like', '%' . $this->buscar . '%')
                    ->orWhereHas('tags', function ($subQuery) {
                        $subQuery->where('nombre', 'like', '%' . $this->buscar . '%');
                    });
            });

        $this->productosEncontrados = $query->limit(20)
            ->get()
            ->map(function ($producto) {
                $cantidadPorMedida = $producto->cantidad ?? 1;
                $tieneControl = $producto->control ?? true;

                // Si el producto NO tiene control de stock, stock siempre es 999999
                if (!$tieneControl) {
                    $stockDisponible = 999999;
                    $stockFormateado = 'Sin control';
                } else {
                    // Calcular stock real disponible (stock - comprometido)
                    $stockComprometido = $this->calcularStockComprometido($producto->id);
                    $stockDisponible = $producto->stock - $stockComprometido;

                    // Formatear stock disponible
                    if ($cantidadPorMedida > 1) {
                        $enteros = intdiv($stockDisponible, $cantidadPorMedida);
                        $unidades = $stockDisponible % $cantidadPorMedida;
                        $medidaAbrev = strtolower(substr($producto->medida ?? 'u', 0, 1));
                        $stockFormateado = $enteros . $medidaAbrev . ($unidades > 0 ? ' - ' . $unidades . 'u' : '');
                    } else {
                        $stockFormateado = $stockDisponible . 'u';
                    }
                }

                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'codigo' => $producto->codigo,
                    'imagen' => $producto->photo_url,
                    'stock' => $stockDisponible,
                    'stock_formateado' => $stockFormateado,
                    'precio_por_menor' => $producto->precio_por_menor,
                    'precio_por_mayor' => $producto->precio_por_mayor,
                    'medida' => $producto->medida ?? 'u',
                    'cantidad' => $cantidadPorMedida,
                    'control' => $tieneControl,
                ];
            })
            ->toArray();
    }

    public function agregarProducto($productoId)
    {
        $producto = Producto::findOrFail($productoId);

        // Verificar si ya existe en los items
        $existe = collect($this->items)->firstWhere('producto_id', $productoId);

        if ($existe) {
            $this->toast('warning', 'El producto ya está en el carrito');
            return;
        }

        // Precio de venta del producto (por menor por defecto)
        $precioVenta = $producto->precio_por_menor ?? 0;

        // Validar stock disponible solo si el producto tiene control de stock
        if ($producto->control) {
            $stockComprometido = $this->calcularStockComprometido($productoId);
            $stockDisponible = $producto->stock - $stockComprometido;

            if (1 > $stockDisponible) {
                $stockDisponibleFormateado = $this->formatearCantidad($stockDisponible, $producto);
                $stockComprometidoFormateado = $this->formatearCantidad($stockComprometido, $producto);
                $this->toast('error', 'Stock insuficiente. Disponible: ' . $stockDisponibleFormateado . ' (Comprometido: ' . $stockComprometidoFormateado . ')');
                return;
            }
        }

        // Cantidad inicial: 1 unidad
        $cantidadInicial = 1;
        $subtotalInicial = $this->redondearSubtotal($precioVenta);

        // Recalcular precio basado en el subtotal redondeado
        $precioVenta = $subtotalInicial;

        // Crear el item en la base de datos
        $prestamoItem = PrestamoItem::create([
            'prestamo_id' => $this->prestamoId,
            'producto_id' => $productoId,
            'cantidad' => $cantidadInicial,
            'precio' => $precioVenta,
            'subtotal' => $subtotalInicial,
        ]);

        // Agregar al array de items
        $this->items[] = [
            'id' => $prestamoItem->id,
            'producto_id' => $productoId,
            'nombre' => $producto->nombre,
            'imagen' => $producto->photo_url,
            'medida' => $producto->medida ?? 'u',
            'cantidad_por_medida' => 1,
            'enteros' => 0,
            'unidades' => 1,
            'precio' => $precioVenta,
            'subtotal' => $subtotalInicial,
        ];

        $this->buscar = '';
        $this->productosEncontrados = [];

        // Devolver el foco al buscador
        $this->dispatch('focusBuscador');

        // Emitir evento al navegador para actualizar badge
        $this->dispatch('actualizar-badge-prestamo');
    }

    public function actualizarItem($index)
    {
        $item = $this->items[$index];

        // Validar que unidades no sean negativas
        $this->items[$index]['unidades'] = max(0, intval($item['unidades']));
        $cantidadTotal = $this->items[$index]['unidades'];

        // Validar stock disponible solo si el producto tiene control de stock
        $producto = Producto::find($item['producto_id']);

        if ($producto->control) {
            // Calcular stock comprometido en otros préstamos pendientes
            $stockComprometido = $this->calcularStockComprometido($item['producto_id']);
            $stockDisponible = $producto->stock - $stockComprometido;

            if ($cantidadTotal > $stockDisponible) {
                $stockDisponibleFormateado = $this->formatearCantidad($stockDisponible, $producto);
                $stockComprometidoFormateado = $this->formatearCantidad($stockComprometido, $producto);
                $this->toast('error', 'Stock insuficiente. Disponible: ' . $stockDisponibleFormateado . ' (Comprometido: ' . $stockComprometidoFormateado . ')');
                // Ajustar a stock disponible
                $this->items[$index]['unidades'] = $stockDisponible;
                $cantidadTotal = $stockDisponible;
            }
        }

        // Calcular subtotal: siempre usar precio por menor (precio unitario)
        $subtotalCalculado = 0;

        if ($cantidadTotal > 0) {
            // Usar precio_por_menor (precio unitario)
            $subtotalCalculado = $cantidadTotal * $producto->precio_por_menor;
            $this->items[$index]['precio'] = $producto->precio_por_menor;
        }

        $this->items[$index]['subtotal'] = $this->redondearSubtotal($subtotalCalculado);

        // Actualizar en base de datos
        PrestamoItem::find($item['id'])->update([
            'cantidad' => $cantidadTotal,
            'precio' => $this->items[$index]['precio'],
            'subtotal' => $this->items[$index]['subtotal'],
        ]);

        // Recargar items para mantener sincronización
        $this->cargarItems();
        $this->actualizarTotales();

        // Devolver el foco al buscador
        $this->dispatch('focusBuscador');
    }

    public function actualizarSubtotal($index)
    {
        $item = $this->items[$index];

        // Obtener producto de la base de datos
        $producto = Producto::find($item['producto_id']);

        // Cantidad total = solo unidades
        $cantidadTotal = $item['unidades'];

        if ($cantidadTotal > 0) {
            // Aplicar redondeo al subtotal modificado manualmente
            $this->items[$index]['subtotal'] = $this->redondearSubtotal($item['subtotal']);

            // Siempre usar precio_por_menor (precio unitario)
            $this->items[$index]['precio'] = $producto->precio_por_menor;
        } else {
            $this->items[$index]['subtotal'] = 0;
        }

                PrestamoItem::find($item['id'])->update([
            'precio' => $this->items[$index]['precio'],
            'subtotal' => $this->items[$index]['subtotal'],
        ]);

        // Recargar items para mantener sincronización
        $this->cargarItems();
        $this->actualizarTotales();

        // Devolver el foco al buscador
        $this->dispatch('focusBuscador');
    }

    public function actualizarTotales()
    {
        // Los totales se calculan reactivamente con el método #[Computed]
    }

    public function confirmEliminarItem($index)
    {
        $item = $this->items[$index];
        $this->confirmDelete(
            $item['id'],
            '¿Eliminar producto?',
            '¿Estás seguro de eliminar ' . $item['nombre'] . ' del carrito?',
            'eliminarItem'
        );
    }

    #[On('eliminarItem')]
    public function eliminarItem($id)
    {
        $item = PrestamoItem::find($id);
        if ($item) {
            $item->delete();
            $this->cargarItems();
            $this->actualizarTotales();
            $this->toast('success', 'Producto eliminado del carrito');

            // Emitir evento al navegador para actualizar badge
            $this->dispatch('actualizar-badge-prestamo');

            // Devolver el foco al buscador
            $this->dispatch('focusBuscador');
        }
    }

    // ==================== BOTONES Y FLUJO DE PAGO ====================

    public function cancelarVenta()
    {
        $this->confirmDelete(
            $this->ventaId,
            '¿Cancelar venta?',
            '¿Estás seguro de cancelar esta venta? Se eliminarán todos los productos del carrito.',
            'ejecutarCancelarVenta'
        );
    }

    #[On('ejecutarCancelarVenta')]
    public function ejecutarCancelarVenta($id)
    {
        try {
            $prestamo = PrestamoModel::findOrFail($id);

            // Eliminar items
            PrestamoItem::where('venta_id', $id)->delete();

            // Eliminar venta
            $prestamo->delete();

            $this->toast('success', 'Venta cancelada exitosamente');

            // Redirigir a la lista de ventas
            return redirect()->route('prestamos');
        } catch (\Exception $e) {
            Log::error('Error al cancelar venta: ' . $e->getMessage());
            $this->toast('error', 'Error al cancelar la venta');
        }
    }

    public function iniciarCompletarVenta()
    {
        // Verificar que haya items
        if (count($this->items) === 0) {
            $this->toast('warning', 'Debe agregar al menos un producto');
            return;
        }

        // Verificar que todos los items tengan cantidad > 0
        $sinCantidad = collect($this->items)->filter(function ($item) {
            return ($item['enteros'] * $item['cantidad_por_medida'] + $item['unidades']) === 0;
        });

        if ($sinCantidad->count() > 0) {
            $this->toast('warning', 'Todos los productos deben tener cantidad mayor a 0');
            return;
        }

        $this->pasoActual = 1;
    }

    public function avanzarPaso1()
    {
        $this->pasoActual = 2;
    }

    public function updatedBuscarCliente()
    {
        if (strlen($this->buscarCliente) == 0) {
            $this->clientesEncontrados = [];
            $this->mostrarFormNuevoCliente = false;
            return;
        }

        // Si es un número de 8 dígitos, buscar por celular
        if (is_numeric($this->buscarCliente) && strlen($this->buscarCliente) == 8) {
            $this->clientesEncontrados = Cliente::where('celular', $this->buscarCliente)->get()->toArray();

            if (count($this->clientesEncontrados) == 0) {
                $this->mostrarFormNuevoCliente = true;
                $this->nuevoCliente['celular'] = $this->buscarCliente;
            } else {
                $this->mostrarFormNuevoCliente = false;

                // Si hay exactamente 1 resultado, seleccionarlo automáticamente
                if (count($this->clientesEncontrados) === 1) {
                    $this->seleccionarCliente($this->clientesEncontrados[0]['id']);
                }
            }
        } else {
            // Búsqueda por nombre
            $this->clientesEncontrados = Cliente::where('nombre', 'like', '%' . $this->buscarCliente . '%')
                ->limit(10)
                ->get()
                ->toArray();

            $this->mostrarFormNuevoCliente = false;

            // Si hay exactamente 1 resultado, seleccionarlo automáticamente
            if (count($this->clientesEncontrados) === 1) {
                $this->seleccionarCliente($this->clientesEncontrados[0]['id']);
            }
        }
    }

    public function seleccionarCliente($clienteId)
    {
        $this->clienteSeleccionado = $clienteId;

        // Obtener saldo de caja
        $this->obtenerSaldoCaja();

        // Avanzar a paso 3: pago
        $this->pasoActual = 3;
        $total = round(collect($this->items)->sum('subtotal'), 2);
        $this->montoPagoEfectivo = $total; // Por defecto el monto total en efectivo
        $this->montoPagoOnline = 0;
    }

    public function mostrarFormAgregarCliente()
    {
        $this->mostrarFormNuevoCliente = true;
        $this->clientesEncontrados = [];
    }

    public function crearYSeleccionarCliente()
    {
        $this->validate([
            'nuevoCliente.nombre' => 'required|string|max:255',
            'nuevoCliente.celular' => 'nullable|string|max:20',
            'nuevoCliente.direccion' => 'nullable|string|max:255',
            'nuevoCliente.nit' => 'nullable|string|max:50',
        ]);

        try {
            $cliente = Cliente::create([
                'tenant_id' => currentTenantId(),
                'nombre' => $this->nuevoCliente['nombre'],
                'celular' => $this->nuevoCliente['celular'],
                'direccion' => $this->nuevoCliente['direccion'],
                'nit' => $this->nuevoCliente['nit'],
            ]);

            $this->toast('success', 'Cliente creado exitosamente');
            $this->seleccionarCliente($cliente->id);
        } catch (\Exception $e) {
            Log::error('Error al crear cliente: ' . $e->getMessage());
            $this->toast('error', 'Error al crear cliente');
        }
    }

    public function avanzarPaso2SinCliente()
    {
        // Obtener saldo de caja
        $this->obtenerSaldoCaja();

        // Avanzar a paso 3: pago
        $this->pasoActual = 3;
        $total = round(collect($this->items)->sum('subtotal'), 2);
        $this->montoPagoEfectivo = $total; // Por defecto el monto total en efectivo
        $this->montoPagoOnline = 0;
    }

    public function obtenerSaldoCaja()
    {
        $ultimoMovimiento = Movimiento::latest()->first();
        $this->saldoCaja = $ultimoMovimiento ? $ultimoMovimiento->saldo : 0;
    }

    public function updatedMontoPagoEfectivo()
    {
        // Redondear a 2 decimales
        $this->montoPagoEfectivo = round(floatval($this->montoPagoEfectivo), 2);
    }

    public function updatedMontoPagoOnline()
    {
        // Redondear a 2 decimales
        $this->montoPagoOnline = round(floatval($this->montoPagoOnline), 2);

        // Recalcular efectivo automáticamente
        $total = round(collect($this->items)->sum('subtotal'), 2);
        $efectivoRestante = $total - $this->montoPagoOnline;
        $this->montoPagoEfectivo = max(0, round($efectivoRestante, 2));
    }

    public function procesarDepósito()
    {
        $total = round(collect($this->items)->sum('subtotal'), 2);
        $totalPagado = round($this->montoPagoEfectivo + $this->montoPagoOnline, 2);

        // Validar que el pago cubra el total
        if ($totalPagado < $total) {
            $this->toast('error', 'El monto pagado debe cubrir el total. Falta: Bs. ' . number_format($total - $totalPagado, 2));
            return;
        }

        // Verificar fondos en caja si hay pago en efectivo
        if ($this->montoPagoEfectivo > 0) {
            $this->obtenerSaldoCaja();

            if ($this->saldoCaja < $this->montoPagoEfectivo) {
                $this->toast('error', 'Fondos insuficientes en caja.<br>Saldo: Bs. ' . number_format($this->saldoCaja, 2));
                return;
            }
        }

        // Mostrar spinner de procesando
        $this->procesandoPago = true;

        // Procesar el pago
        $this->finalizarPrestamo();
    }

    public function retrocederPaso()
    {
        if ($this->pasoActual > 0) {
            $this->pasoActual--;

            // Limpiar datos según el paso
            if ($this->pasoActual === 1) {
                $this->clienteSeleccionado = null;
                $this->mostrarFormNuevoCliente = false;
            } elseif ($this->pasoActual === 2) {
                // Limpiar datos de pago
                $this->montoPagoEfectivo = 0;
                $this->montoPagoOnline = 0;
                $this->procesandoPago = false;
            }
        }
    }

    public function finalizarPrestamo()
    {
        try {
            DB::beginTransaction();

            $totalDeposito = collect($this->items)->sum('subtotal');

            // Obtener el nombre del cliente si existe
            $nombreCliente = null;
            if ($this->clienteSeleccionado) {
                $cliente = Cliente::find($this->clienteSeleccionado);
                $nombreCliente = $cliente ? $cliente->nombre : null;
            }

            // El depósito es el total calculado
            $deposito = round($totalDeposito, 2);

            // Calcular fecha de vencimiento (+7 días desde fecha de préstamo)
            $fechaVencimiento = \Carbon\Carbon::parse($this->fechaPrestamo)->addDays(7)->format('Y-m-d');

            // Actualizar el préstamo
            $this->prestamo->update([
                'cliente_id' => $this->clienteSeleccionado,
                'estado' => 'Prestado',
                'deposito' => $deposito,
                'fecha_prestamo' => $this->fechaPrestamo,
                'fecha_vencimiento' => $fechaVencimiento,
            ]);

            // Actualizar productos en Kardex y reducir stock
            foreach ($this->items as $item) {
                $producto = Producto::lockForUpdate()->find($item['producto_id']);
                $cantidadTotal = ($item['enteros'] * $item['cantidad_por_medida']) + $item['unidades'];

                if ($producto->control) {
                    // PRODUCTOS CON CONTROL: Reducir stock normalmente
                    $stockAnterior = $producto->stock;
                    $producto->stock -= $cantidadTotal;
                    $producto->save();

                    // Registrar SALIDA en Kardex
                    if ($cantidadTotal > 0) {
                        Kardex::create([
                            'tenant_id' => currentTenantId(),
                            'user_id' => Auth::id(),
                            'producto_id' => $producto->id,
                            'entrada' => 0,
                            'salida' => $cantidadTotal,
                            'anterior' => $stockAnterior,
                            'saldo' => $producto->stock,
                            'precio' => $item['precio'],
                            'total' => $item['subtotal'],
                            'obs' => 'Préstamo #' . $this->prestamo->numero_folio . ($nombreCliente ? ' - ' . $nombreCliente : ''),
                        ]);
                    }
                } else {
                    // PRODUCTOS SIN CONTROL: No modificar stock, pero registrar movimiento
                    if ($cantidadTotal > 0) {
                        Kardex::create([
                            'tenant_id' => currentTenantId(),
                            'user_id' => Auth::id(),
                            'producto_id' => $producto->id,
                            'entrada' => 0,
                            'salida' => $cantidadTotal, // Registrar salida normalmente
                            'anterior' => 0,
                            'saldo' => 0,
                            'precio' => $item['precio'],
                            'total' => $item['subtotal'],
                            'obs' => 'Préstamo #' . $this->prestamo->numero_folio . ($nombreCliente ? ' - ' . $nombreCliente : ''),
                        ]);
                    }
                }
            }

            // Registrar INGRESO del depósito en Movimientos
            if ($deposito > 0) {
                $detalle = 'Depósito préstamo #' . $this->prestamo->numero_folio;

                if ($nombreCliente) {
                    $detalle .= ' - ' . $nombreCliente;
                }

                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => Auth::id(),
                    'detalle' => $detalle,
                    'ingreso' => $deposito,
                    'egreso' => 0,
                ]);
            }

            DB::commit();

            $this->toast('success', 'Préstamo completado exitosamente');

            // Redirigir a la lista de préstamos
            return redirect()->route('prestamos');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al finalizar préstamo: ' . $e->getMessage());
            $this->toast('error', 'Error al finalizar el préstamo');
            $this->procesandoPago = false;
        }
    }

    public function cancelarPagoEnProceso()
    {
        $this->pasoActual = 0;
        $this->clienteSeleccionado = null;
        $this->mostrarFormNuevoCliente = false;
        $this->montoAñadirCaja = 0;
        $this->montoPagoEfectivo = 0;
        $this->montoPagoOnline = 0;
    }

    #[Computed]
    public function total()
    {
        return collect($this->items)->sum(function ($item) {
            return floatval($item['subtotal'] ?? 0);
        });
    }

    public function render()
    {
        return view('livewire.prestamo')->layout('layouts.tenant.theme');
    }
}


