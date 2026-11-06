<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase ImagenVariante dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ImagenVariante extends Model
{
    protected $table = 'imagenes_variantes';

    protected $fillable = ['variante_id', 'ruta_imagen', 'imagen_principal', 'orden', 'fecha_creacion'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase ImagenVariante.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['imagen_principal' => 'boolean', 'fecha_creacion' => 'datetime'];
    }

    /**
     * Ejecuta la operación variant definida por la clase ImagenVariante.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'variante_id');
    }
}
