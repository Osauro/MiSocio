<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $venta->numero_folio }}</title>
    @php
        $is58mm = ($config->papel_tamano ?? '80mm') === '58mm';
        $paperWidth = $is58mm ? '58mm' : '80mm';
        $bodyWidth = $is58mm ? '52mm' : '74mm';
        $logoWidth = $is58mm ? '35mm' : '50mm';
        $logoHeight = $is58mm ? '18mm' : '25mm';
        $fontBase = $is58mm ? '9px' : '11px';
        $fontTienda = $is58mm ? '12px' : '16px';
        $fontInfo = $is58mm ? '8px' : '10px';
        $fontSeccion = $is58mm ? '10px' : '13px';
        $fontItems = $is58mm ? '9px' : '11px';
        $fontCant = $is58mm ? '8px' : '10px';
        $fontTotales = $is58mm ? '10px' : '12px';
        $fontTotal = $is58mm ? '11px' : '14px';
        $fontMensaje = $is58mm ? '10px' : '12px';
        $fontPie = $is58mm ? '7px' : '9px';
        $cantWidth = $is58mm ? '40px' : '55px';
        $precioWidth = $is58mm ? '45px' : '55px';
        $totalWidth = $is58mm ? '55px' : '65px';
        $labelWidth = $is58mm ? '55px' : '70px';
        $nombreLimit = $is58mm ? 16 : 22;
    @endphp
    <style>
        /* Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            /* Ancho de papel térmico dinámico */
            size: {{ $paperWidth }} auto;
            margin: {{ $is58mm ? '1mm 2mm' : '2mm 3mm' }};
        }

        body {
            font-family: 'Courier New', 'Lucida Console', monospace;
            font-size: {{ $fontBase }};
            line-height: {{ $is58mm ? '1.2' : '1.3' }};
            color: #000;
            width: {{ $bodyWidth }};
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }

        .logo {
            display: block;
            margin: 0 auto {{ $is58mm ? '2px' : '4px' }};
            max-width: {{ $logoWidth }};
            max-height: {{ $logoHeight }};
        }

        .nombre-tienda {
            font-size: {{ $fontTienda }};
            font-weight: bold;
            text-align: center;
            margin-bottom: {{ $is58mm ? '1px' : '2px' }};
        }

        .info-tienda {
            text-align: center;
            font-size: {{ $fontInfo }};
            margin-bottom: {{ $is58mm ? '1px' : '2px' }};
        }

        .linea {
            border: none;
            border-top: 1px dashed #000;
            margin: {{ $is58mm ? '2px 0' : '4px 0' }};
        }

        .linea-doble {
            border: none;
            border-top: {{ $is58mm ? '1px' : '2px' }} solid #000;
            margin: {{ $is58mm ? '2px 0' : '4px 0' }};
        }

        .titulo-seccion {
            text-align: center;
            font-weight: bold;
            font-size: {{ $fontSeccion }};
            letter-spacing: {{ $is58mm ? '1px' : '2px' }};
            margin: {{ $is58mm ? '1px 0' : '2px 0' }};
        }

        .datos-venta {
            font-size: {{ $fontItems }};
        }

        .datos-venta td {
            padding: {{ $is58mm ? '0' : '1px 0' }};
            vertical-align: top;
        }

        .datos-venta .label {
            font-weight: bold;
            width: {{ $labelWidth }};
        }

        /* Tabla de items */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: {{ $fontItems }};
            margin: {{ $is58mm ? '1px 0' : '2px 0' }};
        }

        .items-table td {
            padding: {{ $is58mm ? '1px 0' : '2px 0' }};
            vertical-align: top;
        }

        .items-table .cant {
            width: {{ $cantWidth }};
            text-align: left;
            white-space: nowrap;
            font-size: {{ $fontCant }};
        }

        .items-table .nombre {
            text-align: left;
            overflow: hidden;
            white-space: nowrap;
        }

        .items-table .precio {
            width: {{ $precioWidth }};
            text-align: right;
        }

        /* Totales */
        .totales-table {
            width: 100%;
            border-collapse: collapse;
            font-size: {{ $fontTotales }};
        }

        .totales-table td {
            padding: {{ $is58mm ? '0' : '1px 0' }};
        }

        .totales-table .total-label {
            text-align: right;
            font-weight: bold;
            padding-right: {{ $is58mm ? '4px' : '8px' }};
        }

        .totales-table .total-valor {
            text-align: right;
            width: {{ $totalWidth }};
        }

        .total-principal {
            font-size: {{ $fontTotal }};
            font-weight: bold;
        }

        .mensaje-final {
            text-align: center;
            font-weight: bold;
            font-size: {{ $fontMensaje }};
            margin: {{ $is58mm ? '3px 0 1px' : '6px 0 2px' }};
        }

        .pie {
            text-align: center;
            font-size: {{ $fontPie }};
            color: #555;
            margin-top: {{ $is58mm ? '2px' : '4px' }};
        }

        /* Auto-print al abrir */
        @media print {
            body { width: 100%; }
        }
    </style>
</head>
<body>
    {{-- Logo --}}
    @if($config->logo)
        <img src="{{ public_path('storage/' . $config->logo) }}" class="logo" alt="Logo">
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

    {{-- Título --}}
    <div class="titulo-seccion">TICKET DE VENTA</div>

    <hr class="linea-doble">

    {{-- Datos de la venta --}}
    <table class="datos-venta" width="100%">
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
        <tr>
            <td class="label">FOLIO:</td>
            <td>#{{ $venta->numero_folio }}</td>
        </tr>
    </table>

    <hr class="linea">

    {{-- Detalle --}}
    <div class="center bold" style="letter-spacing: 3px; margin: 2px 0;">D E T A L L E</div>

    <hr class="linea">

    {{-- Items --}}
    <table class="items-table">
        @foreach($venta->ventaItems as $item)
            <tr>
                <td class="cant">{{ $item->cantidad_formateada }}</td>
                <td class="nombre">{{ Str::limit($item->producto->nombre ?? 'Producto', $nombreLimit, '') }}</td>
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

    {{-- Mensaje final --}}
    <div class="mensaje-final">GRACIAS POR SU COMPRA</div>

    @if($config->propietario_celular)
        <div class="center" style="font-size: 10px; margin-top: 2px;">
            CEL: {{ $config->propietario_celular }}
        </div>
    @endif

    <div class="pie">
        {{ now()->format('d/m/Y H:i:s') }} | LicoPOS
    </div>
</body>
</html>
