<?php

namespace App\Models\Comercio;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EventoEnvioPedido extends Model
{
    protected $table = 'eventos_envios_pedidos';

    protected $fillable = [
        'envio_pedido_id', 'estado', 'descripcion', 'fuente', 'identificador_externo',
        'ocurrido_en', 'registrado_en', 'datos_seguros',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'ocurrido_en' => 'datetime',
            'registrado_en' => 'datetime',
            'datos_seguros' => 'array',
        ];
    }

    public function envio(): BelongsTo
    {
        return $this->belongsTo(EnvioPedido::class, 'envio_pedido_id');
    }
}
