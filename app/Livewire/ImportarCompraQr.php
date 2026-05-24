<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Compra as CompraModel;
use App\Models\GaleriaImagen;
use App\Models\CompraItem;
use App\Models\Kardex;
use App\Models\Movimiento;
use App\Models\Producto;
use App\Traits\RequiresTenant;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ImportarCompraQr extends Component
{
    use RequiresTenant, SweetAlertTrait;

    public bool   $abierto = false;
    public string $fase    = 'scanner';

    public string $urlEscaneada = '';
    public string $errorUrl     = '';
    public string $numeroVenta  = '';

    public array $productosQueue = [];
    public int   $productoIndex  = 0;
    public string $productoActual = '';

    public array $logItems = [];

    public ?int  $compraId    = null;
    public float $totalCompra = 0;

    public float  $saldoCaja   = 0;
    public float  $montoAnadir = 0;
    public string $errorFondos = '';

    public int $productosCreados  = 0;
    public int $productosBuscados = 0;

    // ----------------------------------------------

    public function abrir(): void
    {
        $this->reset([
            'urlEscaneada', 'errorUrl', 'numeroVenta', 'productosQueue', 'productoIndex',
            'productoActual', 'logItems', 'compraId', 'totalCompra',
            'saldoCaja', 'montoAnadir', 'errorFondos',
            'productosCreados', 'productosBuscados',
        ]);
        $this->fase   = 'scanner';
        $this->abierto = true;
        $this->dispatch('qr-modal-abierto');
    }

    public function cerrar(): void
    {
        if ($this->compraId && $this->fase === 'procesando') {
            CompraModel::withoutGlobalScopes()->where('id', $this->compraId)->delete();
        }
        $this->abierto = false;
        $this->dispatch('qr-modal-cerrado');
    }

    // ----------------------------------------------
    // Paso 1: Fetch JSON y crear Compra
    // ----------------------------------------------

    public function procesarNumeroVenta(): void
    {
        $numero = trim($this->numeroVenta);
        if (empty($numero) || !ctype_digit($numero)) {
            $this->errorUrl = 'Ingresa un número de venta válido';
            return;
        }
        $this->procesarUrl('https://fadi.com.bo/' . intval($numero));
    }

    public function procesarUrl(string $url): void
    {
        $this->urlEscaneada      = trim($url);
        $this->errorUrl          = '';
        $this->productosQueue    = [];
        $this->productoIndex     = 0;
        $this->logItems          = [];
        $this->productosCreados  = 0;
        $this->productosBuscados = 0;
        $this->totalCompra       = 0;
        $this->compraId          = null;

        if (empty($this->urlEscaneada)) {
            $this->errorUrl = 'La URL no puede estar vac�a';
            return;
        }

        try {
            $response = Http::timeout(15)->get($this->urlEscaneada);
            if (!$response->successful()) {
                $this->errorUrl = "Error HTTP {$response->status()} al obtener el JSON";
                return;
            }
            $data = $response->json();
            if (empty($data['productos'])) {
                $this->errorUrl = 'El JSON no contiene productos';
                return;
            }
            $this->productosQueue = $data['productos'];
        } catch (\Exception $e) {
            $this->errorUrl = 'Error al conectar: ' . $e->getMessage();
            return;
        }

        try {
            $compra = CompraModel::create([
                'tenant_id' => currentTenantId(),
                'user_id'   => Auth::id(),
                'estado'    => 'Pendiente',
                'efectivo'  => 0,
                'credito'   => 0,
            ]);
            $this->compraId = $compra->id;
        } catch (\Exception $e) {
            $this->errorUrl = 'Error al crear la compra: ' . $e->getMessage();
            return;
        }

        $this->fase = 'procesando';
        $this->dispatch('procesar-siguiente');
    }

    // ----------------------------------------------
    // Paso 2: Procesar UN producto por llamada
    // ----------------------------------------------

    public function procesarSiguiente(): void
    {
        if ($this->fase !== 'procesando') return;

        $total = count($this->productosQueue);

        if ($this->productoIndex >= $total) {
            $this->terminarProcesado();
            return;
        }

        $p = $this->productosQueue[$this->productoIndex];

        $nombre       = $p['nombre']    ?? '';
        $catNombre    = $p['categoria'] ?? 'General';
        $medidaNombre = $p['medida']    ?? 'Unidad';
        $cantidad     = (int)   ($p['cantidad']  ?? 1);
        $unidades     = (int)   ($p['unidades']  ?? $cantidad);
        $precio       = (float) ($p['precio']    ?? 0);
        $subtotal     = (float) ($p['subtotal']  ?? ($precio * ($unidades / max($cantidad, 1))));

        $this->productoActual = $nombre ?: '(sin nombre)';
        $this->productosBuscados++;

        if (empty($nombre)) {
            $this->productoIndex++;
            $this->dispatch('procesar-siguiente');
            return;
        }

        try {
            $esNuevo  = false;
            $producto = Producto::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->first();

            if (!$producto) {
                $esNuevo = true;
                $this->productosCreados++;

                $cat = Categoria::whereRaw('LOWER(nombre) = ?', [mb_strtolower($catNombre)])->first()
                    ?? Categoria::create(['tenant_id' => currentTenantId(), 'nombre' => $catNombre]);

                $precioPorMayor = ceil($precio * 1.10);
                $precioPorMenor = ceil(($precioPorMayor / max($cantidad, 1)) * 1.05 * 2) / 2;

                // Buscar imagen en galería por nombre o tags del producto
                $imagenGaleria = GaleriaImagen::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])
                    ->orWhere('tags', 'like', '%' . $nombre . '%')
                    ->orderByDesc('veces_usado')
                    ->first();

                $producto = Producto::create([
                    'tenant_id'        => currentTenantId(),
                    'categoria_id'     => $cat->id,
                    'nombre'           => $nombre,
                    'codigo'           => '',
                    'medida'           => $medidaNombre,
                    'cantidad'         => $cantidad,
                    'precio_de_compra' => $precio,
                    'precio_por_mayor' => $precioPorMayor,
                    'precio_por_menor' => $precioPorMenor,
                    'stock'            => 0,
                    'control'          => true,
                    'imagen'           => $imagenGaleria?->url,
                ]);

                if ($imagenGaleria) {
                    $imagenGaleria->increment('veces_usado');
                }
            }

            CompraItem::create([
                'compra_id'   => $this->compraId,
                'producto_id' => $producto->id,
                'cantidad'    => $unidades,
                'precio'      => $precio,
                'subtotal'    => $subtotal,
            ]);

            $this->totalCompra += $subtotal;

            $this->logItems[] = [
                'nombre'   => $nombre,
                'nuevo'    => $esNuevo,
                'unidades' => $unidades,
                'subtotal' => $subtotal,
                'error'    => false,
            ];

        } catch (\Exception $e) {
            Log::error('ImportarCompraQr procesarSiguiente: ' . $e->getMessage());
            $this->logItems[] = [
                'nombre'   => $nombre,
                'nuevo'    => false,
                'unidades' => 0,
                'subtotal' => 0,
                'error'    => true,
            ];
        }

        $this->productoIndex++;

        if ($this->productoIndex < $total) {
            $this->dispatch('procesar-siguiente');
        } else {
            $this->terminarProcesado();
        }
    }

    private function terminarProcesado(): void
    {
        if ($this->compraId) {
            CompraModel::withoutGlobalScopes()
                ->where('id', $this->compraId)
                ->update(['efectivo' => $this->totalCompra]);
        }

        $this->productoActual = '';

        // Añadir el total completo de la compra como fondos para evitar desfases
        $compra = CompraModel::withoutGlobalScopes()->find($this->compraId);
        Movimiento::create([
            'tenant_id' => currentTenantId(),
            'user_id'   => Auth::id(),
            'detalle'   => 'Fondos Compra QR #' . ($compra->numero_folio ?? $this->compraId),
            'ingreso'   => $this->totalCompra,
            'egreso'    => 0,
        ]);

        $this->finalizarCompra();
    }


    // ----------------------------------------------
    // Finalizar compra (llamada automaticamente desde terminarProcesado)
    // ----------------------------------------------

    public function finalizarCompra(): void
    {
        if (!$this->compraId) {
            $this->toast('error', 'No hay compra pendiente');
            return;
        }

        try {
            DB::beginTransaction();

            $compra = CompraModel::withoutGlobalScopes()->findOrFail($this->compraId);

            if ($compra->estado !== 'Pendiente') {
                DB::rollBack();
                $this->toast('error', 'La compra ya fue procesada');
                return;
            }

            $ultimo      = Movimiento::orderBy('id', 'desc')->first();
            $saldoActual = $ultimo ? (float)$ultimo->saldo : 0;
            $efectivo    = min($this->totalCompra, $saldoActual);
            $credito     = max(0, $this->totalCompra - $efectivo);

            $compra->update([
                'estado'     => 'Completo',
                'efectivo'   => $efectivo,
                'credito'    => $credito,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $items = CompraItem::where('compra_id', $compra->id)->with('producto')->get();

            foreach ($items as $item) {
                $producto = $item->producto;
                if (!$producto) continue;

                $cantidadTotal = $item->cantidad;

                if ((bool)($producto->control ?? false)) {
                    $stockAnterior   = $producto->stock;
                    $producto->increment('stock', $cantidadTotal);
                    $producto->refresh();

                    $precioActual    = (float)$producto->precio_de_compra;
                    $precioNuevo     = (float)$item->precio;
                    $stockTotal      = $stockAnterior + $cantidadTotal;
                    $precioPonderado = $stockTotal > 0
                        ? (($stockAnterior * $precioActual) + ($cantidadTotal * $precioNuevo)) / $stockTotal
                        : $precioNuevo;

                    if ($precioNuevo > $precioActual) {
                        $diffMayor       = ceil($producto->precio_por_mayor - $precioActual);
                        $mayCalc         = $precioPonderado + $diffMayor;
                        $mayFloor        = floor($mayCalc);
                        $nuevoMayor      = ($mayCalc - $mayFloor) <= 0.5 ? $mayFloor + 0.5 : ceil($mayCalc);

                        $cant            = $producto->cantidad ?? 1;
                        $diffMenor       = round((($producto->precio_por_menor * $cant) - $producto->precio_por_mayor) / $cant * 2) / 2;
                        $menCalc         = ($nuevoMayor / $cant) + $diffMenor;
                        $menFloor        = floor($menCalc);
                        $nuevoMenor      = ($menCalc - $menFloor) <= 0.5 ? $menFloor + 0.5 : ceil($menCalc);

                        $producto->update([
                            'precio_de_compra' => $precioPonderado,
                            'precio_por_mayor'  => $nuevoMayor,
                            'precio_por_menor'  => $nuevoMenor,
                        ]);
                    } else {
                        $producto->update(['precio_de_compra' => $precioPonderado]);
                    }

                    Kardex::create([
                        'tenant_id'   => currentTenantId(),
                        'user_id'     => Auth::id(),
                        'producto_id' => $producto->id,
                        'entrada'     => $cantidadTotal,
                        'salida'      => 0,
                        'anterior'    => $stockAnterior,
                        'saldo'       => $producto->stock,
                        'precio'      => $item->precio,
                        'total'       => round(((float)$item->precio / ($producto->cantidad ?: 1)) * $cantidadTotal, 2),
                        'obs'         => 'Compra #' . $compra->numero_folio . ' (QR Import)',
                    ]);
                } else {
                    Kardex::create([
                        'tenant_id'   => currentTenantId(),
                        'user_id'     => Auth::id(),
                        'producto_id' => $producto->id,
                        'entrada'     => $cantidadTotal,
                        'salida'      => 0,
                        'anterior'    => 0,
                        'saldo'       => 0,
                        'precio'      => $item->precio,
                        'total'       => round(((float)$item->precio / ($producto->cantidad ?: 1)) * $cantidadTotal, 2),
                        'obs'         => 'Compra #' . $compra->numero_folio . ' (QR Import)',
                    ]);
                }
            }

            if ($efectivo > 0) {
                $detalle = 'Compra #' . $compra->numero_folio . ' (QR Import)';
                if ($credito > 0) {
                    $detalle .= ' (Bs. ' . number_format($efectivo, 2) . ' ef. + Bs. ' . number_format($credito, 2) . ' cr.)';
                }
                Movimiento::create([
                    'tenant_id' => currentTenantId(),
                    'user_id'   => Auth::id(),
                    'detalle'   => $detalle,
                    'ingreso'   => 0,
                    'egreso'    => $efectivo,
                ]);
            }

            DB::commit();

            $this->abierto = false;
            $this->dispatch('qr-modal-cerrado');
            $this->dispatch('actualizar-lista-compras');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ImportarCompraQr finalizarCompra: ' . $e->getMessage());
            $this->toast('error', 'Error al finalizar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.importar-compra-qr');
    }
}
