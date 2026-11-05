<?php

namespace Database\Factories\Comunicacion;

use App\Models\Comunicacion\Notificacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notificacion>
 */
class NotificacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo' => 'App\\Notifications\\AvisoSistema',
            'tipo_notificable' => User::class,
            'notificable_id' => User::factory(),
            'datos' => ['mensaje' => $this->faker->sentence()],
            'leido_en' => null,
        ];
    }
}
