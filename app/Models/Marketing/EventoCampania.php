<?php

namespace App\Models\Marketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Define la responsabilidad de la clase EventoCampania dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class EventoCampania extends Model
{
    // Usa la tabla 'eventos_campana' como origen de los registros de este modelo.
    protected $table = 'eventos_campana';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = ['campana_id', 'usuario_id', 'tipo', 'sesion_hash', 'creado_en'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase EventoCampania.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['creado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación campaign definida por la clase EventoCampania.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campania::class, 'campana_id');
    }

    /**
     * Ejecuta la operación user definida por la clase EventoCampania.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
