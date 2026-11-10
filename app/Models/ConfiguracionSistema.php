<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Persiste parámetros configurables con alcance empresarial o por sede.
 */
final class ConfiguracionSistema extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'configuraciones_sistema';

    protected $fillable = [
        'empresa_id',
        'sede_id',
        'grupo',
        'clave',
        'valor_json',
        'actualizado_por_usuario_id',
    ];

    protected function casts(): array
    {
        return ['valor_json' => 'array'];
    }
}
