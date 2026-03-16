<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'inventario_id',
        'producto_id',
        'stock_sistema',
        'stock_contado',
        'diferencia',
    ];

    protected $casts = [
        'stock_sistema' => 'integer',
        'stock_contado' => 'integer',
        'diferencia'    => 'integer',
    ];

    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class)->withTrashed();
    }
}
