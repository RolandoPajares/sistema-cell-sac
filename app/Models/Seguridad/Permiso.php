<?php

namespace App\Models\Seguridad;

use Database\Factories\Seguridad\PermisoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permiso extends Model
{
    /** @use HasFactory<PermisoFactory> */
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'permisos';

    protected $fillable = ['modulo_id', 'pantalla_id', 'codigo', 'accion', 'nombre', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'roles_permisos', 'permiso_id', 'rol_id')
            ->withPivot('alcance');
    }
}
