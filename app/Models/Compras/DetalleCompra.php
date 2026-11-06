<?php

namespace App\Models\Compras;

use App\Models\Catalogo\Producto;
use App\Models\Catalogo\VarianteProducto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase DetalleCompra dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class DetalleCompra extends Model
{
    protected $table = 'detalles_compras';

    protected $fillable = ['compra_id', 'producto_id', 'variante_id', 'cantidad', 'costo_unitario'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase DetalleCompra.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['costo_unitario' => 'decimal:2'];
    }

    /**
     * Ejecuta la operación purchase definida por la clase DetalleCompra.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    /**
     * Ejecuta la operación product definida por la clase DetalleCompra.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Ejecuta la operación variante definida por la clase DetalleCompra.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'variante_id');
    }
}
