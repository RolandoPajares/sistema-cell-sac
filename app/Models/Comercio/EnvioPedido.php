<?php

namespace App\Models\Comercio;

use App\Models\Organizacion\Almacen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Define la responsabilidad de la clase EnvioPedido dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class EnvioPedido extends Model
{
    // Usa la tabla 'envios_pedidos' como origen de los registros de este modelo.
    protected $table = 'envios_pedidos';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = [
        'empresa_id', 'sede_id', 'pedido_id', 'almacen_id', 'guia_remision_id', 'codigo', 'modalidad',
        'transportista', 'servicio', 'numero_seguimiento', 'estado', 'moneda', 'costo_envio',
        'destino_historico', 'estimado_entrega_en', 'despachado_en', 'entregado_en',
        'creado_por_usuario_id', 'creado_en',
    ];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase EnvioPedido.
     *
     * @return array Resultado de la operación.
     */
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

    /**
     * Ejecuta la operación pedido definida por la clase EnvioPedido.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * Ejecuta la operación almacen definida por la clase EnvioPedido.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    /**
     * Ejecuta la operación eventos definida por la clase EnvioPedido.
     *
     * @return HasMany Resultado de la operación.
     */
    public function eventos(): HasMany
    {
        return $this->hasMany(EventoEnvioPedido::class, 'envio_pedido_id');
    }

    /**
     * Ejecuta la operación ultimoEvento definida por la clase EnvioPedido.
     *
     * @return HasOne Resultado de la operación.
     */
    public function ultimoEvento(): HasOne
    {
        return $this->hasOne(EventoEnvioPedido::class, 'envio_pedido_id')->latestOfMany('ocurrido_en');
    }
}
