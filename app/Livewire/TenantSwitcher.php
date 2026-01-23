<?php

namespace App\Livewire;

use Livewire\Component;
use App\Traits\SweetAlertTrait;

class TenantSwitcher extends Component
{
    use SweetAlertTrait;

    public $tenants;
    public $currentTenantId;

    public function mount()
    {
        $this->loadTenants();
    }

    public function loadTenants()
    {
        $user = auth()->user();
        $this->tenants = $user->tenants()->wherePivot('is_active', true)->get();
        $this->currentTenantId = currentTenantId();
    }

    public function switchTenant($tenantId)
    {
        if (auth()->user()->switchTenant($tenantId)) {
            $this->currentTenantId = $tenantId;
            $this->showSuccessAlert('Tienda cambiada exitosamente');

            // Recargar la página para refrescar todos los datos
            return redirect()->to(request()->header('Referer', route('dashboard')));
        } else {
            $this->showErrorAlert('No se pudo cambiar de tienda');
        }
    }

    public function render()
    {
        return view('livewire.tenant-switcher');
    }
}
