<?php

namespace Database\Factories\Finanzas;

use App\Models\Finanzas\Caja;
use App\Models\Finanzas\SesionCaja;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SesionCaja>
 */
class SesionCajaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'caja_id' => Caja::factory(),
            'usuario_apertura_id' => User::factory(),
            'estado' => 'abierta',
            'saldo_inicial' => fake()->randomFloat(2, 0, 100),
            'abierta_en' => now(),
        ];
    }
}
