<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CustomerSegment extends Model
{
    protected $table = 'segmentos_clientes';

    protected $fillable = ['nombre', 'tipo', 'criterios', 'activo'];

    protected function casts(): array
    {
        return ['criterios' => 'array', 'activo' => 'boolean'];
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'campana_segmento', 'segmento_id', 'campana_id');
    }
}
