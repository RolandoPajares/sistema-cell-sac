<?php

namespace Database\Factories\Finanzas;

use App\Models\Finanzas\Caja;
use App\Models\Organizacion\Sede;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caja>
 */
class CajaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sede_id' => Sede::factory(),
            'codigo' => fake()->unique()->bothify('CA-#####'),
            'nombre' => 'Caja '.fake()->word(),
            'activa' => true,
            'creado_en' => now(),
        ];
    }
}
