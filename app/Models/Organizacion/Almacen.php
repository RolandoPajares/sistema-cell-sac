<?php

namespace App\Models\Organizacion;

use Database\Factories\Organizacion\AlmacenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase Almacen dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Almacen extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<AlmacenFactory> */
    use HasFactory;

    protected $table = 'almacenes';

    protected $fillable = ['sede_id', 'codigo', 'nombre', 'tipo', 'permite_venta', 'activo'];

    /**
     * Ejecuta la operación casts definida por la clase Almacen.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['permite_venta' => 'boolean', 'activo' => 'boolean'];
    }

    /**
     * Ejecuta la operación sede definida por la clase Almacen.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }
}
