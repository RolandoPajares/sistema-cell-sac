<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'rol' => 'cliente_minorista',
            'token_recordatorio' => Str::random(10),
        ];
    }

    /**
     * Ejecuta la operación administrator definida por la clase UserFactory.
     *
     * @return static Resultado de la operación.
     */
    public function administrator(): static
    {
        return $this->state(fn (array $attributes): array => ['rol' => 'administrador']);
    }
}
