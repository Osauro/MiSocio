<?php

namespace App\Livewire;

use App\Models\Compra as CompraModel;
use App\Models\CompraItem;
use App\Models\Producto;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function mount($compraId = null)
    {
        if (!$compraId) {
            // Si no viene ID, redirigir a compras
            return redirect()->route('tenant.compras');
        }

        // Cargar la compra
        $this->compra = CompraModel::findOrFail($compraId);

        // Verificar que sea del usuario actual y esté pendiente
        if ($this->compra->user_id !== Auth::id() || $this->compra->estado !== 'Pendiente') {
            return redirect()->route('tenant.compras');
        }

        $this->compraId = $this->compra->id;
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
                ->limit(10)
                ->get()
                ->map(function ($producto) {
                    return [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'codigo' => $producto->codigo,
                        'stock' => $producto->stock,
                        'medida' => $producto->medida,
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
        }
    }

    public function render()
    {
        return view('livewire.compra')->layout('layouts.tenant.theme');
    }
}
