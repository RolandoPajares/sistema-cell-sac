<?php

namespace App\Models\Comercio;

use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase DevolucionGarantia dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class DevolucionGarantia extends Model
{
    // Usa la tabla 'devoluciones_garantias' como origen de los registros de este modelo.
    protected $table = 'devoluciones_garantias';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = [
        'codigo',
        'pedido_id',
        'cliente_id',
        'producto_id',
        'tipo',
        'motivo',
        'estado',
        'fecha_solicitud',
        'fecha_limite',
        'resolucion',
        'creado_por_usuario_id',
        'activo',
        'empresa_id',
        'sede_id',
        'venta_id',
    ];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase DevolucionGarantia.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'date',
            'fecha_limite' => 'date',
            'activo' => 'boolean',
            'creado_en' => 'datetime',
        ];
    }

    /**
     * Ejecuta la operación order definida por la clase DevolucionGarantia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * Ejecuta la operación customer definida por la clase DevolucionGarantia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Ejecuta la operación product definida por la clase DevolucionGarantia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * Ejecuta la operación sale definida por la clase DevolucionGarantia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    /**
     * Ejecuta la operación creator definida por la clase DevolucionGarantia.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_usuario_id');
    }
}
