<?php

namespace App\Models\Finanzas;

use App\Models\User;
use Database\Factories\Finanzas\SesionCajaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesionCaja extends Model
{
    /** @use HasFactory<SesionCajaFactory> */
    use HasFactory;

    protected $table = 'sesiones_caja';

    protected $fillable = [
        'caja_id', 'usuario_apertura_id', 'usuario_cierre_id', 'estado', 'saldo_inicial',
        'saldo_cierre_declarado', 'abierta_en', 'cerrada_en',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'saldo_inicial' => 'decimal:2', 'saldo_cierre_declarado' => 'decimal:2',
            'abierta_en' => 'datetime', 'cerrada_en' => 'datetime',
        ];
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function usuarioApertura(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_apertura_id');
    }

    public function usuarioCierre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_cierre_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'sesion_caja_id')->orderBy('id');
    }
}
