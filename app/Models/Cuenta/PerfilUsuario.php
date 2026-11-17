<?php

namespace App\Models\Cuenta;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerfilUsuario extends Model
{
    // Usa la tabla 'usuarios_perfiles' como origen de los registros de este modelo.
    protected $table = 'usuarios_perfiles';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['usuario_id', 'telefono', 'documento'];

    // Define la relacion 'usuario' entre este modelo y los registros relacionados.
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
