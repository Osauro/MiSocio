<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class TenantSelector extends Component
{
    public $showOverlay = false;
    public $tenants;
    public $currentTenantId;

    protected $listeners = ['openTenantSelector' => 'open'];

    public function mount()
    {
        $this->loadTenants();
    }

    public function loadTenants()
    {
        if (Auth::check()) {
            // Cargar todos los tenants del usuario con su relación pivot
            $allTenants = Auth::user()->tenants()->get();

            // Filtrar: mostrar si is_active=true en pivot O si el tenant está activo (status=1)
            $this->tenants = $allTenants->filter(function($tenant) {
                // Verificar que pivot exista para evitar errores
                if (!$tenant->pivot) {
                    return false;
                }
                return $tenant->pivot->is_active || $tenant->status == 1;
            });

            $this->currentTenantId = currentTenantId();
        }
    }

    public function open()
    {
        $this->showOverlay = true;
        $this->loadTenants();
    }

    public function close()
    {
        $this->showOverlay = false;
    }

    public function selectTenant($tenantId)
    {
        if (Auth::user()->switchTenant($tenantId)) {
            session()->flash('tenant_changed', true);
            $this->showOverlay = false;
            $this->js('window.location.reload()');
        }
    }

    public function render()
    {
        return view('livewire.tenant-selector');
    }
}
