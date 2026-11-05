<?php

namespace Database\Factories\Finanzas;

use App\Models\Finanzas\MovimientoCaja;
use App\Models\Finanzas\SesionCaja;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovimientoCaja>
 */
class MovimientoCajaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sesion_caja_id' => SesionCaja::factory(),
            'tipo' => 'ajuste_manual',
            'direccion' => fake()->randomElement(['entrada', 'salida']),
            'importe' => fake()->randomFloat(2, 1, 50),
            'descripcion' => fake()->sentence(),
            'creado_en' => now(),
        ];
    }
}
