<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamo #{{ $prestamo->numero_folio }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .info-section {
            margin-bottom: 20px;
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 150px;
            padding: 5px 0;
        }
        .info-value {
            display: table-cell;
            padding: 5px 0;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background-color: #28a745; color: white; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-danger  { background-color: #dc3545; color: white; }
        .badge-info    { background-color: #17a2b8; color: white; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table thead { background-color: #f8f9fa; }
        table th, table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        table th { font-weight: bold; }
        table tfoot {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .text-primary { color: #007bff; }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #6c757d;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PRÉSTAMO #{{ $prestamo->numero_folio }}</h1>
        <p style="margin: 5px 0;">{{ currentTenant()->name ?? 'MiSocio' }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Estado:</div>
            <div class="info-value">
                @php $estadoReal = $prestamo->estado_real; @endphp
                <span class="badge {{ $estadoReal === 'Devuelto' ? 'badge-success' : ($estadoReal === 'Vencido' ? 'badge-danger' : ($estadoReal === 'Prestado' ? 'badge-info' : 'badge-warning')) }}">
                    {{ $estadoReal }}
                </span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Cliente:</div>
            <div class="info-value">{{ $prestamo->cliente->nombre ?? 'Sin cliente' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Usuario:</div>
            <div class="info-value">{{ $prestamo->user->name ?? 'Usuario' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha:</div>
            <div class="info-value">{{ $prestamo->created_at->format('d/m/Y H:i') }}</div>
        </div>
        @if ($prestamo->expired_at)
            <div class="info-row">
                <div class="info-label">Vencimiento:</div>
                <div class="info-value">{{ $prestamo->expired_at->format('d/m/Y') }}</div>
            </div>
        @endif
        @if ($prestamo->estado === 'Devuelto')
            <div class="info-row">
                <div class="info-label">Devolución:</div>
                <div class="info-value">{{ $prestamo->updated_at->format('d/m/Y') }}</div>
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="text-center" width="60">Cantidad</th>
                <th class="text-right" width="90">Precio</th>
                <th class="text-right" width="110">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($prestamo->prestamoItems as $item)
                <tr>
                    <td>{{ $item->producto->nombre ?? 'Producto' }}</td>
                    <td class="text-center">{{ $item->cantidad }}</td>
                    <td class="text-right">Bs. {{ number_format($item->precio_por_paquete, 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">Total / En garantía:</td>
                <td class="text-right text-primary">Bs. {{ number_format($prestamo->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }} | MiSocio - Sistema de Gestión</p>
    </div>
</body>
</html>
