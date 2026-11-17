<?php

namespace App\Models\Seguridad;

use App\Models\Cuenta\PerfilCampo;
use App\Models\User;
use Database\Factories\Seguridad\RolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Define la responsabilidad de la clase Rol dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Rol extends Model
{
    /** @use HasFactory<RolFactory> */
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    // Usa la tabla 'roles' como origen de los registros de este modelo.
    protected $table = 'roles';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['codigo', 'nombre', 'descripcion', 'alcance_permitido', 'es_sistema', 'activo'];

    /**
     * Ejecuta la operación casts definida por la clase Rol.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return [
            'es_sistema' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Ejecuta la operación permisos definida por la clase Rol.
     *
     * @return BelongsToMany Resultado de la operación.
     */
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'roles_permisos', 'rol_id', 'permiso_id')
            ->withPivot('alcance');
    }

    /**
     * Ejecuta la operación menus definida por la clase Rol.
     *
     * @return BelongsToMany Resultado de la operación.
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'roles_menus', 'rol_id', 'menu_id');
    }

    /**
     * Ejecuta la operación usuarios definida por la clase Rol.
     *
     * @return BelongsToMany Resultado de la operación.
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuarios_roles', 'rol_id', 'usuario_id')
            ->withPivot(['asignado_por_usuario_id', 'asignado_en', 'vigente_hasta', 'activo']);
    }

    // Define la relacion 'camposPerfil' entre este modelo y los registros relacionados.
    public function camposPerfil(): BelongsToMany
    {
        return $this->belongsToMany(PerfilCampo::class, 'roles_perfil_campos', 'rol_id', 'perfil_campo_id')
            ->withPivot(['visible', 'editable', 'requerido']);
    }
}
