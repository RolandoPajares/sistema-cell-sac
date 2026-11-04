<?php

namespace App\Models\Finanzas;

use App\Models\Comercio\DevolucionGarantia;
use App\Models\User;
use Database\Factories\Finanzas\ReembolsoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reembolso extends Model
{
    /** @use HasFactory<ReembolsoFactory> */
    use HasFactory;

    protected $table = 'reembolsos';

    protected $fillable = ['devolucion_id', 'pago_id', 'importe', 'estado', 'autorizado_por_usuario_id', 'creado_en'];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'importe' => 'decimal:2',
            'creado_en' => 'datetime',
        ];
    }

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(DevolucionGarantia::class, 'devolucion_id');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizado_por_usuario_id');
    }
}
