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

    // Usa la tabla 'configuraciones_sistema' como origen de los registros de este modelo.
    protected $table = 'configuraciones_sistema';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = [
        'empresa_id',
        'sede_id',
        'grupo',
        'clave',
        'valor_json',
        'actualizado_por_usuario_id',
    ];

    // Ejecuta la logica del metodo 'casts' dentro de este modelo.
    protected function casts(): array
    {
        return ['valor_json' => 'array'];
    }
}
