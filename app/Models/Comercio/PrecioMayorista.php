<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;

/**
 * Define la responsabilidad de la clase PrecioMayorista dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class PrecioMayorista extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    // Usa la tabla 'precios_mayoristas' como origen de los registros de este modelo.
    protected $table = 'precios_mayoristas';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = [
        'empresa_id', 'producto_id', 'variante_id', 'precio', 'vigente_desde', 'vigente_hasta', 'motivo', 'creado_por_usuario_id',
    ];

    /**
     * Ejecuta la operación casts definida por la clase PrecioMayorista.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'vigente_desde' => 'datetime',
            'vigente_hasta' => 'datetime',
        ];
    }
}
