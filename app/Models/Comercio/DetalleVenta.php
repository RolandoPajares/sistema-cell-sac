<?php

namespace App\Models\Comercio;

use App\Models\Catalogo\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleVenta extends Model
{
    protected $table = 'detalles_ventas';

    protected $fillable = ['venta_id', 'producto_id', 'variante_id', 'detalle_pedido_id', 'descripcion_historica', 'sku_historico', 'cantidad', 'precio_unitario', 'descuento', 'tasa_impuesto', 'impuesto', 'total_linea'];

    public $timestamps = false;

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
