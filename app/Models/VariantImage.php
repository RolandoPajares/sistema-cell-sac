<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantImage extends Model
{
    protected $table = 'variante_imagenes';

    protected $fillable = ['variante_id', 'ruta_imagen', 'imagen_principal', 'orden', 'fecha_creacion'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['imagen_principal' => 'boolean', 'fecha_creacion' => 'datetime'];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variante_id');
    }
}
