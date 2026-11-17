<?php

namespace App\Models\Comercio;

use App\Models\Catalogo\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase DetalleCotizacion dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class DetalleCotizacion extends Model
{
    // Usa la tabla 'detalles_cotizaciones' como origen de los registros de este modelo.
    protected $table = 'detalles_cotizaciones';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['cotizacion_id', 'producto_id', 'variante_id', 'cantidad', 'precio_unitario', 'descripcion_historica', 'descuento', 'impuesto', 'total_linea'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase DetalleCotizacion.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['precio_unitario' => 'decimal:2'];
    }

    /**
     * Ejecuta la operación quotation definida por la clase DetalleCotizacion.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    /**
     * Ejecuta la operación product definida por la clase DetalleCotizacion.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
