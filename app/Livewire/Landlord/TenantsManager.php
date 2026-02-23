<?php

namespace App\Livewire\Landlord;

use App\Models\Tenant;
use App\Models\User;
use App\Models\PlanSuscripcion;
use App\Traits\SweetAlertTrait;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class TenantsManager extends Component
{
    use WithPagination, SweetAlertTrait;

    public $search = '';
    public $perPage = 10;

    // Modal
    public $modalOpen = false;
    public $tenantId = null;
    public $name;
    public $theme_number = 1;
    public $domain;
    public $plan_suscripcion_id;
    public $subscription_type = 'mensual';
    public $amount = 0;
    public $bill_date;
    public $status = 1;

    // Usuario administrador del tenant
    public $admin_name;
    public $admin_email;
    public $admin_password;
    public $create_admin = false;
    public $selected_admin_id;

    protected $queryString = ['search'];

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'theme_number' => 'required|integer|min:1|max:10',
            'domain' => 'nullable|string|max:255|unique:tenants,domain,' . $this->tenantId,
            'plan_suscripcion_id' => 'nullable|exists:planes_suscripcion,id',
            'subscription_type' => 'required|in:mensual,trimestral,semestral,anual',
            'amount' => 'required|numeric|min:0',
            'bill_date' => 'nullable|date',
            'status' => 'required|boolean',
        ];

        if ($this->create_admin) {
            $rules['admin_name'] = 'required|string|max:255';
            $rules['admin_email'] = 'required|email|unique:users,email';
            $rules['admin_password'] = 'required|min:8';
        } elseif (!$this->tenantId) {
            $rules['selected_admin_id'] = 'required|exists:users,id';
        }

        return $rules;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedPlanSuscripcionId($value)
    {
        if ($value) {
            $plan = PlanSuscripcion::find($value);
            if ($plan) {
                $this->amount = $plan->precio;
                $this->subscription_type = $plan->slug;
            }
        }
    }

    public function create()
    {
        $this->resetForm();
        $this->modalOpen = true;
    }

    public function edit($id)
    {
        $tenant = Tenant::findOrFail($id);

        $this->tenantId = $tenant->id;
        $this->name = $tenant->name;
        $this->theme_number = $tenant->theme_number;
        $this->domain = $tenant->domain;
        $this->plan_suscripcion_id = $tenant->plan_suscripcion_id;
        $this->subscription_type = $tenant->subscription_type;
        $this->amount = $tenant->amount;
        $this->bill_date = $tenant->bill_date?->format('Y-m-d');
        $this->status = $tenant->status;

        $this->modalOpen = true;
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->tenantId) {
                // Actualizar
                $tenant = Tenant::findOrFail($this->tenantId);
                $tenant->update([
                    'name' => $this->name,
                    'theme_number' => $this->theme_number,
                    'domain' => $this->domain,
                    'plan_suscripcion_id' => $this->plan_suscripcion_id,
                    'subscription_type' => $this->subscription_type,
                    'amount' => $this->amount,
                    'bill_date' => $this->bill_date,
                    'status' => $this->status,
                ]);

                $this->alertSuccess('Tenant actualizado exitosamente');
            } else {
                // Crear
                $tenant = Tenant::create([
                    'name' => $this->name,
                    'theme_number' => $this->theme_number,
                    'domain' => $this->domain,
                    'plan_suscripcion_id' => $this->plan_suscripcion_id,
                    'subscription_type' => $this->subscription_type,
                    'amount' => $this->amount,
                    'bill_date' => $this->bill_date,
                    'status' => $this->status,
                ]);

                // Crear o asignar usuario admin
                if ($this->create_admin) {
                    $admin = User::create([
                        'name' => $this->admin_name,
                        'email' => $this->admin_email,
                        'password' => bcrypt($this->admin_password),
                        'is_landlord' => false,
                    ]);

                    $tenant->users()->attach($admin->id, [
                        'role' => 'tenant_admin',
                        'is_active' => true
                    ]);
                } else {
                    $tenant->users()->attach($this->selected_admin_id, [
                        'role' => 'tenant_admin',
                        'is_active' => true
                    ]);
                }

                $this->alertSuccess('Tenant creado exitosamente');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            $this->alertError('Error: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $tenant = Tenant::findOrFail($id);
            $tenant->delete();
            $this->alertSuccess('Tenant eliminado exitosamente');
        } catch (\Exception $e) {
            $this->alertError('Error al eliminar: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $tenant = Tenant::findOrFail($id);
            $tenant->update(['status' => !$tenant->status]);
            $this->alertSuccess('Estado actualizado');
        } catch (\Exception $e) {
            $this->alertError('Error: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->modalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->tenantId = null;
        $this->name = '';
        $this->theme_number = 1;
        $this->domain = '';
        $this->plan_suscripcion_id = null;
        $this->subscription_type = 'mensual';
        $this->amount = 0;
        $this->bill_date = null;
        $this->status = 1;
        $this->admin_name = '';
        $this->admin_email = '';
        $this->admin_password = '';
        $this->create_admin = false;
        $this->selected_admin_id = null;
        $this->resetErrorBag();
    }

    #[Layout('layouts.landlord.theme')]
    public function render()
    {
        $tenants = Tenant::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('domain', 'like', '%' . $this->search . '%')
                    ->orWhere('subscription_type', 'like', '%' . $this->search . '%');
            })
            ->with('planSuscripcion')
            ->withCount('users')
            ->latest()
            ->paginate($this->perPage);

        $availableAdmins = User::whereDoesntHave('tenants')->get();
        $planes = PlanSuscripcion::activos()->ordenados()->get();

        return view('livewire.landlord.tenants-manager', [
            'tenants' => $tenants,
            'availableAdmins' => $availableAdmins,
            'planes' => $planes
        ]);
    }
}
