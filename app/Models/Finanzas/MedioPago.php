<?php

namespace App\Models\Finanzas;

use Database\Factories\Finanzas\MedioPagoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase MedioPago dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class MedioPago extends Model
{
    /** @use HasFactory<MedioPagoFactory> */
    use HasFactory;

    protected $table = 'medios_pago';

    protected $fillable = ['codigo', 'nombre', 'requiere_caja', 'requiere_banco', 'activo'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase MedioPago.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return [
            'requiere_caja' => 'boolean',
            'requiere_banco' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    /**
     * Ejecuta la operación pagos definida por la clase MedioPago.
     *
     * @return HasMany Resultado de la operación.
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'medio_pago_id');
    }
}
