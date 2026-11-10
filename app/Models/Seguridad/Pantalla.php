<?php

namespace App\Models\Seguridad;

use Database\Factories\Seguridad\PantallaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase Pantalla dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Pantalla extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<PantallaFactory> */
    use HasFactory;

    protected $table = 'pantallas';

    protected $fillable = ['modulo_id', 'codigo', 'nombre', 'ruta', 'icono', 'habilitada'];

    /**
     * Ejecuta la operación casts definida por la clase Pantalla.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['habilitada' => 'boolean'];
    }

    /**
     * Ejecuta la operación modulo definida por la clase Pantalla.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class, 'pantalla_id');
    }
}
