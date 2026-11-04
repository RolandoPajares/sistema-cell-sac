<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SegmentoCliente extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'segmentos_clientes';

    protected $fillable = ['nombre', 'tipo', 'criterios', 'activo'];

    protected function casts(): array
    {
        return ['criterios' => 'array', 'activo' => 'boolean'];
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campania::class, 'campanas_segmentos', 'segmento_id', 'campana_id');
    }
}
