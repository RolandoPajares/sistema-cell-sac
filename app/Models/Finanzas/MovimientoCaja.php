<?php

namespace App\Models\Finanzas;

use App\Models\User;
use Database\Factories\Finanzas\MovimientoCajaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase MovimientoCaja dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class MovimientoCaja extends Model
{
    /** @use HasFactory<MovimientoCajaFactory> */
    use HasFactory;

    // Usa la tabla 'movimientos_caja' como origen de los registros de este modelo.
    protected $table = 'movimientos_caja';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = [
        'sesion_caja_id', 'pago_id', 'tipo', 'direccion', 'importe',
        'descripcion', 'creado_por_usuario_id', 'creado_en',
    ];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase MovimientoCaja.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['importe' => 'decimal:2', 'creado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación sesion definida por la clase MovimientoCaja.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function sesion(): BelongsTo
    {
        return $this->belongsTo(SesionCaja::class, 'sesion_caja_id');
    }

    /**
     * Ejecuta la operación pago definida por la clase MovimientoCaja.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    /**
     * Ejecuta la operación creador definida por la clase MovimientoCaja.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_usuario_id');
    }
}
