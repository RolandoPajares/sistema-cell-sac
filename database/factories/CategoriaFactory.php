<?php

namespace Database\Factories;

use App\Models\Catalogo\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->word(),
            'descripcion' => fake()->sentence(),
            'activo' => true,
            'creado_en' => now(),
        ];
    }
}
