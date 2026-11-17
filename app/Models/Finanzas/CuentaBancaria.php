<?php

namespace App\Models\Finanzas;

use App\Models\Organizacion\Empresa;
use Database\Factories\Finanzas\CuentaBancariaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase CuentaBancaria dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CuentaBancaria extends Model
{
    /** @use HasFactory<CuentaBancariaFactory> */
    use HasFactory;

    // Usa la tabla 'cuentas_bancarias' como origen de los registros de este modelo.
    protected $table = 'cuentas_bancarias';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = [
        'empresa_id', 'codigo', 'banco', 'tipo_cuenta', 'moneda',
        'cuenta_mascara', 'referencia_secreto', 'activa',
    ];

    public $timestamps = false;

    // Oculta estos atributos cuando el modelo se convierte en array o JSON.
    protected $hidden = ['referencia_secreto'];

    /**
     * Ejecuta la operación casts definida por la clase CuentaBancaria.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['activa' => 'boolean'];
    }

    /**
     * Ejecuta la operación empresa definida por la clase CuentaBancaria.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
