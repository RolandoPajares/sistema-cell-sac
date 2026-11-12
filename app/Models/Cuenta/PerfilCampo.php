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

    // Usa la tabla 'perfil_campos' como origen de los registros de este modelo.
    protected $table = 'perfil_campos';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['codigo', 'etiqueta', 'tipo', 'opciones', 'orden', 'activo'];

    // Ejecuta la logica del metodo 'casts' dentro de este modelo.
    protected function casts(): array
    {
        return [
            'opciones' => 'array',
            'orden' => 'integer',
            'activo' => 'boolean',
        ];
    }

    // Define la relacion 'roles' entre este modelo y los registros relacionados.
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'roles_perfil_campos', 'perfil_campo_id', 'rol_id')
            ->withPivot(['visible', 'editable', 'requerido']);
    }

    // Define la relacion 'valores' entre este modelo y los registros relacionados.
    public function valores(): HasMany
    {
        return $this->hasMany(UsuarioPerfilCampo::class, 'perfil_campo_id');
    }
}
