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
        $b .= self::CODEPAGE_WIN1252;  // Seleccionar Windows-1252 para acentos en español

        // Nombre de la tienda (centrado, doble alto+ancho)
        if (!empty($data['store'])) {
            $b .= self::ALIGN_C . self::BOLD_ON . self::SIZE_2X;
            $b .= $this->encode(mb_strtoupper($data['store'])) . self::LF;
            $b .= self::SIZE_N . self::BOLD_OFF;
        }

        // Dirección / teléfono / NIT
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

        // Separador
        $b .= self::ALIGN_L;
        $b .= str_repeat('=', $cols) . self::LF;

        // Título de la sección (ej: "VENTA #23721")
        if (!empty($data['title'])) {
            $b .= self::ALIGN_C . self::BOLD_ON;
            $b .= $this->encode($data['title']) . self::LF;
            $b .= self::BOLD_OFF;
        }

        $b .= str_repeat('=', $cols) . self::LF;

        // Datos de la transacción
        $b .= self::ALIGN_L;
        if (!empty($data['date'])) {
            $b .= $this->padLine('Fecha:', $this->encode($data['date']), $cols) . self::LF;
        }
        if (!empty($data['user'])) {
            $b .= $this->padLine('Cajero:', $this->encode($data['user']), $cols) . self::LF;
        }
        if (!empty($data['client'])) {
            $b .= $this->padLine('Cliente:', $this->encode($data['client']), $cols) . self::LF;
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

        // Encabezado de columnas
        $b .= self::ALIGN_L . self::BOLD_ON;
        $b .= $this->padLine('PRODUCTO / CANT', 'TOTAL', $cols) . self::LF;
        $b .= self::BOLD_OFF;
        $b .= str_repeat('-', $cols) . self::LF;

        foreach ($items as $item) {
            $nombre   = $this->encode($item['nombre']   ?? 'Producto');
            $cant     = $item['cantidad'] ?? '';           // Ya formateado: "2p - 3u"
            $subtotal = $item['subtotal'] ?? 0;
            $precio   = $item['precio']   ?? 0;

            $subtStr = 'Bs.' . number_format($subtotal, 2);

            // Línea 1: nombre (truncado si no cabe; strlen porque ya es CP850)
            $nombreTrunc = strlen($nombre) > $cols ? substr($nombre, 0, $cols - 1) : $nombre;
            $b .= $nombreTrunc . self::LF;

            // Línea 2: cantidad y precio → subtotal (alineado derecha)
            $detalle = '  ' . $cant . ' x Bs.' . number_format($precio, 2);
            $b .= $this->padLine($detalle, $subtStr, $cols) . self::LF;
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
        $b .= self::ALIGN_L;

        // TOTAL principal en doble ancho
        if (isset($totals['TOTAL'])) {
            $b .= self::ALIGN_R . self::BOLD_ON . self::SIZE_2W;
            $b .= 'TOTAL: Bs.' . number_format($totals['TOTAL'], 2) . self::LF;
            $b .= self::SIZE_N . self::BOLD_OFF;
        }

        $b .= self::ALIGN_L;

        // Desglose de pagos
        $map = [
            'efectivo' => 'Efectivo',
            'online'   => 'Online / QR',
            'credito'  => $this->encode('Crédito'),
            'cambio'   => 'Cambio',
        ];

        foreach ($map as $key => $label) {
            if (!empty($totals[$key]) && $totals[$key] > 0) {
                $b .= $this->padLine($label . ':', 'Bs.' . number_format($totals[$key], 2), $cols) . self::LF;
            }
        }

        $b .= str_repeat('=', $cols) . self::LF;

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
        string $message = '¡Gracias por su compra!',
        bool $cut = true,
        bool $cashDrawer = false,
        int $feeds = 5,
        int $cols = 48
    ): string {
        $b = '';

        if ($message) {
            $b .= self::ALIGN_C . self::BOLD_ON;
            $b .= $this->encode($message) . self::LF;
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
