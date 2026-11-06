<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase VarianteProducto dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class VarianteProducto extends Model
{
    protected $table = 'variantes_productos';

    protected $fillable = ['producto_id', 'codigo_sku', 'codigo_barras', 'nombre_color', 'codigo_color', 'existencias', 'controla_serie', 'estado', 'fecha_creacion'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase VarianteProducto.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['existencias' => 'integer', 'controla_serie' => 'boolean', 'fecha_creacion' => 'datetime'];
    }

    /**
     * Ejecuta la operación product definida por la clase VarianteProducto.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Ejecuta la operación images definida por la clase VarianteProducto.
     *
     * @return HasMany Resultado de la operación.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ImagenVariante::class, 'variante_id')->orderBy('orden')->orderBy('id');
    }
}
