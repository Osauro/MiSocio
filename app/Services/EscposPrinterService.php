<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de impresión ESC/POS mediante Print Agent local.
 *
 * El agente corre en http://localhost:9876 y acepta un "UniversalJob"
 * con secciones encriptadas (AES-256-GCM + gzip) en base64 url-safe.
 *
 * Flujo:
 *   1. Construir bytes ESC/POS para cada sección (header, body, totals, footer)
 *   2. Encriptar cada sección con encryptSection()
 *   3. Enviar el job completo con print()
 */
class EscposPrinterService
{
    // ── Comandos ESC/POS ──────────────────────────────────────────────────
    const ESC     = "\x1B";
    const GS      = "\x1D";
    const INIT    = "\x1B\x40";
    const LF      = "\x0A";
    const ALIGN_L = "\x1B\x61\x00";
    const ALIGN_C = "\x1B\x61\x01";
    const ALIGN_R = "\x1B\x61\x02";
    const SIZE_N  = "\x1D\x21\x00";   // Normal
    const SIZE_2H = "\x1D\x21\x01";   // Doble alto
    const SIZE_2W = "\x1D\x21\x10";   // Doble ancho
    const SIZE_2X = "\x1D\x21\x11";   // Doble alto + ancho
    const BOLD_ON = "\x1B\x45\x01";
    const BOLD_OFF = "\x1B\x45\x00";
    const CUT          = "\x1D\x56\x00";       // Corte completo
    const CUT_P        = "\x1D\x56\x41\x00";   // Corte parcial
    const DRAWER       = "\x1B\x70\x00\x32\xFA"; // Apertura de caja
    const CODEPAGE_WIN1252 = "\x1B\x74\x10";   // Windows-1252 (Latin-1 compatible, codepage nativa Windows)

    protected string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('print_agent.secret_key', '');
        $this->baseUrl   = rtrim(config('print_agent.base_url', 'http://localhost:9876'), '/');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ENCRIPTACIÓN
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Encripta una sección ESC/POS con AES-256-GCM + gzip.
     *
     * @param  string $hexKey    Clave en hexadecimal (64 chars → 32 bytes)
     * @param  string $plainBytes Bytes ESC/POS sin encriptar
     * @return string             Base64 url-safe sin padding
     */
    public function encryptSection(string $hexKey, string $plainBytes): string
    {
        $key = hex2bin($hexKey);

        // 1. Comprimir con gzip nivel 9
        $compressed = gzencode($plainBytes, 9, FORCE_GZIP);

        // 2. Cifrar con AES-256-GCM
        $nonce  = random_bytes(12);
        $tag    = '';
        $cipher = openssl_encrypt(
            $compressed,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            '',
            16
        );

        // 3. Formato: nonce(12) + ciphertext + tag(16)
        $raw = $nonce . $cipher . $tag;

        // 4. Base64 url-safe sin padding
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // BUILDERS ESC/POS
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Construye la cabecera del ticket.
     *
     * @param  array $data  Claves: title, date, user, client, store, address, phone, nit
     * @param  int   $cols  Ancho en caracteres (32 o 48 según papel)
     * @return string       Bytes ESC/POS
     */
    public function buildEscHeader(array $data, int $cols = 48): string
    {
        $b = '';
        $b .= self::INIT;
        $b .= self::CODEPAGE_WIN1252;

        // Nombre de la tienda (centrado, doble alto+ancho)
        if (!empty($data['store'])) {
            $b .= self::ALIGN_C . self::BOLD_ON . self::SIZE_2X;
            $b .= $this->encode(mb_strtoupper($data['store'])) . self::LF;
            $b .= self::SIZE_N . self::BOLD_OFF;
        }

        // Dirección / teléfono / NIT (centrados, tamaño normal)
        $b .= self::ALIGN_C;
        if (!empty($data['address'])) {
            $b .= $this->encode($data['address']) . self::LF;
        }
        if (!empty($data['phone'])) {
            $b .= $this->encode('Tel: ' . $data['phone']) . self::LF;
        }
        if (!empty($data['nit'])) {
            $b .= $this->encode('NIT: ' . $data['nit']) . self::LF;
        }

        // Título de la sección (ej: "VENTA #25826") — grande, centrado
        if (!empty($data['title'])) {
            $b .= self::LF;
            $b .= self::ALIGN_C . self::BOLD_ON . self::SIZE_2X;
            $b .= $this->encode(mb_strtoupper($data['title'])) . self::LF;
            $b .= self::SIZE_N . self::BOLD_OFF;
            $b .= self::LF;
        }

        // Datos de la transacción (alineados izquierda)
        $b .= self::ALIGN_L;
        if (!empty($data['date'])) {
            $b .= $this->encode('Fecha:   ' . $data['date']) . self::LF;
        }
        if (!empty($data['user'])) {
            $b .= $this->encode('Cajero:  ' . $data['user']) . self::LF;
        }
        if (!empty($data['client'])) {
            $b .= $this->encode('Cliente: ' . $data['client']) . self::LF;
        }

        $b .= str_repeat('-', $cols) . self::LF;

        return $b;
    }

    /**
     * Construye el cuerpo del ticket (ítems).
     *
     * Cada ítem debe tener: nombre, cantidad (string), precio (float), subtotal (float)
     * Formato por ítem:
     *   Nombre del producto
     *     cant  precio  subtotal (alineado a derecha)
     *
     * @param  array $items  Lista de ítems
     * @param  int   $cols   Ancho en caracteres
     * @return string        Bytes ESC/POS
     */
    public function buildEscBody(array $items, int $cols = 48): string
    {
        $b = '';

        // Título de sección centrado
        $b .= self::ALIGN_C . self::BOLD_ON;
        $b .= $this->encode('D E T A L L E') . self::LF;
        $b .= self::BOLD_OFF . self::ALIGN_L;
        $b .= str_repeat('-', $cols) . self::LF;

        // Formato por línea: [cantStr]  [nombre...dots...][subtStr]
        // subtotal: 7 chars fijos, right-aligned (hasta 9999.99)
        $subtWidth = 7;

        foreach ($items as $item) {
            $nombre   = $this->encode($item['nombre']   ?? 'Producto');
            $cantStr  = (string) ($item['cantidad'] ?? '');
            $subtotal = (float)  ($item['subtotal'] ?? 0);

            $subtStr  = str_pad(number_format($subtotal, 2), $subtWidth, ' ', STR_PAD_LEFT);

            // Zona disponible para nombre + puntos
            // Layout: cantStr + '  ' + nombre + dots + subtStr
            $usedFixed = strlen($cantStr) + 2 + $subtWidth;
            $nameZone  = $cols - $usedFixed;

            if ($nameZone < 4) {
                // Fallback: nombre en línea propia, cant + subtotal en la siguiente
                $b .= $nombre . self::LF;
                $fill = str_repeat(' ', max(1, $cols - strlen($cantStr) - $subtWidth));
                $b .= $cantStr . $fill . $subtStr . self::LF;
            } else {
                if (strlen($nombre) >= $nameZone) {
                    // Truncar y terminar con un espacio
                    $nombre = substr($nombre, 0, $nameZone - 1);
                    $dots   = ' ';
                } else {
                    $dots = str_repeat('.', $nameZone - strlen($nombre));
                }
                $b .= $cantStr . '  ' . $nombre . $dots . $subtStr . self::LF;
            }
        }

        $b .= str_repeat('-', $cols) . self::LF;

        return $b;
    }

    /**
     * Construye la sección de totales.
     *
     * @param  array $totals  Claves posibles: TOTAL, efectivo, online, credito, cambio
     * @param  int   $cols    Ancho en caracteres
     * @return string         Bytes ESC/POS
     */
    public function buildEscTotals(array $totals, int $cols = 48): string
    {
        $b = '';

        // Solo muestra el TOTAL, sin desglose de métodos de pago
        if (isset($totals['TOTAL'])) {
            $totalStr = 'Bs. ' . number_format($totals['TOTAL'], 2);
            // Con SIZE_2W cada carácter ocupa el doble de ancho físico,
            // por lo tanto el ancho lógico disponible es cols/2
            $halfCols = (int) ($cols / 2);
            $label    = 'TOTAL';
            $padding  = $halfCols - strlen($label) - strlen($totalStr);
            $padding  = max(1, $padding);
            $b .= self::ALIGN_L . self::BOLD_ON . self::SIZE_2W;
            $b .= $label . str_repeat(' ', $padding) . $totalStr . self::LF;
            $b .= self::SIZE_N . self::BOLD_OFF;
        }

        $b .= self::LF;

        return $b;
    }

    /**
     * Construye el pie del ticket (mensaje, feeds, corte, cajón).
     *
     * @param  string $message     Mensaje de pie (ej: "¡Gracias por su compra!")
     * @param  bool   $cut         Incluir comando de corte de papel
     * @param  bool   $cashDrawer  Incluir comando de apertura de caja
     * @param  int    $feeds       Número de avances de línea antes del corte
     * @param  int    $cols        Ancho en caracteres
     * @return string              Bytes ESC/POS
     */
    public function buildEscFooter(
        string|array $message = '¡Gracias por su compra!',
        bool $cut = true,
        bool $cashDrawer = false,
        int $feeds = 5,
        int $cols = 48
    ): string {
        $b = '';

        $lines = is_array($message) ? $message : [$message];
        $lines = array_filter($lines, fn($l) => $l !== '' && $l !== null);

        foreach ($lines as $line) {
            $b .= self::ALIGN_C . self::BOLD_ON;
            $b .= $this->encode((string) $line) . self::LF;
            $b .= self::BOLD_OFF;
        }

        // Avances de línea para que el corte quede fuera del área impresa
        // (la distancia física cabezal-cuchilla es ~4-5 líneas en papel térmico)
        for ($i = 0; $i < $feeds; $i++) {
            $b .= self::LF;
        }

        // Corte de papel (REGLA: debe ir dentro del footer encriptado)
        if ($cut) {
            $b .= self::CUT;
        }

        // Apertura de cajón (después del corte)
        if ($cashDrawer) {
            $b .= self::DRAWER;
        }

        return $b;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // ENVÍO AL AGENTE
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Envía un UniversalJob al Print Agent local.
     *
     * El array $job puede contener:
     *   printer (string, obligatorio)
     *   logo    (bool)
     *   header  (string, base64 encriptado)
     *   body    (string, base64 encriptado)
     *   totals  (string, base64 encriptado)
     *   qr      (string, base64 encriptado, opcional)
     *   footer  (string, base64 encriptado)
     *
     * @return array ['ok' => bool, 'status' => int, 'body' => string, 'error' => string]
     */
    public function print(string $printerName, array $job): array
    {
        $payload = array_merge(['printer' => $printerName], $job);

        try {
            $response = Http::timeout(5)
                ->post($this->baseUrl . '/api/print/universal', $payload);

            return [
                'ok'     => $response->successful(),
                'status' => $response->status(),
                'body'   => $response->body(),
                'error'  => $response->successful() ? '' : $response->body(),
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('PrintAgent: no se pudo conectar al agente local', [
                'url'   => $this->baseUrl,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok'     => false,
                'status' => 0,
                'body'   => '',
                'error'  => 'Agente de impresión no disponible: ' . $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            Log::error('PrintAgent: error inesperado', ['error' => $e->getMessage()]);

            return [
                'ok'     => false,
                'status' => 0,
                'body'   => '',
                'error'  => $e->getMessage(),
            ];
        }
    }

    /**
     * Construye y envía el ticket completo de una venta de una sola llamada.
     *
     * @param  string $printerName  Nombre de la impresora en el agente
     * @param  array  $header       Datos para buildEscHeader()
     * @param  array  $items        Datos para buildEscBody()
     * @param  array  $totals       Datos para buildEscTotals()
     * @param  array  $options      logo(bool), cut(bool), cashDrawer(bool), footerMsg(string), cols(int)
     * @return array
     */
    public function printVenta(
        string $printerName,
        array $header,
        array $items,
        array $totals,
        array $options = []
    ): array {
        $cols       = $options['cols']        ?? 48;
        $logo       = $options['logo']        ?? true;
        $cut        = $options['cut']         ?? true;
        $cashDrawer = $options['cashDrawer']  ?? false;
        $footerMsg  = $options['footerMsg']   ?? '¡Gracias por su compra!';
        $key        = $options['secretKey']   ?? $this->secretKey;

        $job = [
            'logo'   => $logo,
            'header' => $this->encryptSection($key, $this->buildEscHeader($header, $cols)),
            'body'   => $this->encryptSection($key, $this->buildEscBody($items, $cols)),
            'totals' => $this->encryptSection($key, $this->buildEscTotals($totals, $cols)),
            'footer' => $this->encryptSection($key, $this->buildEscFooter($footerMsg, $cut, $cashDrawer, 5, $cols)),
        ];

        return $this->print($printerName, $job);
    }

    /**
     * Construye el UniversalJob encriptado para una venta, listo para despachar al agente.
     * Retorna null si no hay clave configurada.
     */
    public function buildVentaJob(\App\Models\Venta $venta, \App\Models\TenantConfig $config): ?array
    {
        $key = $config->print_agent_secret_key ?? config('print_agent.secret_key');
        if (empty($key)) return null;

        $papel   = $config->papel_tamano_ventas  ?: ($config->papel_tamano    ?? '80mm');
        $printer = $config->impresora_ventas     ?: ($config->impresora_nombre ?? '');
        $cols    = ($papel === '58mm') ? 32 : 48;

        $header = [
            'store'   => $config->nombre_tienda  ?? 'MI TIENDA',
            'address' => $config->direccion      ?? '',
            'phone'   => $config->telefono       ?? '',
            'nit'     => $config->nit            ?? '',
            'title'   => 'VENTA #' . $venta->numero_folio,
            'date'    => $venta->created_at->format('d/m/Y H:i:s'),
            'user'    => $venta->user->name       ?? '',
            'client'  => $venta->cliente->nombre  ?? '',
        ];

        $items = $venta->ventaItems->map(function ($item) {
            $nombre = $item->producto ? $item->producto->nombre : ($item->nombre ?? 'Producto');
            return [
                'nombre'   => $nombre,
                'cantidad' => $item->cantidad_formateada,
                'precio'   => (float) $item->precio_unitario,
                'subtotal' => (float) $item->subtotal,
            ];
        })->toArray();

        // 'total' no es columna en Venta — se calcula como suma de los medios de pago
        $totalVal = (float) ($venta->efectivo ?? 0)
                  + (float) ($venta->online   ?? 0)
                  + (float) ($venta->credito  ?? 0);
        $totales  = ['TOTAL' => $totalVal];

        $footerLines = $this->tenantFooterLines($config);

        return [
            'printer' => $printer,
            'logo'    => (bool) ($config->mostrar_logo ?? true),
            'header'  => $this->encryptSection($key, $this->buildEscHeader($header, $cols)),
            'body'    => $this->encryptSection($key, $this->buildEscBody($items, $cols)),
            'totals'  => $this->encryptSection($key, $this->buildEscTotals($totales, $cols)),
            'footer'  => $this->encryptSection($key, $this->buildEscFooter(
                $footerLines,
                (bool) ($config->corte_automatico ?? true),
                (bool) ($config->abrir_cajon      ?? false),
                5, $cols
            )),
        ];
    }

    /**
     * Construye el UniversalJob encriptado para un préstamo, listo para despachar al agente.
     * Retorna null si no hay clave configurada.
     */
    public function buildPrestamoJob(\App\Models\Prestamo $prestamo, \App\Models\TenantConfig $config): ?array
    {
        $key = $config->print_agent_secret_key ?? config('print_agent.secret_key');
        if (empty($key)) return null;

        $papel   = $config->papel_tamano_prestamos  ?: ($config->papel_tamano    ?? '80mm');
        $printer = $config->impresora_prestamos     ?: ($config->impresora_nombre ?? '');
        $cols    = ($papel === '58mm') ? 32 : 48;

        $header = [
            'store'   => $config->nombre_tienda  ?? 'MI TIENDA',
            'address' => $config->direccion      ?? '',
            'phone'   => $config->telefono       ?? '',
            'nit'     => $config->nit            ?? '',
            'title'   => 'PRÉSTAMO #' . ($prestamo->numero_folio ?? $prestamo->id),
            'date'    => $prestamo->created_at->format('d/m/Y H:i:s'),
            'user'    => $prestamo->user->name      ?? '',
            'client'  => $prestamo->cliente->nombre ?? '',
        ];

        $items = $prestamo->prestamoItems->map(function ($item) {
            $nombre = $item->producto ? $item->producto->nombre : ($item->nombre ?? 'Producto');
            return [
                'nombre'   => $nombre,
                'cantidad' => $item->cantidad_formateada,
                'precio'   => (float) $item->precio,
                'subtotal' => (float) $item->subtotal,
            ];
        })->toArray();

        $footerLines = $this->tenantFooterLines($config);

        return [
            'printer' => $printer,
            'logo'    => (bool) ($config->mostrar_logo ?? true),
            'header'  => $this->encryptSection($key, $this->buildEscHeader($header, $cols)),
            'body'    => $this->encryptSection($key, $this->buildEscBody($items, $cols)),
            'totals'  => $this->encryptSection($key, $this->buildEscTotals(['TOTAL' => (float) $prestamo->total], $cols)),
            'footer'  => $this->encryptSection($key, $this->buildEscFooter(
                $footerLines,
                (bool) ($config->corte_automatico ?? true),
                false, 5, $cols
            )),
        ];
    }

    private function tenantFooterLines(\App\Models\TenantConfig $config): array
    {
        $lines = ['¡Gracias por su compra!'];
        if (!empty($config->propietario_nombre))  $lines[] = $config->propietario_nombre;
        if (!empty($config->propietario_celular)) $lines[] = $config->propietario_celular;
        return $lines;
    }

    // ═══════════════════════════════════════════════════════════════════════
    // UTILIDADES
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Formatea dos cadenas ocupando el ancho total de la línea.
     * Izquierda........Derecha
     */
    public function padLine(string $left, string $right, int $cols): string
    {
        // Usar strlen (no mb_strlen) porque los textos ya están en CP850 (1 byte = 1 char)
        $spaces = $cols - strlen($left) - strlen($right);
        if ($spaces < 1) {
            $spaces = 1;
        }

        return $left . str_repeat(' ', $spaces) . $right;
    }

    /**
     * Convierte texto UTF-8 a Windows-1252 (Latin-1) para impresoras térmicas ESC/POS.
     * Usa mapa estático — no depende de iconv ni mbstring. Fiable en cualquier SO.
     */
    public function encode(string $text): string
    {
        static $map = [
            // Minúsculas con tilde
            'á' => "\xE1", 'é' => "\xE9", 'í' => "\xED", 'ó' => "\xF3", 'ú' => "\xFA",
            // Mayúsculas con tilde
            'Á' => "\xC1", 'É' => "\xC9", 'Í' => "\xCD", 'Ó' => "\xD3", 'Ú' => "\xDA",
            // Eñe
            'ñ' => "\xF1", 'Ñ' => "\xD1",
            // Diéresis
            'ü' => "\xFC", 'Ü' => "\xDC",
            // Signos españoles
            '¿' => "\xBF", '¡' => "\xA1",
            // Otros comunes
            '°' => "\xB0", '·' => "\xB7", '«' => "\xAB", '»' => "\xBB",
        ];
        return strtr($text, $map);
    }

    /**
     * Devuelve la secret key configurada.
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * Verifica si el agente local está disponible (health-check).
     *
     * @return bool
     */
    public function isAgentAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get($this->baseUrl . '/health');
            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
