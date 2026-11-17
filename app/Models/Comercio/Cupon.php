<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;

/**
 * Define la responsabilidad de la clase Cupon dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Cupon extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    // Usa la tabla 'cupones' como origen de los registros de este modelo.
    protected $table = 'cupones';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = [
        'empresa_id', 'codigo', 'tipo_descuento', 'valor', 'minimo_compra', 'limite_usos',
        'usos', 'inicia_en', 'finaliza_en', 'activo',
    ];

    /**
     * Ejecuta la operación casts definida por la clase Cupon.
     *
     * @return array Resultado de la operación.
     */
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
