<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VarianteProducto extends Model
{
    protected $table = 'variantes_productos';

    protected $fillable = ['producto_id', 'codigo_sku', 'codigo_barras', 'nombre_color', 'codigo_color', 'existencias', 'controla_serie', 'estado', 'fecha_creacion'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['existencias' => 'integer', 'controla_serie' => 'boolean', 'fecha_creacion' => 'datetime'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ImagenVariante::class, 'variante_id')->orderBy('orden')->orderBy('id');
    }
}
