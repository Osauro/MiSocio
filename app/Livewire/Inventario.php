<?php

namespace App\Livewire;

use App\Models\Inventario as InventarioModel;
use App\Models\InventarioItem;
use App\Models\Kardex;
use App\Models\Producto;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Inventario extends Component
{
    use RequiresTenant, SweetAlertTrait;

    protected $listeners = ['cancelarInventario', 'ejecutarFinalizar'];

    public $inventarioId;
    public $inventarioFolio;

    // Items del inventario (array local)
    public $items = [];

    // Confirmación de finalizar
    public $procesando = false;

    public function mount($inventarioId = null)
    {
        if (!$inventarioId) {
            session()->flash('error', 'No se especificó ID de inventario');
            return redirect()->route('inventarios');
        }

        try {
            $inventario = InventarioModel::withoutGlobalScopes()->find($inventarioId);
        } catch (\Exception $e) {
            session()->flash('error', 'El inventario no existe');
            return redirect()->route('inventarios');
        }

        if (!$inventario) {
            session()->flash('error', 'El inventario no existe');
            return redirect()->route('inventarios');
        }

        if ($inventario->estado !== 'Pendiente') {
            session()->flash('error', 'Solo se pueden editar inventarios pendientes');
            return redirect()->route('inventarios');
        }

        if ($inventario->user_id !== Auth::id() && !canManageTenant()) {
            session()->flash('error', 'No tienes permiso para editar este inventario');
            return redirect()->route('inventarios');
        }

        $this->inventarioId    = $inventario->id;
        $this->inventarioFolio = $inventario->numero_folio;
        $this->cargarItems();
    }

    public function cargarItems()
    {
        $dbItems = InventarioItem::where('inventario_id', $this->inventarioId)
            ->with('producto')
            ->get();

        $this->items = $dbItems->map(function ($item) {
            $producto = $item->producto;
            $cantPorMedida = $producto ? ($producto->cantidad ?? 1) : 1;

            // Descomponer stock_sistema en ent/uni para mostrar referencia
            $sysEnt = $cantPorMedida > 1 ? intdiv($item->stock_sistema, $cantPorMedida) : $item->stock_sistema;
            $sysUni = $cantPorMedida > 1 ? ($item->stock_sistema % $cantPorMedida) : 0;

            // Descomponer stock_contado en ent/uni para los inputs
            $cntEnt = $cantPorMedida > 1 ? intdiv($item->stock_contado, $cantPorMedida) : $item->stock_contado;
            $cntUni = $cantPorMedida > 1 ? ($item->stock_contado % $cantPorMedida) : 0;

            // ¿El usuario ya tocó los inputs? Si stock_contado difiere del sistema, sí
            $contado = $item->stock_contado !== $item->stock_sistema || $item->diferencia !== 0;

            return [
                'id'                 => $item->id,
                'producto_id'        => $item->producto_id,
                'nombre'             => $producto ? $producto->nombre : 'Producto',
                'imagen'             => $producto ? ($producto->photo_url ?? null) : null,
                'medida'             => $producto ? $producto->medida : 'u',
                'cantidad_por_medida'=> $cantPorMedida,
                'stock_sistema'      => $item->stock_sistema,
                'sys_ent'            => $sysEnt,
                'sys_uni'            => $sysUni,
                'stock_contado'      => $item->stock_contado,
                'cnt_ent'            => $cntEnt,
                'cnt_uni'            => $cntUni,
                'diferencia'         => $item->diferencia,
                'contado'            => $contado,
            ];
        })->toArray();
    }

    public function actualizarStockContado($itemId, $valor)
    {
        $stockContado = max(0, (int) $valor);

        $itemIndex = collect($this->items)->search(fn($i) => $i['id'] == $itemId);
        if ($itemIndex === false) return;

        $stockSistema = $this->items[$itemIndex]['stock_sistema'];
        $diferencia   = $stockContado - $stockSistema;

        $this->items[$itemIndex]['stock_contado'] = $stockContado;
        $this->items[$itemIndex]['diferencia']    = $diferencia;
        $this->items[$itemIndex]['contado']       = true;

        // Actualizar en BD
        InventarioItem::where('id', $itemId)->update([
            'stock_contado' => $stockContado,
            'diferencia'    => $diferencia,
        ]);
    }

    #[Renderless]
    public function actualizarEntUni($itemId, $ent, $uni)
    {
        $itemIndex = collect($this->items)->search(fn($i) => $i['id'] == $itemId);
        if ($itemIndex === false) return;

        $cantPorMedida = $this->items[$itemIndex]['cantidad_por_medida'];
        $ent = max(0, (int) $ent);
        $uni = max(0, (int) $uni);

        // Carry-over: si las unidades superan la cantidad por medida, incrementar enteros
        if ($cantPorMedida > 1 && $uni >= $cantPorMedida) {
            $ent += intdiv($uni, $cantPorMedida);
            $uni  = $uni % $cantPorMedida;
        }

        // Si la medida es 1, solo usamos ent como stock directo
        $stockContado = $cantPorMedida > 1 ? ($ent * $cantPorMedida + $uni) : $ent;
        $stockSistema = $this->items[$itemIndex]['stock_sistema'];
        $diferencia   = $stockContado - $stockSistema;

        $this->items[$itemIndex]['cnt_ent']      = $ent;
        $this->items[$itemIndex]['cnt_uni']      = $uni;
        $this->items[$itemIndex]['stock_contado']= $stockContado;
        $this->items[$itemIndex]['diferencia']   = $diferencia;
        $this->items[$itemIndex]['contado']      = true;

        InventarioItem::where('id', $itemId)->update([
            'stock_contado' => $stockContado,
            'diferencia'    => $diferencia,
        ]);
    }

    public function cancelarInventario()
    {
        InventarioModel::withoutGlobalScopes()
            ->where('id', $this->inventarioId)
            ->update(['estado' => 'Eliminado']);
        return redirect()->route('inventarios');
    }

    public function ejecutarFinalizar($id = null)
    {
        if ($this->procesando) return;

        $this->procesando = true;

        try {
            DB::transaction(function () {
                $folio = $this->inventarioFolio;
                $now   = now();

                foreach ($this->items as $item) {
                    $producto = Producto::lockForUpdate()->find($item['producto_id']);
                    if (!$producto) continue;

                    $diferencia     = $item['stock_contado'] - $item['stock_sistema'];
                    $stockAnterior  = $producto->stock;

                    // Ajustar stock al valor contado físicamente
                    $producto->stock        = $item['stock_contado'];
                    $producto->fecha_control = $now->toDateString();
                    $producto->save();

                    // Registrar en Kardex solo si hay diferencia
                    if ($diferencia === 0) continue;

                    $tipo  = $diferencia > 0 ? 'sobrante' : 'faltante';
                    $obs   = "Inventario #{$folio} - {$tipo}";

                    $precio = $diferencia > 0 ? $producto->precio_de_compra : $producto->precio_por_mayor;

                    Kardex::create([
                        'tenant_id'  => currentTenantId(),
                        'user_id'    => Auth::id(),
                        'producto_id'=> $producto->id,
                        'entrada'    => $diferencia > 0 ? $diferencia : 0,
                        'salida'     => $diferencia < 0 ? abs($diferencia) : 0,
                        'anterior'   => $stockAnterior,
                        'saldo'      => $producto->stock,
                        'precio'     => $precio,
                        'total'      => round(($precio / ($producto->cantidad ?: 1)) * abs($diferencia), 2),
                        'obs'        => $obs,
                    ]);
                }

                // Marcar inventario como completo
                InventarioModel::withoutGlobalScopes()
                    ->where('id', $this->inventarioId)
                    ->update(['estado' => 'Completo']);
            });

            $this->toast('success', 'Inventario finalizado correctamente');
            return redirect()->route('inventarios');
        } catch (\Exception $e) {
            Log::error('Error al finalizar inventario', ['error' => $e->getMessage()]);
            $this->procesando = false;
            $this->toast('error', 'Error al finalizar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.inventario');
    }
}
