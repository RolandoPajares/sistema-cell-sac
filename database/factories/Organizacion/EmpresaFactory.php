<?php

namespace Database\Factories\Organizacion;

use App\Models\Organizacion\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Empresa>
 */
class EmpresaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->bothify('EMP-####'),
            'razon_social' => fake()->company(),
            'nombre_comercial' => fake()->companySuffix(),
            'ruc' => null,
            'moneda_base' => 'PEN',
            'activo' => true,
        ];
    }
}
