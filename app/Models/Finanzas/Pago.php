<?php

namespace App\Models\Finanzas;

use App\Models\Comercio\Pedido;
use App\Models\Comercio\Venta;
use App\Models\Compras\Compra;
use App\Models\User;
use Database\Factories\Finanzas\PagoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Define la responsabilidad de la clase Pago dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Pago extends Model
{
    /** @use HasFactory<PagoFactory> */
    use HasFactory;

    // Usa la tabla 'pagos' como origen de los registros de este modelo.
    protected $table = 'pagos';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = [
        'empresa_id', 'sede_id', 'codigo', 'tipo', 'estado', 'medio_pago_id', 'sesion_caja_id',
        'cuenta_bancaria_id', 'usuario_id', 'pedido_id', 'venta_id', 'compra_id', 'moneda',
        'importe', 'referencia', 'fecha_pago', 'creado_en',
    ];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase Pago.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return [
            'importe' => 'decimal:2',
            'fecha_pago' => 'datetime',
            'creado_en' => 'datetime',
        ];
    }

    /**
     * Ejecuta la operación medioPago definida por la clase Pago.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class, 'medio_pago_id');
    }

    /**
     * Ejecuta la operación sesionCaja definida por la clase Pago.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function sesionCaja(): BelongsTo
    {
        return $this->belongsTo(SesionCaja::class, 'sesion_caja_id');
    }

    /**
     * Ejecuta la operación cuentaBancaria definida por la clase Pago.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function cuentaBancaria(): BelongsTo
    {
        return $this->belongsTo(CuentaBancaria::class, 'cuenta_bancaria_id');
    }

    /**
     * Ejecuta la operación usuario definida por la clase Pago.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Ejecuta la operación pedido definida por la clase Pago.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * Ejecuta la operación venta definida por la clase Pago.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    /**
     * Ejecuta la operación compra definida por la clase Pago.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    /**
     * Ejecuta la operación reembolso definida por la clase Pago.
     *
     * @return HasOne Resultado de la operación.
     */
    public function reembolso(): HasOne
    {
        return $this->hasOne(Reembolso::class, 'pago_id');
    }
}
