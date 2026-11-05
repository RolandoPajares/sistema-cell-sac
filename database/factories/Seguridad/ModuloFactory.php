<?php

namespace Database\Factories\Seguridad;

use App\Models\Seguridad\Modulo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Modulo>
 */
class ModuloFactory extends Factory
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
            'icono' => 'bi-grid',
            'orden' => 0,
            'habilitado' => true,
        ];
    }
}
