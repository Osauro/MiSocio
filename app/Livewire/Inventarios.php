<?php

namespace App\Livewire;

use App\Models\Inventario as InventarioModel;
use App\Models\InventarioItem;
use App\Models\Producto;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Inventarios extends Component
{
    use WithPagination, RequiresTenant, SweetAlertTrait;

    public $search = '';
    public $perPage = 12;

    // Modal de detalles
    public $mostrarModal   = false;
    public $modalData      = [];
    public $modalAllItems  = [];
    public $modalPage      = 1;
    public $modalPerPage   = 8;

    public function mount()
    {
        $this->perPage = isset($_COOKIE['paginateInventarios']) ? (int)$_COOKIE['paginateInventarios'] : 12;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function crearInventario()
    {
        // Verificar si ya hay un inventario pendiente
        $pendiente = InventarioModel::where('user_id', Auth::id())
            ->where('tenant_id', currentTenantId())
            ->where('estado', 'Pendiente')
            ->first();

        if ($pendiente) {
            return redirect()->route('inventario', ['inventarioId' => $pendiente->id]);
        }

        $nuevo = InventarioModel::create([
            'tenant_id' => currentTenantId(),
            'user_id'   => Auth::id(),
            'estado'    => 'Pendiente',
        ]);

        // Auto-cargar los 16 productos con fecha_control más antigua (nulls primero = nunca inventariados)
        $productos = Producto::orderByRaw('fecha_control IS NOT NULL, fecha_control ASC')
            ->limit(24)
            ->get();

        foreach ($productos as $producto) {
            InventarioItem::create([
                'inventario_id' => $nuevo->id,
                'producto_id'   => $producto->id,
                'stock_sistema' => $producto->stock,
                'stock_contado' => $producto->stock,
                'diferencia'    => 0,
            ]);
        }

        return redirect()->route('inventario', ['inventarioId' => $nuevo->id]);
    }

    public function verDetalles($inventarioId)
    {
        $inventario = InventarioModel::with(['user', 'items.producto' => function ($q) {
            $q->withTrashed();
        }])->findOrFail($inventarioId);

        $this->modalData = [
            'folio'  => $inventario->numero_folio,
            'estado' => $inventario->estado,
            'user'   => $inventario->user->name ?? 'N/A',
            'fecha'  => $inventario->created_at->format('d/m/Y H:i'),
        ];

        $this->modalAllItems = $inventario->items->map(function ($item) {
            $producto    = $item->producto;
            $cantidad    = $producto ? ($producto->cantidad ?? 1) : 1;
            $medAbrev    = $producto ? strtolower(substr($producto->medida, 0, 1)) : 'u';
            $precioCosto = $producto ? (float)($producto->precio_de_compra ?? 0) : 0;
            $precioMayor = $producto ? (float)($producto->precio_por_mayor ?? 0) : 0;

            // Helper para formatear stock
            $formatStock = function ($stock) use ($cantidad, $medAbrev) {
                if ($cantidad > 1) {
                    $ent = intdiv($stock, $cantidad);
                    $uni = $stock % $cantidad;
                    if ($ent > 0 && $uni > 0) return $ent . $medAbrev . '-' . $uni . 'u';
                    if ($ent > 0)             return $ent . $medAbrev;
                    return $uni . 'u';
                }
                return $stock . $medAbrev;
            };

            // Diferencia
            $absDif = abs($item->diferencia);
            if ($item->diferencia == 0) {
                $difDisplay = '=';
            } else {
                if ($cantidad > 1) {
                    $difEnt = intdiv($absDif, $cantidad);
                    $difUni = $absDif % $cantidad;
                    if ($difEnt > 0 && $difUni > 0) $difDisplay = $difEnt . $medAbrev . '-' . $difUni . 'u';
                    elseif ($difEnt > 0)             $difDisplay = $difEnt . $medAbrev;
                    else                             $difDisplay = $difUni . 'u';
                } else {
                    $difDisplay = $absDif . $medAbrev;
                }
            }

            // sobrante (entrada) → precio_de_compra, faltante (salida) → precio_por_mayor
            if ($item->diferencia > 0) {
                $precioDisplay = $precioCosto;
            } elseif ($item->diferencia < 0) {
                $precioDisplay = $precioMayor;
            } else {
                $precioDisplay = 0;
            }
            $cantidadesAfectadas = $cantidad > 1 ? ($absDif / $cantidad) : $absDif;
            $total = round($cantidadesAfectadas * $precioDisplay, 2);
            if ($item->diferencia < 0) {
                $total = -$total;
            }

            return [
                'nombre'      => $producto ? $producto->nombre : 'Producto eliminado',
                'sys_display' => $formatStock($item->stock_sistema),
                'cnt_display' => $formatStock($item->stock_contado),
                'dif_display' => $difDisplay,
                'diferencia'  => $item->diferencia,
                'precio'      => $precioDisplay,
                'total'       => $total,
            ];
        })->toArray();

        $this->modalPage  = 1;
        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModal  = false;
        $this->modalData     = [];
        $this->modalAllItems = [];
        $this->modalPage     = 1;
    }

    public function eliminar($inventarioId)
    {
        $inventario = InventarioModel::findOrFail($inventarioId);

        if ($inventario->estado !== 'Pendiente') {
            $this->toast('error', 'Solo se pueden eliminar inventarios pendientes');
            return;
        }

        DB::transaction(function () use ($inventario) {
            $inventario->items()->delete();
            $inventario->delete();
        });

        $this->toast('success', 'Inventario eliminado');
    }

    public function render()
    {
        $inventarios = InventarioModel::with([
            'user',
            'items' => function ($q) {
                $q->with(['producto' => fn($q2) => $q2->withTrashed()]);
            },
        ])
        ->withCount('items')
        ->when($this->search, fn($q) => $q->where('numero_folio', 'like', '%' . $this->search . '%'))
        ->orderBy('created_at', 'desc')
        ->paginate($this->perPage);

        return view('livewire.inventarios', compact('inventarios'));
    }
}
