<?php

namespace App\Models\Organizacion;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase ResponsableOrganizacional dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
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

    /**
     * Ejecuta la operación casts definida por la clase ResponsableOrganizacional.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['vigente_desde' => 'date', 'vigente_hasta' => 'date', 'activo' => 'boolean'];
    }

    /**
     * Ejecuta la operación responsable definida por la clase ResponsableOrganizacional.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_usuario_id');
    }

    /**
     * Ejecuta la operación sede definida por la clase ResponsableOrganizacional.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    /**
     * Ejecuta la operación almacen definida por la clase ResponsableOrganizacional.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    /**
     * Ejecuta la operación relevoDe definida por la clase ResponsableOrganizacional.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function relevoDe(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reemplaza_asignacion_id');
    }
}
