<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('cell:user-create')]
#[Description('Crea una cuenta CELL SAC con uno de los siete roles permitidos.')]
class CrearUsuarioCell extends Command
{
    public function handle(): int
    {
        $name = trim((string) $this->ask('Nombre'));
        $email = mb_strtolower(trim((string) $this->ask('Correo')));
        $role = $this->choice('Rol', User::ROLES, 'cliente_minorista');
        $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)');

        if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 12) {
            $this->error('Datos inválidos o contraseña corta.');

            return self::FAILURE;
        }

        if (User::query()->where('correo', $email)->exists()) {
            $this->error('El correo ya está registrado.');

            return self::FAILURE;
        }

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
