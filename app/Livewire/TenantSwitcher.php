<?php

namespace App\Livewire;

use Livewire\Component;
use App\Traits\SweetAlertTrait;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();
        if ($user) {
            $this->tenants = $user->tenants()->wherePivot('is_active', true)->get();
            $this->currentTenantId = currentTenantId();
        } else {
            $this->tenants = collect([]);
            $this->currentTenantId = null;
        }
    }

    public function switchTenant($tenantId)
    {
        $user = Auth::user();
        if ($user && $user->switchTenant($tenantId)) {
            $this->currentTenantId = $tenantId;
            // El JavaScript se encargará de recargar la página
            return true;
        }

        return false;
    }

    public function render()
    {
        return view('livewire.tenant-switcher');
    }
}
