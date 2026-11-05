<?php

namespace Database\Factories\Finanzas;

use App\Models\Finanzas\Reembolso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reembolso>
 */
class ReembolsoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'devolucion_id' => 1,
            'pago_id' => 1,
            'importe' => fake()->randomFloat(2, 0.01, 5000),
            'estado' => 'pendiente',
            'creado_en' => now(),
        ];
    }
}
