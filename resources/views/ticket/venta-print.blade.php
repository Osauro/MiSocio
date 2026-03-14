<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta #{{ $venta->numero_folio }}</title>
    @php
        // Configuración dinámica según tamaño de papel
        $is58mm = ($config->papel_tamano ?? '80mm') === '58mm';
        $paperWidth = $is58mm ? '58mm' : '80mm';
        $bodyWidth = $is58mm ? '52mm' : '70mm'; // Reducido para evitar cortes
        $logoWidth = $is58mm ? '30mm' : '40mm'; // Logo más pequeño para mejor resolución
        $logoHeight = $is58mm ? '15mm' : '20mm';
        $fontSize = $is58mm ? '10px' : '11px';
        $lineHeight = $is58mm ? '1.2' : '1.3';
        $margin = $is58mm ? '2mm' : '4mm';
        $nombreLimit = $is58mm ? 16 : 22;

        // Función para truncar texto en el centro
        $truncateMiddle = function($text, $limit) {
            if (mb_strlen($text) <= $limit) return $text;
            $start = (int) floor(($limit - 3) / 2);
            $end = (int) ceil(($limit - 3) / 2);
            return mb_substr($text, 0, $start) . '...' . mb_substr($text, -$end);
        };
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* El alto es AUTO - se ajusta al contenido */
        @page {
            size: {{ $paperWidth }} auto;
            margin: {{ $margin }};
        }

        body {
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
            font-size: {{ $fontSize }};
            line-height: {{ $lineHeight }};
            color: #000;
            width: {{ $bodyWidth }};
            margin: 0;
            padding: 0;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }

        .logo {
            display: block;
            margin: 0 auto 4px;
            max-width: {{ $logoWidth }};
            max-height: {{ $logoHeight }};
        }

        .nombre-tienda {
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 2px;
        }

        .info-tienda {
            text-align: center;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .linea {
            border: none;
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        .linea-doble {
            border: none;
            border-top: 2px solid #000;
            margin: 4px 0;
        }

        .titulo-seccion {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            letter-spacing: 2px;
            margin: 2px 0;
        }

        .datos-venta {
            font-size: 11px;
            width: 100%;
        }

        .datos-venta td {
            padding: 1px 0;
            vertical-align: top;
        }

        .datos-venta .label {
            font-weight: bold;
            width: 55px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 2px 0;
        }

        .items-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .items-table .cant { width: 45px; text-align: left; white-space: nowrap; font-size: 10px; font-weight: bold; }
        .items-table .nombre { text-align: left; overflow: hidden; white-space: nowrap; }
        .items-table .precio { width: 55px; text-align: right; font-weight: bold; }

        .totales-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .totales-table td { padding: 1px 0; }
        .totales-table .total-label { text-align: right; font-weight: bold; padding-right: 4px; }
        .totales-table .total-valor { text-align: right; width: 55px; }

        .total-principal { font-size: 14px; font-weight: bold; }

        .mensaje-final {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 6px 0 2px;
        }

        .pie {
            text-align: center;
            font-size: 10px;
            color: #555;
            margin-top: 4px;
        }

        /* Ocultar controles en impresión */
        .no-print { display: block; text-align: center; margin: 10px 0; }
        @media print {
            body { width: 100%; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    {{-- Logo --}}
    @if($config->logo)
        <img src="{{ asset('storage/' . $config->logo) }}" class="logo" alt="Logo">
    @endif

    {{-- Nombre de la tienda --}}
    <div class="nombre-tienda">{{ strtoupper($config->nombre_tienda ?? 'MI TIENDA') }}</div>

    {{-- Info de la tienda --}}
    @if($config->direccion)
        <div class="info-tienda">{{ $config->direccion }}</div>
    @endif
    @if($config->telefono)
        <div class="info-tienda">Tel: {{ $config->telefono }}</div>
    @endif
    @if($config->nit)
        <div class="info-tienda">NIT: {{ $config->nit }}</div>
    @endif

    <hr class="linea-doble">
    <div class="titulo-seccion">Venta #{{ $venta->numero_folio }}</div>
    <hr class="linea-doble">

    {{-- Datos de la venta --}}
    <table class="datos-venta">
        <tr>
            <td class="label">FECHA:</td>
            <td>{{ $venta->created_at->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <td class="label">USUARIO:</td>
            <td>{{ strtoupper($venta->user->name ?? 'Usuario') }}</td>
        </tr>
        <tr>
            <td class="label">CLIENTE:</td>
            <td>{{ $venta->cliente->nombre ?? 'Consumidor Final' }}</td>
        </tr>
    </table>

    <hr class="linea">
    <div class="center bold" style="letter-spacing: 3px; margin: 2px 0;">D E T A L L E</div>
    <hr class="linea">

    {{-- Items --}}
    <table class="items-table">
        @foreach($venta->ventaItems as $item)
            <tr>
                <td class="cant">{{ $item->cantidad_formateada }}</td>
                <td class="nombre">{{ $truncateMiddle($item->producto->nombre ?? 'Producto', $nombreLimit) }}</td>
                <td class="precio">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <hr class="linea">

    {{-- Totales --}}
    <table class="totales-table">
        <tr class="total-principal">
            <td class="total-label">TOTAL:</td>
            <td class="total-valor">{{ number_format($venta->efectivo + $venta->online + $venta->credito, 2) }}</td>
        </tr>
        @if($venta->efectivo > 0)
            <tr>
                <td class="total-label">EFECTIVO:</td>
                <td class="total-valor">{{ number_format($venta->efectivo, 2) }}</td>
            </tr>
        @endif
        @if($venta->online > 0)
            <tr>
                <td class="total-label">ONLINE:</td>
                <td class="total-valor">{{ number_format($venta->online, 2) }}</td>
            </tr>
        @endif
        @if($venta->credito > 0)
            <tr>
                <td class="total-label">CRÉDITO:</td>
                <td class="total-valor">{{ number_format($venta->credito, 2) }}</td>
            </tr>
        @endif
        @if($venta->cambio > 0)
            <tr>
                <td class="total-label">CAMBIO:</td>
                <td class="total-valor">{{ number_format($venta->cambio, 2) }}</td>
            </tr>
        @endif
    </table>

    <hr class="linea-doble">
    <div class="mensaje-final">GRACIAS POR SU COMPRA</div>

    @if($config->propietario_celular)
        <div class="center" style="font-size: 10px; margin-top: 2px;">
            CEL: {{ $config->propietario_celular }}
        </div>
    @endif

    <div class="pie">
        {{ now()->format('d/m/Y H:i:s') }} | MiSocio
    </div>

    {{-- Botón para reimprimir (visible solo en pantalla) --}}
    <div class="no-print" style="margin-top: 15px;">
        <button onclick="window.print()" style="padding: 8px 20px; font-size: 14px; cursor: pointer; background: #28a745; color: white; border: none; border-radius: 4px;">
            🖨️ Imprimir
        </button>
        <button onclick="window.close()" style="padding: 8px 20px; font-size: 14px; cursor: pointer; background: #6c757d; color: white; border: none; border-radius: 4px; margin-left: 5px;">
            ✕ Cerrar
        </button>
    </div>

    <script>
        // Auto-imprimir al cargar la página
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
