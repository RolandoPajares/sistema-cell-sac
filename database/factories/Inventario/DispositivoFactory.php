<?php

namespace Database\Factories\Inventario;

use App\Models\Catalogo\Producto;
use App\Models\Inventario\Dispositivo;
use App\Models\Organizacion\Almacen;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dispositivo>
 */
class DispositivoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'variante_id' => null,
            'imei' => null,
            'numero_serie' => null,
            'detalle_compra_id' => null,
            'almacen_actual_id' => Almacen::factory(),
            'estado' => 'disponible',
            'fecha_ingreso' => null,
            'fecha_salida' => null,
        ];
    }
}
