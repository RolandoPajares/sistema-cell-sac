<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;

/**
 * Define la responsabilidad de la clase ConfiguracionTienda dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ConfiguracionTienda extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'configuraciones_tienda';

    protected $fillable = ['empresa_id', 'configuracion_json', 'actualizado_por_usuario_id'];

    /**
     * Ejecuta la operación casts definida por la clase ConfiguracionTienda.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['configuracion_json' => 'array'];
    }
}
