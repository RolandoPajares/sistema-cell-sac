<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase MovimientoDispositivo dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class MovimientoDispositivo extends Model
{
    protected $table = 'movimientos_dispositivos';

    protected $fillable = [
        'dispositivo_id', 'movimiento_inventario_id', 'estado_anterior', 'estado_nuevo', 'creado_en',
    ];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase MovimientoDispositivo.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['creado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación dispositivo definida por la clase MovimientoDispositivo.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class, 'dispositivo_id');
    }
}
