<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SegmentoCliente extends Model
{
    protected $table = 'segmentos_clientes';

    protected $fillable = ['nombre', 'tipo', 'criterios', 'activo'];

    protected function casts(): array
    {
        return ['criterios' => 'array', 'activo' => 'boolean'];
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campania::class, 'campana_segmento', 'segmento_id', 'campana_id');
    }
}
