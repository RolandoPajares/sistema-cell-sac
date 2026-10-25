<?php

namespace App\Models\Comercio;

use App\Models\Catalogo\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCotizacion extends Model
{
    protected $table = 'cotizacion_detalles';

    protected $fillable = ['cotizacion_id', 'producto_id', 'cantidad', 'precio_unitario'];

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
