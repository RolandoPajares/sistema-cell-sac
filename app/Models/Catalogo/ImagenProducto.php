<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase ImagenProducto dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ImagenProducto extends Model
{
    // Usa la tabla 'referencias_imagenes_productos' como origen de los registros de este modelo.
    protected $table = 'referencias_imagenes_productos';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['producto_id', 'ruta_imagen', 'orden', 'actualizado_en'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase ImagenProducto.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['actualizado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación product definida por la clase ImagenProducto.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
