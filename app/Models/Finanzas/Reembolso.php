<?php

namespace App\Models\Finanzas;

use App\Models\Comercio\DevolucionGarantia;
use App\Models\User;
use Database\Factories\Finanzas\ReembolsoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase Reembolso dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Reembolso extends Model
{
    /** @use HasFactory<ReembolsoFactory> */
    use HasFactory;

    // Usa la tabla 'reembolsos' como origen de los registros de este modelo.
    protected $table = 'reembolsos';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['devolucion_id', 'pago_id', 'importe', 'estado', 'autorizado_por_usuario_id', 'creado_en'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase Reembolso.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return [
            'importe' => 'decimal:2',
            'creado_en' => 'datetime',
        ];
    }

    /**
     * Ejecuta la operación devolucion definida por la clase Reembolso.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(DevolucionGarantia::class, 'devolucion_id');
    }

    /**
     * Ejecuta la operación pago definida por la clase Reembolso.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    /**
     * Ejecuta la operación autorizadoPor definida por la clase Reembolso.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function autorizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autorizado_por_usuario_id');
    }
}
