<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('cell:user-create')]
#[Description('Crea una cuenta CELL SAC con un rol activo del catálogo RBAC.')]
/**
 * Define la responsabilidad de la clase CrearUsuarioCell dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CrearUsuarioCell extends Command
{
    /**
     * Solicita los datos de una cuenta, valida sus credenciales y la registra
     * utilizando el rol RBAC elegido por el operador de consola.
     */
    /**
     * Ejecuta la operación principal asignada al componente.
     *
     * @return int Resultado de la operación.
     */
    public function handle(): int
    {
        $name = trim((string) $this->ask('Nombre'));
        $email = mb_strtolower(trim((string) $this->ask('Correo')));
        $role = $this->choice('Rol', User::availableRoleCodes(), 'cliente_minorista');
        $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)');

        // Detiene el proceso si faltan datos, el correo no tiene formato valido
        // o la contrasena no cumple la longitud minima solicitada.
        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 12) {
            $this->error('Datos inválidos o contraseña corta.');

            return self::FAILURE;
        }

        if (User::query()->where('correo', $email)->exists()) {
            $this->error('El correo ya está registrado.');

            return self::FAILURE;
        }

        // Crea la cuenta usando el modelo User, que aplica sus casts y reglas
        // de almacenamiento de la contrasena antes de guardar los datos.
        User::query()->create([
            'nombre' => $name,
            'correo' => $email,
            'password' => $password,
            'rol' => $role,
        ]);

        $this->info('Usuario creado. Guarda sus credenciales de forma segura.');

        return self::SUCCESS;
    }
}
