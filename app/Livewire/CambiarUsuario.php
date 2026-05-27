<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class CambiarUsuario extends Component
{
    public bool $showOverlay = false;
    public ?int $usuarioSeleccionado = null;
    public string $pin = '';
    public string $error = '';

    /** @var array<int, array<string, mixed>> */
    public array $usuarios = [];

    protected $listeners = ['openCambiarUsuario' => 'open'];

    public function open(): void
    {
        $tenantId = currentTenantId();

        $this->usuarios = User::whereHas('tenants', function ($q) use ($tenantId) {
                $q->where('tenants.id', $tenantId)->wherePivot('is_active', true);
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

        $this->reset(['pin', 'error', 'usuarioSeleccionado']);
        $this->showOverlay = true;

        if (count($this->usuarios) === 1) {
            $this->usuarioSeleccionado = $this->usuarios[0]['id'];
            $this->dispatch('focusPinInput');
        }
    }

    public function seleccionarUsuario(int $id): void
    {
        $this->usuarioSeleccionado = $id;
        $this->pin  = '';
        $this->error = '';
        $this->dispatch('focusPinInput');
    }

    public function confirmar(): void
    {
        if (!$this->usuarioSeleccionado || strlen($this->pin) !== 4) {
            $this->error = 'Ingresa el PIN de 4 dígitos';
            return;
        }

        $user = User::find($this->usuarioSeleccionado);

        if (!$user || !Hash::check($this->pin, $user->password)) {
            $this->error = 'PIN incorrecto';
            $this->pin   = '';
            $this->dispatch('focusPinInput');
            return;
        }

        // Verificar que el usuario aún pertenece al tenant actual
        $tenantId = currentTenantId();
        $esMiembro = $user->tenants()
            ->where('tenants.id', $tenantId)
            ->wherePivot('is_active', true)
            ->exists();

        if (!$esMiembro) {
            $this->error = 'El usuario no tiene acceso a esta tienda';
            $this->pin   = '';
            return;
        }

        Auth::login($user);
        // Mantener el mismo tenant en sesión
        session(['current_tenant_id' => $tenantId]);

        $this->showOverlay = false;
        $this->js('window.location.reload()');
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
