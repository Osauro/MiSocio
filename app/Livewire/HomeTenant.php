<?php

namespace App\Livewire;

use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Kardex;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Traits\RequiresTenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class HomeTenant extends Component
{
    use RequiresTenant;

    public $estadisticasResumen = [];
    public $ventasSemanales = [];
    public $estadisticasMensuales = [];
    public $productosMasVendidos = [];
    public $productosMasComprados = [];
    public $ventasDelDia = [];

    // Filtros independientes para cada sección
    public $anioMensual;  // Para el gráfico mensual
    public $mesMasVendidos;  // Para productos más vendidos
    public $mesMasComprados;  // Para productos más comprados
    public $semanaFecha;  // Para gráfico semanal
    public $anosDisponibles = [];

    // Filtros para las tarjetas de resumen
    public $anioSeleccionado;
    public $mesSeleccionado;

    public function mount()
    {
        // Obtener años con datos
        $this->anosDisponibles = Venta::selectRaw('YEAR(created_at) as anio')
            ->groupBy('anio')
            ->orderByDesc('anio')
            ->pluck('anio')
            ->toArray();

        // Si no hay datos, usar año actual
        if (empty($this->anosDisponibles)) {
            $this->anosDisponibles = [Carbon::now()->year];
        }

        // Por defecto usar el año más reciente con datos
        $anioMasReciente = $this->anosDisponibles[0] ?? Carbon::now()->year;

        // Inicializar filtros
        $this->anioSeleccionado = $anioMasReciente;
        $this->mesSeleccionado = Carbon::now()->month;
        $this->anioMensual = $anioMasReciente;
        $this->mesMasVendidos = Carbon::now()->month;
        $this->mesMasComprados = Carbon::now()->month;
        $this->semanaFecha = Carbon::now()->format('Y-m-d');

        $this->cargarEstadisticas();
    }

    public function cargarEstadisticas()
    {
        $this->estadisticasResumen = $this->obtenerEstadisticasResumen();
        $this->ventasSemanales = $this->obtenerVentasSemanales();
        $this->estadisticasMensuales = $this->obtenerEstadisticasMensuales();
        $this->productosMasVendidos = $this->obtenerProductosMasVendidos();
        $this->productosMasComprados = $this->obtenerProductosMasComprados();
        $this->ventasDelDia = $this->obtenerVentasDelDia();
    }

    private function obtenerEstadisticasResumen()
    {
        // Capital: Valor total del inventario actual
        $capital = Producto::sum(DB::raw('stock * precio_de_compra'));

        // Beneficio del mes seleccionado
        $beneficioMes = VentaItem::whereHas('venta', function ($query) {
            $query->whereMonth('created_at', $this->mesSeleccionado)
                  ->whereYear('created_at', $this->anioSeleccionado);
        })->sum('beneficio');

        // Crédito pendiente (ventas a crédito no pagadas)
        $creditoPendiente = Venta::where('credito', '>', 0)
            ->sum('credito');

        // Ventas online del mes seleccionado
        $onlineMes = Venta::whereMonth('created_at', $this->mesSeleccionado)
            ->whereYear('created_at', $this->anioSeleccionado)
            ->sum('online');

        return [
            'capital' => $capital,
            'beneficio' => $beneficioMes,
            'credito' => $creditoPendiente,
            'online' => $onlineMes,
        ];
    }

    private function obtenerVentasSemanales()
    {
        $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $fechaBase = Carbon::parse($this->semanaFecha);
        $inicioSemana = $fechaBase->copy()->startOfWeek();

        $ventas = [];
        $ganancias = [];

        for ($i = 0; $i < 7; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);

            $ventaDia = Venta::whereDate('created_at', $dia->toDateString())
                ->sum(DB::raw('efectivo + online'));

            $gananciaDia = VentaItem::whereHas('venta', function ($query) use ($dia) {
                $query->whereDate('created_at', $dia->toDateString());
            })->sum('beneficio');

            $ventas[] = round($ventaDia, 2);
            $ganancias[] = round($gananciaDia, 2);
        }

        return [
            'dias' => $diasSemana,
            'ventas' => $ventas,
            'ganancias' => $ganancias,
        ];
    }

    private function obtenerEstadisticasMensuales()
    {
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        $ventas = [];
        $compras = [];
        $ganancias = [];

        for ($mes = 1; $mes <= 12; $mes++) {
            // Ventas del mes
            $ventaMes = Venta::whereMonth('created_at', $mes)
                ->whereYear('created_at', $this->anioMensual)
                ->sum(DB::raw('efectivo + online'));

            // Compras del mes
            $compraMes = Compra::whereMonth('created_at', $mes)
                ->whereYear('created_at', $this->anioMensual)
                ->sum('efectivo');

            // Ganancias del mes
            $gananciaMes = VentaItem::whereHas('venta', function ($query) use ($mes) {
                $query->whereMonth('created_at', $mes)
                      ->whereYear('created_at', $this->anioMensual);
            })->sum('beneficio');

            $ventas[] = round($ventaMes, 2);
            $compras[] = round($compraMes, 2);
            $ganancias[] = round($gananciaMes, 2);
        }

        return [
            'meses' => $meses,
            'ventas' => $ventas,
            'compras' => $compras,
            'ganancias' => $ganancias,
        ];
    }

    public function cambiarAnioMensual()
    {
        $this->estadisticasMensuales = $this->obtenerEstadisticasMensuales();
        $this->dispatch('actualizarGraficoMensual');
    }

    public function cambiarSemana($direccion)
    {
        $fecha = Carbon::parse($this->semanaFecha);
        if ($direccion === 'anterior') {
            $this->semanaFecha = $fecha->subWeek()->format('Y-m-d');
        } else {
            $this->semanaFecha = $fecha->addWeek()->format('Y-m-d');
        }
        $this->ventasSemanales = $this->obtenerVentasSemanales();
        $this->dispatch('actualizarGraficoSemanal');
    }

    public function cambiarMesMasVendidos()
    {
        $this->productosMasVendidos = $this->obtenerProductosMasVendidos();
        $this->dispatch('actualizarGraficoVendidos');
    }

    public function cambiarMesMasComprados()
    {
        $this->productosMasComprados = $this->obtenerProductosMasComprados();
        $this->dispatch('actualizarGraficoComprados');
    }

    private function obtenerProductosMasVendidos($limite = 15)
    {
        return VentaItem::select('producto_id', DB::raw('SUM(cantidad) as total_vendido'))
            ->with('producto')
            ->whereHas('venta', function ($query) {
                $query->whereMonth('created_at', $this->mesMasVendidos)
                      ->whereYear('created_at', $this->anioSeleccionado);
            })
            ->whereHas('producto', function ($query) {
                $query->where('categoria_id', '!=', 6); // Excluir categoría Envases
            })
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit($limite)
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->producto->nombre ?? 'Producto eliminado',
                    'cantidad' => $item->total_vendido,
                    'cantidad_empaque' => $item->producto->cantidad ?? 1,
                    'medida' => $item->producto->medida ?? 'unidad',
                ];
            });
    }

    private function obtenerProductosMasComprados($limite = 15)
    {
        return CompraItem::select('producto_id', DB::raw('SUM(cantidad) as total_comprado'))
            ->with('producto')
            ->whereHas('compra', function ($query) {
                $query->whereMonth('created_at', $this->mesMasComprados)
                      ->whereYear('created_at', $this->anioSeleccionado);
            })
            ->whereHas('producto', function ($query) {
                $query->where('categoria_id', '!=', 6); // Excluir categoría Envases
            })
            ->groupBy('producto_id')
            ->orderByDesc('total_comprado')
            ->limit($limite)
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->producto->nombre ?? 'Producto eliminado',
                    'cantidad' => $item->total_comprado,
                    'cantidad_empaque' => $item->producto->cantidad ?? 1,
                    'medida' => $item->producto->medida ?? 'unidad',
                ];
            });
    }

    private function obtenerVentasDelDia()
    {
        $hoy = Carbon::now()->toDateString();

        $productos = Kardex::select('producto_id', DB::raw('SUM(salida) as total_salida'), DB::raw('SUM(total) as total_bs'))
            ->where('salida', '>', 0)
            ->where('obs', 'like', 'Venta%')
            ->whereDate('created_at', $hoy)
            ->with('producto')
            ->groupBy('producto_id')
            ->orderByDesc('total_bs')
            ->get()
            ->map(function ($item) {
                $producto = $item->producto;
                return [
                    'nombre'    => $producto->nombre ?? 'Producto eliminado',
                    'cantidad'  => $this->formatearCantidadDia($item->total_salida, $producto),
                    'total'     => round($item->total_bs, 2),
                ];
            });

        return [
            'productos'   => $productos,
            'total_count' => $productos->count(),
        ];
    }

    private function formatearCantidadDia($cantidad, $producto): string
    {
        if (!$producto || $cantidad == 0) {
            return '0';
        }

        $cantidadPorMedida = $producto->cantidad ?? 1;

        if ($cantidadPorMedida <= 1) {
            return number_format($cantidad, 0) . 'u';
        }

        $medidaAbrev = strtolower(substr($producto->medida ?? 'u', 0, 1));
        $packs   = floor($cantidad / $cantidadPorMedida);
        $units   = $cantidad % $cantidadPorMedida;

        if ($packs > 0 && $units > 0) {
            return "{$packs}{$medidaAbrev} - {$units}u";
        } elseif ($packs > 0) {
            return "{$packs}{$medidaAbrev}";
        } else {
            return "{$units}u";
        }
    }

    public function updatedAnioSeleccionado()
    {
        $this->estadisticasResumen = $this->obtenerEstadisticasResumen();
    }

    public function updatedMesSeleccionado()
    {
        $this->estadisticasResumen = $this->obtenerEstadisticasResumen();
    }

    public function updatedSemanaFecha()
    {
        $this->ventasSemanales = $this->obtenerVentasSemanales();
        $this->dispatch('actualizarGraficoSemanal', datos: $this->ventasSemanales);
    }

    public function updatedAnioMensual()
    {
        $this->estadisticasMensuales = $this->obtenerEstadisticasMensuales();
        $this->dispatch('actualizarGraficoMensual', datos: $this->estadisticasMensuales, anio: $this->anioMensual);
    }

    public function updatedMesMasVendidos()
    {
        $this->productosMasVendidos = $this->obtenerProductosMasVendidos();
        $this->dispatch('actualizarGraficoVendidos', productos: $this->productosMasVendidos);
    }

    public function updatedMesMasComprados()
    {
        $this->productosMasComprados = $this->obtenerProductosMasComprados();
        $this->dispatch('actualizarGraficoComprados', productos: $this->productosMasComprados);
    }

    public function render()
    {
        return view('livewire.home-tenant');
    }
}
