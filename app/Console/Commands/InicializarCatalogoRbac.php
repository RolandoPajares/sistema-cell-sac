<?php

namespace App\Console\Commands;

use App\Servicios\RegistradorAuditoria;
use Database\Seeders\RbacInicialSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

#[Signature('technology-cell:inicializar-rbac {--aplicar : Autoriza la inicialización del catálogo RBAC existente}')]
#[Description('Inicializa el catálogo RBAC sin crear ni modificar usuarios u organización')]
final class InicializarCatalogoRbac extends Command
{
    public function handle(RbacInicialSeeder $inicializador, RegistradorAuditoria $registradorAuditoria): int
    {
        foreach (['usuarios', 'roles', 'usuarios_roles', 'empresas', 'sedes', 'almacenes', 'registros_auditoria'] as $tabla) {
            if (! Schema::hasTable($tabla)) {
                $this->error('Falta la tabla requerida '.$tabla.'.');

                return self::FAILURE;
            }
        }

        $administradoresVigentes = $this->contarAdministradoresVigentes();
        if ($administradoresVigentes === 0) {
            $this->error('No existe un administrador activo con una asignación RBAC vigente. No se crearán cuentas ni asignaciones.');

            return self::FAILURE;
        }

        $empresasActivas = DB::table('empresas')->where('activo', true)->orderBy('id')->limit(2)->pluck('id');
        if ($empresasActivas->count() !== 1) {
            $this->error('Se requiere exactamente una empresa activa para habilitar el alcance empresarial vigente.');

            return self::FAILURE;
        }

        $empresaId = (int) $empresasActivas->first();
        $sedesActivas = DB::table('sedes')
            ->where('empresa_id', $empresaId)
            ->where('activa', true)
            ->count();
        $almacenesActivos = $this->contarAlmacenesActivos($empresaId);

        if ($sedesActivas === 0 || $almacenesActivos === 0) {
            $this->error('La empresa activa debe tener al menos una sede y un almacén activos antes de habilitar operaciones.');

            return self::FAILURE;
        }

        $diagnostico = $inicializador->diagnosticarPreparacion();
        if ($diagnostico['errores'] !== []) {
            foreach ($diagnostico['errores'] as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->line('Base configurada: '.DB::connection()->getDatabaseName());
        $this->line('Administradores con asignación vigente: '.$administradoresVigentes);
        $this->line('Empresa activa: '.$empresaId.'; sedes activas: '.$sedesActivas.'; almacenes activos: '.$almacenesActivos.'.');
        $this->line('Roles existentes que se conservarán: '.$diagnostico['roles_existentes'].'.');
        foreach ($diagnostico['catalogos'] as $tabla => $cantidad) {
            $this->line($tabla.': '.$cantidad.' registro(s) actual(es).');
        }

        if ($diagnostico['ya_inicializado']) {
            $this->info('El catálogo RBAC ya está inicializado; no se modificó ningún dato.');

            return self::SUCCESS;
        }

        if (! $diagnostico['puede_inicializar']) {
            $this->error('El catálogo RBAC requiere conciliación antes de inicializarse.');

            return self::FAILURE;
        }

        $this->line('Se prepararán módulos, pantallas, permisos y menús definidos en el proyecto.');

        if (! $this->option('aplicar')) {
            $this->line('La vista previa no realiza escrituras. Para aplicar, ejecute nuevamente con --aplicar.');

            return self::SUCCESS;
        }

        $this->line('La aplicación solo insertará el catálogo RBAC existente y un evento de auditoría.');

        if (! $this->confirm('¿Autoriza inicializar el catálogo RBAC en esta base de datos?', false)) {
            $this->info('Operación cancelada; no se escribieron datos.');

            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($inicializador, $registradorAuditoria, $empresaId): void {
                $estadoActual = $inicializador->diagnosticarPreparacion();
                if ($estadoActual['errores'] !== []) {
                    throw new \RuntimeException(implode(' ', $estadoActual['errores']));
                }

                if ($estadoActual['ya_inicializado']) {
                    return;
                }

                $inicializador->run();
                $registradorAuditoria->record(
                    null,
                    'catalogo_rbac_inicializado',
                    'permisos',
                    null,
                    null,
                    ['version' => 'rbac-inicial-v1', 'empresa_id' => $empresaId],
                    null,
                    observations: 'Inicialización local del catálogo RBAC desde consola.',
                    scope: ['empresa_id' => $empresaId],
                );
            }, 3);
        } catch (Throwable $excepcion) {
            Log::error('La inicialización del catálogo RBAC fue revertida.', [
                'tipo' => class_basename($excepcion),
                'codigo' => (string) $excepcion->getCode(),
            ]);
            $this->error('No se completó la inicialización del catálogo RBAC; la transacción fue revertida.');

            return self::FAILURE;
        }

        $this->info('Catálogo RBAC inicializado y registrado en auditoría.');

        return self::SUCCESS;
    }

    private function contarAdministradoresVigentes(): int
    {
        return DB::table('usuarios_roles')
            ->join('usuarios', 'usuarios.id', '=', 'usuarios_roles.usuario_id')
            ->join('roles', 'roles.id', '=', 'usuarios_roles.rol_id')
            ->where('usuarios.activo', true)
            ->where('usuarios_roles.activo', true)
            ->where('roles.codigo', 'administrador')
            ->where('roles.activo', true)
            ->where(function ($consulta): void {
                $consulta->whereNull('usuarios_roles.vigente_hasta')
                    ->orWhere('usuarios_roles.vigente_hasta', '>', now());
            })
            ->count();
    }

    private function contarAlmacenesActivos(int $empresaId): int
    {
        return DB::table('almacenes')
            ->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
            ->where('sedes.empresa_id', $empresaId)
            ->where('sedes.activa', true)
            ->where('almacenes.activo', true)
            ->count();
    }
}
