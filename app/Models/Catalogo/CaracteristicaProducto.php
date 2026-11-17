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
    // Usa la tabla 'caracteristicas_productos' como origen de los registros de este modelo.
    protected $table = 'caracteristicas_productos';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['producto_id',
                             'nombre', 
                             'valor'];

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
