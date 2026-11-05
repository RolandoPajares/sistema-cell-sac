<?php

namespace App\Models\Organizacion;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ResponsableOrganizacional extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'responsables_organizacionales';

    protected $fillable = [
        'empresa_id', 'sede_id', 'tipo_recurso', 'almacen_id',
        'responsable_usuario_id', 'cargo', 'vigente_desde', 'vigente_hasta',
        'motivo', 'reemplaza_asignacion_id', 'asignado_por_usuario_id', 'activo',
    ];

    protected function casts(): array
    {
        return ['vigente_desde' => 'date', 'vigente_hasta' => 'date', 'activo' => 'boolean'];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_usuario_id');
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function relevoDe(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reemplaza_asignacion_id');
    }
}
