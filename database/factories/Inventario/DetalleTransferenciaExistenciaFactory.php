<?php

namespace Database\Factories\Inventario;

use App\Models\Catalogo\Producto;
use App\Models\Inventario\DetalleTransferenciaExistencia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleTransferenciaExistencia>
 */
class DetalleTransferenciaExistenciaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transferencia_id' => TransferenciaExistencia::factory(),
            'producto_id' => Producto::factory(),
            'variante_id' => null,
            'cantidad_solicitada' => 1,
            'cantidad_enviada' => 0,
            'cantidad_recibida' => 0,
        ];
    }
}
