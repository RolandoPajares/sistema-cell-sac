<?php

namespace App\Models\Auditoria;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroAuditoria extends Model
{
    protected $table = 'registros_auditoria';

    protected $fillable = [
        'usuario_id', 'accion', 'entidad', 'entidad_id', 'valores_anteriores',
        'valores_nuevos', 'direccion_ip', 'creado_en',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'valores_anteriores' => 'array',
            'valores_nuevos' => 'array',
            'creado_en' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
