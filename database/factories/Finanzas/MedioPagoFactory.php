<?php

namespace Database\Factories\Finanzas;

use App\Models\Finanzas\MedioPago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedioPago>
 */
class MedioPagoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->bothify('MED-####'),
            'nombre' => 'Medio sintético de prueba',
            'requiere_caja' => false,
            'requiere_banco' => false,
            'activo' => true,
        ];
    }
}
