<?php

namespace App\Models\Cuenta;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioPerfilCampo extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    // Usa la tabla 'usuarios_perfil_campos' como origen de los registros de este modelo.
    protected $table = 'usuarios_perfil_campos';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['usuario_id', 'perfil_campo_id', 'valor'];

    // Define la relacion 'user' entre este modelo y los registros relacionados.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Define la relacion 'campo' entre este modelo y los registros relacionados.
    public function campo(): BelongsTo
    {
        return $this->belongsTo(PerfilCampo::class, 'perfil_campo_id');
    }
}
