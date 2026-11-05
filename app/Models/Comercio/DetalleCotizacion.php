<?php

namespace App\Models\Comercio;

use App\Models\Catalogo\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCotizacion extends Model
{
    protected $table = 'detalles_cotizaciones';

    protected $fillable = ['cotizacion_id', 'producto_id', 'variante_id', 'cantidad', 'precio_unitario', 'descripcion_historica', 'descuento', 'impuesto', 'total_linea'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['precio_unitario' => 'decimal:2'];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
