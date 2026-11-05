<?php

namespace Database\Factories\Finanzas;

use App\Models\Finanzas\CuentaBancaria;
use App\Models\Organizacion\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CuentaBancaria>
 */
class CuentaBancariaFactory extends Factory
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
            'codigo' => fake()->unique()->bothify('CTA-#####'),
            'banco' => fake()->company(),
            'tipo_cuenta' => 'corriente',
            'moneda' => 'PEN',
            'cuenta_mascara' => '****'.fake()->numerify('####'),
            'activa' => true,
        ];
    }
}
