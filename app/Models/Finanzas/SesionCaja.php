<?php

namespace App\Models\Finanzas;

use App\Models\User;
use Database\Factories\Finanzas\SesionCajaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase SesionCaja dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
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

    /**
     * Ejecuta la operación casts definida por la clase SesionCaja.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return [
            'saldo_inicial' => 'decimal:2', 'saldo_cierre_declarado' => 'decimal:2',
            'abierta_en' => 'datetime', 'cerrada_en' => 'datetime',
        ];
    }

    /**
     * Ejecuta la operación caja definida por la clase SesionCaja.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    /**
     * Ejecuta la operación usuarioApertura definida por la clase SesionCaja.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function usuarioApertura(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_apertura_id');
    }

    /**
     * Ejecuta la operación usuarioCierre definida por la clase SesionCaja.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function usuarioCierre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_cierre_id');
    }

    /**
     * Ejecuta la operación movimientos definida por la clase SesionCaja.
     *
     * @return HasMany Resultado de la operación.
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'sesion_caja_id')->orderBy('id');
    }
}
