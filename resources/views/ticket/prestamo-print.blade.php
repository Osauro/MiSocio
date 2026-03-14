<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamo #{{ $prestamo->numero_folio }}</title>
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

        .logo-container {
            text-align: center;
            margin-bottom: 4px;
        }

        .logo {
            display: inline-block;
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

        .datos-prestamo {
            font-size: 11px;
            width: 100%;
        }

        .datos-prestamo td {
            padding: 1px 0;
            vertical-align: top;
        }

        .datos-prestamo .label {
            font-weight: bold;
            width: 55px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin: 2px 0;
        }

        .items-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .items-table .producto {
            text-align: left;
            font-weight: 500;
        }

        .items-table .producto .cant {
            font-weight: bold;
            display: inline;
        }

        .items-table .producto .nombre {
            display: inline;
        }

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

        /* Resaltar información del préstamo */
        .info-prestamo {
            background-color: #f0f0f0;
            border: 1px solid #333;
            padding: 3px;
            margin: 4px 0;
            text-align: center;
            font-weight: bold;
        }

        /* Ocultar controles en impresión */
        .no-print { display: block; text-align: center; margin: 10px 0; }
        @media print {
            body { width: 100%; padding: 0; }
            .no-print { display: none !important; }
            .info-prestamo { background-color: #f0f0f0; }
        }
    </style>
</head>
<body>
    {{-- Logo --}}
    @if($config->logo)
        <div class="logo-container">
            <img src="{{ asset('storage/' . $config->logo) }}" class="logo" alt="Logo">
        </div>
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
    <div class="titulo-seccion">Préstamo #{{ $prestamo->numero_folio }}</div>
    <hr class="linea-doble">

    {{-- Datos del préstamo --}}
    <table class="datos-prestamo">
        <tr>
            <td class="label">FECHA:</td>
            <td>{{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <td class="label">USUARIO:</td>
            <td>{{ strtoupper($prestamo->user->name ?? 'Usuario') }}</td>
        </tr>
        <tr>
            <td class="label">CLIENTE:</td>
            <td>{{ $prestamo->cliente->nombre ?? 'Sin cliente' }}</td>
        </tr>
        <tr>
            <td class="label">ESTADO:</td>
            <td>{{ strtoupper($prestamo->estado) }}</td>
        </tr>
        @if($prestamo->expired_at)
        <tr>
            <td class="label">VENCE:</td>
            <td>{{ \Carbon\Carbon::parse($prestamo->expired_at)->format('d/m/Y') }}</td>
        </tr>
        @endif
    </table>

    <hr class="linea">
    <div class="center bold" style="letter-spacing: 3px; margin: 2px 0;">P R O D U C T O S</div>
    <hr class="linea">

    {{-- Items --}}
    <table class="items-table">
        @foreach($prestamo->prestamoItems as $item)
            <tr>
                <td class="producto">
                    <span class="cant">{{ number_format($item->cantidad, 0) }} {{ $item->producto->unidad_medida ?? 'und' }}</span>
                    <span class="nombre">{{ $truncateMiddle($item->producto->nombre ?? 'Producto', $nombreLimit) }}</span>
                </td>
                <td class="precio">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <hr class="linea">

    {{-- Total depósito --}}
    <table class="totales-table">
        <tr class="total-principal">
            <td class="total-label">DEPÓSITO:</td>
            <td class="total-valor">{{ number_format($prestamo->total, 2) }}</td>
        </tr>
    </table>

    <hr class="linea-doble">

    {{-- Información importante del préstamo --}}
    <div class="info-prestamo">
        ⚠️ PRÉSTAMO - DEBE DEVOLVERSE
    </div>

    <div class="mensaje-final">{{ $config->mensaje_ticket ?? '¡GRACIAS POR SU PREFERENCIA!' }}</div>

    <div class="pie">
        Sistema MiSocio<br>
        {{ now()->format('d/m/Y H:i:s') }}
    </div>

    {{-- Botones de acción (no se imprimen) --}}
    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 4px; margin: 5px;">
            🖨️ Imprimir
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; background: #6c757d; color: white; border: none; border-radius: 4px; margin: 5px;">
            ✖️ Cerrar
        </button>
    </div>

    <script>
        // Auto-imprimir cuando la página cargue completamente
        window.addEventListener('load', function() {
            // Dar un pequeño delay para que el contenido se renderice completamente
            setTimeout(function() {
                window.print();
            }, 250);
        });
    </script>
</body>
</html>
