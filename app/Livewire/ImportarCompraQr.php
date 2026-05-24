<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Compra as CompraModel;
use App\Models\CompraItem;
use App\Models\Kardex;
use App\Models\Medida;
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

    // Modal / estado
    public bool $abierto = false;
    public string $fase = 'scanner'; // scanner | procesando | fondos | resumen

    // URL escaneada
    public string $urlEscaneada = '';
    public string $errorUrl = '';

    // Log de progreso
    public array $logProceso = [];

    // Datos del JSON
    public array $jsonData = [];

    // Compra creada
    public ?int $compraId = null;
    public float $totalCompra = 0;

    // Fondos
    public float $saldoCaja = 0;
    public float $montoAñadir = 0;
    public string $errorFondos = '';

    // Resumen final
    public int $productosCreados = 0;
    public int $productosBuscados = 0;

    // ──────────────────────────────────────────────
    // Abrir / cerrar
    // ──────────────────────────────────────────────

    public function abrir(): void
    {
        $this->reset([
            'urlEscaneada', 'errorUrl', 'logProceso', 'jsonData',
            'compraId', 'totalCompra', 'saldoCaja', 'montoAñadir',
            'errorFondos', 'productosCreados', 'productosBuscados',
        ]);
        $this->fase = 'scanner';
        $this->abierto = true;
        $this->dispatch('qr-modal-abierto');
    }

    public function cerrar(): void
    {
        $this->abierto = false;
        $this->dispatch('qr-modal-cerrado');
    }

    // ──────────────────────────────────────────────
    // Recibir URL desde el escáner JS
    // ──────────────────────────────────────────────

    public function procesarUrl(string $url): void
    {
        $this->urlEscaneada = $url;
        $this->errorUrl = '';
        $this->fase = 'procesando';
        $this->logProceso = [];
        $this->productosCreados = 0;
        $this->productosBuscados = 0;

        // Fetch del JSON
        try {
            $this->agregarLog('info', "Conectando con: {$url}");
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                $this->errorUrl = "Error HTTP {$response->status()} al obtener el JSON";
                $this->fase = 'scanner';
                return;
            }

            $data = $response->json();

            if (empty($data['productos'])) {
                $this->errorUrl = 'El JSON no contiene productos';
                $this->fase = 'scanner';
                return;
            }

            $this->jsonData = $data;
            $this->agregarLog('success', 'JSON recibido: ' . count($data['productos']) . ' productos');

        } catch (\Exception $e) {
            $this->errorUrl = 'Error al conectar: ' . $e->getMessage();
            $this->fase = 'scanner';
            return;
        }

        // Procesar productos y crear compra
        $this->procesarImportacion();
    }

    // ──────────────────────────────────────────────
    // Lógica principal de importación
    // ──────────────────────────────────────────────

    private function procesarImportacion(): void
    {
        try {
            DB::beginTransaction();

            // 1. Crear compra en estado Pendiente
            $compra = CompraModel::create([
                'tenant_id'  => currentTenantId(),
                'user_id'    => Auth::id(),
                'estado'     => 'Pendiente',
                'efectivo'   => 0,
                'credito'    => 0,
            ]);

            $this->compraId    = $compra->id;
            $this->agregarLog('info', "Compra #{$compra->numero_folio} creada");

            $totalCompra = 0;

            foreach ($this->jsonData['productos'] as $productoJson) {
                $this->productosBuscados++;
                $nombre     = $productoJson['nombre']    ?? '';
                $categoria  = $productoJson['categoria'] ?? 'General';
                $medidaNombre = $productoJson['medida']  ?? 'Unidad';
                $cantidad   = (int)   ($productoJson['cantidad']  ?? 1);
                $unidades   = (int)   ($productoJson['unidades']  ?? $cantidad);
                $precio     = (float) ($productoJson['precio']    ?? 0);
                $subtotal   = (float) ($productoJson['subtotal']  ?? ($precio * ($unidades / max($cantidad, 1))));

                if (empty($nombre)) {
                    $this->agregarLog('warning', 'Producto sin nombre, omitido');
                    continue;
                }

                // 2. Buscar o crear el producto
                $producto = $this->buscarOCrearProducto(
                    $nombre, $categoria, $medidaNombre, $cantidad, $precio
                );

                // 3. Crear item
                CompraItem::create([
                    'compra_id'   => $compra->id,
                    'producto_id' => $producto->id,
                    'cantidad'    => $unidades,
                    'precio'      => $precio,
                    'subtotal'    => $subtotal,
                ]);

                $totalCompra += $subtotal;
                $this->agregarLog('success', "Item agregado: {$nombre} x{$unidades} = Bs. " . number_format($subtotal, 2));
            }

            // 4. Actualizar total de la compra
            $compra->update(['efectivo' => $totalCompra]);
            $this->totalCompra = $totalCompra;

            DB::commit();

            $this->agregarLog('success', 'Total: Bs. ' . number_format($totalCompra, 2));
            $this->agregarLog('info', 'Verificando fondos en caja...');

            // 5. Verificar saldo de caja
            $ultimoMovimiento = Movimiento::orderBy('id', 'desc')->first();
            $this->saldoCaja  = $ultimoMovimiento ? (float)$ultimoMovimiento->saldo : 0;

            if ($this->saldoCaja < $totalCompra) {
                $faltante = $totalCompra - $this->saldoCaja;
                $this->agregarLog('warning', 'Saldo insuficiente. Faltan Bs. ' . number_format($faltante, 2));
                $this->montoAñadir = round($faltante, 2);
                $this->fase = 'fondos';
            } else {
                $this->fase = 'confirmar';
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ImportarCompraQR error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->agregarLog('error', 'Error: ' . $e->getMessage());
            // Si se creó la compra pendiente, eliminarla
            if ($this->compraId) {
                CompraModel::withoutGlobalScopes()->where('id', $this->compraId)->delete();
                $this->compraId = null;
            }
        }
    }

    private function buscarOCrearProducto(
        string $nombre,
        string $categoriaNombre,
        string $medidaNombre,
        int $cantidad,
        float $precio
    ): Producto {
        // Buscar producto existente por nombre (case-insensitive)
        $producto = Producto::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])->first();

        if ($producto) {
            $this->agregarLog('info', "Encontrado: {$nombre}");
            return $producto;
        }

        // No existe → crear
        $this->agregarLog('warning', "Creando producto: {$nombre}");
        $this->productosCreados++;

        // Buscar o crear categoría
        $categoria = Categoria::whereRaw('LOWER(nombre) = ?', [mb_strtolower($categoriaNombre)])->first();
        if (!$categoria) {
            $categoria = Categoria::create([
                'tenant_id' => currentTenantId(),
                'nombre'    => $categoriaNombre,
            ]);
            $this->agregarLog('info', "Categoría creada: {$categoriaNombre}");
        }

        // Calcular precios de venta
        $precioPorMayor = ceil($precio * 1.10); // +10%, redondeado hacia arriba
        $precioPorMenor = ceil(($precioPorMayor / max($cantidad, 1)) * 1.05 * 2) / 2; // +5%, redondeo a 0.5

        $producto = Producto::create([
            'tenant_id'        => currentTenantId(),
            'categoria_id'     => $categoria->id,
            'nombre'           => $nombre,
            'codigo'           => '',
            'medida'           => $medidaNombre,
            'cantidad'         => $cantidad,
            'precio_de_compra' => $precio,
            'precio_por_mayor' => $precioPorMayor,
            'precio_por_menor' => $precioPorMenor,
            'stock'            => 0,
            'control'          => true,
        ]);

        $this->agregarLog('success', "Producto creado: {$nombre} | Mayor: Bs.{$precioPorMayor} | Menor: Bs.{$precioPorMenor}");

        return $producto;
    }

    // ──────────────────────────────────────────────
    // Paso: Añadir fondos
    // ──────────────────────────────────────────────

    public function añadirFondosYContinuar(): void
    {
        if ($this->montoAñadir <= 0) {
            $this->errorFondos = 'El monto debe ser mayor a 0';
            return;
        }

        try {
            $compra = CompraModel::withoutGlobalScopes()->find($this->compraId);

            Movimiento::create([
                'tenant_id' => currentTenantId(),
                'user_id'   => Auth::id(),
                'detalle'   => 'Aporte de fondos para Compra #' . ($compra->numero_folio ?? $this->compraId),
                'ingreso'   => $this->montoAñadir,
                'egreso'    => 0,
            ]);

            // Recalcular saldo
            $ultimo = Movimiento::orderBy('id', 'desc')->first();
            $this->saldoCaja = $ultimo ? (float)$ultimo->saldo : 0;

            $this->agregarLog('success', 'Fondos añadidos: Bs. ' . number_format($this->montoAñadir, 2));
            $this->errorFondos = '';

            if ($this->saldoCaja >= $this->totalCompra) {
                $this->fase = 'confirmar';
            } else {
                $faltante = $this->totalCompra - $this->saldoCaja;
                $this->errorFondos = 'Aún faltan Bs. ' . number_format($faltante, 2);
                $this->montoAñadir = round($faltante, 2);
            }

        } catch (\Exception $e) {
            $this->errorFondos = 'Error al añadir fondos: ' . $e->getMessage();
        }
    }

    public function omitirFondos(): void
    {
        // Avanzar igualmente (pago parcial → crédito = diferencia)
        $this->fase = 'confirmar';
    }

    // ──────────────────────────────────────────────
    // Finalizar compra
    // ──────────────────────────────────────────────

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

            // Refrescar saldo
            $ultimo = Movimiento::orderBy('id', 'desc')->first();
            $saldoActual = $ultimo ? (float)$ultimo->saldo : 0;

            $efectivo = min($this->totalCompra, $saldoActual);
            $credito  = max(0, $this->totalCompra - $efectivo);

            // Actualizar la compra
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
                    $stockAnterior = $producto->stock;
                    $producto->increment('stock', $cantidadTotal);
                    $producto->refresh();

                    // Precio promedio ponderado
                    $precioCompraActual = (float)$producto->precio_de_compra;
                    $precioNuevo = (float)$item->precio;
                    $valorAnterior = $stockAnterior * $precioCompraActual;
                    $valorNuevo = $cantidadTotal * $precioNuevo;
                    $stockTotal = $stockAnterior + $cantidadTotal;
                    $precioPonderado = $stockTotal > 0 ? ($valorAnterior + $valorNuevo) / $stockTotal : $precioNuevo;

                    if ($precioNuevo > $precioCompraActual) {
                        $diffMayor = ceil($producto->precio_por_mayor - $precioCompraActual);
                        $precioMayorCalc = $precioPonderado + $diffMayor;
                        $parteEntera = floor($precioMayorCalc);
                        $parteDecimal = $precioMayorCalc - $parteEntera;
                        $nuevoPrecioMayor = $parteDecimal <= 0.5 ? $parteEntera + 0.5 : ceil($precioMayorCalc);

                        $cant = $producto->cantidad ?? 1;
                        $diffMenor = round((($producto->precio_por_menor * $cant) - $producto->precio_por_mayor) / $cant * 2) / 2;
                        $precioMenorCalc = ($nuevoPrecioMayor / $cant) + $diffMenor;
                        $parteEnteraMenor = floor($precioMenorCalc);
                        $parteDecimalMenor = $precioMenorCalc - $parteEnteraMenor;
                        $nuevoPrecioMenor = $parteDecimalMenor <= 0.5 ? $parteEnteraMenor + 0.5 : ceil($precioMenorCalc);

                        $producto->update([
                            'precio_de_compra' => $precioPonderado,
                            'precio_por_mayor'  => $nuevoPrecioMayor,
                            'precio_por_menor'  => $nuevoPrecioMenor,
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

            // Registrar egreso en caja
            if ($efectivo > 0) {
                $detalle = 'Compra #' . $compra->numero_folio . ' (QR Import)';
                if ($credito > 0) {
                    $detalle .= ' (Bs. ' . number_format($efectivo, 2) . ' efectivo + Bs. ' . number_format($credito, 2) . ' crédito)';
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

            $this->fase = 'resumen';
            $this->agregarLog('success', '¡Compra #' . $compra->numero_folio . ' finalizada!');
            $this->dispatch('compra-qr-completada');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ImportarCompraQR finalizarCompra: ' . $e->getMessage());
            $this->toast('error', 'Error al finalizar: ' . $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────
    // Ir al detalle de la compra creada
    // ──────────────────────────────────────────────

    public function irACompra(): void
    {
        if ($this->compraId) {
            $this->cerrar();
            $this->dispatch('actualizar-lista-compras');
        }
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    private function agregarLog(string $tipo, string $mensaje): void
    {
        $this->logProceso[] = ['tipo' => $tipo, 'mensaje' => $mensaje];
        // Emitir scroll al final del log vía JS
        $this->dispatch('log-actualizado');
    }

    public function render()
    {
        return view('livewire.importar-compra-qr');
    }
}
