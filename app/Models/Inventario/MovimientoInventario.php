<?php

namespace App\Models\Inventario;

use App\Models\Catalogo\Producto;
use App\Models\Catalogo\VarianteProducto;
use App\Models\Organizacion\Almacen;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase MovimientoInventario dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = ['producto_id', 'usuario_id', 'tipo_movimiento', 'cantidad', 'notas', 'creado_en', 'empresa_id', 'sede_id', 'almacen_id', 'variante_id', 'documento_tipo', 'documento_id'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase MovimientoInventario.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['creado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación product definida por la clase MovimientoInventario.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Ejecuta la operación user definida por la clase MovimientoInventario.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Ejecuta la operación almacen definida por la clase MovimientoInventario.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    /**
     * Ejecuta la operación variante definida por la clase MovimientoInventario.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'variante_id');
    }
}
