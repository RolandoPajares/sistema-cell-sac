<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase CaracteristicaProducto dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CaracteristicaProducto extends Model
{
    protected $table = 'caracteristicas_productos';

    protected $fillable = ['producto_id', 'nombre', 'valor'];

    public $timestamps = false;

    /**
     * Ejecuta la operación product definida por la clase CaracteristicaProducto.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
