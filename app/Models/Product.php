<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'marca', 'nombre', 'categoria_id', 'categoria', 'precio', 'precio_original',
        'descuento', 'precio_oferta', 'url_imagen', 'existencias', 'almacenamiento',
        'color', 'etiqueta', 'descripcion', 'activo', 'creado_en',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'precio_original' => 'decimal:2',
            'precio_oferta' => 'decimal:2',
            'activo' => 'boolean',
            'creado_en' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categoria_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'producto_id')->orderBy('orden');
    }

    public function features(): HasMany
    {
        return $this->hasMany(ProductFeature::class, 'producto_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'producto_id');
    }

    public function intelligenceProfile(): HasOne
    {
        return $this->hasOne(ProductIntelligenceProfile::class, 'producto_id');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'producto_proveedor', 'producto_id', 'proveedor_id');
    }

    public function getEffectivePriceAttribute(): string
    {
        return (string) ($this->precio_oferta ?? $this->precio);
    }
}
