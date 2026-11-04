<?php

namespace Database\Factories\Seguridad;

use App\Models\Seguridad\Modulo;
use App\Models\Seguridad\Permiso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permiso>
 */
class PermisoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'modulo_id' => Modulo::factory(),
            'pantalla_id' => null,
            'codigo' => fake()->unique()->slug(3),
            'accion' => 'consultar',
            'nombre' => fake()->sentence(3),
            'activo' => true,
        ];
    }
}
