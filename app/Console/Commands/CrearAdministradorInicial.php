<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

#[Signature('cell:crear-administrador-inicial')]
#[Description('Crea el único administrador inicial de cell_sac_limpia mediante credenciales interactivas.')]
class CrearAdministradorInicial extends Command
{
    public function handle(): int
    {
        if (DB::connection()->getDatabaseName() !== 'cell_sac_limpia') {
            $this->error('Comando detenido: la base configurada no es cell_sac_limpia.');

            return self::FAILURE;
        }

        foreach (['usuarios', 'roles', 'empresas', 'sedes', 'usuarios_roles'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->error('Falta la tabla requerida: '.$table.'.');

                return self::FAILURE;
            }
        }

        if (DB::table('usuarios')->exists()) {
            $this->error('Ya existe un usuario. No se creará un segundo administrador.');

            return self::FAILURE;
        }

        $data = [
            'nombre' => trim((string) $this->ask('Nombre del administrador')),
            'correo' => mb_strtolower(trim((string) $this->ask('Correo del administrador'))),
            'password' => (string) $this->secret('Contraseña'),
            'password_confirmation' => (string) $this->secret('Repite la contraseña'),
        ];

        $validator = Validator::make($data, [
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:160'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->line('Base: cell_sac_limpia; se creará un solo usuario con rol administrador.');
        if (! $this->confirm('¿Confirmas la creación?', false)) {
            $this->info('Operación cancelada.');

            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($data): void {
                if (DB::table('usuarios')->exists()) {
                    throw new RuntimeException('La base dejó de estar vacía durante la operación.');
                }

                $companyId = DB::table('empresas')->where('activo', true)->orderBy('id')->value('id');
                $roleId = DB::table('roles')->where('codigo', 'administrador')->where('activo', true)->value('id');
                if ($companyId === null || ! DB::table('sedes')->where('activa', true)->exists() || $roleId === null) {
                    throw new RuntimeException('Faltan empresa, sede o rol administrador inicial.');
                }

                $userId = DB::table('usuarios')->insertGetId([
                    'nombre' => $data['nombre'],
                    'correo' => $data['correo'],
                    'resumen_contrasena' => Hash::make($data['password']),
                    'rol' => 'administrador',
                    'activo' => true,
                    'acceso_controlado' => true,
                    'creado_en' => now(),
                    'actualizado_en' => now(),
                ]);

                DB::table('usuarios_roles')->insert([
                    'usuario_id' => $userId,
                    'rol_id' => $roleId,
                    'asignado_en' => now(),
                    'activo' => true,
                ]);

            });
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Administrador inicial creado correctamente.');

        return self::SUCCESS;
    }
}
