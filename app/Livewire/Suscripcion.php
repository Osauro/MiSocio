<?php

namespace App\Livewire;

use App\Models\Tenant;
use App\Models\PlanSuscripcion;
use App\Traits\SweetAlertTrait;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Suscripcion extends Component
{
    use SweetAlertTrait;

    // Tenant actual
    public $tenant;
    public $diasRestantes;
    public $estadoSuscripcion;
    public $proximoPago;
    public $historialPagos;
    public $colorEstado;
    public $textoEstado;
    public $tipoSuscripcion;

    // Todos los tenants del usuario
    public $misTenants;

    // Modal de nuevo tenant
    public $modalOpen = false;
    public $name;
    public $theme_number = 1;
    public $domain;
    public $plan_suscripcion_id;
    public $subscription_type = 'demo';
    public $yaExisteDemo = false;

    protected $rules = [
        'name' => 'required|string|max:255|min:3',
        'theme_number' => 'required|integer|min:1|max:10',
        'domain' => 'nullable|string|max:255|unique:tenants,domain',
        'plan_suscripcion_id' => 'nullable|exists:planes_suscripcion,id',
        'subscription_type' => 'required|in:demo,mensual,trimestral,semestral,anual',
    ];

    public function mount()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        // Cargar todos los tenants del usuario
        $this->misTenants = auth()->user()
            ->tenants()
            ->with(['planSuscripcion', 'membresias'])
            ->withCount('users')
            ->orderBy('created_at', 'desc')
            ->get();

        // Verificar si tiene tenants activos o por vencer
        $tieneTenantsActivos = $this->misTenants->filter(function($t) {
            if (!$t->bill_date) return false;
            $diasRestantes = now()->diffInDays($t->bill_date, false);
            return $diasRestantes >= 0; // Activo o por vencer (no vencido)
        })->count() > 0;

        // Solo ofrecer demo si NO tiene ningún tenant activo o por vencer
        $this->yaExisteDemo = $tieneTenantsActivos;

        // Si ya existe demo, cambiar el tipo de suscripción por defecto
        if ($this->yaExisteDemo && $this->subscription_type === 'demo') {
            $this->subscription_type = 'mensual';
        }

        // Cargar datos del tenant actual
        $tenantId = currentTenantId();

        if ($tenantId) {
            $this->tenant = Tenant::with('membresias')->find($tenantId);
            if ($this->tenant) {
                $this->calcularDatosSubscripcion();
                $this->calcularTipoSuscripcion();
                $this->historialPagos = $this->tenant->membresias()
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            }
        }
    }

    public function calcularDatosSubscripcion()
    {
        if ($this->tenant->bill_date) {
            $fechaPago = Carbon::parse($this->tenant->bill_date);
            $hoy = Carbon::now();

            // Calcular días restantes
            $this->diasRestantes = $hoy->diffInDays($fechaPago, false);

            // Determinar estado
            if ($this->diasRestantes < 0) {
                $this->estadoSuscripcion = 'vencida';
            } elseif ($this->diasRestantes <= 7) {
                $this->estadoSuscripcion = 'por-vencer';
            } else {
                $this->estadoSuscripcion = 'activa';
            }

            $this->proximoPago = $fechaPago->format('d/m/Y');
        } else {
            $this->diasRestantes = null;
            $this->estadoSuscripcion = 'sin-fecha';
            $this->proximoPago = 'No definida';
        }

        // Calcular color y texto del estado
        $this->colorEstado = match($this->estadoSuscripcion) {
            'activa' => 'success',
            'por-vencer' => 'warning',
            'vencida' => 'danger',
            default => 'secondary',
        };

        $this->textoEstado = match($this->estadoSuscripcion) {
            'activa' => 'Activa',
            'por-vencer' => 'Por Vencer',
            'vencida' => 'Vencida',
            'sin-fecha' => 'Sin Fecha',
            default => 'Desconocido',
        };
    }

    public function calcularTipoSuscripcion()
    {
        $tipos = [
            'demo' => 'Demo',
            'mensual' => 'Mensual',
            'trimestral' => 'Trimestral',
            'semestral' => 'Semestral',
            'anual' => 'Anual',
        ];

        $this->tipoSuscripcion = $tipos[$this->tenant->subscription_type] ?? ucfirst($this->tenant->subscription_type ?? 'No definido');
    }

    public function crearTenant()
    {
        $this->reset(['name', 'theme_number', 'domain', 'plan_suscripcion_id', 'subscription_type']);
        $this->theme_number = rand(1, 10);

        // Si ya existe demo, no puede ser demo
        if ($this->yaExisteDemo) {
            $this->subscription_type = 'mensual';
        } else {
            $this->subscription_type = 'demo';
        }

        $this->modalOpen = true;
    }

    public function updatedPlanSuscripcionId($value)
    {
        if ($value) {
            $plan = PlanSuscripcion::find($value);
            if ($plan) {
                $this->subscription_type = $plan->slug;
            }
        }
    }

    public function guardarTenant()
    {
        // Validar límite de demo
        if ($this->subscription_type === 'demo' && $this->yaExisteDemo) {
            $this->alertError('El plan Demo solo está disponible para nuevos usuarios sin tiendas activas. Elige un plan de pago.');
            return;
        }

        $this->validate();

        try {
            // Determinar precio y fecha según el tipo
            $amount = 0;
            $bill_date = null;

            if ($this->plan_suscripcion_id) {
                $plan = PlanSuscripcion::find($this->plan_suscripcion_id);
                $amount = $plan->precio;
                $bill_date = $plan->duracion_meses > 0
                    ? now()->addMonths($plan->duracion_meses)
                    : now()->addDays(15); // Demo: 15 días
            } else {
                // Configuración manual
                $duraciones = ['demo' => 0, 'mensual' => 1, 'trimestral' => 3, 'semestral' => 6, 'anual' => 12];
                $precios = ['demo' => 0, 'mensual' => 120, 'trimestral' => 330, 'semestral' => 630, 'anual' => 1200];

                $meses = $duraciones[$this->subscription_type];
                $amount = $precios[$this->subscription_type];
                $bill_date = $meses > 0 ? now()->addMonths($meses) : now()->addDays(15);
            }

            // Crear tenant
            $tenant = Tenant::create([
                'name' => $this->name,
                'theme_number' => $this->theme_number,
                'domain' => $this->domain,
                'plan_suscripcion_id' => $this->plan_suscripcion_id,
                'subscription_type' => $this->subscription_type,
                'amount' => $amount,
                'bill_date' => $bill_date,
                'status' => true,
            ]);

            // Asociar usuario como propietario
            $tenant->users()->attach(auth()->id(), [
                'role' => 'tenant',
                'is_active' => true
            ]);

            $this->modalOpen = false;
            $this->cargarDatos();
            $this->alertSuccess('Tenant creado exitosamente. Haz clic en "Acceder" para configurarlo.');

        } catch (\Exception $e) {
            $this->alertError('Error al crear tenant: ' . $e->getMessage());
        }
    }

    public function cambiarTenant($tenantId)
    {
        $tenant = auth()->user()->tenants()->find($tenantId);

        if ($tenant && auth()->user()->switchTenant($tenantId)) {
            $this->cargarDatos();
            $this->alertSuccess('Has cambiado al tenant: ' . $tenant->name);

            // Refrescar la página para actualizar el sidebar y tema
            return redirect()->route('home');
        } else {
            $this->alertError('No se pudo cambiar de tenant');
        }
    }

    public function closeModal()
    {
        $this->modalOpen = false;
        $this->resetErrorBag();
    }

    public function renovarSuscripcion()
    {
        // Aquí puedes implementar la lógica de renovación
        // Por ejemplo, redirigir a un proceso de pago
        $this->alertInfo('La renovación manual estará disponible próximamente.');
    }

    public function render()
    {
        $planes = PlanSuscripcion::activos()->ordenados()->get();

        // Si ya tiene tenants activos, excluir el plan demo
        if ($this->yaExisteDemo) {
            $planes = $planes->filter(function($plan) {
                return $plan->precio > 0; // Solo planes de pago
            });
        }

        return view('livewire.suscripcion', [
            'planes' => $planes
        ]);
    }
}
