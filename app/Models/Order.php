<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $table = 'pedidos';

    protected $fillable = ['usuario_id', 'total', 'subtotal', 'descuento', 'cupon_codigo', 'estado', 'creado_en'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'creado_en' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'pedido_id');
    }

    public function wholesaleLogistics(): HasOne
    {
        return $this->hasOne(WholesaleLogistics::class, 'pedido_id');
    }
}
