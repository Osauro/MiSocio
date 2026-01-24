<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = [
        'nombre',
    ];

    /**
     * Relación con productos.
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_tag');
    }

    /**
     * Normalizar el nombre del tag antes de guardarlo.
     */
    public function setNombreAttribute($value): void
    {
        // Normalizar: primera letra mayúscula, resto minúsculas, sin espacios extras
        $this->attributes['nombre'] = ucfirst(trim(strtolower($value)));
    }

    /**
     * Buscar o crear un tag por nombre.
     */
    public static function findOrCreateByName(string $nombre): self
    {
        $nombreNormalizado = ucfirst(trim(strtolower($nombre)));

        return static::firstOrCreate(['nombre' => $nombreNormalizado]);
    }
}
