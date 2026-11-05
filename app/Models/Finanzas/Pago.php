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

class Pago extends Model
{
    /** @use HasFactory<PagoFactory> */
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'empresa_id', 'sede_id', 'codigo', 'tipo', 'estado', 'medio_pago_id', 'sesion_caja_id',
        'cuenta_bancaria_id', 'usuario_id', 'pedido_id', 'venta_id', 'compra_id', 'moneda',
        'importe', 'referencia', 'fecha_pago', 'creado_en',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'importe' => 'decimal:2',
            'fecha_pago' => 'datetime',
            'creado_en' => 'datetime',
        ];
    }

    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class, 'medio_pago_id');
    }

    public function sesionCaja(): BelongsTo
    {
        return $this->belongsTo(SesionCaja::class, 'sesion_caja_id');
    }

    public function cuentaBancaria(): BelongsTo
    {
        return $this->belongsTo(CuentaBancaria::class, 'cuenta_bancaria_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    public function reembolso(): HasOne
    {
        return $this->hasOne(Reembolso::class, 'pago_id');
    }
}
