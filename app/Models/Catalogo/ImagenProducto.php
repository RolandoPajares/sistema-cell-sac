<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagenProducto extends Model
{
    protected $table = 'producto_imagen_referencia';

    protected $fillable = ['producto_id', 'ruta_imagen', 'orden', 'actualizado_en'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['actualizado_en' => 'datetime'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
