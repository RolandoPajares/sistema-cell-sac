<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;

class Cupon extends Model
{
    protected $table = 'cupones';

    protected $fillable = [
        'codigo', 'tipo_descuento', 'valor', 'minimo_compra', 'limite_usos',
        'usos', 'inicia_en', 'finaliza_en', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'minimo_compra' => 'decimal:2',
            'inicia_en' => 'datetime',
            'finaliza_en' => 'datetime',
            'activo' => 'boolean',
        ];
    }
}
