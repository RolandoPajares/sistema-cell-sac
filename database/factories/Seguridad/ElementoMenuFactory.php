<?php

namespace Database\Factories\Seguridad;

use App\Models\Seguridad\ElementoMenu;
use App\Models\Seguridad\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ElementoMenu>
 */
class ElementoMenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'padre_id' => null,
            'pantalla_id' => null,
            'permiso_id' => null,
            'codigo' => fake()->unique()->slug(2),
            'etiqueta' => fake()->words(2, true),
            'icono' => 'bi-grid',
            'tipo' => 'grupo',
            'orden' => 0,
            'habilitado' => true,
        ];
    }
}
