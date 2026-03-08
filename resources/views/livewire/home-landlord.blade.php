<div>
    <div class="container-fluid" style="padding-top: 0 !important;">

        {{-- ── Tarjetas de estadísticas ─────────────────────────────────────── --}}
        <div class="row g-2 mb-2">
            <div class="col-6 col-md-3">
                <div class="card mb-0 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1" style="font-size:.75rem;">Total Tenants</p>
                                <h3 class="mb-0 fw-bold">{{ $totalTenants }}</h3>
                                <small class="text-success"><i class="fa-solid fa-circle-check me-1"></i>{{ $tenantsActivos }} activos</small>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                style="width:46px;height:46px;background:rgba(27,85,226,.12);">
                                <i class="fa-solid fa-store text-primary fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card mb-0 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1" style="font-size:.75rem;">Ingresos del Mes</p>
                                <h3 class="mb-0 fw-bold text-success">Bs. {{ number_format($ingresosMes, 0) }}</h3>
                                <small class="text-muted">Total: Bs. {{ number_format($ingresosTotal, 0) }}</small>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                style="width:46px;height:46px;background:rgba(40,167,69,.12);">
                                <i class="fa-solid fa-dollar-sign text-success fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card mb-0 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1" style="font-size:.75rem;">Pagos Pendientes</p>
                                <h3 class="mb-0 fw-bold {{ $pagosPendientes > 0 ? 'text-warning' : 'text-muted' }}">{{ $pagosPendientes }}</h3>
                                <small class="{{ $tenantsVencidos > 0 ? 'text-danger' : 'text-muted' }}">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $tenantsVencidos }} tenants vencidos
                                </small>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                style="width:46px;height:46px;background:rgba(255,193,7,.12);">
                                <i class="fa-solid fa-clock text-warning fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card mb-0 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1" style="font-size:.75rem;">Usuarios</p>
                                <h3 class="mb-0 fw-bold">{{ $totalUsuarios }}</h3>
                                <small class="{{ $tenantsProximos > 0 ? 'text-warning' : 'text-muted' }}">
                                    <i class="fa-solid fa-calendar-days me-1"></i>{{ $tenantsProximos }} vencen en 7d
                                </small>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                style="width:46px;height:46px;background:rgba(108,117,125,.12);">
                                <i class="fa-solid fa-users text-secondary fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Gráficos ──────────────────────────────────────────────────────── --}}
        <div class="row g-2 mb-2">
            {{-- Ingresos por mes del año seleccionado --}}
            <div class="col-12 col-md-8">
                <div class="card mb-0 h-100">
                    <div class="card-header card-no-border py-2 px-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Ingresos por mes</h6>
                        <select wire:model.live="anioIngresos" class="form-select form-select-sm" style="width:auto;">
                            @foreach($aniosDisponibles as $anio)
                                <option value="{{ $anio }}">{{ $anio }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="card-body p-2" wire:ignore>
                        <div id="chart-ingresos"></div>
                    </div>
                </div>
            </div>

            {{-- Distribución estados de pago --}}
            <div class="col-12 col-md-4">
                <div class="card mb-0 h-100">
                    <div class="card-header card-no-border pb-0 pt-3 px-3">
                        <h6 class="mb-0"><i class="fa-solid fa-chart-pie me-2 text-warning"></i>Estado de pagos</h6>
                    </div>
                    <div class="card-body p-2 d-flex align-items-center justify-content-center">
                        <div id="chart-estados" style="width:100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-2">
            {{-- Distribución por tipo de suscripción --}}
            <div class="col-12 col-md-4">
                <div class="card mb-0 h-100">
                    <div class="card-header card-no-border pb-0 pt-3 px-3">
                        <h6 class="mb-0"><i class="fa-solid fa-layer-group me-2 text-info"></i>Tipo de suscripción</h6>
                    </div>
                    <div class="card-body p-2 d-flex align-items-center justify-content-center">
                        <div id="chart-suscripcion" style="width:100%;"></div>
                    </div>
                </div>
            </div>

            {{-- Tenants próximos a vencer --}}
            <div class="col-12 col-md-8">
                <div class="card mb-0 h-100">
                    <div class="card-header card-no-border pb-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa-solid fa-calendar-xmark me-2 text-danger"></i>Próximos a vencer</h6>
                        <a href="{{ route('admin.tenants') }}" class="btn btn-sm btn-outline-primary py-0">Ver todos</a>
                    </div>
                    <div class="card-body p-2">
                        @if($proximosVencer->isEmpty())
                            <div class="text-center py-3 text-muted">
                                <i class="fa-solid fa-circle-check fa-2x mb-2 text-success"></i>
                                <p class="mb-0 small">Ningún tenant vence próximamente</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" style="font-size:.82rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tenant</th>
                                            <th>Plan</th>
                                            <th>Vence</th>
                                            <th>Días</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($proximosVencer as $t)
                                            @php $dias = (int) now()->diffInDays($t->bill_date, false); @endphp
                                            <tr>
                                                <td class="fw-semibold">{{ $t->name }}</td>
                                                <td><span class="badge bg-primary" style="font-size:.65rem;">{{ ucfirst($t->subscription_type) }}</span></td>
                                                <td>{{ $t->bill_date->format('d/m/Y') }}</td>
                                                <td>
                                                    <span class="badge {{ $dias <= 3 ? 'bg-danger' : 'bg-warning text-dark' }}" style="font-size:.65rem;">
                                                        {{ $dias }}d
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Tablas inferiores ────────────────────────────────────────────── --}}
        <div class="row g-2 mb-4">
            {{-- Últimos pagos pendientes --}}
            <div class="col-12 col-md-6">
                <div class="card mb-0">
                    <div class="card-header card-no-border pb-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa-solid fa-receipt me-2 text-warning"></i>Pagos pendientes</h6>
                        <a href="{{ route('admin.pagos') }}" class="btn btn-sm btn-outline-warning py-0">Ver todos</a>
                    </div>
                    <div class="card-body p-2">
                        @if($ultimosPendientes->isEmpty())
                            <div class="text-center py-3 text-muted">
                                <i class="fa-solid fa-circle-check fa-2x mb-2 text-success"></i>
                                <p class="mb-0 small">Sin pagos pendientes</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0" style="font-size:.82rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Tenant</th>
                                            <th>Plan</th>
                                            <th class="text-end">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($ultimosPendientes as $p)
                                            <tr>
                                                <td class="text-muted">{{ $p->id }}</td>
                                                <td class="fw-semibold">{{ $p->tenant->name ?? '-' }}</td>
                                                <td><span class="badge bg-info" style="font-size:.65rem;">{{ ucfirst($p->plan_nombre) }}</span></td>
                                                <td class="text-end text-success fw-semibold">Bs. {{ number_format($p->monto, 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Últimos tenants creados --}}
            <div class="col-12 col-md-6">
                <div class="card mb-0">
                    <div class="card-header card-no-border pb-0 pt-3 px-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa-solid fa-store me-2 text-success"></i>Últimos tenants</h6>
                        <a href="{{ route('admin.tenants') }}" class="btn btn-sm btn-outline-success py-0">Ver todos</a>
                    </div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" style="font-size:.82rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Plan</th>
                                        <th>Estado</th>
                                        <th>Creado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ultimosTenants as $t)
                                        <tr>
                                            <td class="fw-semibold">{{ $t->name }}</td>
                                            <td><span class="badge bg-primary" style="font-size:.65rem;">{{ ucfirst($t->subscription_type) }}</span></td>
                                            <td>
                                                <span class="badge {{ $t->status ? 'bg-success' : 'bg-secondary' }}" style="font-size:.65rem;">
                                                    {{ $t->status ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td class="text-muted">{{ $t->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── ApexCharts ────────────────────────────────────────────────────────── --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Ingresos por mes del año seleccionado
            const ingresosData = @json($ingresosPorMes);
            window.chartIngresos = new ApexCharts(document.querySelector('#chart-ingresos'), {
                chart: { type: 'area', height: 200, toolbar: { show: false } },
                series: [{ name: 'Ingresos (Bs.)', data: ingresosData.map(d => d.total) }],
                xaxis: { categories: ingresosData.map(d => d.mes), labels: { style: { fontSize: '11px' } } },
                yaxis: { labels: { formatter: v => 'Bs. ' + Math.round(v).toLocaleString(), style: { fontSize: '11px' } } },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 } },
                colors: ['#1b55e2'],
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                tooltip: { y: { formatter: v => 'Bs. ' + v.toLocaleString() } },
                grid: { borderColor: '#f1f1f1', padding: { left: 5, right: 5 } },
            });
            window.chartIngresos.render();

            Livewire.on('anioIngresosActualizado', ({ datos }) => {
                window.chartIngresos.updateOptions(
                    { xaxis: { categories: datos.map(d => d.mes) } }, false, false
                );
                window.chartIngresos.updateSeries([{ name: 'Ingresos (Bs.)', data: datos.map(d => d.total) }]);
            });

            // Estado de pagos
            const estadosData = @json($estadosPago);
            const estadosLabels = Object.keys(estadosData).map(k => k.charAt(0).toUpperCase() + k.slice(1));
            const estadosValues = Object.values(estadosData);
            new ApexCharts(document.querySelector('#chart-estados'), {
                chart: { type: 'donut', height: 220, toolbar: { show: false } },
                series: estadosValues,
                labels: estadosLabels,
                colors: ['#ffc107', '#28a745', '#dc3545'],
                legend: { position: 'bottom', fontSize: '12px' },
                dataLabels: { enabled: true, formatter: (val) => Math.round(val) + '%' },
                plotOptions: { pie: { donut: { size: '65%' } } },
                tooltip: { y: { formatter: v => v + ' pagos' } },
            }).render();

            // Tipo de suscripción
            const suscData = @json($porSuscripcion);
            const suscLabels = Object.keys(suscData).map(k => k.charAt(0).toUpperCase() + k.slice(1));
            const suscValues = Object.values(suscData);
            new ApexCharts(document.querySelector('#chart-suscripcion'), {
                chart: { type: 'bar', height: 220, toolbar: { show: false } },
                series: [{ name: 'Tenants', data: suscValues }],
                xaxis: { categories: suscLabels, labels: { style: { fontSize: '11px' } } },
                yaxis: { labels: { style: { fontSize: '11px' } }, tickAmount: Math.max(...suscValues) > 0 ? undefined : 1 },
                colors: ['#1b55e2'],
                dataLabels: { enabled: true },
                plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
                grid: { borderColor: '#f1f1f1' },
                tooltip: { y: { formatter: v => v + ' tenants' } },
            }).render();
        });
    </script>
    @endpush
</div>

