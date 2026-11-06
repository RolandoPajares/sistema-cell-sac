<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase EventoEnvioPedido dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class EventoEnvioPedido extends Model
{
    protected $table = 'eventos_envios_pedidos';

    protected $fillable = [
        'envio_pedido_id', 'estado', 'descripcion', 'fuente', 'identificador_externo',
        'ocurrido_en', 'registrado_en', 'datos_seguros',
    ];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase EventoEnvioPedido.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return [
            'ocurrido_en' => 'datetime',
            'registrado_en' => 'datetime',
            'datos_seguros' => 'array',
        ];
    }

    /**
     * Ejecuta la operación envio definida por la clase EventoEnvioPedido.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function envio(): BelongsTo
    {
        return $this->belongsTo(EnvioPedido::class, 'envio_pedido_id');
    }
}
