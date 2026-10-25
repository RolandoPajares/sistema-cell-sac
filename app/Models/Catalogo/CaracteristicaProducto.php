<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaracteristicaProducto extends Model
{
    protected $table = 'producto_caracteristicas';

    protected $fillable = ['producto_id', 'nombre', 'valor'];

    public $timestamps = false;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
