<?php

namespace App\Services;

use App\Models\TenantConfig;
use App\Models\Venta;

/**
 * Genera una imagen PNG de ticket de venta estilo ticket impreso, usando GD2.
 * No requiere dependencias externas.
 */
class TicketImageService
{
    private const WIDTH   = 480;
    private const PADDING = 20;
    private const LOGO_MAX = 160; // píxeles máximos para el logo (cuadrado)

    // Fuentes GD built-in: 3=7x13, 4=8x16, 5=9x15
    private const F_LARGE = 5;
    private const F_MED   = 4;
    private const F_SMALL = 3;

    public function generarTicketVenta(Venta $venta, TenantConfig $config): string
    {
        $tienda      = $config->nombre_tienda ?: 'Mi Tienda';
        $direccion   = $config->direccion ?? '';
        $propNombre  = $config->propietario_nombre ?? '';
        $propCelular = $config->propietario_celular ?? '';

        $folio   = $venta->numero_folio ?? $venta->id;
        $fecha   = $venta->created_at ? $venta->created_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s');
        $cajero  = optional($venta->user)->name ?? '-';
        $cliente = optional($venta->cliente)->nombre ?? 'Sin cliente';

        $efectivo = (float) ($venta->efectivo ?? 0);
        $online   = (float) ($venta->online   ?? 0);
        $credito  = (float) ($venta->credito  ?? 0);
        $total    = $efectivo + $online + $credito;

        $items = [];
        foreach ($venta->ventaItems as $item) {
            $nombre    = optional($item->producto)->nombre ?? "Producto #{$item->producto_id}";
            $items[] = [
                'nombre'   => $nombre,
                'cantidad' => (string) ($item->cantidad_formateada ?? $item->cantidad),
                'subtotal' => number_format((float) $item->subtotal, 2),
            ];
        }

        // ── Cargar logo ────────────────────────────────────────────────────
        $logoPath = public_path('assets/images/misocio_bg.png');
        $logoImg  = null;
        $logoW    = 0;
        $logoH    = 0;

        if (file_exists($logoPath)) {
            [$origW, $origH] = getimagesize($logoPath);
            $scale  = self::LOGO_MAX / max($origW, $origH);
            $logoW  = (int) ($origW * $scale);
            $logoH  = (int) ($origH * $scale);
            $logoImg = imagecreatefrompng($logoPath);
            imagealphablending($logoImg, true);
        }

        // ── Métricas de fuentes ────────────────────────────────────────────
        $lhL = imagefontheight(self::F_LARGE) + 5;
        $lhM = imagefontheight(self::F_MED)   + 5;
        $lhS = imagefontheight(self::F_SMALL) + 4;

        $W = self::WIDTH;
        $P = self::PADDING;

        // Columnas de items (font 3 = 7px/char)
        $fw      = imagefontwidth(self::F_SMALL);  // 7
        $cols    = (int)(($W - 2 * $P) / $fw);     // ≈62
        $qtyLen  = 5;   // "  1u "
        $priceLen = 8;  // "9999.99 "
        $nameLen = $cols - $qtyLen - $priceLen;     // ≈49

        // ── Calcular altura total ──────────────────────────────────────────
        $hLogo   = $logoImg ? ($logoH + 10) : 0;
        $hTienda = $lhL + ($direccion ? $lhS : 0) + 6;
        $hSep    = $lhS + 4;
        $hFolio  = $lhL + 6;
        $hSep2   = $lhS + 4;
        $hInfo   = $lhS * 3 + 4;
        $hSep3   = $lhS * 2 + 6;   // separator + "DETALLE" + separator
        $hItems  = max(1, count($items)) * ($lhS + 2) + 6;
        $hSep4   = $lhS + 4;
        $hTotal  = $lhL + 6;
        $hPago   = ($efectivo > 0 ? $lhS : 0)
                 + ($online   > 0 ? $lhS : 0)
                 + ($credito  > 0 ? $lhS : 0) + 4;
        $hFooter = $lhS + ($propNombre ? $lhS : 0) + ($propCelular ? $lhS : 0) + 14;

        $height = $P + $hLogo + $hTienda + $hSep + $hFolio + $hSep2
                + $hInfo + $hSep3 + $hItems + $hSep4 + $hTotal
                + $hPago + $hFooter + $P;

        // ── Crear canvas ───────────────────────────────────────────────────
        $img = imagecreatetruecolor($W, $height);
        imagealphablending($img, true);

        $cBg    = imagecolorallocate($img, 255, 255, 255);
        $cDark  = imagecolorallocate($img, 20,  20,  20);
        $cMuted = imagecolorallocate($img, 100, 100, 100);
        $cGreen = imagecolorallocate($img, 27,  94,  48);
        $cRed   = imagecolorallocate($img, 200, 40,  40);
        $cLine  = imagecolorallocate($img, 140, 140, 140);

        imagefill($img, 0, 0, $cBg);

        $y = $P;

        // ── Logo MiSocio ───────────────────────────────────────────────────
        if ($logoImg) {
            $tmp = imagecreatetruecolor($logoW, $logoH);
            imagefill($tmp, 0, 0, $cBg);
            imagecopyresampled($tmp, $logoImg, 0, 0, 0, 0, $logoW, $logoH, imagesx($logoImg), imagesy($logoImg));
            imagedestroy($logoImg);

            $lx = (int)(($W - $logoW) / 2);
            imagecopy($img, $tmp, $lx, $y, 0, 0, $logoW, $logoH);
            imagedestroy($tmp);

            $y += $logoH + 10;
        }

        // ── Nombre del negocio ─────────────────────────────────────────────
        $this->centered($img, self::F_LARGE, $y, $tienda, $cGreen, $W, true);
        $y += $lhL;

        if ($direccion) {
            $this->centered($img, self::F_SMALL, $y, $direccion, $cMuted, $W);
            $y += $lhS;
        }
        $y += 6;

        // ── Separador ─────────────────────────────────────────────────────
        $this->dashes($img, $P, $y, $W - $P, $cLine, self::F_SMALL);
        $y += $lhS + 4;

        // ── VENTA #XX ─────────────────────────────────────────────────────
        $this->centered($img, self::F_LARGE, $y, "VENTA #{$folio}", $cDark, $W, true);
        $y += $lhL + 6;

        // ── Separador ─────────────────────────────────────────────────────
        $this->dashes($img, $P, $y, $W - $P, $cLine, self::F_SMALL);
        $y += $lhS + 4;

        // ── Fecha / Cajero / Cliente ───────────────────────────────────────
        imagestring($img, self::F_SMALL, $P, $y, "Fecha:   $fecha",  $cDark);  $y += $lhS;
        imagestring($img, self::F_SMALL, $P, $y, "Cajero:  $cajero", $cDark);  $y += $lhS;
        imagestring($img, self::F_SMALL, $P, $y, "Cliente: $cliente", $cDark); $y += $lhS + 4;

        // ── Separador + DETALLE + Separador ────────────────────────────────
        $this->dashes($img, $P, $y, $W - $P, $cLine, self::F_SMALL);
        $y += $lhS;
        $this->centered($img, self::F_SMALL, $y, 'D E T A L L E', $cMuted, $W);
        $y += $lhS;
        $this->dashes($img, $P, $y, $W - $P, $cLine, self::F_SMALL);
        $y += $lhS + 4;

        // ── Items ──────────────────────────────────────────────────────────
        foreach ($items as $item) {
            $qty   = mb_substr($item['cantidad'], 0, $qtyLen - 1);
            $qty   = str_pad($qty, $qtyLen - 1, ' ', STR_PAD_LEFT) . ' ';

            // nombre + dots + precio
            $price = $item['subtotal'];
            $nombre = mb_substr($item['nombre'], 0, $nameLen);
            $dots   = str_repeat('.', max(0, $nameLen - mb_strlen($nombre)));

            imagestring($img, self::F_SMALL, $P, $y, $qty . $nombre . $dots, $cDark);

            $px = $W - $P - strlen($price) * $fw;
            imagestring($img, self::F_SMALL, $px, $y, $price, $cDark);

            $y += $lhS + 2;
        }
        $y += 4;

        // ── Separador ─────────────────────────────────────────────────────
        $this->dashes($img, $P, $y, $W - $P, $cLine, self::F_SMALL);
        $y += $lhS + 4;

        // ── TOTAL ─────────────────────────────────────────────────────────
        $totalLabel = 'TOTAL';
        $totalStr   = 'Bs.  ' . number_format($total, 2);
        imagestring($img, self::F_LARGE, $P, $y, $totalLabel, $cDark);
        imagestring($img, self::F_LARGE, $P + 1, $y, $totalLabel, $cDark); // bold
        $tx = $W - $P - strlen($totalStr) * imagefontwidth(self::F_LARGE);
        imagestring($img, self::F_LARGE, $tx, $y, $totalStr, $cDark);
        imagestring($img, self::F_LARGE, $tx + 1, $y, $totalStr, $cDark); // bold
        $y += $lhL + 4;

        // ── Desglose de pagos ──────────────────────────────────────────────
        if ($efectivo > 0) {
            $s = 'Efectivo:            Bs. ' . number_format($efectivo, 2);
            imagestring($img, self::F_SMALL, $W - $P - strlen($s) * $fw, $y, $s, $cMuted);
            $y += $lhS;
        }
        if ($online > 0) {
            $s = 'Online:              Bs. ' . number_format($online, 2);
            imagestring($img, self::F_SMALL, $W - $P - strlen($s) * $fw, $y, $s, $cMuted);
            $y += $lhS;
        }
        if ($credito > 0) {
            $s = 'Credito pendiente:   Bs. ' . number_format($credito, 2);
            imagestring($img, self::F_SMALL, $W - $P - strlen($s) * $fw, $y, $s, $cRed);
            $y += $lhS;
        }
        $y += 10;

        // ── Footer ────────────────────────────────────────────────────────
        $this->dashes($img, $P, $y, $W - $P, $cLine, self::F_SMALL);
        $y += $lhS + 4;

        $this->centered($img, self::F_SMALL, $y, '¡Gracias por su compra!', $cDark, $W);
        $y += $lhS;

        if ($propNombre) {
            $this->centered($img, self::F_SMALL, $y, $propNombre, $cMuted, $W);
            $y += $lhS;
        }
        if ($propCelular) {
            $this->centered($img, self::F_SMALL, $y, $propCelular, $cMuted, $W);
        }

        // ── Exportar PNG ───────────────────────────────────────────────────
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png;
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function centered($img, int $font, int $y, string $text, $color, int $W, bool $bold = false): void
    {
        $tw = strlen($text) * imagefontwidth($font);
        $x  = (int) max(self::PADDING, ($W - $tw) / 2);
        imagestring($img, $font, $x, $y, $text, $color);
        if ($bold) {
            imagestring($img, $font, $x + 1, $y, $text, $color);
        }
    }

    private function dashes($img, int $x1, int $y, int $x2, $color, int $font): void
    {
        $cw    = imagefontwidth($font);
        $count = (int) (($x2 - $x1) / $cw);
        imagestring($img, $font, $x1, $y, str_repeat('-', $count), $color);
    }
}

