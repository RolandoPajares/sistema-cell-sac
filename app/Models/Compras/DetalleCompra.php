<?php

namespace App\Models\Compras;

use App\Models\Catalogo\Producto;
use App\Models\Catalogo\VarianteProducto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCompra extends Model
{
    protected $table = 'detalles_compras';

    protected $fillable = ['compra_id', 'producto_id', 'variante_id', 'cantidad', 'costo_unitario'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['costo_unitario' => 'decimal:2'];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'variante_id');
    }
}
