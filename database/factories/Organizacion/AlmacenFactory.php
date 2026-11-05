<?php

namespace Database\Factories\Organizacion;

use App\Models\Organizacion\Almacen;
use App\Models\Organizacion\Sede;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Almacen>
 */
class AlmacenFactory extends Factory
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
            'codigo' => fake()->unique()->bothify('ALM-###'),
            'nombre' => 'Almacén '.fake()->word(),
            'tipo' => 'general',
            'permite_venta' => true,
            'activo' => true,
        ];
    }
}
