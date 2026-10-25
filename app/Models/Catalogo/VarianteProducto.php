<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VarianteProducto extends Model
{
    protected $table = 'producto_variantes';

    protected $fillable = ['producto_id', 'nombre_color', 'codigo_color', 'stock', 'estado', 'fecha_creacion'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['fecha_creacion' => 'datetime'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ImagenVariante::class, 'variante_id')->orderBy('orden');
    }
}
