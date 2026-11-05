<?php

namespace App\Models\Comercio;

use App\Models\Organizacion\Almacen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class EnvioPedido extends Model
{
    protected $table = 'envios_pedidos';

    protected $fillable = [
        'empresa_id', 'sede_id', 'pedido_id', 'almacen_id', 'guia_remision_id', 'codigo', 'modalidad',
        'transportista', 'servicio', 'numero_seguimiento', 'estado', 'moneda', 'costo_envio',
        'destino_historico', 'estimado_entrega_en', 'despachado_en', 'entregado_en',
        'creado_por_usuario_id', 'creado_en',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'costo_envio' => 'decimal:2',
            'destino_historico' => 'array',
            'estimado_entrega_en' => 'datetime',
            'despachado_en' => 'datetime',
            'entregado_en' => 'datetime',
            'creado_en' => 'datetime',
        ];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(EventoEnvioPedido::class, 'envio_pedido_id');
    }

    public function ultimoEvento(): HasOne
    {
        return $this->hasOne(EventoEnvioPedido::class, 'envio_pedido_id')->latestOfMany('ocurrido_en');
    }
}
