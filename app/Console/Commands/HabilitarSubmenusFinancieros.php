<?php

namespace App\Console\Commands;

use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

#[Signature('technology-cell:habilitar-submenus-financieros {--aplicar : Autoriza habilitar los submenús financieros existentes}')]
#[Description('Corrige el acceso RBAC de pagos, cajas y reembolsos sin cambiar roles ni privilegios comerciales')]
/**
 * Define la responsabilidad de la clase HabilitarSubmenusFinancieros dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class HabilitarSubmenusFinancieros extends Command
{
    /** @var array<string, array{modulo:string,nombre:string,ruta:string}> */
    private const PANTALLAS_OBJETIVO = [
        'admin.payments' => [
            'modulo' => 'pagos',
            'nombre' => 'Pagos y cobros',
            'ruta' => 'management.payments',
        ],
        'admin.cash_registers' => [
            'modulo' => 'pagos',
            'nombre' => 'Cajas, aperturas y cierres',
            'ruta' => 'management.payments.preview.cash_registers',
        ],
        'admin.refunds' => [
            'modulo' => 'reembolsos',
            'nombre' => 'Reembolsos',
            'ruta' => 'management.refunds',
        ],
    ];

    /**
     * Ejecuta la operación principal asignada al componente.
     *
     * @param  mixed  $registradorAuditoria  Parámetro recibido por la operación.
     * @return int Resultado de la operación.
     */
    public function handle(RegistradorAuditoria $registradorAuditoria): int
    {
        foreach (['usuarios', 'roles', 'usuarios_roles', 'modulos', 'pantallas', 'permisos',
            'roles_permisos', 'menus', 'roles_menus', 'elementos_menu', 'registros_auditoria'] as $tabla) {
            if (! Schema::hasTable($tabla)) {
                $this->error('Falta la tabla requerida '.$tabla.'.');

                return self::FAILURE;
            }
        }

        $rol = DB::table('roles')->where('codigo', 'administrador')->where('activo', true)->first(['id']);
        if ($rol === null || ! $this->hayAdministradorVigente((int) $rol->id)) {
            $this->error('No existe un administrador activo con asignación RBAC vigente. No se modificaron datos.');

            return self::FAILURE;
        }

        $menuId = DB::table('menus')->where('codigo', 'administrador.sidebar')->value('id');
        if ($menuId === null || ! DB::table('roles_menus')->where('rol_id', $rol->id)->where('menu_id', $menuId)->exists()) {
            $this->error('No existe la asignación del menú administrativo actual. No se crearán menús ni roles.');

            return self::FAILURE;
        }

        $estado = $this->consultarEstado((int) $rol->id, (int) $menuId);
        if ($estado['errores'] !== []) {
            foreach ($estado['errores'] as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->line('Base configurada: '.DB::connection()->getDatabaseName());
        $this->line('Rol existente: administrador; se conservarán los roles y sus asignaciones actuales.');
        foreach ($estado['pantallas'] as $codigo => $pantalla) {
            $this->line($codigo.': '.($pantalla['habilitada'] ? 'habilitada' : 'se habilitará'));
            $this->line('Permiso de consulta: '.($pantalla['permiso_activo'] ? 'vigente' : 'se habilitará'));
            $this->line('Asignación administrativa: '.($pantalla['asignacion_existente'] ? 'vigente' : 'se agregará'));
            $this->line('Elemento de menú: '.($pantalla['menu_vinculado'] ? 'vinculado' : 'se vinculará'));
        }

        if (! $estado['requiere_cambios']) {
            $this->info('Los tres submenús financieros ya están habilitados; no se modificó ningún dato.');

            return self::SUCCESS;
        }

        if (! $this->option('aplicar')) {
            $this->line('La vista previa es de solo lectura. Para aplicar, ejecute nuevamente con --aplicar.');

            return self::SUCCESS;
        }

        $this->line('Se habilitarán únicamente estos submenús existentes para el rol administrador y se registrará auditoría.');
        if (! $this->confirm('¿Autoriza habilitar estos submenús financieros en esta base de datos?', false)) {
            $this->info('Operación cancelada; no se escribieron datos.');

            return self::FAILURE;
        }

        try {
            DB::transaction(function () use ($rol, $menuId, $registradorAuditoria, $estado): void {
                $permisosHabilitados = [];
                foreach ($estado['pantallas'] as $codigo => $pantalla) {
                    $codigoPermiso = 'pantalla.'.$codigo.'.consultar';
                    $permisoId = DB::table('permisos')->where('codigo', $codigoPermiso)->lockForUpdate()->value('id');
                    if ($permisoId === null) {
                        $permisoId = DB::table('permisos')->insertGetId([
                            'codigo' => $codigoPermiso,
                            'modulo_id' => $pantalla['modulo_id'],
                            'pantalla_id' => $pantalla['id'],
                            'accion' => 'consultar',
                            'nombre' => 'Consultar - '.$pantalla['nombre'],
                            'es_sistema' => true,
                            'activo' => true,
                            'creado_en' => now(),
                            'actualizado_en' => now(),
                        ]);
                    } else {
                        DB::table('permisos')->where('id', $permisoId)->update([
                            'activo' => true,
                            'actualizado_en' => now(),
                        ]);
                    }

                    $asignacion = DB::table('roles_permisos')->where('rol_id', $rol->id)
                        ->where('permiso_id', $permisoId)->first(['alcance']);
                    if ($asignacion === null) {
                        DB::table('roles_permisos')->insert([
                            'rol_id' => $rol->id,
                            'permiso_id' => $permisoId,
                            'alcance' => 'empresa',
                            'creado_en' => now(),
                        ]);
                    }

                    DB::table('pantallas')->where('id', $pantalla['id'])->update([
                        'habilitada' => true,
                        'actualizado_en' => now(),
                    ]);
                    DB::table('elementos_menu')->where('menu_id', $menuId)
                        ->where('pantalla_id', $pantalla['id'])->update([
                            'permiso_id' => $permisoId,
                            'habilitado' => true,
                            'actualizado_en' => now(),
                        ]);
                    $permisosHabilitados[$codigo] = (int) $permisoId;
                }

                $registradorAuditoria->record(
                    null,
                    'catalogo_rbac_submenus_financieros_habilitados',
                    'pantallas',
                    null,
                    $estado['resumen_anterior'],
                    ['pantallas' => array_keys(self::PANTALLAS_OBJETIVO), 'permisos' => $permisosHabilitados],
                    null,
                    observations: 'Actualización puntual del catálogo RBAC existente; sin cambios a usuarios, empresas, sedes, almacenes ni roles.',
                );
            }, 3);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->error('No se pudo aplicar la actualización RBAC; la transacción fue revertida.');

            return self::FAILURE;
        }

        $this->info('Submenús financieros habilitados y cambio registrado en auditoría.');

        return self::SUCCESS;
    }

    /**
     * Ejecuta la operación hayAdministradorVigente definida por la clase HabilitarSubmenusFinancieros.
     *
     * @param  mixed  $rolId  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function hayAdministradorVigente(int $rolId): bool
    {
        return DB::table('usuarios_roles as asignacion')
            ->join('usuarios', 'usuarios.id', '=', 'asignacion.usuario_id')
            ->where('asignacion.rol_id', $rolId)
            ->where('asignacion.activo', true)
            ->where('usuarios.activo', true)
            ->where(fn ($consulta) => $consulta->whereNull('asignacion.vigente_hasta')->orWhere('asignacion.vigente_hasta', '>', now()))
            ->exists();
    }

    /**
     * @return array{
     *     errores:array<int,string>,
     *     requiere_cambios:bool,
     *     resumen_anterior:array<string,mixed>,
     *     pantallas:array<string,array{id:int,modulo_id:int,nombre:string,habilitada:bool,permiso_activo:bool,asignacion_existente:bool,menu_vinculado:bool}>
     * }
     */
    private function consultarEstado(int $rolId, int $menuId): array
    {
        $filas = DB::table('pantallas as pantalla')
            ->join('modulos as modulo', 'modulo.id', '=', 'pantalla.modulo_id')
            ->whereIn('pantalla.codigo', array_keys(self::PANTALLAS_OBJETIVO))
            ->get([
                'pantalla.id', 'pantalla.codigo', 'pantalla.nombre', 'pantalla.ruta', 'pantalla.habilitada',
                'pantalla.modulo_id', 'modulo.codigo as codigo_modulo', 'modulo.habilitado as modulo_habilitado',
            ])->keyBy('codigo');
        $errores = [];
        $pantallas = [];
        $resumenAnterior = [];

        foreach (self::PANTALLAS_OBJETIVO as $codigo => $definicion) {
            $fila = $filas->get($codigo);
            if ($fila === null) {
                $errores[] = 'No existe la pantalla RBAC '.$codigo.'.';

                continue;
            }
            if ($fila->codigo_modulo !== $definicion['modulo'] || $fila->ruta !== $definicion['ruta'] || ! $fila->modulo_habilitado) {
                $errores[] = 'La pantalla '.$codigo.' no coincide con su módulo o ruta vigente; requiere conciliación manual.';

                continue;
            }

            $codigoPermiso = 'pantalla.'.$codigo.'.consultar';
            $permiso = DB::table('permisos')->where('codigo', $codigoPermiso)->first(['id', 'modulo_id', 'pantalla_id', 'accion', 'activo', 'es_sistema']);
            if ($permiso !== null && ((int) $permiso->modulo_id !== (int) $fila->modulo_id
                || (int) $permiso->pantalla_id !== (int) $fila->id
                || $permiso->accion !== 'consultar' || ! $permiso->es_sistema)) {
                $errores[] = 'El permiso '.$codigoPermiso.' tiene una identidad distinta y no se modificará.';

                continue;
            }

            $permisoId = $permiso === null ? null : (int) $permiso->id;
            $asignacion = $permisoId === null ? null : DB::table('roles_permisos')->where('rol_id', $rolId)
                ->where('permiso_id', $permisoId)->first(['alcance']);
            if ($asignacion !== null && $asignacion->alcance !== 'empresa') {
                $errores[] = 'El permiso '.$codigoPermiso.' ya tiene un alcance administrativo distinto y requiere conciliación manual.';

                continue;
            }

            $elemento = DB::table('elementos_menu')->where('menu_id', $menuId)->where('pantalla_id', $fila->id)
                ->first(['id', 'permiso_id', 'habilitado']);
            if ($elemento === null || ($elemento->permiso_id !== null && $permisoId !== null
                && (int) $elemento->permiso_id !== $permisoId)) {
                $errores[] = 'El elemento del menú para '.$codigo.' no coincide con la pantalla; no se creará uno nuevo.';

                continue;
            }

            $pantallas[$codigo] = [
                'id' => (int) $fila->id,
                'modulo_id' => (int) $fila->modulo_id,
                'nombre' => $definicion['nombre'],
                'habilitada' => (bool) $fila->habilitada,
                'permiso_activo' => $permiso !== null && (bool) $permiso->activo,
                'asignacion_existente' => $asignacion !== null,
                'menu_vinculado' => $permisoId !== null && (int) ($elemento->permiso_id ?? 0) === $permisoId && (bool) $elemento->habilitado,
            ];
            $resumenAnterior[$codigo] = [
                'pantalla_habilitada' => (bool) $fila->habilitada,
                'permiso_id' => $permisoId,
                'permiso_activo' => $permiso !== null && (bool) $permiso->activo,
                'asignacion_alcance' => $asignacion?->alcance,
                'elemento_permiso_id' => $elemento->permiso_id === null ? null : (int) $elemento->permiso_id,
                'elemento_habilitado' => (bool) $elemento->habilitado,
            ];
        }

        $requiereCambios = count($pantallas) === count(self::PANTALLAS_OBJETIVO)
            && collect($pantallas)->contains(static fn (array $pantalla): bool => ! $pantalla['habilitada']
                || ! $pantalla['permiso_activo'] || ! $pantalla['asignacion_existente'] || ! $pantalla['menu_vinculado']);

        return [
            'errores' => $errores,
            'requiere_cambios' => $requiereCambios,
            'resumen_anterior' => $resumenAnterior,
            'pantallas' => $pantallas,
        ];
    }
}
