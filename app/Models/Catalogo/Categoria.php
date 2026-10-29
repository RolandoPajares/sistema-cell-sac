<?php

namespace App\Models\Catalogo;

use Database\Factories\CategoriaFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected static function newFactory(): Factory
    {
        return CategoriaFactory::new();
    }

    /** @use HasFactory<CategoriaFactory> */
    use HasFactory;

    protected $table = 'categorias';

    protected $fillable = ['nombre', 'descripcion', 'activo', 'creado_en'];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['activo' => 'boolean', 'creado_en' => 'datetime'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
