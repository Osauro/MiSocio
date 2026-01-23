<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'tenant_id',
        'name',
        'celular',
        'imagen',
        'password',
        'role',
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
     * Obtener el tenant al que pertenece el usuario.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
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
