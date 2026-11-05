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
        'evento_uuid', 'identificador_universal_evento', 'rol_codigo', 'empresa_id', 'sede_id', 'modulo', 'resultado',
        'agente_usuario', 'correlacion_id', 'observaciones',
        'rol_contexto', 'modulo_codigo', 'tabla_afectada', 'identificador_entidad',
        'cambios', 'origen', 'ocurrido_en', 'observacion',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'valores_anteriores' => 'array',
            'valores_nuevos' => 'array',
            'creado_en' => 'datetime',
            'ocurrido_en' => 'datetime',
            'cambios' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
