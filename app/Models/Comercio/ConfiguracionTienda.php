<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionTienda extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'configuraciones_tienda';

    protected $fillable = ['empresa_id', 'configuracion_json', 'actualizado_por_usuario_id'];

    protected function casts(): array
    {
        return ['configuracion_json' => 'array'];
    }
}
