<?php

namespace App\Livewire;

use App\Models\Tenant;
use App\Models\PlanSuscripcion;
use App\Models\Membresia;
use App\Traits\SweetAlertTrait;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.landlord.theme')]
class CrearTenant extends Component
{
    use SweetAlertTrait, WithFileUploads;

    // Control de pasos
    public $paso = 1; // 1 = Selección de plan, 2 = Formulario de tienda, 3 = Pago y comprobante

    // Plan seleccionado
    public $planSeleccionado = null;
    public $tipoPlan = null; // 'demo', 'mensual', etc.

    // Datos de la tienda
    public $name;
    public $theme_number = 1;
    public $domain;

    // Pago
    public $comprobante;

    // Datos temporales del tenant creado
    public $tenantCreado = null;

    protected $rules = [
        'name' => 'required|string|max:255|min:3',
        'theme_number' => 'required|integer|min:1|max:10',
        'domain' => 'nullable|string|max:255|unique:tenants,domain',
        'comprobante' => 'nullable|image|max:2048', // 2MB máximo
    ];

    public function mount()
    {
        // No se necesita verificación previa
    }

    public function seleccionarPlan($planId)
    {
        $this->planSeleccionado = $planId;

        // Obtener el plan para determinar el tipo
        $plan = PlanSuscripcion::find($planId);
        $this->tipoPlan = ($plan && $plan->precio == 0) ? 'demo' : 'pago';

        $this->paso = 2;
    }

    public function volverAPlanes()
    {
        $this->paso = 1;
        $this->planSeleccionado = null;
        $this->tipoPlan = null;
    }

    public function volverAlFormulario()
    {
        $this->paso = 2;
    }

    public function crearTenant()
    {
        $this->validate([
            'name' => 'required|string|max:255|min:3',
            'theme_number' => 'required|integer|min:1|max:10',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
        ]);

        try {
            // Generar dominio automático si no se proporciona
            if (empty($this->domain)) {
                $this->domain = Str::slug($this->name) . '-' . Str::random(4);
            }

            // Obtener el plan seleccionado
            $plan = PlanSuscripcion::find($this->planSeleccionado);

            if (!$plan) {
                throw new \Exception('Plan no encontrado');
            }

            // Si es plan gratuito (demo), crear y activar directamente
            if ($plan->precio == 0) {
                return $this->crearTenantConPlan($plan, true);
            }

            // Si es plan de pago, ir al paso 3 (pago)
            $this->paso = 3;
        } catch (\Exception $e) {
            $this->alertError('Error al procesar', $e->getMessage());
        }
    }

    private function crearTenantConPlan($plan, $esGratuito = false)
    {
        try {
            // Calcular duración
            $duracionDias = $plan->duracion_meses > 0 ? ($plan->duracion_meses * 30) : 15;

            // Crear el tenant
            $tenant = Tenant::create([
                'name' => $this->name,
                'domain' => $this->domain,
                'theme_number' => $this->theme_number,
                'status' => $esGratuito ? 1 : 0, // Activo si es gratuito
                'bill_date' => now()->addDays($duracionDias),
                'plan_suscripcion_id' => $plan->id,
                'subscription_type' => $plan->slug, // Usar el slug del plan (demo, mensual, trimestral, etc.)
            ]);

            // Asociar el usuario actual al tenant como administrador
            $tenant->users()->attach(Auth::id(), [
                'role' => 'tenant',
                'is_active' => $esGratuito,
            ]);

            // Crear registro de membresía
            $tenant->membresias()->create([
                'plan_nombre' => $plan->nombre,
                'tipo' => $plan->slug,
                'duracion_meses' => $plan->duracion_meses,
                'monto' => $plan->precio,
                'fecha_inicio' => now(),
                'fecha_fin' => now()->addDays($duracionDias),
                'estado_pago' => $esGratuito ? 'verificado' : 'pendiente',
                'verificado_at' => $esGratuito ? now() : null,
                'verificado_por' => $esGratuito ? Auth::id() : null,
            ]);

            if ($esGratuito) {
                // Establecer este tenant como el actual en la sesión
                session(['current_tenant_id' => $tenant->id]);

                $this->alertSuccess("¡Tienda creada exitosamente! Tienes {$duracionDias} días de prueba.");

                // Redirigir al home del tenant
                return redirect()->route('dashboard');
            }

            return $tenant;
        } catch (\Exception $e) {
            $this->alertError('Error al crear la tienda', $e->getMessage());
            return null;
        }
    }

    public function procesarPago()
    {
        $this->validate([
            'comprobante' => 'required|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Obtener datos del plan
            $plan = PlanSuscripcion::find($this->planSeleccionado);
            if (!$plan) {
                throw new \Exception('Plan no encontrado');
            }

            // Crear el tenant (pendiente de verificación)
            $tenant = $this->crearTenantConPlan($plan, false);

            if (!$tenant) {
                throw new \Exception('Error al crear el tenant');
            }

            // Guardar comprobante
            $comprobanteUrl = $this->comprobante->store('comprobantes', 'public');

            // Actualizar la membresía con el comprobante (sin scope global)
            $membresia = Membresia::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->latest()
                ->first();

            if (!$membresia) {
                throw new \Exception('No se pudo obtener la membresía del tenant');
            }

            $membresia->update([
                'comprobante_url' => $comprobanteUrl,
            ]);

            DB::commit();

            $this->alertSuccess('¡Pago enviado exitosamente! Tu tienda será activada una vez verificado el pago.');

            // Redirigir a la página de login o home
            return redirect()->route('login')->with('message', 'Tu pago está en revisión. Te notificaremos cuando sea verificado.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->alertError('Error al procesar el pago', $e->getMessage());
        }
    }

    public function render()
    {
        // Obtener todos los planes activos
        $planes = PlanSuscripcion::activos()->ordenados()->get();

        // Verificar si el usuario ya tiene tenants activos o por vencer (excluyendo demo vencidos)
        $tieneTenantsActivos = Auth::user()
            ->tenants()
            ->where(function ($query) {
                $query->where('bill_date', '>=', now())
                    ->orWhereNull('bill_date');
            })
            ->count() > 0;

        // Si tiene tenants activos, excluir el plan demo
        if ($tieneTenantsActivos) {
            $planes = $planes->filter(function($plan) {
                return $plan->precio > 0; // Solo planes de pago
            });
        }

        // Obtener datos del plan seleccionado para el paso 3
        $planSeleccionadoData = null;
        if ($this->planSeleccionado) {
            $planSeleccionadoData = PlanSuscripcion::find($this->planSeleccionado);
        }

        return view('livewire.crear-tenant', [
            'planes' => $planes,
            'planData' => $planSeleccionadoData
        ]);
    }
}
