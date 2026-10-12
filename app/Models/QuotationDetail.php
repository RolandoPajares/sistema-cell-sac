<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationDetail extends Model
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
        return $this->belongsTo(Quotation::class, 'cotizacion_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }
}
