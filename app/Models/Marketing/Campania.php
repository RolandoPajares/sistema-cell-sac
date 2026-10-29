<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campania extends Model
{
    protected $table = 'campanas_publicitarias';

    protected $fillable = [
        'nombre', 'ubicacion', 'titulo', 'descripcion', 'texto_boton', 'url_boton',
        'url_imagen', 'precio_anterior', 'precio_oferta', 'inicia_en', 'finaliza_en',
        'activo', 'vistas', 'clics', 'creado_en', 'actualizado_en',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'precio_anterior' => 'decimal:2',
            'precio_oferta' => 'decimal:2',
            'inicia_en' => 'datetime',
            'finaliza_en' => 'datetime',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(SegmentoCliente::class, 'campana_segmento', 'campana_id', 'segmento_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(EventoCampania::class, 'campana_id');
    }
}
