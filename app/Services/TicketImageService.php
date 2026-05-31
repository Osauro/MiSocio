<?php

namespace App\Services;

use App\Models\TenantConfig;
use App\Models\Venta;

/**
 * Genera una imagen PNG de ticket de venta usando GD2 (built-in PHP).
 * No requiere dependencias externas.
 */
class TicketImageService
{
    // Dimensiones del ticket
    private const WIDTH   = 600;
    private const PADDING = 28;

    /**
     * Genera un PNG del ticket de venta y lo devuelve como string binario.
     */
    public function generarTicketVenta(Venta $venta, TenantConfig $config): string
    {
        $tienda  = $config->nombre_tienda ?: 'Mi Tienda';
        $folio   = $venta->numero_folio ?? $venta->id;
        $fecha   = $venta->created_at ? $venta->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i');
        $cajero  = optional($venta->user)->name ?? '-';
        $cliente = optional($venta->cliente)->nombre ?? 'Sin cliente';

        $efectivo = (float) ($venta->efectivo ?? 0);
        $online   = (float) ($venta->online   ?? 0);
        $credito  = (float) ($venta->credito  ?? 0);
        $total    = $efectivo + $online + $credito;

        // Construir líneas de items
        $items = [];
        foreach ($venta->ventaItems as $item) {
            $nombre = optional($item->producto)->nombre ?? "Producto #{$item->producto_id}";
            $items[] = [
                'nombre'   => mb_substr($nombre, 0, 36),
                'cantidad' => $item->cantidad_formateada ?? $item->cantidad,
                'subtotal' => number_format((float) $item->subtotal, 2),
            ];
        }

        // ── Calcular altura dinámica ────────────────────────────────────────
        $lineH     = 26;   // altura de línea normal
        $headerH   = 90;   // logo + nombre tienda
        $infoH     = 60;   // folio / fecha / cajero / cliente
        $dividerH  = 20;
        $itemsH    = max(1, count($items)) * ($lineH + 4) + 10;
        $totalesH  = 80;
        $footerH   = 40;

        $height = $headerH + $infoH + $dividerH + $itemsH + $dividerH + $totalesH + $footerH + self::PADDING * 2;

        $img = imagecreatetruecolor(self::WIDTH, $height);

        // Paleta de colores
        $bgColor       = imagecolorallocate($img, 255, 255, 255);
        $primaryColor  = imagecolorallocate($img, 25,  90,  45);   // verde oscuro
        $textDark      = imagecolorallocate($img, 30,  30,  30);
        $textMuted     = imagecolorallocate($img, 100, 100, 100);
        $textWhite     = imagecolorallocate($img, 255, 255, 255);
        $dividerColor  = imagecolorallocate($img, 200, 200, 200);
        $accentColor   = imagecolorallocate($img, 40,  167, 69);   // verde claro
        $creditColor   = imagecolorallocate($img, 220, 53,  69);   // rojo

        imagefill($img, 0, 0, $bgColor);

        $W = self::WIDTH;
        $P = self::PADDING;

        // ── Header (barra verde) ────────────────────────────────────────────
        imagefilledrectangle($img, 0, 0, $W, $headerH, $primaryColor);

        // Nombre de tienda centrado en el header
        $fontTitle = 5; // fuente GD built-in más grande
        $textW     = strlen($tienda) * imagefontwidth($fontTitle);
        $textX     = max($P, ($W - $textW) / 2);
        imagestring($img, $fontTitle, (int) $textX, 20, $tienda, $textWhite);

        // Sub-línea: "Ticket de Venta"
        $sub  = 'Ticket de Venta';
        $subW = strlen($sub) * imagefontwidth(3);
        imagestring($img, 3, (int) (($W - $subW) / 2), 50, $sub, $textWhite);

        // ── Info básica ─────────────────────────────────────────────────────
        $y = $headerH + 14;
        imagestring($img, 4, $P, $y, "Folio: #$folio", $textDark);
        imagestring($img, 3, $W - $P - strlen("Fecha: $fecha") * imagefontwidth(3), $y, "Fecha: $fecha", $textMuted);

        $y += $lineH;
        imagestring($img, 3, $P, $y, "Cajero: $cajero", $textMuted);
        imagestring($img, 3, $P, $y + $lineH - 4, "Cliente: $cliente", $textMuted);

        // ── Divisor ─────────────────────────────────────────────────────────
        $y = $headerH + $infoH;
        imageline($img, $P, $y, $W - $P, $y, $dividerColor);

        // ── Encabezado de items ─────────────────────────────────────────────
        $y += 8;
        imagestring($img, 3, $P, $y, 'PRODUCTO', $textMuted);
        imagestring($img, 3, $W - $P - 50, $y, 'Bs.', $textMuted);
        $y += $lineH;

        // ── Filas de items ──────────────────────────────────────────────────
        foreach ($items as $item) {
            $nombreLine = $item['nombre'] . ' x' . $item['cantidad'];
            imagestring($img, 3, $P, $y, $nombreLine, $textDark);

            $bsW = strlen($item['subtotal']) * imagefontwidth(3);
            imagestring($img, 3, $W - $P - $bsW, $y, $item['subtotal'], $textDark);
            $y += $lineH + 2;
        }

        // ── Divisor ─────────────────────────────────────────────────────────
        $y += 6;
        imageline($img, $P, $y, $W - $P, $y, $dividerColor);
        $y += 10;

        // ── Totales ─────────────────────────────────────────────────────────
        $totalStr = 'TOTAL: Bs. ' . number_format($total, 2);
        $totalW   = strlen($totalStr) * imagefontwidth(5);
        imagestring($img, 5, $W - $P - $totalW, $y, $totalStr, $primaryColor);
        $y += $lineH + 4;

        if ($efectivo > 0) {
            $s = 'Efectivo: Bs. ' . number_format($efectivo, 2);
            imagestring($img, 3, $W - $P - strlen($s) * imagefontwidth(3), $y, $s, $textMuted);
            $y += $lineH;
        }
        if ($online > 0) {
            $s = 'Online: Bs. ' . number_format($online, 2);
            imagestring($img, 3, $W - $P - strlen($s) * imagefontwidth(3), $y, $s, $textMuted);
            $y += $lineH;
        }
        if ($credito > 0) {
            $s = 'Credito pendiente: Bs. ' . number_format($credito, 2);
            imagestring($img, 3, $W - $P - strlen($s) * imagefontwidth(3), $y, $s, $creditColor);
            $y += $lineH;
        }

        // ── Footer ──────────────────────────────────────────────────────────
        $y = $height - $footerH + 8;
        imagefilledrectangle($img, 0, $y - 8, $W, $height, $accentColor);
        $footer = 'Generado por MiSocio  |  misocio.bo';
        $fW     = strlen($footer) * imagefontwidth(2);
        imagestring($img, 2, (int) (($W - $fW) / 2), $y + 4, $footer, $textWhite);

        // ── Capturar PNG como string ────────────────────────────────────────
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png;
    }
}
