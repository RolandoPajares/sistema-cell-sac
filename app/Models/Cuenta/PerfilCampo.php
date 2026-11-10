<?php

namespace App\Models\Cuenta;

use App\Models\Seguridad\Rol;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerfilCampo extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'perfil_campos';

    protected $fillable = ['codigo', 'etiqueta', 'tipo', 'opciones', 'orden', 'activo'];

    protected function casts(): array
    {
        return [
            'opciones' => 'array',
            'orden' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'roles_perfil_campos', 'perfil_campo_id', 'rol_id')
            ->withPivot(['visible', 'editable', 'requerido']);
    }

    public function valores(): HasMany
    {
        return $this->hasMany(UsuarioPerfilCampo::class, 'perfil_campo_id');
    }
}
