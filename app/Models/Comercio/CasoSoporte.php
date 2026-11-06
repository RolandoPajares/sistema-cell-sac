<?php

namespace App\Models\Comercio;

use App\Models\Clientes\Cliente;
use App\Models\Organizacion\Sede;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase CasoSoporte dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class CasoSoporte extends Model
{
    protected $table = 'casos_soporte';

    protected $fillable = [
        'empresa_id', 'sede_id', 'cliente_id', 'asignado_a_usuario_id', 'codigo', 'asunto', 'estado', 'prioridad',
        'creado_en', 'actualizado_en',
    ];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase CasoSoporte.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['creado_en' => 'datetime', 'actualizado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación cliente definida por la clase CasoSoporte.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Ejecuta la operación sede definida por la clase CasoSoporte.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    /**
     * Ejecuta la operación asignado definida por la clase CasoSoporte.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function asignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_a_usuario_id');
    }
}
