<?php

namespace App\Models\Organizacion;

use Database\Factories\Organizacion\SedeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase Sede dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Sede extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<SedeFactory> */
    use HasFactory;

    // Usa la tabla 'sedes' como origen de los registros de este modelo.
    protected $table = 'sedes';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['empresa_id', 'codigo', 'nombre', 'direccion', 'telefono', 'zona_horaria', 'activa'];

    /**
     * Ejecuta la operación casts definida por la clase Sede.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    /**
     * Ejecuta la operación empresa definida por la clase Sede.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Ejecuta la operación almacenes definida por la clase Sede.
     *
     * @return HasMany Resultado de la operación.
     */
    public function almacenes(): HasMany
    {
        return $this->hasMany(Almacen::class, 'sede_id')->orderBy('nombre');
    }
}
