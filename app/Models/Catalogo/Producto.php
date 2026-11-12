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

/**
 * Define la responsabilidad de la clase Producto dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory;

    /**
     * Ejecuta la operación newFactory definida por la clase Producto.
     *
     * @return Factory Resultado de la operación.
     */
    protected static function newFactory(): Factory
    {
        return ProductoFactory::new();
    }

    // Usa la tabla 'productos' como origen de los registros de este modelo.
    protected $table = 'productos';

    // Define los campos que pueden asignarse de forma masiva desde formularios o servicios.
    protected $fillable = [
        'marca', 'nombre', 'categoria_id', 'categoria', 'precio', 'precio_original',
        'descuento', 'precio_oferta', 'url_imagen', 'existencias', 'almacenamiento',
        'color', 'etiqueta', 'descripcion', 'activo', 'creado_en', 'empresa_id',
    ];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase Producto.
     *
     * @return array Resultado de la operación.
     */
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

    /**
     * Ejecuta la operación scopeActive definida por la clase Producto.
     *
     * @param  mixed  $query  Parámetro recibido por la operación.
     * @return Builder Resultado de la operación.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * Ejecuta la operación category definida por la clase Producto.
     *
     * @return BelongsTo Resultado de la operación.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Ejecuta la operación images definida por la clase Producto.
     *
     * @return HasMany Resultado de la operación.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ImagenProducto::class, 'producto_id')->orderBy('orden')->orderBy('id');
    }

    /**
     * Ejecuta la operación features definida por la clase Producto.
     *
     * @return HasMany Resultado de la operación.
     */
    public function features(): HasMany
    {
        return $this->hasMany(CaracteristicaProducto::class, 'producto_id');
    }

    /**
     * Ejecuta la operación variants definida por la clase Producto.
     *
     * @return HasMany Resultado de la operación.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(VarianteProducto::class, 'producto_id');
    }

    // Define la relacion 'reviews' entre este modelo y los registros relacionados.
    public function reviews(): HasMany
    {
        return $this->hasMany(ResenaProducto::class, 'producto_id');
    }

    // Define la relacion 'favorites' entre este modelo y los registros relacionados.
    public function favorites(): HasMany
    {
        return $this->hasMany(FavoritoProducto::class, 'producto_id');
    }

    /**
     * Ejecuta la operación perfilInteligencia definida por la clase Producto.
     *
     * @return HasOne Resultado de la operación.
     */
    public function perfilInteligencia(): HasOne
    {
        return $this->hasOne(PerfilInteligenciaProducto::class, 'producto_id');
    }

    /**
     * Ejecuta la operación suppliers definida por la clase Producto.
     *
     * @return BelongsToMany Resultado de la operación.
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Proveedor::class, 'productos_proveedores', 'producto_id', 'proveedor_id');
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return string Resultado de la operación.
     */
    public function getEffectivePriceAttribute(): string
    {
        return (string) ($this->precio_oferta ?? $this->precio);
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return mixed Resultado de la operación.
     */
    public function getUrlImagenPrincipalAttribute(): ?string
    {
        $rutaImagenGaleria = $this->relationLoaded('images')
            ? $this->images->first()?->ruta_imagen
            : null;
        $rutaImagen = $rutaImagenGaleria ?: $this->url_imagen;

        return static::urlPublicaDeImagen(is_string($rutaImagen) ? $rutaImagen : null);
    }

    /**
     * Ejecuta la operación urlPublicaDeImagen definida por la clase Producto.
     *
     * @param  mixed  $rutaImagen  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
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
