<?php

namespace App\Models\Finanzas;

use App\Models\Organizacion\Sede;
use Database\Factories\Finanzas\CajaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase Caja dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Caja extends Model
{
    /** @use HasFactory<CajaFactory> */
    use HasFactory;

    // Usa la tabla 'cajas' como origen de los registros de este modelo.
    protected $table = 'cajas';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['sede_id', 'codigo', 'nombre', 'activa', 'creado_en'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase Caja.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['activa' => 'boolean', 'creado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación sede definida por la clase Caja.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_id');
    }

    /**
     * Ejecuta la operación sesiones definida por la clase Caja.
     *
     * @return HasMany Resultado de la operación.
     */
    public function sesiones(): HasMany
    {
        return $this->hasMany(SesionCaja::class, 'caja_id')->latest('id');
    }
}
