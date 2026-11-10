<?php

namespace App\Models\Cuenta;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioPerfilCampo extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'usuarios_perfil_campos';

    protected $fillable = ['usuario_id', 'perfil_campo_id', 'valor'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function campo(): BelongsTo
    {
        return $this->belongsTo(PerfilCampo::class, 'perfil_campo_id');
    }
}
