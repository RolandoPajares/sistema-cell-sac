<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase PerfilInteligenciaProducto dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class PerfilInteligenciaProducto extends Model
{
    // Usa la tabla 'perfiles_inteligentes_productos' como origen de los registros de este modelo.
    protected $table = 'perfiles_inteligentes_productos';

    protected $primaryKey = 'producto_id';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['producto_id', 'puntuacion_rendimiento', 'puntuacion_camara', 'puntuacion_bateria', 'puntuacion_valor', 'actualizado_en'];

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase PerfilInteligenciaProducto.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['actualizado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación product definida por la clase PerfilInteligenciaProducto.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
