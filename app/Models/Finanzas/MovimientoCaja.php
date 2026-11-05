<?php

namespace App\Models\Finanzas;

use App\Models\User;
use Database\Factories\Finanzas\MovimientoCajaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoCaja extends Model
{
    /** @use HasFactory<MovimientoCajaFactory> */
    use HasFactory;

    protected $table = 'movimientos_caja';

    protected $fillable = [
        'sesion_caja_id', 'pago_id', 'tipo', 'direccion', 'importe',
        'descripcion', 'creado_por_usuario_id', 'creado_en',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['importe' => 'decimal:2', 'creado_en' => 'datetime'];
    }

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(SesionCaja::class, 'sesion_caja_id');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_usuario_id');
    }
}
