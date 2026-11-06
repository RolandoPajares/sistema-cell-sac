<?php

namespace App\Models\Catalogo;

use Database\Factories\CategoriaFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Define la responsabilidad de la clase Categoria dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class Categoria extends Model
{
    /**
     * Ejecuta la operación newFactory definida por la clase Categoria.
     *
     * @return Factory Resultado de la operación.
     */
    protected static function newFactory(): Factory
    {
        return CategoriaFactory::new();
    }

    /** @use HasFactory<CategoriaFactory> */
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = ['nombre', 'descripcion', 'activo', 'creado_en', 'empresa_id'];

    public $timestamps = false;

    /**
     * Ejecuta la operación casts definida por la clase Categoria.
     *
     * @return array Resultado de la operación.
     */
    protected function casts(): array
    {
        return ['activo' => 'boolean', 'creado_en' => 'datetime'];
    }

    /**
     * Ejecuta la operación products definida por la clase Categoria.
     *
     * @return HasMany Resultado de la operación.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
