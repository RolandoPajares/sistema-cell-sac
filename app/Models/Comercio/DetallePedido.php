<?php

namespace App\Models\Comercio;

use App\Models\Catalogo\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePedido extends Model
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
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
