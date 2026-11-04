<?php

namespace Database\Factories\Finanzas;

use App\Models\Finanzas\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pago>
 */
class PagoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'empresa_id' => 1,
            'sede_id' => 1,
            'codigo' => fake()->unique()->bothify('PAG-########'),
            'tipo' => 'cobro',
            'estado' => 'registrado',
            'medio_pago_id' => 1,
            'moneda' => 'PEN',
            'importe' => fake()->randomFloat(2, 0.01, 5000),
            'fecha_pago' => now(),
            'creado_en' => now(),
        ];
    }
}
