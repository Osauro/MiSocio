<?php

namespace App\Livewire;

use App\Models\Membresia;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class HomeLandlord extends Component
{
    #[Layout('layouts.landlord.theme')]

    public int $anioIngresos;

    public function mount(): void
    {
        $this->anioIngresos = now()->year;
    }

    public function updatedAnioIngresos(): void
    {
        $datos = $this->calcularIngresosPorMes($this->anioIngresos);
        $this->dispatch('anioIngresosActualizado', datos: $datos);
    }

    private function calcularIngresosPorMes(int $anio): array
    {
        return collect(range(1, 12))->map(function ($mes) use ($anio) {
            $total = Membresia::withoutGlobalScope('tenant')
                ->where('estado_pago', 'verificado')
                ->whereYear('verificado_at', $anio)
                ->whereMonth('verificado_at', $mes)
                ->sum('monto');
            return [
                'mes'   => Carbon::createFromDate($anio, $mes, 1)->translatedFormat('M'),
                'total' => (float) $total,
            ];
        })->toArray();
    }

    public function render()
    {
        $hoy = Carbon::today();

        // ── Tarjetas de estadísticas ──────────────────────────────────────────
        $totalTenants     = Tenant::count();
        $tenantsActivos   = Tenant::where('status', 1)->count();
        $tenantsVencidos  = Tenant::where('status', 1)->whereNotNull('bill_date')->where('bill_date', '<', $hoy)->count();
        $tenantsProximos  = Tenant::where('status', 1)->whereNotNull('bill_date')
                               ->whereBetween('bill_date', [$hoy, $hoy->copy()->addDays(7)])->count();
        $totalUsuarios    = User::where('is_super_admin', false)->count();

        $pagosPendientes  = Membresia::withoutGlobalScope('tenant')->where('estado_pago', 'pendiente')->count();
        $ingresosMes      = Membresia::withoutGlobalScope('tenant')
                               ->where('estado_pago', 'verificado')
                               ->whereYear('verificado_at', $hoy->year)
                               ->whereMonth('verificado_at', $hoy->month)
                               ->sum('monto');
        $ingresosTotal    = Membresia::withoutGlobalScope('tenant')->where('estado_pago', 'verificado')->sum('monto');

        // ── Gráfico: Ingresos 12 meses del año seleccionado ──────────────────
        $ingresosPorMes = $this->calcularIngresosPorMes($this->anioIngresos);

        // Años disponibles (desde el primer registro verificado hasta hoy)
        $primerAnio = (int) (Membresia::withoutGlobalScope('tenant')
            ->where('estado_pago', 'verificado')
            ->min(DB::raw('YEAR(verificado_at)')) ?? $hoy->year);
        $aniosDisponibles = range($hoy->year, max($primerAnio, $hoy->year - 5));

        // ── Gráfico: Distribución de estados de pago ─────────────────────────
        $estadosPago = Membresia::withoutGlobalScope('tenant')
            ->select('estado_pago', DB::raw('count(*) as total'))
            ->groupBy('estado_pago')
            ->pluck('total', 'estado_pago');

        // ── Gráfico: Tenants por tipo de suscripción ─────────────────────────
        $porSuscripcion = Tenant::where('status', 1)
            ->select('subscription_type', DB::raw('count(*) as total'))
            ->groupBy('subscription_type')
            ->pluck('total', 'subscription_type');

        // ── Tablas ────────────────────────────────────────────────────────────
        $ultimosPendientes = Membresia::withoutGlobalScope('tenant')
            ->with('tenant')
            ->where('estado_pago', 'pendiente')
            ->latest()
            ->limit(6)
            ->get();

        $proximosVencer = Tenant::where('status', 1)
            ->whereNotNull('bill_date')
            ->where('bill_date', '>=', $hoy)
            ->orderBy('bill_date')
            ->limit(6)
            ->get();

        $ultimosTenants = Tenant::latest()->limit(5)->get();

        return view('livewire.home-landlord', compact(
            'totalTenants', 'tenantsActivos',
            'tenantsVencidos', 'tenantsProximos', 'totalUsuarios',
            'pagosPendientes', 'ingresosMes', 'ingresosTotal',
            'ingresosPorMes', 'aniosDisponibles',
            'estadosPago', 'porSuscripcion',
            'ultimosPendientes', 'proximosVencer', 'ultimosTenants'
        ));
    }
}

