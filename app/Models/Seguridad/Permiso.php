<?php

namespace App\Models\Seguridad;

use Database\Factories\Seguridad\PermisoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Define la responsabilidad de la clase Permiso dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Permiso extends Model
{
    /** @use HasFactory<PermisoFactory> */
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    // Usa la tabla 'permisos' como origen de los registros de este modelo.
    protected $table = 'permisos';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['modulo_id', 'pantalla_id', 'codigo', 'accion', 'nombre', 'activo'];

    /**
     * Ejecuta la operación casts definida por la clase Permiso.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    /**
     * Ejecuta la operación roles definida por la clase Permiso.
     *
     * @return BelongsToMany Resultado de la operación.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'roles_permisos', 'permiso_id', 'rol_id')
            ->withPivot('alcance');
    }
}
