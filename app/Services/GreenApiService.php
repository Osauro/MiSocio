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
    public function sendMessage(string $phone, string $message): bool
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (empty($this->instanceId) || empty($this->apiToken) || empty($phone)) {
            return false;
        }

        $url = "{$this->baseUrl}/waInstance{$this->instanceId}/sendMessage/{$this->apiToken}";

        try {
            $response = Http::timeout(10)->post($url, [
                'chatId'  => "{$phone}@c.us",
                'message' => $message,
            ]);

            if (!$response->successful()) {
                Log::warning('GreenAPI: mensaje no enviado', [
                    'phone'  => $phone,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('GreenAPI: error al enviar mensaje', [
                'phone'   => $phone,
                'error'   => $e->getMessage(),
            ]);
            return false;
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
        if (empty($config->propietario_celular)) return;

        $prefijo = preg_replace('/\D/', '', $config->propietario_celular_prefijo ?? '591');
        $numero  = preg_replace('/\D/', '', $config->propietario_celular);
        $phone   = $prefijo . $numero;

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
        $phone = preg_replace('/\D/', '', $phone);

        if (empty($this->instanceId) || empty($this->apiToken) || empty($phone)) {
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
                'chatId'  => "{$phone}@c.us",
                'caption' => mb_substr($caption, 0, 1024),
            ]);

            if (!$response->successful()) {
                Log::warning('GreenAPI: archivo no enviado', [
                    'phone'  => $phone,
                    'file'   => $fileName,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('GreenAPI: error al enviar archivo', [
                'phone' => $phone,
                'error' => $e->getMessage(),
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
        if (empty($config->propietario_celular)) return false;

        $prefijo = preg_replace('/\D/', '', $config->propietario_celular_prefijo ?? '591');
        $numero  = preg_replace('/\D/', '', $config->propietario_celular);
        $phone   = $prefijo . $numero;

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
}
