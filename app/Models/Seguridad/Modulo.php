<?php

namespace App\Models\Seguridad;

use Database\Factories\Seguridad\ModuloFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase Modulo dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Modulo extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** @use HasFactory<ModuloFactory> */
    use HasFactory;

    protected $table = 'modulos';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'icono', 'orden', 'habilitado'];

    /**
     * Ejecuta la operación casts definida por la clase Modulo.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['orden' => 'integer', 'habilitado' => 'boolean'];
    }

    /**
     * Ejecuta la operación pantallas definida por la clase Modulo.
     *
     * @return HasMany Resultado de la operación.
     */
    public function pantallas(): HasMany
    {
        return $this->hasMany(Pantalla::class, 'modulo_id')->orderBy('nombre');
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class, 'modulo_id');
    }
}
