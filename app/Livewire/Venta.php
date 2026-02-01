<?php

namespace App\Livewire;

use App\Models\Venta as VentaModel;
use App\Models\VentaItem;
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

class Venta extends Component
{
    use RequiresTenant, SweetAlertTrait;

    public $ventaId;
    public $venta;
    public $buscar = '';
    public $productosEncontrados = [];
    public $items = [];
    public $mostrarBuscador = true;

    // Variables para el flujo de pago
    public $pasoActual = 0; // 0: no iniciado, 1: fecha, 2: cliente, 3: pago
    public $fechaVenta;
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

    public function mount($ventaId = null)
    {
        if (!$ventaId) {
            // Si no viene ID, redirigir a ventas
            return redirect()->route('ventas');
        }

        // Cargar la venta
        $this->venta = VentaModel::findOrFail($ventaId);

        // Verificar que sea del usuario actual y esté pendiente
        if ($this->venta->user_id !== Auth::id() || $this->venta->estado !== 'Pendiente') {
            return redirect()->route('ventas');
        }

        $this->ventaId = $this->venta->id;
        $this->fechaVenta = now()->format('Y-m-d');
        $this->cargarItems();
    }

    public function cargarItems()
    {
        $ventaItems = VentaItem::where('venta_id', $this->ventaId)
            ->with('producto')
            ->get();

        $this->items = $ventaItems->map(function ($item) {
            $cantidadPorMedida = $item->producto->cantidad ?? 1;
            $enteros = intdiv($item->cantidad, $cantidadPorMedida);
            $unidades = $item->cantidad % $cantidadPorMedida;

            return [
                'id' => $item->id,
                'producto_id' => $item->producto_id,
                'nombre' => $item->producto->nombre ?? 'Producto',
                'imagen' => $item->producto->photo_url ?? null,
                'medida' => $item->producto->medida ?? 'u',
                'cantidad_por_medida' => $cantidadPorMedida,
                'enteros' => $enteros,
                'unidades' => $unidades,
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
        // Sumar cantidades del producto en todas las ventas pendientes excepto la actual
        return VentaItem::whereHas('venta', function($query) {
            $query->where('estado', 'Pendiente')
                  ->where('id', '!=', $this->ventaId);
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
        if (strlen($this->buscar) >= 2) {
            $this->productosEncontrados = Producto::where('nombre', 'like', '%' . $this->buscar . '%')
                ->orWhere('codigo', 'like', '%' . $this->buscar . '%')
                ->orWhereHas('tags', function ($query) {
                    $query->where('nombre', 'like', '%' . $this->buscar . '%');
                })
                ->limit(10)
                ->get()
                ->map(function ($producto) {
                    $cantidadPorMedida = $producto->cantidad ?? 1;

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
                    ];
                })
                ->toArray();
        } else {
            $this->productosEncontrados = [];
        }
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

        // Precio de venta del producto (por mayor por defecto)
        $precioVenta = $producto->precio_por_mayor ?? 0;
        $cantidadPorMedida = $producto->cantidad ?? 1;

        // Validar stock disponible considerando stock comprometido
        $stockComprometido = $this->calcularStockComprometido($productoId);
        $stockDisponible = $producto->stock - $stockComprometido;

        if ($cantidadPorMedida > $stockDisponible) {
            $stockDisponibleFormateado = $this->formatearCantidad($stockDisponible, $producto);
            $stockComprometidoFormateado = $this->formatearCantidad($stockComprometido, $producto);
            $this->toast('error', 'Stock insuficiente. Disponible: ' . $stockDisponibleFormateado . ' (Comprometido: ' . $stockComprometidoFormateado . ')');
            return;
        }

        // Cantidad inicial: 1 entero (1 caja/paquete)
        // El subtotal inicial es el precio_por_mayor (precio de 1 caja)
        $cantidadInicial = $cantidadPorMedida;
        $subtotalInicial = $this->redondearSubtotal($precioVenta);

        // Recalcular precio basado en el subtotal redondeado
        $precioVenta = $subtotalInicial;

        // Crear el item en la base de datos
        $ventaItem = VentaItem::create([
            'venta_id' => $this->ventaId,
            'producto_id' => $productoId,
            'cantidad' => $cantidadInicial,
            'precio' => $precioVenta,
            'subtotal' => $subtotalInicial,
        ]);

        // Agregar al array de items
        $this->items[] = [
            'id' => $ventaItem->id,
            'producto_id' => $productoId,
            'nombre' => $producto->nombre,
            'imagen' => $producto->photo_url,
            'medida' => $producto->medida ?? 'u',
            'cantidad_por_medida' => $cantidadPorMedida,
            'enteros' => 1,
            'unidades' => 0,
            'precio' => $precioVenta,
            'subtotal' => $subtotalInicial,
        ];

        $this->buscar = '';
        $this->productosEncontrados = [];

        // Devolver el foco al buscador
        $this->dispatch('focusBuscador');

        // Emitir evento al navegador para actualizar badge
        $this->dispatch('actualizar-badge-venta');
    }

    public function actualizarItem($index)
    {
        $item = $this->items[$index];

        // Validar que enteros y unidades no sean negativos
        $this->items[$index]['enteros'] = max(0, intval($item['enteros']));
        $this->items[$index]['unidades'] = max(0, intval($item['unidades']));

        // Convertir automáticamente unidades a enteros si es necesario
        if ($this->items[$index]['unidades'] >= $item['cantidad_por_medida']) {
            $enterosAdicionales = intdiv($this->items[$index]['unidades'], $item['cantidad_por_medida']);
            $this->items[$index]['enteros'] += $enterosAdicionales;
            $this->items[$index]['unidades'] = $this->items[$index]['unidades'] % $item['cantidad_por_medida'];
        }

        // Validar stock disponible
        $producto = Producto::find($item['producto_id']);

        // Calcular stock comprometido en otras ventas pendientes
        $stockComprometido = $this->calcularStockComprometido($item['producto_id']);
        $stockDisponible = $producto->stock - $stockComprometido;

        // cantidad = (enteros * producto.cantidad) + unidades
        $cantidadTotal = ($this->items[$index]['enteros'] * $item['cantidad_por_medida']) + $this->items[$index]['unidades'];

        if ($cantidadTotal > $stockDisponible) {
            $stockDisponibleFormateado = $this->formatearCantidad($stockDisponible, $producto);
            $stockComprometidoFormateado = $this->formatearCantidad($stockComprometido, $producto);
            $this->toast('error', 'Stock insuficiente. Disponible: ' . $stockDisponibleFormateado . ' (Comprometido: ' . $stockComprometidoFormateado . ')');
            // Ajustar a stock disponible
            $this->items[$index]['enteros'] = intdiv($stockDisponible, $item['cantidad_por_medida']);
            $this->items[$index]['unidades'] = $stockDisponible % $item['cantidad_por_medida'];
            $cantidadTotal = $stockDisponible;
        }

        // Determinar qué precio usar según si hay enteros o solo unidades
        $enteros = $this->items[$index]['enteros'];
        $unidades = $this->items[$index]['unidades'];

        // Calcular subtotal basado en enteros y unidades
        $subtotalCalculado = 0;

        if ($enteros > 0) {
            // Hay enteros (cajas/paquetes), usar precio_por_mayor
            $subtotalCalculado = $enteros * $producto->precio_por_mayor;
            $this->items[$index]['precio'] = $producto->precio_por_mayor;

            // Si también hay unidades sueltas, agregarlas al precio por menor
            if ($unidades > 0) {
                $subtotalCalculado += $unidades * $producto->precio_por_menor;
            }
        } else if ($unidades > 0) {
            // Solo hay unidades sueltas, usar precio_por_menor
            $subtotalCalculado = $unidades * $producto->precio_por_menor;
            $this->items[$index]['precio'] = $producto->precio_por_menor;
        }

        $this->items[$index]['subtotal'] = $this->redondearSubtotal($subtotalCalculado);

        // Actualizar en base de datos
        VentaItem::find($item['id'])->update([
            'cantidad' => $cantidadTotal,
            'precio' => $this->items[$index]['precio'],
            'subtotal' => $this->items[$index]['subtotal'],
        ]);

        $this->actualizarTotales();

        // Devolver el foco al buscador
        $this->dispatch('focusBuscador');
    }

    public function actualizarSubtotal($index)
    {
        $item = $this->items[$index];

        // Obtener producto de la base de datos
        $producto = Producto::find($item['producto_id']);

        // cantidad = (enteros * producto.cantidad) + unidades
        $enteros = $item['enteros'];
        $unidades = $item['unidades'];
        $cantidadTotal = ($enteros * $item['cantidad_por_medida']) + $unidades;

        if ($cantidadTotal > 0) {
            // Aplicar redondeo al subtotal modificado manualmente
            $this->items[$index]['subtotal'] = $this->redondearSubtotal($item['subtotal']);

            // Recalcular precio basado en si hay enteros o solo unidades
            if ($enteros > 0) {
                // Usar precio_por_mayor
                $this->items[$index]['precio'] = $producto->precio_por_mayor;
            } else if ($unidades > 0) {
                // Usar precio_por_menor
                $this->items[$index]['precio'] = $producto->precio_por_menor;
            }
        } else {
            $this->items[$index]['subtotal'] = 0;
        }

        VentaItem::find($item['id'])->update([
            'precio' => $this->items[$index]['precio'],
            'subtotal' => $this->items[$index]['subtotal'],
        ]);

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
        $item = VentaItem::find($id);
        if ($item) {
            $item->delete();
            $this->cargarItems();
            $this->actualizarTotales();
            $this->toast('success', 'Producto eliminado del carrito');

            // Emitir evento al navegador para actualizar badge
            $this->dispatch('actualizar-badge-venta');

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
            $venta = VentaModel::findOrFail($id);

            // Eliminar items
            VentaItem::where('venta_id', $id)->delete();

            // Eliminar venta
            $venta->delete();

            $this->toast('success', 'Venta cancelada exitosamente');

            // Redirigir a la lista de ventas
            return redirect()->route('ventas');
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
        // Continuar sin cliente
        $this->clienteSeleccionado = null;

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
    }

    public function procesarPago()
    {
        $total = round(collect($this->items)->sum('subtotal'), 2);
        $totalPagado = round($this->montoPagoEfectivo + $this->montoPagoOnline, 2);

        // Validar monto
        if ($totalPagado > $total) {
            // Si se paga de más en efectivo, hay cambio
            $cambio = $totalPagado - $total;
        } elseif ($totalPagado < $total) {
            // Si se paga menos, debe tener cliente para crédito
            if ($this->clienteSeleccionado === null) {
                $this->toast('error', 'Debe seleccionar un cliente para vender a crédito');
                $this->pasoActual = 2;
                return;
            }
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
        $this->finalizarVenta();
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

    public function finalizarVenta()
    {
        try {
            DB::beginTransaction();

            $total = collect($this->items)->sum('subtotal');

            // Obtener el nombre del cliente si existe
            $nombreCliente = null;
            if ($this->clienteSeleccionado) {
                $cliente = Cliente::find($this->clienteSeleccionado);
                $nombreCliente = $cliente ? $cliente->nombre : null;
            }

            // Determinar efectivo, online y crédito
            $efectivo = $this->montoPagoEfectivo;
            $online = $this->montoPagoOnline;
            $totalPagado = $efectivo + $online;
            $cambio = 0;
            $credito = 0;

            if ($totalPagado > $total) {
                // Hay cambio (solo si se pagó en efectivo)
                $cambio = $totalPagado - $total;
                $efectivo = $efectivo - $cambio; // Ajustar efectivo
            } elseif ($totalPagado < $total) {
                // Hay crédito
                $credito = $total - $totalPagado;
            }

            // Actualizar la venta
            $this->venta->update([
                'cliente_id' => $this->clienteSeleccionado,
                'estado' => 'Completo',
                'efectivo' => round($efectivo, 2),
                'online' => round($online, 2),
                'credito' => round($credito, 2),
                'cambio' => round($cambio, 2),
            ]);

            // Actualizar productos en Kardex y reducir stock
            foreach ($this->items as $item) {
                $producto = Producto::lockForUpdate()->find($item['producto_id']);
                $cantidadTotal = ($item['enteros'] * $item['cantidad_por_medida']) + $item['unidades'];

                // Reducir stock
                $stockAnterior = $producto->stock;
                $producto->stock -= $cantidadTotal;
                $producto->save();

                // Registrar en Kardex (solo si hay cantidad)
                if ($cantidadTotal > 0) {
                    Kardex::create([
                        'tenant_id' => currentTenantId(),
                        'user_id' => Auth::id(),
                        'producto_id' => $producto->id,
                        'entrada' => 0,
                        'salida' => $cantidadTotal,
                        'anterior' => $stockAnterior,
                        'saldo' => $producto->stock, // Stock después de la reducción
                        'precio' => $item['precio'],
                        'total' => $item['subtotal'],
                        'obs' => 'Venta #' . $this->venta->numero_folio . ($nombreCliente ? ' - ' . $nombreCliente : ''),
                    ]);
                }
            }

            // Registrar ingreso por la venta en efectivo
            if ($efectivo > 0) {
                $detalle = 'Venta #' . $this->venta->numero_folio;

                if ($nombreCliente) {
                    $detalle .= ' - ' . $nombreCliente;
                }

                if ($credito > 0) {
                    $detalle .= ' (Pago parcial: Bs. ' . number_format($efectivo, 2) . ' efectivo';
                    if ($online > 0) {
                        $detalle .= ' + Bs. ' . number_format($online, 2) . ' online';
                    }
                    $detalle .= ' + Bs. ' . number_format($credito, 2) . ' crédito)';
                } else {
                    $detalle .= ' (Pago total';
                    if ($online > 0) {
                        $detalle .= ': Bs. ' . number_format($efectivo, 2) . ' efectivo + Bs. ' . number_format($online, 2) . ' online';
                    } else {
                        $detalle .= ' en efectivo';
                    }
                    if ($cambio > 0) {
                        $detalle .= ' - Cambio: Bs. ' . number_format($cambio, 2);
                    }
                    $detalle .= ')';
                }

                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => Auth::id(),
                    'detalle' => $detalle,
                    'ingreso' => $efectivo,
                    'egreso' => 0,
                ]);
            }

            DB::commit();

            $this->toast('success', 'Venta completada exitosamente');

            // Redirigir a la lista de ventas
            return redirect()->route('ventas');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al finalizar venta: ' . $e->getMessage());
            $this->toast('error', 'Error al finalizar la venta');
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
        return view('livewire.venta')->layout('layouts.tenant.theme');
    }
}
