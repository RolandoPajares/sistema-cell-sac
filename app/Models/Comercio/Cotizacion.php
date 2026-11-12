<?php

namespace App\Models\Comercio;

use App\Models\Clientes\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase Cotizacion dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Cotizacion extends Model
{
    // Usa la tabla 'cotizaciones' como origen de los registros de este modelo.
    protected $table = 'cotizaciones';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['cliente_id', 'creado_por_usuario_id', 'total', 'estado', 'notas', 'activo', 'creado_en', 'empresa_id', 'sede_id'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase Cotizacion.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['total' => 'decimal:2', 'activo' => 'boolean', 'creado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación customer definida por la clase Cotizacion.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Ejecuta la operación creator definida por la clase Cotizacion.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_usuario_id');
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @return HasMany Resultado de la operación.
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetalleCotizacion::class, 'cotizacion_id');
    }
}
