<?php

namespace App\Livewire;

use App\Models\Compra as CompraModel;
use App\Models\CompraItem;
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

class Compra extends Component
{
    use RequiresTenant, SweetAlertTrait;

    public $compraId;
    public $compra;
    public $buscar = '';
    public $productosEncontrados = [];
    public $items = [];
    public $mostrarBuscador = true;

    // Variables para el flujo de pago
    public $pasoActual = 0; // 0: no iniciado, 1: fecha, 2: proveedor, 3: añadir saldo, 4: pago
    public $fechaCompra;
    public $buscarProveedor = '';
    public $proveedoresEncontrados = [];
    public $proveedorSeleccionado = null;
    public $mostrarFormNuevoProveedor = false;
    public $nuevoProveedor = [
        'nombre' => '',
        'celular' => '',
        'direccion' => '',
        'nit' => '',
    ];
    public $montoAñadirCaja = 0;
    public $montoPago = 0;
    public $saldoCaja = 0;
    public $procesandoPago = false;

    public function mount($compraId = null)
    {
        if (!$compraId) {
            // Si no viene ID, redirigir a compras
            return redirect()->route('compras');
        }

        // Cargar la compra
        $this->compra = CompraModel::findOrFail($compraId);

        // Verificar que sea del usuario actual y esté pendiente
        if ($this->compra->user_id !== Auth::id() || $this->compra->estado !== 'Pendiente') {
            return redirect()->route('compras');
        }

        $this->compraId = $this->compra->id;
        $this->fechaCompra = now()->format('Y-m-d');
        $this->cargarItems();
    }

    public function cargarItems()
    {
        $compraItems = CompraItem::where('compra_id', $this->compraId)
            ->with('producto')
            ->get();

        $this->items = $compraItems->map(function ($item) {
            $cantidadPorMedida = $item->producto->cantidad ?? 1;
            $enteros = intdiv($item->cantidad, $cantidadPorMedida);
            $unidades = $item->cantidad % $cantidadPorMedida;

            return [
                'id' => $item->id,
                'producto_id' => $item->producto_id,
                'nombre' => $item->producto->nombre,
                'imagen' => $item->producto->photo_url,
                'medida' => $item->producto->medida ?? 'u',
                'cantidad_por_medida' => $cantidadPorMedida,
                'enteros' => $enteros,
                'unidades' => $unidades,
                'precio' => $item->precio,
                'precio_por_mayor' => $item->producto->precio_por_mayor ?? 0,
                'precio_por_menor' => $item->producto->precio_por_menor ?? 0,
                'subtotal' => $item->subtotal,
            ];
        })->toArray();
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
                    $stockFormateado = '';

                    if ($cantidadPorMedida > 1) {
                        $enteros = intdiv($producto->stock, $cantidadPorMedida);
                        $unidades = $producto->stock % $cantidadPorMedida;
                        $medidaAbrev = strtolower(substr($producto->medida ?? 'u', 0, 1));
                        $stockFormateado = $enteros . $medidaAbrev . ($unidades > 0 ? ' - ' . $unidades . 'u' : '');
                    } else {
                        $stockFormateado = $producto->stock . 'u';
                    }

                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'codigo' => $producto->codigo,
                        'stock' => $producto->stock,
                        'stock_formateado' => $stockFormateado,
                        'medida' => $producto->medida,
                        'cantidad' => $producto->cantidad ?? 1,
                        'photo_url' => $producto->photo_url,
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
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'El producto ya está agregado a la compra'
            ]);
            return;
        }

        // Precio de compra del producto
        $precioCompra = $producto->precio_de_compra ?? 0;

        // Crear el item en la base de datos
        $compraItem = CompraItem::create([
            'compra_id' => $this->compraId,
            'producto_id' => $productoId,
            'cantidad' => 0,
            'precio' => $precioCompra,
            'subtotal' => 0,
        ]);

        // Agregar al array de items
        $this->items[] = [
            'id' => $compraItem->id,
            'producto_id' => $productoId,
            'nombre' => $producto->nombre,
            'imagen' => $producto->photo_url,
            'medida' => $producto->medida ?? 'u',
            'cantidad_por_medida' => $producto->cantidad ?? 1,
            'enteros' => 0,
            'unidades' => 0,
            'precio' => $precioCompra,
            'precio_por_mayor' => $producto->precio_por_mayor ?? 0,
            'precio_por_menor' => $producto->precio_por_menor ?? 0,
            'subtotal' => 0,
        ];

        $this->buscar = '';
        $this->productosEncontrados = [];

        // Devolver el foco al buscador
        $this->dispatch('focusBuscador');

        // Emitir evento al navegador para actualizar badge
        $this->dispatch('actualizar-badge-compra');
    }

    public function actualizarItem($index)
    {
        $item = $this->items[$index];

        // Validar que enteros y unidades no sean negativos
        $this->items[$index]['enteros'] = max(0, intval($item['enteros']));
        $this->items[$index]['unidades'] = max(0, intval($item['unidades']));

        // Actualizar la referencia del item
        $item = $this->items[$index];

        // Si las unidades son iguales o mayores a cantidad_por_medida, convertir a enteros
        if ($item['unidades'] >= $item['cantidad_por_medida']) {
            $enterosExtras = floor($item['unidades'] / $item['cantidad_por_medida']);
            $this->items[$index]['enteros'] += $enterosExtras;
            $this->items[$index]['unidades'] = $item['unidades'] % $item['cantidad_por_medida'];

            // Actualizar la referencia del item
            $item = $this->items[$index];
        }

        $cantidadTotal = ($item['enteros'] * $item['cantidad_por_medida']) + $item['unidades'];

        // Calcular subtotal: (precio / cantidad_por_medida) * cantidad_total
        // El precio siempre representa el precio de compra del paquete completo
        if ($item['cantidad_por_medida'] > 0) {
            $subtotal = ($item['precio'] / $item['cantidad_por_medida']) * $cantidadTotal;
        } else {
            $subtotal = 0;
        }

        $this->items[$index]['subtotal'] = round($subtotal, 2);

        // Actualizar en la base de datos
        CompraItem::where('id', $item['id'])->update([
            'cantidad' => $cantidadTotal,
            'precio' => $item['precio'],
            'subtotal' => $this->items[$index]['subtotal'],
        ]);

        $this->actualizarTotales();
    }

    public function actualizarSubtotal($index)
    {
        $item = $this->items[$index];

        $cantidadTotal = ($item['enteros'] * $item['cantidad_por_medida']) + $item['unidades'];

        // Recalcular el precio basado en el nuevo subtotal
        // precio = (subtotal / cantidad_total) * cantidad_por_medida
        if ($cantidadTotal > 0 && $item['cantidad_por_medida'] > 0) {
            $nuevoPrecio = ($item['subtotal'] / $cantidadTotal) * $item['cantidad_por_medida'];
            $this->items[$index]['precio'] = round($nuevoPrecio, 2);
        }

        // Actualizar en la base de datos cuando se edita el subtotal manualmente
        CompraItem::where('id', $item['id'])->update([
            'subtotal' => $item['subtotal'],
            'precio' => $this->items[$index]['precio'],
        ]);

        $this->actualizarTotales();
    }

    public function actualizarTotales()
    {
        $total = collect($this->items)->sum('subtotal');

        // Por ahora todo va a efectivo
        $this->compra->update([
            'efectivo' => $total,
            'credito' => 0,
        ]);

        $this->compra->refresh();
    }

    public function confirmEliminarItem($index)
    {
        $item = $this->items[$index];

        $this->confirmDelete(
            $item['id'],
            '¿Eliminar producto?',
            "¿Desea eliminar {$item['nombre']} de la compra?",
            'eliminarItem'
        );
    }

    #[On('eliminarItem')]
    public function eliminarItem($id)
    {
        // El parámetro viene como array desde el JS: {id: itemId}
        $itemId = is_array($id) && isset($id['id']) ? $id['id'] : $id;

        // Eliminar de la base de datos
        if (CompraItem::destroy($itemId)) {
            $this->cargarItems();
            $this->actualizarTotales();
            $this->toast('success', 'Producto eliminado de la compra');

            // Emitir evento al navegador para actualizar badge
            $this->dispatch('actualizar-badge-compra');
        }
    }

    // ==================== BOTONES Y FLUJO DE PAGO ====================

    public function cancelarCompra()
    {
        $this->confirmDelete(
            $this->compraId,
            '¿Cancelar compra?',
            'Se eliminará la compra y todos sus items. Esta acción no se puede deshacer.',
            'ejecutarCancelarCompra'
        );
    }

    #[On('ejecutarCancelarCompra')]
    public function ejecutarCancelarCompra($id)
    {
        $compraId = is_array($id) && isset($id['id']) ? $id['id'] : $id;

        try {
            $compra = CompraModel::findOrFail($compraId);

            // Verificar que sea del usuario actual y esté pendiente
            if ($compra->user_id !== Auth::id() || $compra->estado !== 'Pendiente') {
                $this->toast('error', 'No se puede cancelar esta compra');
                return;
            }

            // Eliminar físicamente la compra pendiente (los items se eliminan en cascada)
            $compra->delete();

            $this->toast('success', 'Compra cancelada exitosamente');

            // Redirigir a la lista de compras
            return redirect()->route('compras');
        } catch (\Exception $e) {
            Log::error('Error al cancelar compra: ' . $e->getMessage());
            $this->toast('error', 'Error al cancelar la compra');
        }
    }

    public function iniciarCompletarCompra()
    {
        // Validar que haya items
        if (count($this->items) == 0) {
            $this->toast('warning', 'Debe agregar al menos un producto a la compra');
            return;
        }

        // Validar que todos los items tengan cantidad > 0
        $itemsSinCantidad = collect($this->items)->filter(function ($item) {
            $cantidadTotal = ($item['enteros'] * $item['cantidad_por_medida']) + $item['unidades'];
            return $cantidadTotal <= 0;
        });

        if ($itemsSinCantidad->count() > 0) {
            $this->toast('warning', 'Todos los productos deben tener cantidad mayor a 0');
            return;
        }

        // Iniciar paso 1: Fecha
        $this->pasoActual = 1;
    }

    public function avanzarPaso1()
    {
        // Validar fecha
        if (!$this->fechaCompra) {
            $this->toast('warning', 'Debe seleccionar una fecha');
            return;
        }

        // Avanzar a paso 2: Proveedor
        $this->pasoActual = 2;
    }

    public function updatedBuscarProveedor()
    {
        if (strlen($this->buscarProveedor) == 0) {
            $this->proveedoresEncontrados = [];
            $this->mostrarFormNuevoProveedor = false;
            return;
        }

        // Verificar si son 8 dígitos numéricos (búsqueda por celular)
        if (strlen($this->buscarProveedor) == 8 && is_numeric($this->buscarProveedor)) {
            $this->proveedoresEncontrados = Cliente::where('celular', $this->buscarProveedor)
                ->limit(10)
                ->get()
                ->toArray();

            // Si no hay resultados, mostrar form para nuevo cliente
            if (empty($this->proveedoresEncontrados)) {
                $this->mostrarFormNuevoProveedor = true;
                $this->nuevoProveedor['celular'] = $this->buscarProveedor;
            } else {
                $this->mostrarFormNuevoProveedor = false;

                // Si hay exactamente 1 resultado, seleccionarlo automáticamente
                if (count($this->proveedoresEncontrados) === 1) {
                    $this->seleccionarProveedor($this->proveedoresEncontrados[0]['id']);
                }
            }
        } else {
            // Búsqueda por nombre
            $this->proveedoresEncontrados = Cliente::where('nombre', 'like', '%' . $this->buscarProveedor . '%')
                ->limit(10)
                ->get()
                ->toArray();

            $this->mostrarFormNuevoProveedor = false;

            // Si hay exactamente 1 resultado, seleccionarlo automáticamente
            if (count($this->proveedoresEncontrados) === 1) {
                $this->seleccionarProveedor($this->proveedoresEncontrados[0]['id']);
            }
        }
    }

    public function seleccionarProveedor($proveedorId)
    {
        $this->proveedorSeleccionado = $proveedorId;

        // Obtener saldo de caja
        $this->obtenerSaldoCaja();

        // Avanzar a paso 3: añadir saldo a caja
        $this->pasoActual = 3;
        $this->montoAñadirCaja = 0;
    }

    public function mostrarFormAgregarProveedor()
    {
        $this->mostrarFormNuevoProveedor = true;
        $this->nuevoProveedor = [
            'nombre' => '',
            'celular' => '',
            'direccion' => '',
            'nit' => '',
        ];
    }

    public function crearYSeleccionarProveedor()
    {
        // Si no hay nombre, avanzar sin proveedor
        if (empty(trim($this->nuevoProveedor['nombre']))) {
            $this->avanzarPaso2SinProveedor();
            return;
        }

        // Validar datos requeridos solo si hay nombre
        $this->validate([
            'nuevoProveedor.nombre' => 'required|string|max:255',
            'nuevoProveedor.celular' => 'required|string|max:20',
        ], [
            'nuevoProveedor.nombre.required' => 'El nombre es requerido',
            'nuevoProveedor.celular.required' => 'El celular es requerido',
        ]);

        try {
            // Crear el nuevo cliente/proveedor
            $cliente = Cliente::create([
                'tenant_id' => currentTenantId(),
                'nombre' => $this->nuevoProveedor['nombre'],
                'celular' => $this->nuevoProveedor['celular'],
                'direccion' => $this->nuevoProveedor['direccion'],
                'nit' => $this->nuevoProveedor['nit'],
            ]);

            $this->toast('success', 'Proveedor creado exitosamente');
            $this->seleccionarProveedor($cliente->id);
        } catch (\Exception $e) {
            Log::error('Error al crear proveedor: ' . $e->getMessage());
            $this->toast('error', 'Error al crear el proveedor');
        }
    }

    public function avanzarPaso2SinProveedor()
    {
        // Continuar sin proveedor (solo efectivo)
        $this->proveedorSeleccionado = null;

        // Obtener saldo de caja
        $this->obtenerSaldoCaja();

        // Avanzar a paso 3: añadir saldo a caja
        $this->pasoActual = 3;
        $this->montoAñadirCaja = 0;
    }

    public function obtenerSaldoCaja()
    {
        $ultimoMovimiento = Movimiento::orderBy('id', 'desc')->first();
        $this->saldoCaja = $ultimoMovimiento ? $ultimoMovimiento->saldo : 0;
    }

    public function avanzarPaso3()
    {
        // Si hay monto a añadir, registrar el movimiento
        if ($this->montoAñadirCaja > 0) {
            try {
                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => Auth::id(),
                    'detalle' => 'Aporte de fondos para Compra #' . $this->compra->numero_folio,
                    'ingreso' => $this->montoAñadirCaja,
                    'egreso' => 0,
                ]);

                // Actualizar saldo de caja
                $this->obtenerSaldoCaja();

                $this->toast('success', 'Fondos añadidos a caja');
            } catch (\Exception $e) {
                Log::error('Error al añadir fondos: ' . $e->getMessage());
                $this->toast('error', 'Error al añadir fondos a caja');
                return;
            }
        }

        // Avanzar a paso 4: pago
        $this->pasoActual = 4;
        $total = round(collect($this->items)->sum('subtotal'), 2);
        $this->montoPago = $total; // Por defecto el monto total

        // Si el saldo en caja es menor al total y hay proveedor, ajustar el monto al saldo disponible
        if ($this->saldoCaja < $total && $this->proveedorSeleccionado !== null) {
            $this->montoPago = round($this->saldoCaja, 2);
        }
    }

    public function updatedMontoPago()
    {
        // Redondear a 2 decimales
        $this->montoPago = round(floatval($this->montoPago), 2);

        $total = round(collect($this->items)->sum('subtotal'), 2);

        // No permitir monto superior al total
        if ($this->montoPago > $total) {
            $this->montoPago = $total;
            $this->toast('warning', 'El monto no puede ser superior al total de la compra');
        }

        // Si el monto es menor al total y no hay proveedor, mostrar error
        if ($this->montoPago < $total && $this->proveedorSeleccionado === null) {
            $this->toast('error', 'Debe seleccionar un proveedor para pagar a crédito');
            $this->montoPago = $total;
        }
    }

    public function procesarPago()
    {
        $total = round(collect($this->items)->sum('subtotal'), 2);

        // Validar monto
        if ($this->montoPago > $total) {
            $this->toast('error', 'El monto no puede ser superior al total');
            return;
        }

        // Si el monto es menor y no hay proveedor, error
        if ($this->montoPago < $total && $this->proveedorSeleccionado === null) {
            $this->toast('error', 'Debe seleccionar un proveedor para pagar a crédito');
            $this->montoPago = $total;
            return;
        }

        // Verificar fondos en caja si hay pago en efectivo
        if ($this->montoPago > 0) {
            $this->obtenerSaldoCaja();

            // Si no hay saldo suficiente
            if ($this->saldoCaja < $this->montoPago) {
                // Si hay proveedor, ajustar el monto al saldo disponible y el resto a crédito
                if ($this->proveedorSeleccionado !== null) {
                    $this->montoPago = round($this->saldoCaja, 2);
                    $this->toast('warning', 'Saldo insuficiente.<br>Se pagará Bs. ' . number_format($this->montoPago, 2) . ' en efectivo y Bs. ' . number_format($total - $this->montoPago, 2) . ' a crédito');
                } else {
                    // Sin proveedor, regresar al paso 2 para seleccionar uno
                    $this->toast('warning', 'Fondos insuficientes.<br>Debe seleccionar un proveedor para pagar a crédito');
                    $this->pasoActual = 2;
                    return;
                }
            }
        }

        // Mostrar spinner de procesando
        $this->procesandoPago = true;

        // Procesar el pago
        $this->finalizarCompra();
    }

    public function retrocederPaso()
    {
        if ($this->pasoActual > 0) {
            $this->pasoActual--;

            // Limpiar datos según el paso al que retrocedemos
            if ($this->pasoActual === 1) {
                // Limpiar datos de proveedor
                $this->buscarProveedor = '';
                $this->proveedoresEncontrados = [];
                $this->proveedorSeleccionado = null;
                $this->mostrarFormNuevoProveedor = false;
            } elseif ($this->pasoActual === 2) {
                // Limpiar datos de añadir saldo
                $this->montoAñadirCaja = 0;
            } elseif ($this->pasoActual === 3) {
                // Limpiar datos de pago
                $this->montoPago = 0;
                $this->procesandoPago = false;
            }
        }
    }

    public function finalizarCompra()
    {
        try {
            DB::beginTransaction();

            $total = collect($this->items)->sum('subtotal');

            // Obtener el nombre del proveedor si existe
            $nombreProveedor = null;
            if ($this->proveedorSeleccionado) {
                $proveedor = Cliente::find($this->proveedorSeleccionado);
                $nombreProveedor = $proveedor ? $proveedor->nombre : null;
            }

            // Determinar efectivo y crédito
            $efectivo = $this->montoPago;
            $credito = $total - $this->montoPago;

            // Actualizar la compra
            $this->compra->update([
                'proveedor_id' => $this->proveedorSeleccionado,
                'estado' => 'Completo',
                'efectivo' => $efectivo,
                'credito' => $credito,
                'created_at' => $this->fechaCompra,
                'updated_at' => $this->fechaCompra,
            ]);

            // Actualizar stock de productos y crear movimientos en kardex
            foreach ($this->items as $item) {
                $producto = Producto::find($item['producto_id']);
                if ($producto) {
                    $cantidadTotal = ($item['enteros'] * $item['cantidad_por_medida']) + $item['unidades'];

                    // Guardar el stock anterior antes de incrementar
                    $stockAnterior = $producto->stock;

                    // Incrementar stock
                    $producto->increment('stock', $cantidadTotal);

                    // Refrescar el producto para obtener el nuevo stock
                    $producto->refresh();

                    // Calcular precio promedio ponderado
                    $precioCompraActual = $producto->precio_de_compra ?? 0;
                    $precioNuevaCompra = $item['precio'];

                    // Valor del inventario anterior + Valor de la nueva compra / Total unidades
                    $valorInventarioAnterior = $stockAnterior * $precioCompraActual;
                    $valorNuevaCompra = $cantidadTotal * $precioNuevaCompra;
                    $stockTotal = $stockAnterior + $cantidadTotal;

                    $precioPonderado = $stockTotal > 0 ? ($valorInventarioAnterior + $valorNuevaCompra) / $stockTotal : $precioNuevaCompra;

                    // Solo actualizar precios por mayor y menor si el precio nuevo es mayor al actual
                    if ($precioNuevaCompra > $precioCompraActual) {
                        // Calcular diferencias (márgenes) actuales
                        $diffPrecioMayor = $producto->precio_por_mayor - $precioCompraActual;
                        $diffPrecioMayor = ceil($diffPrecioMayor); // Redondear hacia arriba

                        $cantidad = $producto->cantidad ?? 1;
                        $diffPrecioMenor = (($producto->precio_por_menor * $cantidad) - $producto->precio_por_mayor) / $cantidad;
                        $diffPrecioMenor = round($diffPrecioMenor * 2) / 2; // Redondear a 0.50

                        // Calcular nuevos precios manteniendo los márgenes
                        $precioMayorCalculado = $precioPonderado + $diffPrecioMayor;

                        // Redondeo inteligente para precio por mayor: <= 0.5 → 0.5, > 0.5 → entero siguiente
                        $parteEnteraMayor = floor($precioMayorCalculado);
                        $parteDecimalMayor = $precioMayorCalculado - $parteEnteraMayor;

                        if ($parteDecimalMayor <= 0.5) {
                            $nuevoPrecioMayor = $parteEnteraMayor + 0.5;
                        } else {
                            $nuevoPrecioMayor = ceil($precioMayorCalculado);
                        }

                        $precioMenorCalculado = ($nuevoPrecioMayor / $cantidad) + $diffPrecioMenor;

                        // Redondeo inteligente para precio por menor: <= 0.5 → 0.5, > 0.5 → entero siguiente
                        $parteEnteraMenor = floor($precioMenorCalculado);
                        $parteDecimalMenor = $precioMenorCalculado - $parteEnteraMenor;

                        if ($parteDecimalMenor <= 0.5) {
                            $nuevoPrecioMenor = $parteEnteraMenor + 0.5;
                        } else {
                            $nuevoPrecioMenor = ceil($precioMenorCalculado);
                        }

                        // Actualizar todos los precios
                        $producto->update([
                            'precio_de_compra' => $precioPonderado,
                            'precio_por_mayor' => $nuevoPrecioMayor,
                            'precio_por_menor' => $nuevoPrecioMenor,
                        ]);
                    } else {
                        // Solo actualizar precio de compra con la media ponderada
                        $producto->update([
                            'precio_de_compra' => $precioPonderado,
                        ]);
                    }

                    // Crear registro en kardex con el stock actualizado
                    Kardex::create([
                        'tenant_id' => currentTenantId(),
                        'user_id' => Auth::id(),
                        'producto_id' => $producto->id,
                        'entrada' => $cantidadTotal,
                        'salida' => 0,
                        'anterior' => $stockAnterior,
                        'saldo' => $producto->stock, // Stock después del incremento
                        'precio' => $item['precio'],
                        'total' => $item['subtotal'],
                        'obs' => 'Compra #' . $this->compra->numero_folio . ($nombreProveedor ? ' - ' . $nombreProveedor : ''),
                    ]);
                }
            }

            // Registrar egreso por la compra si hay pago en efectivo
            if ($efectivo > 0) {
                // Construir descripción más específica
                $detalle = 'Compra #' . $this->compra->numero_folio;

                if ($nombreProveedor) {
                    $detalle .= ' - ' . $nombreProveedor;
                }

                // Si hay pago parcial (efectivo + crédito), indicarlo
                if ($credito > 0) {
                    $detalle .= ' (Pago parcial: Bs. ' . number_format($efectivo, 2) . ' efectivo + Bs. ' . number_format($credito, 2) . ' crédito)';
                } else {
                    $detalle .= ' (Pago total en efectivo)';
                }

                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id' => Auth::id(),
                    'detalle' => $detalle,
                    'ingreso' => 0,
                    'egreso' => $efectivo,
                ]);
            }

            DB::commit();

            $this->toast('success', 'Compra completada exitosamente');

            // Redirigir a la lista de compras
            return redirect()->route('compras');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al finalizar compra: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            $this->toast('error', 'Error al finalizar la compra: ' . $e->getMessage());
        }
    }

    public function cancelarPagoEnProceso()
    {
        $this->pasoActual = 0;
        $this->proveedorSeleccionado = null;
        $this->buscarProveedor = '';
        $this->proveedoresEncontrados = [];
        $this->mostrarFormNuevoProveedor = false;
        $this->metodoPago = 'efectivo';
        $this->montoPago = 0;
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
        return view('livewire.compra')->layout('layouts.tenant.theme');
    }
}
