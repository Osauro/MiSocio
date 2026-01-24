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
     * Cada palabra con primera letra en mayúscula (Title Case).
     */
    public function setNombreAttribute($value): void
    {
        // Normalizar: Title Case (primera letra de cada palabra en mayúscula)
        $this->attributes['nombre'] = mb_convert_case(trim($value), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Buscar o crear un tag por nombre normalizado.
     */
    public static function findOrCreateByName(string $nombre): self
    {
        $nombreNormalizado = mb_convert_case(trim($nombre), MB_CASE_TITLE, 'UTF-8');

        return static::firstOrCreate(['nombre' => $nombreNormalizado]);
    }
}
