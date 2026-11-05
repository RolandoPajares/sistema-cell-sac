<?php

namespace Database\Factories\Seguridad;

use App\Models\Seguridad\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rol>
 */
class RolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->slug(2),
            'nombre' => fake()->words(2, true),
            'descripcion' => fake()->sentence(),
            'alcance_permitido' => fake()->randomElement(['empresa', 'sede', 'propio']),
            'es_sistema' => false,
            'activo' => true,
        ];
    }
}
