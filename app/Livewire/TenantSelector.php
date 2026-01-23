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
            $this->tenants = Auth::user()->tenants()->wherePivot('is_active', true)->get();
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
            $this->showOverlay = false;
            return true;
        }
        return false;
    }

    public function render()
    {
        return view('livewire.tenant-selector');
    }
}
