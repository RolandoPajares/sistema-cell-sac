<?php

namespace App\Models\Comercio;

use App\Models\Clientes\Cliente;
use App\Models\Finanzas\Pago;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase Venta dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Venta extends Model
{
    // Usa la tabla 'ventas' como origen de los registros de este modelo.
    protected $table = 'ventas';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['empresa_id', 'sede_id', 'almacen_id', 'pedido_id', 'cliente_id', 'usuario_id', 'codigo', 'estado', 'moneda', 'subtotal', 'descuento', 'impuestos', 'total', 'fecha_venta', 'canal', 'observaciones'];

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /**
     * Ejecuta la operación casts definida por la clase Venta.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['fecha_venta' => 'datetime', 'creado_en' => 'datetime', 'total' => 'decimal:2'];
    }

    /**
     * Ejecuta la operación cliente definida por la clase Venta.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Ejecuta la operación usuario definida por la clase Venta.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Ejecuta la operación pedido definida por la clase Venta.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @return HasMany Resultado de la operación.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    /**
     * Ejecuta la operación pagos definida por la clase Venta.
     *
     * @return HasMany Resultado de la operación.
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'venta_id');
    }
}
