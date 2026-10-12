<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseDetail extends Model
{
    protected $table = 'compra_detalles';

    protected $fillable = ['compra_id', 'producto_id', 'cantidad', 'costo_unitario'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['costo_unitario' => 'decimal:2'];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'compra_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }
}
