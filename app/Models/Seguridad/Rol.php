<?php

namespace App\Models\Seguridad;

use App\Models\User;
use Database\Factories\Seguridad\RolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    /** @use HasFactory<RolFactory> */
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'roles';

    protected $fillable = ['codigo', 'nombre', 'descripcion', 'alcance_permitido', 'es_sistema', 'activo'];

    protected function casts(): array
    {
        return [
            'es_sistema' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(Permiso::class, 'roles_permisos', 'rol_id', 'permiso_id')
            ->withPivot('alcance');
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'roles_menus', 'rol_id', 'menu_id');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuarios_roles', 'rol_id', 'usuario_id')
            ->withPivot(['asignado_por_usuario_id', 'asignado_en', 'vigente_hasta', 'activo']);
    }
}
