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
    /**
     * Verifica que la base sea la prevista y crea el primer administrador
     * junto con su asignacion al rol administrador.
     */
    // Coordina el proceso completo que ejecuta este comando de consola.
    public function handle(): int
    {
        // Comprueba la condicion DB::connection()->getDatabaseName() !== 'cell_sac_limpia' antes de continuar.
        if (DB::connection()->getDatabaseName() !== 'cell_sac_limpia') {
            $this->error('Comando detenido: la base configurada no es cell_sac_limpia.');

            return self::FAILURE;
        }

        // Recorre ['usuarios', 'roles', 'empresas', 'sedes', 'usuarios_roles'] as $table para validar o procesar cada elemento.
        // Confirma que existan las tablas minimas para crear el usuario y
        // relacionarlo con la empresa, sede y rol disponibles.
        foreach (['usuarios', 'roles', 'empresas', 'sedes', 'usuarios_roles'] as $table) {
            // Comprueba la condicion ! Schema::hasTable($table) antes de continuar.
            if (! Schema::hasTable($table)) {
                $this->error('Falta la tabla requerida: '.$table.'.');

                return self::FAILURE;
            }
        }

        // Comprueba la condicion DB::table('usuarios')->exists() antes de continuar.
        if (DB::table('usuarios')->exists()) {
            $this->error('Ya existe un usuario. No se creará un segundo administrador.');

            return self::FAILURE;
        }

        // Reune los datos ingresados por el operador para validarlos y guardarlos.
        // Reune las credenciales introducidas en consola antes de validarlas.
        $data = [
            'nombre' => trim((string) $this->ask('Nombre del administrador')),
            'correo' => mb_strtolower(trim((string) $this->ask('Correo del administrador'))),
            'password' => (string) $this->secret('Contraseña'),
            'password_confirmation' => (string) $this->secret('Repite la contraseña'),
        ];

        // Crea el validador que comprueba los datos capturados en la consola.
        // Valida nombre, correo y confirmacion de contrasena antes de tocar la base.
        $validator = Validator::make($data, [
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:160'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->numbers()],
        ]);

        // Comprueba la condicion $validator->fails() antes de continuar.
        if ($validator->fails()) {
            // Recorre $validator->errors()->all() as $error para validar o procesar cada elemento.
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->line('Base: cell_sac_limpia; se creará un solo usuario con rol administrador.');
        // Comprueba la condicion ! $this->confirm('¿Confirmas la creación?', false) antes de continuar.
        if (! $this->confirm('¿Confirmas la creación?', false)) {
            $this->info('Operación cancelada.');

            return self::FAILURE;
        }

        // Ejecuta la operacion dentro de un bloque controlado para atender posibles errores.
        // La transaccion evita dejar una cuenta parcialmente registrada si falla una insercion.
        try {
            // Agrupa las inserciones para confirmar todo o revertirlo si ocurre un error.
            DB::transaction(function () use ($data): void {
                // Comprueba la condicion DB::table('usuarios')->exists() antes de continuar.
                if (DB::table('usuarios')->exists()) {
                    throw new RuntimeException('La base dejó de estar vacía durante la operación.');
                }

                // Obtiene la empresa activa y el rol administrador que recibira la cuenta.
                $companyId = DB::table('empresas')->where('activo', true)->orderBy('id')->value('id');
                $roleId = DB::table('roles')->where('codigo', 'administrador')->where('activo', true)->value('id');
                // Comprueba la condicion $companyId === null || ! DB::table('sedes')->where('activa', true)->exists() || $roleId === null antes de continuar.
                if ($companyId === null || ! DB::table('sedes')->where('activa', true)->exists() || $roleId === null) {
                    throw new RuntimeException('Faltan empresa, sede o rol administrador inicial.');
                }

                // Inserta la cuenta con la contrasena protegida mediante Hash::make().
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

                // Relaciona la cuenta creada con el rol administrador para activar su RBAC.
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
