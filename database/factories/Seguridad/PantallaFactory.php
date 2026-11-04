<?php

namespace Database\Factories\Seguridad;

use App\Models\Seguridad\Modulo;
use App\Models\Seguridad\Pantalla;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pantalla>
 */
class PantallaFactory extends Factory
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
            'codigo' => fake()->unique()->slug(3),
            'nombre' => fake()->words(2, true),
            'ruta' => null,
            'icono' => 'bi-grid',
            'habilitada' => true,
        ];
    }
}
