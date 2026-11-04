<?php

namespace Database\Factories\Seguridad;

use App\Models\Seguridad\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
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
            'contexto' => 'sidebar',
            'habilitado' => true,
        ];
    }
}
