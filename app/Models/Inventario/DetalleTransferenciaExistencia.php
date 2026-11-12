<?php

namespace App\Models\Inventario;

use App\Models\Catalogo\Producto;
use App\Models\Catalogo\VarianteProducto;
use Database\Factories\Inventario\DetalleTransferenciaExistenciaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase DetalleTransferenciaExistencia dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class DetalleTransferenciaExistencia extends Model
{
    /** @use HasFactory<DetalleTransferenciaExistenciaFactory> */
    use HasFactory;

    // Usa la tabla 'detalles_transferencias_existencias' como origen de los registros de este modelo.
    protected $table = 'detalles_transferencias_existencias';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = [
        'transferencia_id', 'producto_id', 'variante_id', 'cantidad_solicitada', 'cantidad_enviada',
        'cantidad_recibida', 'cantidad_devuelta', 'cantidad_conciliada',
    ];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase DetalleTransferenciaExistencia.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return [
            'cantidad_solicitada' => 'decimal:6',
            'cantidad_enviada' => 'decimal:6',
            'cantidad_recibida' => 'decimal:6',
            'cantidad_devuelta' => 'decimal:6',
            'cantidad_conciliada' => 'decimal:6',
        ];
    }

    /**
     * Ejecuta la operación transferencia definida por la clase DetalleTransferenciaExistencia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function transferencia(): BelongsTo
    {
        return $this->belongsTo(TransferenciaExistencia::class, 'transferencia_id');
    }

    /**
     * Ejecuta la operación producto definida por la clase DetalleTransferenciaExistencia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Ejecuta la operación variante definida por la clase DetalleTransferenciaExistencia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'variante_id');
    }
}
