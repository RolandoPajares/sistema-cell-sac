<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Define la responsabilidad de la clase SegmentoCliente dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class SegmentoCliente extends Model
{
    protected $table = 'segmentos_clientes';

    protected $fillable = ['nombre', 'tipo', 'criterios', 'activo'];

    /**
     * Ejecuta la operación casts definida por la clase SegmentoCliente.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['criterios' => 'array', 'activo' => 'boolean'];
    }

    /**
     * Ejecuta la operación campaigns definida por la clase SegmentoCliente.
     *
     * @return BelongsToMany Resultado de la operación.
     */
    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campania::class, 'campana_segmento', 'segmento_id', 'campana_id');
    }
}
