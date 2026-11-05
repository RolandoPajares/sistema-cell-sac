<?php

namespace Database\Factories\Organizacion;

use App\Models\Organizacion\Empresa;
use App\Models\Organizacion\Sede;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sede>
 */
class SedeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'codigo' => fake()->unique()->bothify('SEDE-###'),
            'nombre' => 'Sede '.fake()->city(),
            'direccion' => fake()->address(),
            'telefono' => null,
            'zona_horaria' => 'America/Lima',
            'activa' => true,
        ];
    }
}
