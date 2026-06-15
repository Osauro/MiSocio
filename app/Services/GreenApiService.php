<?php

namespace App\Services;

use App\Models\Membresia;
use App\Models\Tenant;
use App\Models\TenantConfig;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GreenApiService
{
    private string $instanceId;
    private string $apiToken;
    private string $baseUrl;
    private string $landlordPhone;

    public function __construct()
    {
        $this->instanceId    = (string) config('greenapi.instance_id', '');
        $this->apiToken      = (string) config('greenapi.api_token', '');
        $this->baseUrl       = (string) config('greenapi.base_url', 'https://api.green-api.com');
        $this->landlordPhone = (string) config('greenapi.landlord_phone', '');
    }

    // ── Core ────────────────────────────────────────────────────────────────

    /**
     * Send a WhatsApp message via Green API.
     *
     * @param  string  $phone   Phone number completo con código de país (e.g. 59173010688)
     * @param  string  $message
     */
    /**
     * Resuelve un chatId válido para Green API.
     * Acepta un número de teléfono ("59173010688") o un chatId completo ("120363@g.us").
     */
    private function resolveChatId(string $phoneOrChatId): string
    {
        if (str_contains($phoneOrChatId, '@')) {
            return $phoneOrChatId;
        }
        return preg_replace('/\D/', '', $phoneOrChatId) . '@c.us';
    }

    public function sendMessage(string $phone, string $message): bool
    {
        $chatId = $this->resolveChatId($phone);
        $bare   = str_replace(['@c.us', '@g.us'], '', $chatId);

        if (empty($this->instanceId) || empty($this->apiToken) || empty($bare)) {
            return false;
        }

        $url = "{$this->baseUrl}/waInstance{$this->instanceId}/sendMessage/{$this->apiToken}";

        try {
            $response = Http::timeout(10)->post($url, [
                'chatId'  => $chatId,
                'message' => $message,
            ]);

            if (!$response->successful()) {
                Log::warning('GreenAPI: mensaje no enviado', [
                    'chatId' => $chatId,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('GreenAPI: error al enviar mensaje', [
                'chatId' => $chatId,
                'error'  => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Obtiene los grupos de WhatsApp a los que pertenece la instancia.
     * Llama a getChats y filtra los que terminan en @g.us.
     *
     * @return array<int, array{id: string, name: string}>
     */
    public function getChats(): array
    {
        if (empty($this->instanceId) || empty($this->apiToken)) {
            return [];
        }

        $url = "{$this->baseUrl}/waInstance{$this->instanceId}/getChats/{$this->apiToken}";

        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                Log::warning('GreenAPI: getChats falló', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return [];
            }

            $chats = $response->json();
            if (!is_array($chats)) {
                return [];
            }

            return collect($chats)
                ->filter(fn($c) => str_ends_with((string) ($c['id'] ?? ''), '@g.us'))
                ->map(fn($c) => [
                    'id'   => (string) $c['id'],
                    'name' => (string) ($c['name'] ?? $c['id']),
                ])
                ->values()
                ->toArray();
        } catch (\Throwable $e) {
            Log::error('GreenAPI: error al obtener chats', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ── Landlord notifications ───────────────────────────────────────────────

    /**
     * Notify landlord about a new tenant registration (pending payment).
     */
    public function notifyNuevoTenant(Tenant $tenant, User $user): void
    {
        if (empty($this->landlordPhone)) return;

        $msg = "🆕 *Nuevo tenant registrado*\n"
            . "Negocio: {$tenant->name}\n"
            . "Usuario: {$user->name} ({$user->celular})\n"
            . "Plan: {$tenant->subscription_type}\n"
            . "Estado: Pendiente de verificación";

        $this->sendMessage($this->landlordPhone, $msg);
    }

    /**
     * Notify landlord that a payment was verified.
     */
    public function notifyPagoVerificadoLandlord(Tenant $tenant, Membresia $pago): void
    {
        if (empty($this->landlordPhone)) return;

        $fechaFin = $pago->fecha_fin ? $pago->fecha_fin->format('d/m/Y') : '-';

        $msg = "✅ *Pago verificado*\n"
            . "Negocio: {$tenant->name}\n"
            . "Plan: {$pago->plan_nombre}\n"
            . "Monto: Bs. " . number_format((float) $pago->monto, 2) . "\n"
            . "Vence: {$fechaFin}";

        $this->sendMessage($this->landlordPhone, $msg);
    }

    // ── Tenant notifications ─────────────────────────────────────────────────

    /**
     * Notify tenant admin that their subscription was activated/renewed.
     */
    public function notifyTenantActivado(Tenant $tenant, Membresia $pago): void
    {
        $admin = $tenant->users()->wherePivot('role', 'tenant')->first();
        if (!$admin || empty($admin->celular)) return;

        $fechaFin = $pago->fecha_fin ? $pago->fecha_fin->format('d/m/Y') : '-';

        $msg = "✅ *Tu suscripción en MiSocio ha sido activada*\n"
            . "Negocio: {$tenant->name}\n"
            . "Plan: {$pago->plan_nombre}\n"
            . "Válido hasta: {$fechaFin}\n"
            . "¡Ya puedes acceder a tu tienda!";

        $this->sendMessage($admin->celular, $msg);
    }

    /**
     * Notify tenant admin (and landlord) that their subscription is expiring soon.
     */
    public function notifyTenantPorVencer(Tenant $tenant, int $diasRestantes): void
    {
        $admin = $tenant->users()->wherePivot('role', 'tenant')->first();
        $fechaVence = $tenant->bill_date ? $tenant->bill_date->format('d/m/Y') : '-';

        if ($admin && !empty($admin->celular)) {
            $msg = "⚠️ *Tu suscripción en MiSocio vence pronto*\n"
                . "Negocio: {$tenant->name}\n"
                . "Vence el: {$fechaVence}\n"
                . "Días restantes: {$diasRestantes}\n"
                . "Por favor, renueva tu plan para continuar usando el sistema.";

            $this->sendMessage($admin->celular, $msg);
        }

        if (!empty($this->landlordPhone)) {
            $landlordMsg = "⏰ *Tenant por vencer*\n"
                . "Negocio: {$tenant->name}\n"
                . "Vence: {$fechaVence} ({$diasRestantes} días)";

            $this->sendMessage($this->landlordPhone, $landlordMsg);
        }
    }

    /**
     * Notify user that their password was changed.
     */
    public function notifyPasswordCambiado(User $user): void
    {
        if (empty($user->celular)) return;

        $fecha = now()->format('d/m/Y H:i');

        $msg = "🔑 *Contraseña actualizada - MiSocio*\n"
            . "Usuario: {$user->name}\n"
            . "Fecha: {$fecha}\n"
            . "Si no realizaste este cambio, contacta al administrador.";

        $this->sendMessage($user->celular, $msg);
    }

    /**
     * Notify tenant admin about a completed sale (if enabled in config).
     */
    public function notifyVenta(Venta $venta, TenantConfig $config): void
    {
        // Usar grupo configurado o caer al celular del propietario
        if (!empty($config->greenapi_group_ventas)) {
            $phone = $config->greenapi_group_ventas; // chatId del grupo (ej. 120363@g.us)
        } elseif (!empty($config->propietario_celular)) {
            $prefijo = preg_replace('/\D/', '', $config->propietario_celular_prefijo ?? '591');
            $numero  = preg_replace('/\D/', '', $config->propietario_celular);
            $phone   = $prefijo . $numero;
        } else {
            return;
        }

        $total   = (float) ($venta->efectivo ?? 0)
                 + (float) ($venta->online   ?? 0)
                 + (float) ($venta->credito  ?? 0);
        $tienda  = $config->nombre_tienda ?: 'Tu tienda';
        $cajero  = optional($venta->user)->name ?? '-';
        $cliente = optional($venta->cliente)->nombre;

        $msg = "🛒 *Nueva venta - {$tienda}*\n"
            . "Folio: #{$venta->numero_folio}\n"
            . "Total: Bs. " . number_format($total, 2) . "\n"
            . "Cajero: {$cajero}";

        if ($cliente) {
            $msg .= "\nCliente: {$cliente}";
        }

        $this->sendMessage($phone, $msg);
    }

    /**
     * Notifica al cliente cuando se registra una venta a crédito.
     */
    public function notifyVentaCredito(Venta $venta, TenantConfig $config): bool
    {
        $cliente = $venta->cliente;
        if (!$cliente || empty($cliente->celular)) return false;

        $prefijo = preg_replace('/\D/', '', $config->propietario_celular_prefijo ?? '591');
        $phone   = $prefijo . preg_replace('/\D/', '', $cliente->celular);
        $tienda  = $config->nombre_tienda ?: 'Tu proveedor';
        $total   = (float) ($venta->efectivo ?? 0)
                 + (float) ($venta->online   ?? 0)
                 + (float) ($venta->credito  ?? 0);
        $credito = (float) ($venta->credito ?? 0);
        $fecha   = $venta->fecha ? \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') : now()->format('d/m/Y');

        $msg = "\uD83D\uDED2 *Venta a crédito - {$tienda}*\n"
            . "Hola {$cliente->nombre},\n\n"
            . "Se registró una venta a tu cuenta:\n"
            . "Folio: #{$venta->numero_folio}\n"
            . "Fecha: {$fecha}\n"
            . "Total: Bs. " . number_format($total, 2) . "\n"
            . "Saldo pendiente: *Bs. " . number_format($credito, 2) . "*\n\n"
            . "Por favor, acércate a cancelar tu deuda. ¡Gracias!";

        return $this->sendMessage($phone, $msg);
    }

    /**
     * Notifica al cliente cuando realiza un pago de su crédito.
     */
    public function notifyPagoCredito(Venta $venta, float $montoPagado, float $saldoPendiente, TenantConfig $config): bool
    {
        $cliente = $venta->cliente;
        if (!$cliente || empty($cliente->celular)) return false;

        $prefijo = preg_replace('/\D/', '', $config->propietario_celular_prefijo ?? '591');
        $phone   = $prefijo . preg_replace('/\D/', '', $cliente->celular);
        $tienda  = $config->nombre_tienda ?: 'Tu proveedor';

        if ($saldoPendiente > 0) {
            $msg = "\u2705 *Pago de crédito recibido - {$tienda}*\n"
                . "Hola {$cliente->nombre},\n\n"
                . "Recibimos tu pago de la venta #{$venta->numero_folio}:\n"
                . "Monto pagado: Bs. " . number_format($montoPagado, 2) . "\n"
                . "Saldo pendiente: *Bs. " . number_format($saldoPendiente, 2) . "*\n\n"
                . "¡Gracias por tu pago!";
        } else {
            $msg = "\u2705 *Deuda cancelada - {$tienda}*\n"
                . "Hola {$cliente->nombre},\n\n"
                . "Tu deuda de la venta #{$venta->numero_folio} ha sido cancelada completamente.\n"
                . "Monto pagado: Bs. " . number_format($montoPagado, 2) . "\n\n"
                . "¡Muchas gracias!";
        }

        return $this->sendMessage($phone, $msg);
    }

    /**
     * Envía credenciales de acceso a un nuevo usuario recién creado.
     */
    public function notifyNuevoUsuario(User $user, string $pin, TenantConfig $config): bool
    {
        if (empty($user->celular)) return false;

        $tienda  = $config->nombre_tienda ?: 'MiSocio';
        $prefijo = preg_replace('/\D/', '', $config->propietario_celular_prefijo ?? '591');
        $url     = 'https://misocio.bo';

        $msg = "\u{1F44B} *Bienvenido a {$tienda}*\n"
            . "Hola {$user->name}, tu cuenta ha sido creada.\n\n"
            . "\u{1F4F1} *Tus credenciales de acceso:*\n"
            . "Celular: {$user->celular}\n"
            . "PIN: *{$pin}*\n\n"
            . "\u{1F310} Ingresa en: {$url}";

        return $this->sendMessage($prefijo . $user->celular, $msg);
    }

    /**
     * Envía un PIN reseteado al usuario por WhatsApp.
     */
    public function notifyResetPin(User $user, string $pin, TenantConfig $config): bool
    {
        if (empty($user->celular)) return false;

        $tienda  = $config->nombre_tienda ?: 'MiSocio';
        $prefijo = preg_replace('/\D/', '', $config->propietario_celular_prefijo ?? '591');
        $url     = 'https://misocio.bo';

        $msg = "\u{1F511} *Tu PIN ha sido reseteado - {$tienda}*\n"
            . "Hola {$user->name},\n\n"
            . "*Tus nuevas credenciales:*\n"
            . "Celular: {$user->celular}\n"
            . "PIN: *{$pin}*\n\n"
            . "\u{1F310} Ingresa en: {$url}";

        return $this->sendMessage($prefijo . $user->celular, $msg);
    }

    // ── Imagen / archivo ─────────────────────────────────────────────────────

    /**
     * Envía un archivo (imagen, PDF, etc.) vía Green API usando multipart upload.
     *
     * @param  string  $phone     Número con prefijo (e.g. 59173010688)
     * @param  string  $filePath  Ruta absoluta al archivo local
     * @param  string  $fileName  Nombre del archivo con extensión (e.g. ticket.png)
     * @param  string  $caption   Leyenda opcional (máx. 1024 chars)
     */
    public function sendFileByUpload(string $phone, string $filePath, string $fileName, string $caption = ''): bool
    {
        $chatId = $this->resolveChatId($phone);
        $bare   = str_replace(['@c.us', '@g.us'], '', $chatId);

        if (empty($this->instanceId) || empty($this->apiToken) || empty($bare)) {
            return false;
        }

        $url = "{$this->baseUrl}/waInstance{$this->instanceId}/sendFileByUpload/{$this->apiToken}";

        try {
            $response = Http::timeout(30)->attach(
                'file',
                file_get_contents($filePath),
                $fileName,
                ['Content-Type' => 'image/png']
            )->post($url, [
                'chatId'  => $chatId,
                'caption' => mb_substr($caption, 0, 1024),
            ]);

            if (!$response->successful()) {
                Log::warning('GreenAPI: archivo no enviado', [
                    'chatId' => $chatId,
                    'file'   => $fileName,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('GreenAPI: error al enviar archivo', [
                'chatId' => $chatId,
                'error'  => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Genera un ticket de venta como imagen PNG y lo envía por WhatsApp
     * al número del propietario configurado en TenantConfig.
     */
    public function sendVentaImagen(Venta $venta, TenantConfig $config): bool
    {
        // Usar grupo configurado o caer al celular del propietario
        if (!empty($config->greenapi_group_ventas)) {
            $phone = $config->greenapi_group_ventas;
        } elseif (!empty($config->propietario_celular)) {
            $prefijo = preg_replace('/\D/', '', $config->propietario_celular_prefijo ?? '591');
            $numero  = preg_replace('/\D/', '', $config->propietario_celular);
            $phone   = $prefijo . $numero;
        } else {
            return false;
        }

        $venta->loadMissing(['ventaItems.producto', 'cliente', 'user']);

        /** @var TicketImageService $ticketService */
        $ticketService = app(TicketImageService::class);
        $png           = $ticketService->generarTicketVenta($venta, $config);

        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "ticket_venta_{$venta->id}_" . time() . '.png';
        file_put_contents($tmpPath, $png);

        $total   = (float) ($venta->efectivo ?? 0)
                 + (float) ($venta->online   ?? 0)
                 + (float) ($venta->credito  ?? 0);
        $tienda  = $config->nombre_tienda ?: 'Tu tienda';
        $cajero  = optional($venta->user)->name ?? '-';
        $cliente = optional($venta->cliente)->nombre ?? '';

        $caption = "🛒 *Nueva venta - {$tienda}*\n"
            . "Folio: #{$venta->numero_folio}  |  Total: Bs. " . number_format($total, 2) . "\n"
            . "Cajero: {$cajero}"
            . ($cliente ? "\nCliente: {$cliente}" : '');

        try {
            $result = $this->sendFileByUpload($phone, $tmpPath, "ticket_{$venta->numero_folio}.png", $caption);
        } finally {
            @unlink($tmpPath);
        }

        return $result;
    }

    /**
     * Genera el ticket de la venta y lo envía al CLIENTE como recordatorio de deuda pendiente.
     */
    public function sendRecordatorioCredito(Venta $venta, TenantConfig $config): bool
    {
        $cliente = $venta->cliente;
        if (!$cliente || empty($cliente->celular)) return false;

        $prefijo = preg_replace('/\D/', '', $config->propietario_celular_prefijo ?? '591');
        $phone   = $prefijo . preg_replace('/\D/', '', $cliente->celular);
        $tienda  = $config->nombre_tienda ?: 'Tu proveedor';
        $credito = (float) ($venta->credito ?? 0);

        $venta->loadMissing(['ventaItems.producto', 'user']);

        /** @var TicketImageService $ticketService */
        $ticketService = app(TicketImageService::class);
        $png           = $ticketService->generarTicketVenta($venta, $config);

        $tmpPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "ticket_recordatorio_{$venta->id}_" . time() . '.png';
        file_put_contents($tmpPath, $png);

        $caption = "⏰ *Recordatorio de deuda - {$tienda}*\n"
            . "Hola {$cliente->nombre}, te recordamos que tienes una deuda pendiente:\n"
            . "Venta: #{$venta->numero_folio}\n"
            . "Saldo pendiente: *Bs. " . number_format($credito, 2) . "*\n\n"
            . "Por favor, acércate a cancelar tu deuda. ¡Gracias!";

        try {
            $result = $this->sendFileByUpload($phone, $tmpPath, "ticket_{$venta->numero_folio}.png", $caption);
        } finally {
            @unlink($tmpPath);
        }

        return $result;
    }

    /**
     * Notifica al cliente cuando se crea un nuevo préstamo.
     */
    public function notifyNuevoPrestamo(\App\Models\Prestamo $prestamo, TenantConfig $config): bool
    {
        $cliente = $prestamo->cliente;
        if (!$cliente || empty($cliente->celular)) return false;

        $prefijo = preg_replace('/\D/', '', $config->propietario_celular_prefijo ?? '591');
        $phone   = $prefijo . preg_replace('/\D/', '', $cliente->celular);
        $tienda  = $config->nombre_tienda ?: 'Tu proveedor';

        $prestamo->loadMissing(['prestamoItems.producto']);

        $lineasItems = '';
        foreach ($prestamo->prestamoItems as $item) {
            $nombre = $item->producto?->nombre ?? 'Artículo';
            $lineasItems .= "  • {$nombre} x{$item->cantidad} — Bs. " . number_format($item->subtotal, 2) . "\n";
        }

        $vencimiento = $prestamo->expired_at
            ? $prestamo->expired_at->format('d/m/Y')
            : 'Sin fecha';

        // Link de ubicación
        $ubicacion = '';
        if (!empty($config->latitud) && !empty($config->longitud)) {
            $lat = $config->latitud;
            $lng = $config->longitud;
            $ubicacion = "\n📍 *Ubicación de la tienda:*\nhttps://www.google.com/maps?q={$lat},{$lng}";
        } elseif (!empty($config->direccion)) {
            $ubicacion = "\n📍 *Dirección:* {$config->direccion}";
        }

        $mensaje = "📦 *Nuevo préstamo registrado - {$tienda}*\n"
            . "Hola *{$cliente->nombre}*, se registró un préstamo a tu nombre:\n\n"
            . "*Folio:* #{$prestamo->numero_folio}\n"
            . "*Fecha:* " . now()->format('d/m/Y') . "\n"
            . "*Vence:* {$vencimiento}\n\n"
            . "*Artículos prestados:*\n{$lineasItems}"
            . "*Depósito/Garantía:* Bs. " . number_format($prestamo->total, 2) . "\n"
            . $ubicacion . "\n\n"
            . "Por favor devuelve los artículos antes de la fecha de vencimiento. ¡Gracias!";

        return $this->sendMessage($phone, $mensaje);
    }

    /**
     * Notifica al cliente cuando devuelve el préstamo.
     */
    public function notifyDevolucionPrestamo(\App\Models\Prestamo $prestamo, TenantConfig $config): bool
    {
        $cliente = $prestamo->cliente;
        if (!$cliente || empty($cliente->celular)) return false;

        $prefijo = preg_replace('/\D/', '', $config->propietario_celular_prefijo ?? '591');
        $phone   = $prefijo . preg_replace('/\D/', '', $cliente->celular);
        $tienda  = $config->nombre_tienda ?: 'Tu proveedor';

        $mensaje = "✅ *Devolución registrada - {$tienda}*\n"
            . "Hola *{$cliente->nombre}*, confirmamos que devolviste correctamente el préstamo:\n\n"
            . "*Folio:* #{$prestamo->numero_folio}\n"
            . "*Fecha de devolución:* " . now()->format('d/m/Y') . "\n"
            . "*Depósito devuelto:* Bs. " . number_format($prestamo->total, 2) . "\n\n"
            . "¡Gracias por tu puntualidad! Vuelve cuando necesites. 😊";

        return $this->sendMessage($phone, $mensaje);
    }

    /**
     * Notifica al cliente sobre vencimiento próximo o vencido del préstamo.
     *
     * @param bool $manana true = aviso 1 día antes, false = aviso de vencimiento hoy
     */
    public function notifyVencimientoPrestamo(\App\Models\Prestamo $prestamo, TenantConfig $config, bool $manana = false): bool
    {
        $cliente = $prestamo->cliente;
        if (!$cliente || empty($cliente->celular)) return false;

        $prefijo = preg_replace('/\D/', '', $config->propietario_celular_prefijo ?? '591');
        $phone   = $prefijo . preg_replace('/\D/', '', $cliente->celular);
        $tienda  = $config->nombre_tienda ?: 'Tu proveedor';

        $vencimiento = $prestamo->expired_at
            ? $prestamo->expired_at->format('d/m/Y')
            : 'Sin fecha';

        $ubicacion = '';
        if (!empty($config->latitud) && !empty($config->longitud)) {
            $lat = $config->latitud;
            $lng = $config->longitud;
            $ubicacion = "\n📍 *Ubicación de la tienda:*\nhttps://www.google.com/maps?q={$lat},{$lng}";
        } elseif (!empty($config->direccion)) {
            $ubicacion = "\n📍 *Dirección:* {$config->direccion}";
        }

        if ($manana) {
            $mensaje = "⚠️ *Préstamo por vencer mañana - {$tienda}*\n"
                . "Hola *{$cliente->nombre}*, tu préstamo *#{$prestamo->numero_folio}* vence *mañana {$vencimiento}*.\n\n"
                . "Por favor acércate a devolver los artículos. Depósito en garantía: Bs. " . number_format($prestamo->total, 2) . "."
                . $ubicacion . "\n\n"
                . "¡Gracias por tu atención!";
        } else {
            $mensaje = "🚨 *Préstamo VENCIDO hoy - {$tienda}*\n"
                . "Hola *{$cliente->nombre}*, tu préstamo *#{$prestamo->numero_folio}* venció *hoy {$vencimiento}*.\n\n"
                . "Por favor comunícate con nosotros a la brevedad para regularizar la situación."
                . $ubicacion . "\n\n"
                . "¡Gracias!";
        }

        return $this->sendMessage($phone, $mensaje);
    }
}
