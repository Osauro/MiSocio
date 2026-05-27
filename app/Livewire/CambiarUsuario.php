<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CambiarUsuario extends Component
{
    public bool $showOverlay = false;

    /** @var array<int, array<string, mixed>> */
    public array $usuarios = [];

    protected $listeners = ['openCambiarUsuario' => 'open'];

    public function open(): void
    {
        $tenantId = currentTenantId();

        $this->usuarios = User::whereHas('tenants', function ($q) use ($tenantId) {
                $q->where('tenants.id', $tenantId)->where('tenant_user.is_active', true);
            })
            ->with(['tenants' => function ($q) use ($tenantId) {
                $q->where('tenants.id', $tenantId);
            }])
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'     => $u->id,
                'name'   => $u->name,
                'photo'  => $u->photo_url,
                'role'   => $u->tenants->first()?->pivot->role ?? 'user',
            ])
            ->toArray();

        $this->showOverlay = true;

        if (count($this->usuarios) === 1) {
            $this->cambiarA($this->usuarios[0]['id']);
        }
    }

    public function cambiarA(int $id): void
    {
        $user = User::find($id);
        if (!$user) return;

        $tenantId = currentTenantId();
        Auth::login($user);
        session(['current_tenant_id' => $tenantId]);

        $this->showOverlay = false;
        $this->redirect(route('ventas'));
    }

    public function close(): void
    {
        $this->showOverlay = false;
    }

    public function render()
    {
        return view('livewire.cambiar-usuario');
    }
}
