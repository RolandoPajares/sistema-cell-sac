<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerfilInteligenciaProducto extends Model
{
    protected $table = 'perfiles_inteligentes_productos';

    protected $primaryKey = 'producto_id';

    protected $fillable = ['producto_id', 'puntuacion_rendimiento', 'puntuacion_camara', 'puntuacion_bateria', 'puntuacion_valor', 'actualizado_en'];

    public $incrementing = false;

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
