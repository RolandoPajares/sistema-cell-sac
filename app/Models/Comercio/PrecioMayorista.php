<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;

class PrecioMayorista extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $table = 'precios_mayoristas';

    protected $fillable = [
        'empresa_id', 'producto_id', 'variante_id', 'precio', 'vigente_desde', 'vigente_hasta', 'motivo', 'creado_por_usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'vigente_desde' => 'datetime',
            'vigente_hasta' => 'datetime',
        ];
    }
}
