<?php

namespace App\Models\Catalogo;

use App\Models\Compras\Proveedor;
use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return ProductoFactory::new();
    }

    protected $table = 'productos';

    protected $fillable = [
        'marca', 'nombre', 'categoria_id', 'categoria', 'precio', 'precio_original',
        'descuento', 'precio_oferta', 'url_imagen', 'existencias', 'almacenamiento',
        'color', 'etiqueta', 'descripcion', 'activo', 'creado_en', 'empresa_id',
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
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ImagenProducto::class, 'producto_id')->orderBy('orden')->orderBy('id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(CaracteristicaProducto::class, 'producto_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(VarianteProducto::class, 'producto_id');
    }

    public function intelligenceProfile(): HasOne
    {
        return $this->hasOne(PerfilInteligenciaProducto::class, 'producto_id');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Proveedor::class, 'productos_proveedores', 'producto_id', 'proveedor_id');
    }

    public function getEffectivePriceAttribute(): string
    {
        return (string) ($this->precio_oferta ?? $this->precio);
    }

    public function getUrlImagenPrincipalAttribute(): ?string
    {
        $rutaImagenGaleria = $this->relationLoaded('images')
            ? $this->images->first()?->ruta_imagen
            : null;
        $rutaImagen = $rutaImagenGaleria ?: $this->url_imagen;

        return static::urlPublicaDeImagen(is_string($rutaImagen) ? $rutaImagen : null);
    }

    public static function urlPublicaDeImagen(?string $rutaImagen): ?string
    {
        $rutaImagen = trim((string) $rutaImagen);

        if ($rutaImagen === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $rutaImagen) === 1) {
            return $rutaImagen;
        }

        $rutaNormalizada = ltrim($rutaImagen, '/');

        if (str_starts_with($rutaNormalizada, 'storage/') || str_starts_with($rutaNormalizada, 'assets/')) {
            return asset($rutaNormalizada);
        }

        return asset('assets/cell-legacy/'.$rutaNormalizada);
    }
}
