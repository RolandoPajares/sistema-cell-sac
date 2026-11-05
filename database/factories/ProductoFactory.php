<?php

namespace Database\Factories;

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'marca' => 'Samsung',
            'nombre' => fake()->unique()->words(2, true),
            'categoria_id' => Categoria::factory(),
            'categoria' => 'Celulares',
            'precio' => 500,
            'precio_original' => null,
            'descuento' => null,
            'precio_oferta' => null,
            'url_imagen' => null,
            'existencias' => 4,
            'almacenamiento' => null,
            'color' => null,
            'etiqueta' => null,
            'descripcion' => fake()->sentence(),
            'activo' => true,
            'creado_en' => now(),
        ];
    }
}
