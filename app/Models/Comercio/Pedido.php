<?php

namespace App\Models\Comercio;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Define la responsabilidad de la clase Pedido dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Pedido extends Model
{
    // Usa la tabla 'pedidos' como origen de los registros de este modelo.
    protected $table = 'pedidos';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['usuario_id', 'cliente_id', 'cliente_invitado_nombre', 'cliente_invitado_correo', 'cliente_invitado_telefono', 'token_acceso_publico', 'direccion_entrega_snapshot', 'cotizacion_id', 'canal', 'tipo_venta', 'observaciones', 'total', 'subtotal', 'descuento', 'cupon_id', 'cupon_codigo', 'estado', 'creado_en', 'empresa_id', 'sede_id', 'almacen_id'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase Pedido.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'creado_en' => 'datetime',
            'direccion_entrega_snapshot' => 'array',
        ];
    }

    /**
     * Ejecuta la operación user definida por la clase Pedido.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @return HasMany Resultado de la operación.
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    /**
     * Ejecuta la operación wholesaleLogistics definida por la clase Pedido.
     *
     * @return HasOne Resultado de la operación.
     */
    public function wholesaleLogistics(): HasOne
    {
        return $this->hasOne(LogisticaMayorista::class, 'pedido_id');
    }
}
