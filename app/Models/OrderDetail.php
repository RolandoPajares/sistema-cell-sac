<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $table = 'detalle_pedidos';

    protected $fillable = ['pedido_id', 'producto_id', 'cantidad', 'precio_unitario'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['precio_unitario' => 'decimal:2'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'pedido_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'producto_id');
    }
}
