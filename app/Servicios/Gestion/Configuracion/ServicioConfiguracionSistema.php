<?php

namespace App\Servicios\Gestion\Configuracion;

use App\Models\ConfiguracionSistema;
use App\Models\Organizacion\Empresa;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ServicioConfiguracionSistema dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ServicioConfiguracionSistema
{
    /** @var array<string, list<string>> */
    private const CLAVES_CONFIGURABLES = [
        'general' => ['nombre_sistema', 'nombre_corto', 'idioma', 'zona_horaria', 'formato_fecha', 'formato_hora', 'separador_decimal', 'registros_por_pagina', 'moneda'],
        'parametros-comerciales' => ['vigencia_cotizacion', 'descuento_maximo', 'plazo_pedido'],
        'inventario' => ['stock_minimo_alerta', 'permitir_stock_negativo', 'requiere_aprobacion_ajuste'],
        'finanzas' => ['moneda', 'redondeo_monetario', 'tolerancia_arqueo', 'tolerancia_pago'],
    ];

    /** @return array<string, mixed> */
    public function consultarConfiguracion(string $grupo, int $empresaId, ?int $sedeId = null): array
    {
        if (! Schema::hasTable('configuraciones_sistema')) {
            return [];
        }

        return ConfiguracionSistema::query()
            ->where('empresa_id', $empresaId)
            ->where('grupo', $grupo)
            ->where(function ($consulta) use ($sedeId): void {
                $consulta->whereNull('sede_id');
                if ($sedeId !== null) {
                    $consulta->orWhere('sede_id', $sedeId);
                }
            })
            ->orderByRaw('sede_id IS NULL ASC')
            ->get(['sede_id', 'clave', 'valor_json'])
            ->unique('clave')
            ->mapWithKeys(static fn (ConfiguracionSistema $registro): array => [$registro->clave => $registro->valor_json['valor'] ?? null])
            ->all();
    }

    public function sedesConfigurables(int $empresaId): Collection
    {
        return DB::table('sedes')
            ->where('empresa_id', $empresaId)
            ->where('activa', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array{empresa_id:?int,sede_id:?int}  $ubicacion
     */
    public function guardarConfiguracion(string $grupo, string $alcance, ?int $sedeId, array $datos, User $usuario, array $ubicacion, ?string $ip): void
    {
        abort_unless(Schema::hasTable('configuraciones_sistema'), 503,
            'La persistencia de configuración requiere aplicar la migración aprobada.');
        abort_unless(isset(self::CLAVES_CONFIGURABLES[$grupo]), 422, 'El grupo de configuración no es válido.');
        abort_unless($ubicacion['empresa_id'] !== null, 409, 'No existe una empresa autorizada.');

        $sedeId = $alcance === 'sede' ? $sedeId : null;
        if ($sedeId !== null) {
            abort_unless(DB::table('sedes')->where('id', $sedeId)->where('empresa_id', $ubicacion['empresa_id'])->exists(), 422,
                'La sede no pertenece a la empresa autorizada.');
        }

        $permitidas = array_flip(self::CLAVES_CONFIGURABLES[$grupo]);
        $configuracion = array_intersect_key($datos, $permitidas);

        DB::transaction(function () use ($grupo, $sedeId, $configuracion, $usuario, $ubicacion, $ip): void {
            $anteriores = $this->consultarConfiguracion($grupo, (int) $ubicacion['empresa_id'], $sedeId);
            foreach ($configuracion as $clave => $valor) {
                ConfiguracionSistema::query()->updateOrCreate(
                    ['empresa_id' => $ubicacion['empresa_id'], 'sede_id' => $sedeId, 'grupo' => $grupo, 'clave' => $clave],
                    ['valor_json' => ['valor' => $valor], 'actualizado_por_usuario_id' => $usuario->getKey()],
                );
            }

            app(RegistradorAuditoria::class)->record(
                (int) $usuario->getKey(), 'configuracion_sistema.actualizada', 'configuraciones_sistema', null,
                $anteriores, $configuracion, $ip,
                scope: ['empresa_id' => (int) $ubicacion['empresa_id'], 'sede_id' => $sedeId],
            );
        }, 3);
    }

    /** @param array{empresa_id:?int,sede_id:?int} $ubicacion
     * @return array{empresa:?Empresa,sedes:Collection,usuariosVinculados:int,integracionesActivas:int,series:Collection,cambios:Collection}
     */
    public function obtenerDatosGenerales(User $usuario, array $ubicacion): array
    {
        $empresa = $ubicacion['empresa_id'] === null ? null : Empresa::query()->find($ubicacion['empresa_id']);
        $sedes = $empresa?->sedes()->where('activa', true)->get() ?? collect();
        $usuariosVinculados = $ubicacion['sede_id'] === null || ! Schema::hasTable('usuarios_sedes_roles')
            ? 0
            : DB::table('usuarios_sedes_roles')->join('usuarios', 'usuarios.id', '=', 'usuarios_sedes_roles.usuario_id')
                ->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')->where('roles.activo', true)
                ->where('usuarios_sedes_roles.sede_id', $ubicacion['sede_id'])->where('usuarios_sedes_roles.activo', true)
                ->where('usuarios.activo', true)->where(function ($consulta): void {
                    $consulta->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now());
                })->distinct('usuarios.id')->count('usuarios.id');
        $integracionesActivas = $empresa === null || ! Schema::hasTable('integraciones')
            ? 0
            : DB::table('integraciones')->where('empresa_id', $empresa->getKey())->where('activa', true)->count();
        $series = $ubicacion['empresa_id'] === null || $ubicacion['sede_id'] === null || ! Schema::hasTable('series_documentos')
            ? collect()
            : DB::table('series_documentos')->where('empresa_id', $ubicacion['empresa_id'])
                ->where('sede_id', $ubicacion['sede_id'])->where('activa', true)->orderBy('tipo_documento_id')->get();
        $cambios = $this->consultaAuditoria($usuario, $ubicacion['empresa_id'], $ubicacion['sede_id'])
            ->latest('creado_en')->limit(5)->get(['accion', 'entidad', 'creado_en']);

        return compact('empresa', 'sedes', 'usuariosVinculados', 'integracionesActivas', 'series', 'cambios');
    }

    /** @param array{empresa_id:?int,sede_id:?int} $ubicacion
     * @return array{integraciones:LengthAwarePaginator,eventos:Collection,totales:array{total:int,activas:int,inactivas:int,eventos:int}}
     */
    public function consultarIntegraciones(array $ubicacion, ?string $busqueda, ?string $estado): array
    {
        $ultimosEventos = DB::table('eventos_integracion')->select('integracion_id')
            ->selectRaw('MAX(iniciado_en) AS ultima_sincronizacion')->groupBy('integracion_id');
        $consulta = DB::table('integraciones')->leftJoinSub($ultimosEventos, 'ultimos_eventos', function ($union): void {
            $union->on('ultimos_eventos.integracion_id', '=', 'integraciones.id');
        })->select('integraciones.*', 'ultimos_eventos.ultima_sincronizacion')->whereRaw('1 = 0');
        $eventos = collect();
        $total = 0;
        $activas = 0;
        if ($ubicacion['empresa_id'] !== null && Schema::hasTable('integraciones')) {
            $consulta = DB::table('integraciones')->leftJoinSub($ultimosEventos, 'ultimos_eventos', function ($union): void {
                $union->on('ultimos_eventos.integracion_id', '=', 'integraciones.id');
            })->select('integraciones.*', 'ultimos_eventos.ultima_sincronizacion')
                ->where('integraciones.empresa_id', $ubicacion['empresa_id'])
                ->when($busqueda !== null && trim($busqueda) !== '', function ($consulta) use ($busqueda): void {
                    $termino = '%'.trim((string) $busqueda).'%';
                    $consulta->where(function ($subconsulta) use ($termino): void {
                        $subconsulta->where('nombre', 'like', $termino)->orWhere('proveedor', 'like', $termino)->orWhere('codigo', 'like', $termino);
                    });
                })
                ->when($estado !== null && $estado !== '', fn ($consulta) => $consulta->where('activa', $estado === 'Activa'));
            $total = (clone $consulta)->count();
            $activas = (clone $consulta)->where('activa', true)->count();
            if (Schema::hasTable('eventos_integracion')) {
                $eventos = DB::table('eventos_integracion')->join('integraciones', 'integraciones.id', '=', 'eventos_integracion.integracion_id')
                    ->where('integraciones.empresa_id', $ubicacion['empresa_id'])
                    ->orderByDesc('eventos_integracion.iniciado_en')->limit(10)
                    ->get(['eventos_integracion.id', 'integraciones.nombre', 'eventos_integracion.estado', 'eventos_integracion.direccion', 'eventos_integracion.iniciado_en']);
            }
        }
        $integraciones = (clone $consulta)->orderBy('nombre')->paginate(12)->withQueryString();

        return [
            'integraciones' => $integraciones,
            'eventos' => $eventos,
            'totales' => ['total' => $total, 'activas' => $activas, 'inactivas' => $total - $activas, 'eventos' => $eventos->count()],
        ];
    }

    /** @param array{empresa_id:?int,sede_id:?int} $ubicacion
     * @return array{sesiones:Collection,eventos:Collection,controles:array<int,array<int,mixed>>}
     */
    public function obtenerDatosSeguridad(User $usuario, array $ubicacion): array
    {
        $sesiones = Schema::hasTable('sesiones_usuarios')
            ? DB::table('sesiones_usuarios')->where('usuario_id', $usuario->getKey())->orderByDesc('ultimo_acceso')->limit(20)->get()
            : collect();
        $eventos = $this->consultaAuditoria($usuario, $ubicacion['empresa_id'], $ubicacion['sede_id'])
            ->where(function ($consulta): void {
                $consulta->where('modulo', 'seguridad')->orWhere('entidad', 'like', '%sesion%')->orWhere('accion', 'like', '%acceso%');
            })->latest('creado_en')->limit(10)->get(['accion', 'entidad', 'resultado', 'creado_en']);
        $controles = [
            ['Políticas de contraseña', 'La tabla usuarios almacena el hash y el bloqueo vigente; no existe una tabla de políticas.', 'bi-key', [['Política', 'Sin configuración persistida'], ['Usuario actual bloqueado', $usuario->bloqueado_hasta?->toDateTimeString() ?? 'No']], 'politica'],
            ['Autenticación en dos pasos', 'No existe configuración persistente de segundo factor en el esquema.', 'bi-lock', [['Estado', 'Sin configuración persistida'], ['Adopción', 'No disponible']], 'dos-pasos'],
            ['Sesiones y expiración', 'Sesiones registradas para el usuario autenticado.', 'bi-clock', [['Sesiones registradas', (string) $sesiones->count()], ['Activas registradas', (string) $sesiones->whereNull('revocada_en')->count()]], 'sesiones'],
            ['Control de IP y dispositivos', 'La tabla de sesiones conserva IP y agente del usuario autenticado.', 'bi-display', [['Sesiones con IP', (string) $sesiones->whereNotNull('direccion_ip')->count()], ['Dispositivos', (string) $sesiones->whereNotNull('agente_usuario')->count()]], 'dispositivos'],
            ['Permisos sensibles', 'Los permisos se administran desde el catálogo RBAC existente.', 'bi-people', [['Rol actual', (string) $usuario->rol], ['Permisos administrativos', 'Consultar en RBAC']], 'permisos'],
            ['Registros de seguridad', 'Eventos de auditoría filtrados por empresa y sede autorizadas.', 'bi-database', [['Eventos recientes', (string) $eventos->count()], ['Retención', 'No configurada en el esquema']], 'registros'],
        ];

        return compact('sesiones', 'eventos', 'controles');
    }

    /**
     * Ejecuta la operación consultaAuditoria definida por la clase ServicioConfiguracionSistema.
     *
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $empresaId  Parámetro recibido por la operación.
     * @param  mixed  $sedeId  Parámetro recibido por la operación.
     * @return Builder Resultado de la operación.
     */
    private function consultaAuditoria(User $usuario, ?int $empresaId, ?int $sedeId): Builder
    {
        return DB::table('registros_auditoria')->where(function ($consulta) use ($usuario, $empresaId): void {
            $consulta->where('usuario_id', $usuario->getKey());
            if ($empresaId !== null) {
                $consulta->orWhere('empresa_id', $empresaId);
            }
        })->when($sedeId !== null, function ($consulta) use ($sedeId): void {
            $consulta->where(function ($alcance) use ($sedeId): void {
                $alcance->whereNull('sede_id')->orWhere('sede_id', $sedeId);
            });
        });
    }
}
