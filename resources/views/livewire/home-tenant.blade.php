<div class="container-fluid">
    <div class="row">
        {{-- Tarjetas de Resumen --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden border-0">
                <div class="card-body" style="border-left: 4px solid #8B5CF6; min-height: 100px;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Capital</p>
                            <h4 class="mb-0 fw-bold">Bs. {{ number_format($estadisticasResumen['capital'], 2) }}</h4>
                        </div>
                        <div class="bg-light-purple rounded-circle p-3" style="background-color: rgba(139, 92, 246, 0.1);">
                            <i class="fa-solid fa-coins fa-2x" style="color: #8B5CF6;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden border-0">
                <div class="card-body" style="border-left: 4px solid #10B981; min-height: 100px;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted small">Beneficio</p>
                            <h4 class="mb-0 fw-bold">Bs. {{ number_format($estadisticasResumen['beneficio'], 2) }}</h4>
                            <small class="text-muted">
                                @php
                                    $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                @endphp
                                {{ $meses[$mesSeleccionado] }} {{ $anioSeleccionado }}
                            </small>
                        </div>
                        <div class="bg-light-success rounded-circle p-3" style="background-color: rgba(16, 185, 129, 0.1);">
                            <i class="fa-solid fa-chart-line fa-2x" style="color: #10B981;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden border-0">
                <div class="card-body" style="border-left: 4px solid #F59E0B; min-height: 100px;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Crédito</p>
                            <h4 class="mb-0 fw-bold">Bs. {{ number_format($estadisticasResumen['credito'], 2) }}</h4>
                        </div>
                        <div class="bg-light-warning rounded-circle p-3" style="background-color: rgba(245, 158, 11, 0.1);">
                            <i class="fa-solid fa-credit-card fa-2x" style="color: #F59E0B;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card o-hidden border-0">
                <div class="card-body" style="border-left: 4px solid #3B82F6; min-height: 100px;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted small">Online</p>
                            <h4 class="mb-0 fw-bold">Bs. {{ number_format($estadisticasResumen['online'], 2) }}</h4>
                            <small class="text-muted">
                                @php
                                    $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                                @endphp
                                {{ $meses[$mesSeleccionado] }} {{ $anioSeleccionado }}
                            </small>
                        </div>
                        <div class="bg-light-primary rounded-circle p-3" style="background-color: rgba(59, 130, 246, 0.1);">
                            <i class="fa-solid fa-globe fa-2x" style="color: #3B82F6;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        {{-- Ventas y Ganancias Semanales --}}
        <div class="col-lg-6 mb-3">
            <div class="card" style="max-height: 400px;">
                <div class="card-header pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Ventas y ganancias de la semana</h5>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($semanaFecha)->startOfWeek()->format('d/m/Y') }} -
                                {{ \Carbon\Carbon::parse($semanaFecha)->endOfWeek()->format('d/m/Y') }}
                            </small>
                        </div>
                        <div>
                            <input type="date" class="form-control form-control-sm" style="width: 150px;" wire:model.live="semanaFecha">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="ventasSemanalesChart" wire:ignore
                        data-dias='@json($ventasSemanales["dias"])'
                        data-ventas='@json($ventasSemanales["ventas"])'
                        data-ganancias='@json($ventasSemanales["ganancias"])'
                        height="250"></canvas>
                </div>
            </div>
        </div>

        {{-- Productos Vendidos Hoy --}}
        <div class="col-lg-6 mb-3">
            <div class="card" style="max-height: 400px;">
                <div class="card-header pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Productos Vendidos Hoy</h5>
                    <span class="badge bg-primary rounded-pill">{{ $ventasDelDia['total_count'] }} productos</span>
                </div>
                <div class="card-body p-0">
                    @if($ventasDelDia['total_count'] > 0)
                        <div style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead style="position: sticky; top: 0; background: #2b2b4b; color: #fff; z-index: 1;">
                                    <tr>
                                        <th class="ps-3 py-2" style="font-size: 0.75rem; letter-spacing: 0.05em; font-weight: 600;">PRODUCTO</th>
                                        <th class="text-end py-2" style="font-size: 0.75rem; letter-spacing: 0.05em; font-weight: 600;">CANTIDAD</th>
                                        <th class="text-end pe-3 py-2" style="font-size: 0.75rem; letter-spacing: 0.05em; font-weight: 600;">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ventasDelDia['productos'] as $prod)
                                        <tr>
                                            <td class="ps-3 py-2" style="font-size: 0.875rem;">{{ $prod['nombre'] }}</td>
                                            <td class="text-end py-2">
                                                <span class="text-primary fw-semibold" style="font-size: 0.875rem;">{{ $prod['cantidad'] }}</span>
                                            </td>
                                            <td class="text-end pe-3 py-2" style="font-size: 0.875rem;">
                                                <strong>{{ number_format($prod['total'], 2) }}</strong>
                                                <small class="text-muted ms-1">Bs</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                            <i class="fa-solid fa-cart-shopping fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Sin ventas hoy</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Estadísticas Mensuales --}}
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-header pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Compras, Ventas y Ganancias Mensuales</h5>
                        <div>
                            <select class="form-select form-select-sm" style="width: 100px;" wire:model.live="anioMensual">
                                @foreach($anosDisponibles as $anio)
                                    <option value="{{ $anio }}">{{ $anio }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="estadisticasMensualesChart" wire:ignore
                        data-meses='@json($estadisticasMensuales["meses"])'
                        data-ventas='@json($estadisticasMensuales["ventas"])'
                        data-compras='@json($estadisticasMensuales["compras"])'
                        data-ganancias='@json($estadisticasMensuales["ganancias"])'
                        data-anio="{{ $anioMensual }}"
                        height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Productos Más Vendidos --}}
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Más vendidos</h5>
                        <div>
                            <select class="form-select form-select-sm" style="width: 120px;" wire:model.live="mesMasVendidos">
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="masVendidosChart" wire:ignore
                        data-productos='@json($productosMasVendidos)'
                        height="300"></canvas>
                </div>
            </div>
        </div>

        {{-- Productos Más Comprados --}}
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Más comprados</h5>
                        <div>
                            <select class="form-select form-select-sm" style="width: 120px;" wire:model.live="mesMasComprados">
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="masCompradosChart" wire:ignore
                        data-productos='@json($productosMasComprados)'
                        height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        console.log('=== INICIO DEL SCRIPT DE GRÁFICOS ===');
        console.log('Chart.js disponible:', typeof Chart !== 'undefined');
        let ventasSemanalesChart, estadisticasMensualesChart, masVendidosChart, masCompradosChart;

        function crearGraficoSemanal(datosNuevos = null) {
            console.log('>>> crearGraficoSemanal() llamada');
            if (ventasSemanalesChart) {
                console.log('Destruyendo gráfico semanal anterior');
                ventasSemanalesChart.destroy();
            }

            const ctxSemanal = document.getElementById('ventasSemanalesChart');
            console.log('Canvas ventasSemanalesChart:', ctxSemanal);
            if (ctxSemanal) {
                // Usar datos nuevos si se proporcionan, sino leer desde atributos data-*
                let dias, ventas, ganancias;
                if (datosNuevos) {
                    dias = datosNuevos.dias || [];
                    ventas = datosNuevos.ventas || [];
                    ganancias = datosNuevos.ganancias || [];
                    console.log('Usando datos nuevos:', datosNuevos);
                } else {
                    dias = JSON.parse(ctxSemanal.dataset.dias || '[]');
                    ventas = JSON.parse(ctxSemanal.dataset.ventas || '[]');
                    ganancias = JSON.parse(ctxSemanal.dataset.ganancias || '[]');
                    console.log('Usando datos desde atributos data-*');
                }
                console.log('Datos semana:', {dias, ventas, ganancias});

                ventasSemanalesChart = new Chart(ctxSemanal.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: dias,
                        datasets: [
                            {
                                label: 'Ventas',
                                data: ventas,
                                borderColor: '#3B82F6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#3B82F6',
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'Ganancias',
                                data: ganancias,
                                borderColor: '#10B981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#10B981',
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                },
                                callbacks: {
                                    title: function(context) {
                                        return context[0].label;
                                    },
                                    label: function(context) {
                                        return context.dataset.label + ': Bs. ' + context.parsed.y.toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                    },
                                    afterBody: function(context) {
                                        const ventas = context[0].chart.data.datasets[0].data[context[0].dataIndex];
                                        const ganancias = context[0].chart.data.datasets[1].data[context[0].dataIndex];
                                        const margen = ventas > 0 ? ((ganancias / ventas) * 100).toFixed(2) : 0;
                                        return '\nMargen: ' + margen + '%';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        if (value >= 1000) {
                                            return 'Bs. ' + (value / 1000).toFixed(0) + 'k';
                                        }
                                        return 'Bs. ' + value.toFixed(0);
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('✓ Gráfico semanal creado exitosamente');
            } else {
                console.error('❌ No se encontró el canvas ventasSemanalesChart');
            }
        }

        function crearGraficoMensual(datosNuevos = null, anioNuevo = null) {
            console.log('>>> crearGraficoMensual() llamada');
            if (estadisticasMensualesChart) {
                console.log('Destruyendo gráfico mensual anterior');
                estadisticasMensualesChart.destroy();
            }

            const ctxMensual = document.getElementById('estadisticasMensualesChart');
            console.log('Canvas estadisticasMensualesChart:', ctxMensual);
            if (ctxMensual) {
                // Usar datos nuevos si se proporcionan, sino leer desde atributos data-*
                let meses, ventas, compras, ganancias, anio;
                if (datosNuevos) {
                    meses = datosNuevos.meses || [];
                    ventas = datosNuevos.ventas || [];
                    compras = datosNuevos.compras || [];
                    ganancias = datosNuevos.ganancias || [];
                    anio = anioNuevo || '';
                    console.log('Usando datos nuevos:', datosNuevos);
                } else {
                    meses = JSON.parse(ctxMensual.dataset.meses || '[]');
                    ventas = JSON.parse(ctxMensual.dataset.ventas || '[]');
                    compras = JSON.parse(ctxMensual.dataset.compras || '[]');
                    ganancias = JSON.parse(ctxMensual.dataset.ganancias || '[]');
                    anio = ctxMensual.dataset.anio || '';
                    console.log('Usando datos desde atributos data-*');
                }
                console.log('Datos mensuales:', {meses, ventas, compras, ganancias, anio});

                estadisticasMensualesChart = new Chart(ctxMensual.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: meses,
                        datasets: [
                            {
                                label: 'Ventas',
                                data: ventas,
                                backgroundColor: '#3B82F6',
                                borderRadius: 6
                            },
                            {
                                label: 'Compras',
                                data: compras,
                                backgroundColor: '#10B981',
                                borderRadius: 6
                            },
                            {
                                label: 'Ganancias',
                                data: ganancias,
                                backgroundColor: '#F59E0B',
                                borderRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom'
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                },
                                callbacks: {
                                    title: function(context) {
                                        return context[0].label + ' ' + anio;
                                    },
                                    label: function(context) {
                                        return context.dataset.label + ': Bs. ' + context.parsed.y.toLocaleString('es-BO', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                    },
                                    afterBody: function(context) {
                                        const ventas = context[0].chart.data.datasets[0].data[context[0].dataIndex];
                                        const compras = context[0].chart.data.datasets[1].data[context[0].dataIndex];
                                        const ganancias = context[0].chart.data.datasets[2].data[context[0].dataIndex];
                                        const margen = ventas > 0 ? ((ganancias / ventas) * 100).toFixed(2) : 0;
                                        return '\nMargen bruto: ' + margen + '%';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 100000,
                                    autoSkip: false,
                                    callback: function(value) {
                                        if (value >= 1000) {
                                            return 'Bs. ' + (value / 1000).toFixed(0) + 'k';
                                        }
                                        return 'Bs. ' + value.toFixed(0);
                                    }
                                },
                                grace: '0%'
                            }
                        }
                    }
                });
                console.log('✓ Gráfico mensual creado exitosamente');
            } else {
                console.error('❌ No se encontró el canvas estadisticasMensualesChart');
            }
        }

        function crearGraficoVendidos(productosNuevos = null) {
            console.log('>>> crearGraficoVendidos() llamada');
            if (masVendidosChart) {
                console.log('Destruyendo gráfico vendidos anterior');
                masVendidosChart.destroy();
            }

            const ctxVendidos = document.getElementById('masVendidosChart');
            console.log('Canvas masVendidosChart:', ctxVendidos);
            if (ctxVendidos) {
                // Usar datos nuevos si se proporcionan, sino leer desde atributos data-*
                let vendidosData;
                if (productosNuevos) {
                    vendidosData = productosNuevos;
                    console.log('Usando productos nuevos:', vendidosData);
                } else {
                    vendidosData = JSON.parse(ctxVendidos.dataset.productos || '[]');
                    console.log('Usando datos desde atributos data-*');
                }
                console.log('Datos vendidos:', vendidosData);

                masVendidosChart = new Chart(ctxVendidos.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: vendidosData.map(p => p.nombre),
                        datasets: [{
                            label: 'Cantidad vendida',
                            data: vendidosData.map(p => p.cantidad),
                            backgroundColor: '#3B82F6',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    size: 13,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 12
                                },
                                callbacks: {
                                    title: function(context) {
                                        return context[0].label;
                                    },
                                    label: function(context) {
                                        const idx = context.dataIndex;
                                        const producto = vendidosData[idx];
                                        const totalUnidades = Math.floor(producto.cantidad);
                                        const cantidadEmpaque = producto.cantidad_empaque || 1;
                                        const medida = producto.medida || 'unidad';

                                        const empaques = Math.floor(totalUnidades / cantidadEmpaque);
                                        const sobrante = totalUnidades % cantidadEmpaque;
                                        const inicialMedida = medida.charAt(0).toLowerCase();

                                        let resultado = 'Total: ' + empaques.toLocaleString('es-BO') + inicialMedida;
                                        if (sobrante > 0) {
                                            resultado += ' - ' + sobrante + 'u';
                                        }

                                        return resultado;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return Math.floor(value);
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('✓ Gráfico vendidos creado exitosamente');
            } else {
                console.error('❌ No se encontró el canvas masVendidosChart');
            }
        }

        function crearGraficoComprados(productosNuevos = null) {
            console.log('>>> crearGraficoComprados() llamada');
            if (masCompradosChart) {
                console.log('Destruyendo gráfico comprados anterior');
                masCompradosChart.destroy();
            }

            const ctxComprados = document.getElementById('masCompradosChart');
            console.log('Canvas masCompradosChart:', ctxComprados);
            if (ctxComprados) {
                // Usar datos nuevos si se proporcionan, sino leer desde atributos data-*
                let compradosData;
                if (productosNuevos) {
                    compradosData = productosNuevos;
                    console.log('Usando productos nuevos:', compradosData);
                } else {
                    compradosData = JSON.parse(ctxComprados.dataset.productos || '[]');
                    console.log('Usando datos desde atributos data-*');
                }
                console.log('Datos comprados:', compradosData);

                masCompradosChart = new Chart(ctxComprados.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: compradosData.map(p => p.nombre),
                        datasets: [{
                            label: 'Cantidad comprada',
                            data: compradosData.map(p => p.cantidad),
                            backgroundColor: '#10B981',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    size: 13,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 12
                                },
                                callbacks: {
                                    title: function(context) {
                                        return context[0].label;
                                    },
                                    label: function(context) {
                                        const idx = context.dataIndex;
                                        const producto = compradosData[idx];
                                        const totalUnidades = Math.floor(producto.cantidad);
                                        const cantidadEmpaque = producto.cantidad_empaque || 1;
                                        const medida = producto.medida || 'unidad';

                                        const empaques = Math.floor(totalUnidades / cantidadEmpaque);
                                        const sobrante = totalUnidades % cantidadEmpaque;
                                        const inicialMedida = medida.charAt(0).toLowerCase();

                                        let resultado = 'Total: ' + empaques.toLocaleString('es-BO') + inicialMedida;
                                        if (sobrante > 0) {
                                            resultado += ' - ' + sobrante + 'u';
                                        }

                                        return resultado;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return Math.floor(value);
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('✓ Gráfico comprados creado exitosamente');
            } else {
                console.error('❌ No se encontró el canvas masCompradosChart');
            }
        }

        function crearGraficos() {
            console.log('=== CREANDO TODOS LOS GRÁFICOS ===');
            console.log('Chart disponible:', typeof Chart);
            Chart.defaults.font.family = "'Nunito Sans', sans-serif";
            Chart.defaults.color = '#6c757d';

            console.log('Iniciando creación de gráficos...');
            crearGraficoSemanal();
            crearGraficoMensual();
            crearGraficoVendidos();
            crearGraficoComprados();
            console.log('=== TODOS LOS GRÁFICOS CREADOS ===');
        }

        // Crear gráficos al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            console.log('*** DOMContentLoaded disparado ***');
            console.log('Livewire disponible:', typeof Livewire !== 'undefined');
            console.log('Chart disponible:', typeof Chart !== 'undefined');
            crearGraficos();
        });

        // Recrear gráficos individuales cuando Livewire actualice los datos
        document.addEventListener('livewire:initialized', () => {
            console.log('*** Livewire initialized ***');

            Livewire.on('actualizarGraficoSemanal', (event) => {
                console.log('Evento actualizarGraficoSemanal recibido', event);
                setTimeout(() => {
                    console.log('Recreando gráfico semanal con datos actualizados...');
                    crearGraficoSemanal(event.datos);
                }, 150);
            });

            Livewire.on('actualizarGraficoMensual', (event) => {
                console.log('Evento actualizarGraficoMensual recibido', event);
                setTimeout(() => {
                    console.log('Recreando gráfico mensual con datos actualizados...');
                    crearGraficoMensual(event.datos, event.anio);
                }, 150);
            });

            Livewire.on('actualizarGraficoVendidos', (event) => {
                console.log('Evento actualizarGraficoVendidos recibido', event);
                setTimeout(() => {
                    console.log('Recreando gráfico vendidos con datos actualizados...');
                    crearGraficoVendidos(event.productos);
                }, 150);
            });

            Livewire.on('actualizarGraficoComprados', (event) => {
                console.log('Evento actualizarGraficoComprados recibido', event);
                setTimeout(() => {
                    console.log('Recreando gráfico comprados con datos actualizados...');
                    crearGraficoComprados(event.productos);
                }, 150);
            });

            console.log('Listeners de Livewire registrados');
        });
    </script>
    @endpush
</div>
