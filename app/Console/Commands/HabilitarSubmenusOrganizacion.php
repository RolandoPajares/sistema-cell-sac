<?php

namespace App\Console\Commands;

use App\Servicios\RegistradorAuditoria;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

#[Signature('technology-cell:habilitar-submenus-organizacion {--aplicar : Aplica los cambios RBAC aprobados} {--incluir-personal : Prepara únicamente la pantalla Personal por sede}')]
#[Description('Prepara o habilita las pantallas RBAC existentes de empresa, sedes y almacenes')]
final class HabilitarSubmenusOrganizacion extends Command
{
    /** @var array<string, array{modulo:string,nombre:string,ruta:string,icono:string}> */
    private const PANTALLAS_OBJETIVO = [
        'admin.company' => [
            'modulo' => 'organizacion',
            'nombre' => 'Datos de la empresa',
            'ruta' => 'management.company_settings.data',
            'icono' => 'bi-building',
        ],
        'admin.sites' => [
            'modulo' => 'organizacion',
            'nombre' => 'Sedes',
            'ruta' => 'management.company_settings.sites',
            'icono' => 'bi-buildings',
        ],
        'admin.warehouses' => [
            'modulo' => 'organizacion',
            'nombre' => 'Almacenes',
            'ruta' => 'management.company_settings.warehouses',
            'icono' => 'bi-house-gear',
        ],
        'admin.staff' => [
            'modulo' => 'organizacion',
            'nombre' => 'Personal por sede',
            'ruta' => 'management.company_settings.staff',
            'icono' => 'bi-person-workspace',
        ],
    ];

    public function handle(RegistradorAuditoria $registradorAuditoria): int
    {
        $pantallasObjetivo = $this->pantallasObjetivo();
        $estado = $this->consultarEstado($pantallasObjetivo);

        if ($estado['errores'] !== []) {
            foreach ($estado['errores'] as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->line('Base configurada: '.DB::connection()->getDatabaseName());
        $this->line('Rol existente: administrador; asignaciones de los otros seis roles permanecerán intactas.');
        foreach ($estado['pantallas'] as $codigo => $pantalla) {
            $this->line($codigo.' · '.$pantalla['ruta_actual'].' → '.$pantalla['ruta_objetivo']);
            $this->line('Permiso de consulta: '.($pantalla['permiso_vigente'] ? 'vigente' : 'se habilitará'));
            $this->line('Asignación administrativa: '.($pantalla['asignacion_vigente'] ? 'vigente' : 'se agregará'));
            $this->line('Vínculo del menú: '.($pantalla['menu_vigente'] ? 'vigente' : 'se habilitará'));
        }

        if (! $estado['requiere_cambios']) {
            $this->info($this->option('incluir-personal')
                ? 'La pantalla Personal por sede ya está habilitada; no se modificó ningún dato.'
                : 'Las tres pantallas de organización ya están habilitadas; no se modificó ningún dato.');

            return self::SUCCESS;
        }

        if (! $this->option('aplicar')) {
            $this->line('Vista previa de solo lectura. No se escribió en la base ni se creó un respaldo.');
            $this->line('Se actualizarán exclusivamente rutas y pantallas RBAC existentes, permisos de consulta del administrador y sus vínculos de menú.');

            return self::SUCCESS;
        }

        try {
            $respaldo = $this->crearRespaldo($estado, $pantallasObjetivo);
            DB::transaction(function () use ($estado, $registradorAuditoria, $respaldo, $pantallasObjetivo): void {
                $estadoActual = $this->consultarEstado($pantallasObjetivo);
                if ($estadoActual['errores'] !== []) {
                    throw new \RuntimeException(implode(' ', $estadoActual['errores']));
                }

                if (! $estadoActual['requiere_cambios']) {
                    return;
                }

                $identificadoresPermiso = [];
                foreach ($pantallasObjetivo as $codigo => $definicion) {
                    $pantalla = DB::table('pantallas')->where('codigo', $codigo)->lockForUpdate()->first();
                    $codigoPermiso = 'pantalla.'.$codigo.'.consultar';
                    $permiso = DB::table('permisos')->where('codigo', $codigoPermiso)->lockForUpdate()->first();

                    if ($permiso === null) {
                        $permisoId = (int) DB::table('permisos')->insertGetId([
                            'codigo' => $codigoPermiso,
                            'modulo_id' => $pantalla->modulo_id,
                            'pantalla_id' => $pantalla->id,
                            'accion' => 'consultar',
                            'nombre' => 'Consultar - '.$definicion['nombre'],
                            'activo' => true,
                            'actualizado_en' => now(),
                        ]);
                    } else {
                        $permisoId = (int) $permiso->id;
                        DB::table('permisos')->where('id', $permisoId)->update([
                            'activo' => true,
                            'actualizado_en' => now(),
                        ]);
                    }

                    DB::table('pantallas')->where('id', $pantalla->id)->update([
                        'ruta' => $definicion['ruta'],
                        'habilitada' => true,
                        'actualizado_en' => now(),
                    ]);

                    if (! DB::table('roles_permisos')->where('rol_id', $estado['rol_id'])->where('permiso_id', $permisoId)->exists()) {
                        DB::table('roles_permisos')->insert([
                            'rol_id' => $estado['rol_id'],
                            'permiso_id' => $permisoId,
                            'alcance' => 'empresa',
                            'creado_en' => now(),
                        ]);
                    }

                    $elemento = DB::table('elementos_menu')->where('menu_id', $estado['menu_id'])
                        ->where('pantalla_id', $pantalla->id)->lockForUpdate()->first();

                    if ($elemento === null) {
                        $grupoId = DB::table('elementos_menu')->where('menu_id', $estado['menu_id'])
                            ->where('codigo', 'grupo.organizacion')->value('id');
                        $orden = (int) DB::table('elementos_menu')->where('menu_id', $estado['menu_id'])
                            ->where('padre_id', $grupoId)->max('orden') + 1;
                        DB::table('elementos_menu')->insert([
                            'menu_id' => $estado['menu_id'],
                            'codigo' => $codigo,
                            'padre_id' => $grupoId,
                            'pantalla_id' => $pantalla->id,
                            'permiso_id' => $permisoId,
                            'etiqueta' => $definicion['nombre'],
                            'icono' => $definicion['icono'],
                            'tipo' => 'pantalla',
                            'orden' => $orden,
                            'habilitado' => true,
                            'actualizado_en' => now(),
                        ]);
                    } else {
                        DB::table('elementos_menu')->where('id', $elemento->id)->update([
                            'permiso_id' => $permisoId,
                            'habilitado' => true,
                            'actualizado_en' => now(),
                        ]);
                    }

                    $identificadoresPermiso[$codigoPermiso] = $permisoId;
                }

                $registradorAuditoria->record(
                    null,
                    'catalogo_rbac_submenus_organizacion_habilitados',
                    'pantallas',
                    null,
                    $estado['resumen_anterior'],
                    [
                        'pantallas' => array_keys($pantallasObjetivo),
                        'permisos' => $identificadoresPermiso,
                        'respaldo_sha256' => $respaldo['sha256'],
                    ],
                    null,
                    observations: 'Actualización puntual del catálogo RBAC existente; sin crear roles, usuarios ni datos empresariales.',
                    scope: ['empresa_id' => $estado['empresa_id']],
                );
            }, 3);
        } catch (Throwable $excepcion) {
            report($excepcion);
            $this->error('No se pudo aplicar la actualización RBAC; la transacción fue revertida.');

            return self::FAILURE;
        }

        $this->info($this->option('incluir-personal')
            ? 'La pantalla Personal por sede quedó habilitada para administrador.'
            : 'Submenús de empresa, sedes y almacenes habilitados para administrador.');
        $this->line('Respaldo verificado: '.$respaldo['ruta']);
        $this->line('SHA-256: '.$respaldo['sha256']);

        return self::SUCCESS;
    }

    /**
     * @return array{
     *     errores:array<int,string>,
     *     requiere_cambios:bool,
     *     rol_id:?int,
     *     menu_id:?int,
     *     empresa_id:?int,
     *     pantallas:array<string,array{ruta_actual:?string,ruta_objetivo:string,permiso_vigente:bool,asignacion_vigente:bool,menu_vigente:bool,pantalla_id:int,modulo_id:int,permiso_id:?int,elemento_id:?int}>,
     *     resumen_anterior:array<string,mixed>
     * }
     */
    /** @param array<string, array{modulo:string,nombre:string,ruta:string,icono:string}> $pantallasObjetivo */
    private function consultarEstado(array $pantallasObjetivo): array
    {
        $tablasRequeridas = [
            'usuarios', 'roles', 'usuarios_roles', 'empresas', 'modulos', 'pantallas', 'permisos',
            'roles_permisos', 'menus', 'roles_menus', 'elementos_menu', 'registros_auditoria',
        ];
        $tablasFaltantes = array_values(array_filter(
            $tablasRequeridas,
            static fn (string $tabla): bool => ! Schema::hasTable($tabla),
        ));
        $estado = [
            'errores' => [],
            'requiere_cambios' => false,
            'rol_id' => null,
            'menu_id' => null,
            'empresa_id' => null,
            'pantallas' => [],
            'resumen_anterior' => [],
        ];

        if ($tablasFaltantes !== []) {
            $estado['errores'][] = 'Faltan tablas RBAC requeridas: '.implode(', ', $tablasFaltantes).'.';

            return $estado;
        }

        $rol = DB::table('roles')->where('codigo', 'administrador')->where('activo', true)->first(['id']);
        if ($rol === null || ! $this->hayAdministradorVigente((int) $rol->id)) {
            $estado['errores'][] = 'No existe un administrador activo con asignación RBAC vigente.';

            return $estado;
        }

        $empresasActivas = DB::table('empresas')->where('activo', true)->orderBy('id')->limit(2)->pluck('id');
        if ($empresasActivas->count() !== 1) {
            $estado['errores'][] = 'Se requiere exactamente una empresa activa para asignar el alcance vigente del administrador.';

            return $estado;
        }

        $modulo = DB::table('modulos')->where('codigo', 'organizacion')->where('habilitado', true)->first(['id']);
        if ($modulo === null) {
            $estado['errores'][] = 'No existe el módulo organización habilitado en el catálogo vigente.';

            return $estado;
        }

        $menu = DB::table('menus')->where('codigo', 'administrador.sidebar')->first(['id', 'habilitado']);
        $menuId = $menu?->id;
        if ($menu === null || ! $menu->habilitado
            || ! DB::table('roles_menus')->where('rol_id', $rol->id)->where('menu_id', $menuId)->exists()) {
            $estado['errores'][] = 'No existe la asignación actual del menú administrativo.';

            return $estado;
        }

        $grupo = DB::table('elementos_menu')->where('menu_id', $menuId)->where('codigo', 'grupo.organizacion')
            ->first(['id', 'tipo', 'habilitado']);
        $grupoId = $grupo?->id;
        if ($grupo === null || $grupo->tipo !== 'grupo' || ! $grupo->habilitado) {
            $estado['errores'][] = 'No existe el grupo organización del menú administrativo; no se creará una estructura nueva.';

            return $estado;
        }

        foreach ($pantallasObjetivo as $codigo => $definicion) {
            if (! Route::has($definicion['ruta'])) {
                $estado['errores'][] = 'La ruta existente '.$definicion['ruta'].' no está registrada en Laravel.';

                continue;
            }

            $pantallasCoincidentes = DB::table('pantallas')->where('codigo', $codigo)->limit(2)->get(['id', 'modulo_id', 'ruta', 'habilitada']);
            if ($pantallasCoincidentes->count() !== 1) {
                $estado['errores'][] = 'La pantalla '.$codigo.' no existe de forma única en el catálogo RBAC.';

                continue;
            }

            $pantalla = $pantallasCoincidentes->first();
            if ((int) $pantalla->modulo_id !== (int) $modulo->id) {
                $estado['errores'][] = 'La pantalla '.$codigo.' no pertenece al módulo organización existente.';

                continue;
            }

            $codigoPermiso = 'pantalla.'.$codigo.'.consultar';
            $permisosCoincidentes = DB::table('permisos')->where('codigo', $codigoPermiso)->limit(2)->get(['id', 'modulo_id', 'pantalla_id', 'activo']);
            if ($permisosCoincidentes->count() > 1) {
                $estado['errores'][] = 'El permiso '.$codigoPermiso.' está duplicado.';

                continue;
            }

            $permiso = $permisosCoincidentes->first();
            if ($permiso !== null && ((int) $permiso->modulo_id !== (int) $modulo->id || (int) $permiso->pantalla_id !== (int) $pantalla->id)) {
                $estado['errores'][] = 'El permiso '.$codigoPermiso.' no coincide con el módulo y pantalla existentes.';

                continue;
            }

            $asignacionesPermiso = $permiso === null ? collect() : DB::table('roles_permisos')
                ->where('permiso_id', $permiso->id)->get(['rol_id', 'alcance']);
            if ($asignacionesPermiso->contains(static fn (object $asignacion): bool => (int) $asignacion->rol_id !== (int) $rol->id)) {
                $estado['errores'][] = 'El permiso '.$codigoPermiso.' ya está asignado a otro rol; activarlo cambiaría su acceso efectivo.';

                continue;
            }

            if ($asignacionesPermiso->contains(static fn (object $asignacion): bool => $asignacion->alcance !== 'empresa')) {
                $estado['errores'][] = 'La asignación existente de '.$codigoPermiso.' tiene un alcance distinto de empresa; no será alterada.';

                continue;
            }

            if ($asignacionesPermiso->count() > 1) {
                $estado['errores'][] = 'El permiso '.$codigoPermiso.' tiene asignaciones administrativas duplicadas.';

                continue;
            }

            $elementosCoincidentes = DB::table('elementos_menu')->where('menu_id', $menuId)
                ->where('pantalla_id', $pantalla->id)->limit(2)->get(['id', 'codigo', 'padre_id', 'permiso_id', 'habilitado']);
            if ($elementosCoincidentes->count() > 1) {
                $estado['errores'][] = 'El vínculo de '.$codigo.' está duplicado en el menú administrativo.';

                continue;
            }

            $elemento = $elementosCoincidentes->first();
            $elementoCodigoExistente = DB::table('elementos_menu')->where('menu_id', $menuId)
                ->where('codigo', $codigo)->first(['id', 'pantalla_id']);
            if ($elemento !== null && ($elemento->codigo !== $codigo || (int) $elemento->padre_id !== (int) $grupoId)) {
                $estado['errores'][] = 'El vínculo de '.$codigo.' está en otro grupo o usa un código distinto; no será reubicado.';

                continue;
            }

            if ($elementoCodigoExistente !== null && (int) $elementoCodigoExistente->pantalla_id !== (int) $pantalla->id) {
                $estado['errores'][] = 'El código de menú '.$codigo.' ya está asociado a otra pantalla.';

                continue;
            }

            if ($elemento === null && $elementoCodigoExistente !== null) {
                $estado['errores'][] = 'El código de menú '.$codigo.' está ocupado por un vínculo incompatible.';

                continue;
            }

            $permisoVigente = $permiso !== null && (bool) $permiso->activo;
            $asignacionVigente = $asignacionesPermiso->isNotEmpty();
            $menuVigente = $elemento !== null && (bool) $elemento->habilitado
                && $permiso !== null && (int) $elemento->permiso_id === (int) $permiso->id;
            $estado['pantallas'][$codigo] = [
                'ruta_actual' => $pantalla->ruta,
                'ruta_objetivo' => $definicion['ruta'],
                'permiso_vigente' => $permisoVigente,
                'asignacion_vigente' => $asignacionVigente,
                'menu_vigente' => $menuVigente,
                'pantalla_id' => (int) $pantalla->id,
                'modulo_id' => (int) $modulo->id,
                'permiso_id' => $permiso === null ? null : (int) $permiso->id,
                'elemento_id' => $elemento === null ? null : (int) $elemento->id,
            ];
            $estado['requiere_cambios'] = $estado['requiere_cambios']
                || $pantalla->ruta !== $definicion['ruta']
                || ! (bool) $pantalla->habilitada
                || ! $permisoVigente
                || ! $asignacionVigente
                || ! $menuVigente;
        }

        if ($estado['errores'] !== []) {
            return $estado;
        }

        $estado['rol_id'] = (int) $rol->id;
        $estado['menu_id'] = (int) $menuId;
        $estado['empresa_id'] = (int) $empresasActivas->first();
        $estado['resumen_anterior'] = collect($estado['pantallas'])->map(static fn (array $pantalla): array => [
            'ruta' => $pantalla['ruta_actual'],
            'permiso_vigente' => $pantalla['permiso_vigente'],
            'asignacion_vigente' => $pantalla['asignacion_vigente'],
            'menu_vigente' => $pantalla['menu_vigente'],
        ])->all();

        return $estado;
    }

    /** @param array<string,mixed> $estado
     * @return array{ruta:string,sha256:string}
     */
    private function crearRespaldo(array $estado, array $pantallasObjetivo): array
    {
        $codigosPantalla = array_keys($pantallasObjetivo);
        $codigosPermiso = array_map(static fn (string $codigo): string => 'pantalla.'.$codigo.'.consultar', $codigosPantalla);
        $idsPantalla = collect($estado['pantallas'])->pluck('pantalla_id')->all();
        $idsPermiso = collect($estado['pantallas'])->pluck('permiso_id')->filter()->all();
        $contenido = json_encode([
            'base' => DB::connection()->getDatabaseName(),
            'creado_en' => now()->toIso8601String(),
            'pantallas' => DB::table('pantallas')->whereIn('codigo', $codigosPantalla)->get()->toArray(),
            'permisos' => DB::table('permisos')->whereIn('codigo', $codigosPermiso)->get()->toArray(),
            'roles_permisos' => DB::table('roles_permisos')->whereIn('permiso_id', $idsPermiso)->get()->toArray(),
            'elementos_menu' => DB::table('elementos_menu')->where('menu_id', $estado['menu_id'])
                ->where(function (Builder $consulta) use ($idsPantalla, $codigosPantalla): void {
                    $consulta->whereIn('pantalla_id', $idsPantalla)->orWhereIn('codigo', $codigosPantalla);
                })->get()->toArray(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $nombreArchivo = 'respaldo-rbac-organizacion-'.now()->format('Ymd-His').'-'.Str::lower(Str::random(8)).'.json';
        $rutaRespaldo = 'respaldos/'.$nombreArchivo;
        $disco = Storage::disk('local');

        if (! $disco->put($rutaRespaldo, $contenido)) {
            throw new \RuntimeException('No se pudo guardar el respaldo previo al cambio RBAC.');
        }

        $contenidoVerificado = $disco->get($rutaRespaldo);
        $hashContenido = hash('sha256', $contenido);
        if (! hash_equals($hashContenido, hash('sha256', $contenidoVerificado))) {
            throw new \RuntimeException('El respaldo RBAC no superó la verificación SHA-256.');
        }

        return ['ruta' => $rutaRespaldo, 'sha256' => $hashContenido];
    }

    /** @return array<string, array{modulo:string,nombre:string,ruta:string,icono:string}> */
    private function pantallasObjetivo(): array
    {
        if ($this->option('incluir-personal')) {
            return ['admin.staff' => self::PANTALLAS_OBJETIVO['admin.staff']];
        }

        return array_diff_key(self::PANTALLAS_OBJETIVO, ['admin.staff' => true]);
    }

    private function hayAdministradorVigente(int $rolId): bool
    {
        return DB::table('usuarios_roles as asignacion')
            ->join('usuarios', 'usuarios.id', '=', 'asignacion.usuario_id')
            ->where('asignacion.rol_id', $rolId)
            ->where('asignacion.activo', true)
            ->where('usuarios.activo', true)
            ->where(function (Builder $consulta): void {
                $consulta->whereNull('asignacion.vigente_hasta')->orWhere('asignacion.vigente_hasta', '>', now());
            })
            ->exists();
    }
}
