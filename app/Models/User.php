<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Los atributos asignables en masa.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'celular',
        'imagen',
        'password',
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Obtener los atributos que deben ser casteados.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Obtener los tenants a los que pertenece el usuario.
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class)
            ->withPivot('role', 'is_active')
            ->withTimestamps();
    }

    /**
     * Obtener el tenant actual de la sesión.
     */
    public function currentTenant(): ?Tenant
    {
        $tenantId = session('current_tenant_id');

        if (!$tenantId) {
            // Usar el primer tenant activo como predeterminado
            $firstTenant = $this->tenants()->wherePivot('is_active', true)->first();
            if ($firstTenant) {
                session(['current_tenant_id' => $firstTenant->id]);
                return $firstTenant;
            }
            return null;
        }

        return $this->tenants()->where('tenants.id', $tenantId)->first();
    }

    /**
     * Obtener el rol del usuario en el tenant actual.
     */
    public function roleInCurrentTenant(): ?string
    {
        $tenant = $this->currentTenant();
        return $tenant ? $tenant->pivot->role : null;
    }

    /**
     * Verificar si el usuario tiene un rol específico en el tenant actual.
     */
    public function hasRoleInCurrentTenant(string $role): bool
    {
        return $this->roleInCurrentTenant() === $role;
    }

    /**
     * Cambiar el tenant actual en la sesión.
     */
    public function switchTenant(int $tenantId): bool
    {
        $tenant = $this->tenants()->where('tenants.id', $tenantId)->wherePivot('is_active', true)->first();

        if ($tenant) {
            session(['current_tenant_id' => $tenantId]);
            return true;
        }

        return false;
    }

    /**
     * Obtener la URL de la foto del usuario o la imagen por defecto.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->imagen && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->imagen)) {
            return asset('storage/' . $this->imagen);
        }
        return asset('assets/images/profile.png');
    }
}
