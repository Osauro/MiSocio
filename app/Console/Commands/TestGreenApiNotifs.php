<?php

namespace App\Console\Commands;

use App\Models\Compra;
use App\Models\Inventario;
use App\Models\Prestamo;
use App\Models\TenantConfig;
use App\Models\Venta;
use App\Services\GreenApiService;
use Illuminate\Console\Command;

class TestGreenApiNotifs extends Command
{
    protected $signature = 'greenapi:test-notifs {tenant=1} {--connection=mysql}';
    protected $description = 'Envía mensajes de prueba al grupo WhatsApp con los últimos registros';

    public function handle(GreenApiService $api): int
    {
        $tenantId   = (int) $this->argument('tenant');
        $connection = $this->option('connection');

        // Cambiar conexión por defecto para que todos los modelos la usen
        \Illuminate\Support\Facades\DB::setDefaultConnection($connection);
        $config   = TenantConfig::getOrCreateForTenant($tenantId);

        $this->info("Tenant #{$tenantId}: " . ($config->nombre_tienda ?: 'Sin nombre'));
        $this->info("Grupo: " . ($config->greenapi_group_ventas ?: 'No configurado'));
        $this->info("Notif ventas activa: " . ($config->greenapi_notif_ventas ? 'SÍ' : 'NO'));
        $this->newLine();

        // ── Venta ──────────────────────────────────────────────────────────
        $venta = Venta::with(['ventaItems.producto', 'cliente', 'user'])
            ->where('tenant_id', $tenantId)
            ->where('estado', 'Completo')
            ->latest()
            ->first();

        if ($venta) {
            $this->info("Enviando venta #{$venta->numero_folio}...");
            $api->notifyVenta($venta, $config);
            $this->info("  ✓ notifyVenta enviado");
        } else {
            $this->warn("  Sin ventas completas");
        }

        // ── Compra ─────────────────────────────────────────────────────────
        $compra = Compra::with(['compraItems', 'proveedor', 'user'])
            ->where('tenant_id', $tenantId)
            ->where('estado', 'Completo')
            ->latest()
            ->first();

        if ($compra) {
            $this->info("Enviando compra #{$compra->numero_folio}...");
            $api->notifyCompra($compra, $config);
            $this->info("  ✓ notifyCompra enviado");
        } else {
            $this->warn("  Sin compras completas");
        }

        // ── Préstamo ───────────────────────────────────────────────────────
        $prestamo = Prestamo::with(['prestamoItems.producto', 'cliente', 'user'])
            ->where('tenant_id', $tenantId)
            ->whereIn('estado', ['Activo', 'Devuelto'])
            ->latest()
            ->first();

        if ($prestamo) {
            $this->info("Enviando préstamo #{$prestamo->numero_folio}...");
            $api->notifyNuevoPrestamo($prestamo, $config);
            $this->info("  ✓ notifyNuevoPrestamo enviado");
        } else {
            $this->warn("  Sin préstamos");
        }

        // ── Inventario ─────────────────────────────────────────────────────
        $inv = Inventario::withoutGlobalScopes()
            ->with('user')
            ->where('tenant_id', $tenantId)
            ->where('estado', 'Completo')
            ->latest()
            ->first();

        if ($inv) {
            $this->info("Enviando inventario #{$inv->numero_folio}...");
            $invItems = \App\Models\InventarioItem::withoutGlobalScopes()
                ->with('producto')
                ->where('inventario_id', $inv->id)
                ->get()
                ->map(fn($i) => [
                    'nombre'        => optional($i->producto)->nombre ?? 'Producto',
                    'stock_sistema' => $i->stock_sistema,
                    'stock_contado' => $i->stock_contado,
                ])->toArray();
            $api->notifyInventario($inv, $config, $invItems);
            $this->info("  ✓ notifyInventario enviado");
        } else {
            $this->warn("  Sin inventarios completos");
        }

        // ── Venta cancelada (simulación) ───────────────────────────────────
        $ventaCancelada = Venta::with(['user', 'ventaItems.producto'])
            ->where('tenant_id', $tenantId)
            ->where('estado', 'Eliminado')
            ->latest()
            ->first();

        if ($ventaCancelada) {
            $cajero = optional($ventaCancelada->user)->name ?? '-';
            $tienda = $config->nombre_tienda ?: 'Tu tienda';
            $total  = $ventaCancelada->efectivo + $ventaCancelada->online + $ventaCancelada->credito;
            $dest   = $api->groupPhone($config);
            if ($dest) {
                $saldo  = $api->getSaldoCajaLine($config->tenant_id);
                $lineas = '';
                foreach ($ventaCancelada->ventaItems as $item) {
                    $nombre  = optional($item->producto)->nombre ?? 'Producto';
                    $cant    = $item->cantidad_formateada ?? $item->cantidad;
                    $sub     = number_format((float) $item->subtotal, 2);
                    $lineas .= "  • {$cant} {$nombre} — Bs. {$sub}\n";
                }
                $msg = "🚫 *Venta cancelada - {$tienda}*\n"
                     . "Folio: #{$ventaCancelada->numero_folio}\n"
                     . "Cajero: {$cajero}";
                if ($lineas) {
                    $msg .= "\n\n" . rtrim($lineas);
                }
                $msg .= "\n*Total: Bs. " . number_format($total, 2) . "*" . $saldo;
                $api->sendMessage($dest, $msg);
                $this->info("  ✓ notificación venta cancelada enviada (#{$ventaCancelada->numero_folio})");
            }
        } else {
            $this->warn("  Sin ventas canceladas");
        }

        $this->newLine();
        $this->info('Prueba completada.');
        return Command::SUCCESS;
    }
}
