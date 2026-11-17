<?php

namespace App\Models\Comercio;

use App\Models\Catalogo\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase DetalleVenta dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class DetalleVenta extends Model
{
    // Usa la tabla 'detalles_ventas' como origen de los registros de este modelo.
    protected $table = 'detalles_ventas';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['venta_id', 'producto_id', 'variante_id', 'detalle_pedido_id', 'descripcion_historica', 'sku_historico', 'cantidad', 'precio_unitario', 'descuento', 'tasa_impuesto', 'impuesto', 'total_linea'];

    public $timestamps = false;

    /**
     * Ejecuta la operación venta definida por la clase DetalleVenta.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    /**
     * Ejecuta la operación producto definida por la clase DetalleVenta.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
