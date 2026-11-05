<?php

namespace App\Models\Comunicacion;

use Database\Factories\Comunicacion\NotificacionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Notificacion extends Model
{
    /** @use HasFactory<NotificacionFactory> */
    use HasFactory, HasUuids;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'notificaciones';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tipo', 'tipo_notificable', 'notificable_id', 'datos', 'leido_en',
    ];

    protected function casts(): array
    {
        return [
            'datos' => 'array',
            'leido_en' => 'datetime',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function nombreTipo(): string
    {
        return class_basename((string) $this->tipo);
    }

    public function datosLegibles(): string
    {
        $datos = is_array($this->datos)
            ? $this->datos
            : ['contenido' => (string) $this->getRawOriginal('datos')];

        if ($datos === []) {
            return 'Sin datos asociados.';
        }

        return json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: 'Contenido no disponible.';
    }
}
