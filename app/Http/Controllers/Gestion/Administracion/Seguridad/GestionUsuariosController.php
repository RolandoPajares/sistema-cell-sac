<?php

namespace App\Http\Controllers\Gestion\Administracion\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Administracion\Seguridad\ActualizarUsuarioRequest;
use App\Http\Requests\Gestion\Administracion\Seguridad\CambiarEstadoUsuarioRequest;
use App\Http\Requests\Gestion\Administracion\Seguridad\GuardarUsuarioRequest;
use App\Models\Clientes\Cliente;
use App\Models\Organizacion\Sede;
use App\Models\Seguridad\Rol;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Administracion\Seguridad\ServicioDelegacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Define la responsabilidad de la clase GestionUsuariosController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class GestionUsuariosController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(Request $request): View
    {
        abort_unless(app(ServicioAutorizacion::class)->allowsGlobal($request->user(), 'gestion.seguridad.consultar'), 403);
        $filters = $this->filtros($request);
        $hasAccountStatus = Schema::hasColumn('usuarios', 'activo');
        $users = $this->consultaUsuarios($filters)
            ->with(['customer:id,usuario_id,documento,razon_social,tipo,ciudad,activo', 'rolesVigentes', 'rolesPorSedeVigentes'])
            ->orderByDesc('creado_en')
            ->orderByDesc('id')
            ->paginate($filters['por_pagina'])
            ->withQueryString();
        $codigosRoles = $this->codigosRoles();
        $totalUsers = User::query()->count();
        $roleLabels = $this->roleLabels($codigosRoles);
        $distribucionRoles = $this->distribucionRoles();
        $recentUsers = User::query()
            ->with(['customer:id,usuario_id,ciudad', 'rolesVigentes', 'rolesPorSedeVigentes'])
            ->latest('creado_en')
            ->latest('id')
            ->limit(5)
            ->get(['id', 'nombre', 'correo', 'rol', 'creado_en']);

        return view('gestion.administracion.seguridad.usuarios.listar', [
            'users' => $users,
            'roles' => $codigosRoles,
            'roleLabels' => $roleLabels,
            'roleScopes' => $this->roleScopes($codigosRoles),
            'roleDistribution' => $this->prepararDistribucionRoles($distribucionRoles),
            'recentUsers' => $recentUsers,
            'filters' => $filters,
            'hasFilters' => $this->hasFilters($filters),
            'userPresentation' => $this->prepararPresentacionUsuarios($users->getCollection()),
            'recentUserPresentation' => $this->prepararPresentacionUsuarios($recentUsers),
            'exportUrls' => [
                'pdf' => route('management.users.export', array_merge($request->query(), ['format' => 'pdf'])),
                'csv' => route('management.users.export', array_merge($request->query(), ['format' => 'csv'])),
                'excel' => route('management.users.export', array_merge($request->query(), ['format' => 'excel'])),
            ],
            'paginationQuery' => $request->except(['page', 'por_pagina']),
            'pageSizes' => [10, 20, 50],
            'cities' => Cliente::query()->whereNotNull('ciudad')->where('ciudad', '<>', '')->distinct()->orderBy('ciudad')->pluck('ciudad'),
            'sites' => Sede::query()->where('activa', true)->orderBy('nombre')->get(['id', 'nombre', 'empresa_id']),
            'totalUsers' => $totalUsers,
            'activeUsers' => $hasAccountStatus ? User::query()->where('activo', true)->count() : null,
            'inactiveUsers' => $hasAccountStatus ? User::query()->where('activo', false)->count() : null,
            'accountStatusAvailable' => $hasAccountStatus,
            'rolesConfigured' => count($codigosRoles),
            'currentMonthUsers' => User::query()->whereBetween('creado_en', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ]);
    }

    /**
     * Genera la salida exportable de la información disponible.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $format  Parámetro recibido por la operación.
     * @param  mixed  $exporter  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return Response|StreamedResponse Resultado de la operación.
     */
    public function export(Request $request, string $format, ExportadorTabular $exporter, RegistradorAuditoria $audit): Response|StreamedResponse
    {
        abort_unless(app(ServicioAutorizacion::class)->allowsGlobal($request->user(), 'gestion.seguridad.consultar'), 403);

        abort_unless(in_array($format, ['csv', 'excel', 'pdf'], true), 422, 'Formato de exportación no permitido.');
        $filters = $this->filtros($request);
        $users = $this->consultaUsuarios($filters)
            ->with(['customer:id,usuario_id,documento,razon_social,tipo,ciudad', 'rolesVigentes', 'rolesPorSedeVigentes'])
            ->orderByDesc('creado_en')->orderByDesc('id')->limit(5000)->get();
        $rows = $users->map(function (User $user): array {
            $roles = $user->rolesVigentes->concat($user->rolesPorSedeVigentes)->pluck('nombre')->unique()->join(', ');

            return [
                $user->nombre,
                $user->correo,
                $roles !== '' ? $roles : 'Sin rol vigente',
                $user->customer?->tipo ?? 'Administrativo',
                $user->customer?->ciudad ?? '',
                Schema::hasColumn('usuarios', 'activo') ? ($user->activo ? 'Activo' : 'Inactivo') : 'Sin control de estado',
                $user->creado_en?->format('Y-m-d H:i:s'),
            ];
        })->all();
        $response = $exporter->download($format, 'usuarios-'.now()->format('Ymd'),
            ['Nombre', 'Correo', 'Roles', 'Tipo', 'Ciudad', 'Estado', 'Registrado'],
            $rows,
            [
                'title' => 'REPORTE DE USUARIOS',
                'subtitle' => 'Cuentas reales consultadas desde MariaDB.',
                'module' => 'Seguridad y Delegación',
                'report_name' => 'Usuarios',
                'filters' => $filters,
                'generated_by' => (string) $request->user()->nombre,
                'role' => 'Usuario autorizado',
                'scope' => 'Alcance administrativo y filtros RBAC vigentes.',
                'indicators' => [['label' => 'Usuarios incluidos', 'value' => count($rows)]],
            ]);
        $audit->record($request->user()->getKey(), 'exportar', 'usuarios', null, null, ['formato' => $format, 'filtros' => $filters, 'cantidad' => count($rows)], $request->ip());

        return $response;
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $delegacion  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarUsuarioRequest $request, ServicioDelegacion $delegacion, RegistradorAuditoria $audit): RedirectResponse
    {
        $data = $request->validated();
        abort_unless(app(ServicioAutorizacion::class)->isSeeded(), 503, 'El control de acceso aún no está inicializado.');
        $role = Rol::query()->where('codigo', $data['rol'])->where('activo', true)->first();
        abort_if($role === null, 422, 'El rol seleccionado ya no está disponible.');
        abort_unless($delegacion->puedeAsignarRol($request->user(), $role), 403, 'No puedes crear una cuenta con privilegios superiores a los tuyos.');

        $user = DB::transaction(function () use ($data, $role, $request, $audit): User {
            $attributes = [
                'nombre' => $data['nombre'],
                'correo' => mb_strtolower($data['correo']),
                'password' => $data['password'],
                'rol' => $data['rol'],
            ];
            $attributes['activo'] = true;

            $user = User::query()->create($attributes);

            if ($role !== null) {
                if ($role->alcance_permitido === 'sede') {
                    $siteId = app(ServicioAutorizacion::class)->operationSite($request->user())['sede_id'];
                    if ($siteId === null) {
                        abort(422, 'No existe una sede interna autorizada para asignar este rol.');
                    }

                    $site = Sede::query()->whereKey($siteId)->where('activa', true)->firstOrFail();
                    abort_unless(app(ServicioAutorizacion::class)->allowsRecord($request->user(), 'gestion.seguridad.asignar', (int) $site->empresa_id, $siteId), 403);

                    DB::table('usuarios_sedes_roles')->insert([
                        'usuario_id' => $user->getKey(),
                        'sede_id' => $siteId,
                        'rol_id' => $role->getKey(),
                        'asignado_por_usuario_id' => $request->user()->getKey(),
                        'asignado_en' => now(),
                        'activo' => true,
                    ]);
                } else {
                    DB::table('usuarios_roles')->insert([
                        'usuario_id' => $user->getKey(),
                        'rol_id' => $role->getKey(),
                        'asignado_por_usuario_id' => $request->user()->getKey(),
                        'asignado_en' => now(),
                        'activo' => true,
                    ]);
                }

            }

            if (str_starts_with($data['rol'], 'cliente_')) {
                Cliente::query()->create([
                    'usuario_id' => $user->getKey(),
                    'tipo' => $data['rol'] === 'cliente_mayorista' ? 'mayorista' : 'minorista',
                    'tipo_documento' => $data['tipo_documento'],
                    'documento' => $data['documento'],
                    'razon_social' => $data['razon_social'] ?? null,
                    'nombre_contacto' => $data['nombre'],
                    'correo' => $user->correo,
                    'telefono' => $data['telefono'] ?? null,
                    'ciudad' => $data['ciudad'] ?? null,
                    'activo' => true,
                    'creado_en' => now(),
                    ...app(ServicioAutorizacion::class)->operationScopeAttributes($request->user(), 'clientes'),
                ]);
            }

            $audit->record($request->user()->getKey(), 'user.created', 'usuarios', $user->getKey(), null, ['rol' => $user->rol], $request->ip());

            return $user;
        });

        return back()->with('status', 'Cuenta creada. La contraseña no se volverá a mostrar.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @param  mixed  $delegacion  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function updateStatus(CambiarEstadoUsuarioRequest $request, User $user, ServicioDelegacion $delegacion, RegistradorAuditoria $audit): RedirectResponse
    {
        abort_unless(Schema::hasColumn('usuarios', 'activo'), 503, 'El bloqueo de cuentas requiere el esquema de seguridad de usuarios.');
        $active = $request->boolean('activo');
        abort_if(! $active && $user->is($request->user()), 422, 'No puedes desactivar tu propia cuenta desde esta sesión.');
        abort_unless($delegacion->puedeAdministrarUsuario($request->user(), $user), 403, 'No puedes administrar una cuenta con privilegios superiores a los tuyos.');

        DB::transaction(function () use ($user, $active, $request, $audit, $delegacion): void {
            $user = User::query()->lockForUpdate()->findOrFail($user->getKey());

            if (! $active && $user->can('gestion.seguridad.consultar')) {
                $delegacion->bloquearAdministradoresGlobales();
                $user->refresh();
                abort_if($delegacion->esUltimoAdministradorGlobal($user), 422, 'No se puede desactivar el último administrador global autorizado.');
            }

            $before = ['activo' => (bool) $user->activo, 'bloqueado_hasta' => $user->bloqueado_hasta?->toIso8601String()];
            $user->forceFill(['activo' => $active, 'bloqueado_hasta' => null])->save();
            if (! $active && Schema::hasTable('sesiones_usuarios')) {
                DB::table('sesiones_usuarios')->where('usuario_id', $user->getKey())->whereNull('revocada_en')
                    ->update(['revocada_en' => now(), 'actualizado_en' => now()]);
            }
            $audit->record($request->user()->getKey(), $active ? 'reactivar_usuario' : 'desactivar_usuario', 'usuarios', $user->getKey(), $before, ['activo' => $active], $request->ip());
        });

        return back()->with('status', $active ? 'La cuenta fue reactivada.' : 'La cuenta fue desactivada y sus sesiones revocadas.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @param  mixed  $delegacion  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarUsuarioRequest $request, User $user, ServicioDelegacion $delegacion, RegistradorAuditoria $audit): RedirectResponse
    {
        abort_unless($delegacion->puedeAdministrarUsuario($request->user(), $user), 403, 'No puedes editar una cuenta con privilegios superiores a los tuyos.');
        $data = $request->validated();

        DB::transaction(function () use ($data, $request, $user, $audit): void {
            $locked = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $before = ['nombre' => $locked->nombre, 'correo' => $locked->correo];
            $attributes = ['nombre' => $data['nombre'], 'correo' => mb_strtolower($data['correo'])];
            if (filled($data['password'] ?? null)) {
                $attributes['password'] = $data['password'];
            }
            $locked->forceFill($attributes)->save();
            $after = ['nombre' => $locked->nombre, 'correo' => $locked->correo, 'password_actualizada' => filled($data['password'] ?? null)];
            $audit->record($request->user()->getKey(), 'user.updated', 'usuarios', $locked->getKey(), $before, $after, $request->ip());
        }, 3);

        return back()->with('status', 'La cuenta fue actualizada correctamente.');
    }

    /** @param array{buscar:?string,rol:?string,estado:?string,ciudad:?string,fecha:?string,por_pagina:int} $filters
     * @return Builder<User>
     */
    private function consultaUsuarios(array $filters): Builder
    {
        return User::query()
            ->when($filters['buscar'], function (Builder $query, string $search): void {
                $query->where(fn (Builder $searchQuery): Builder => $searchQuery
                    ->where('nombre', 'like', '%'.$search.'%')
                    ->orWhere('correo', 'like', '%'.$search.'%'));
            })
            ->when($filters['rol'], fn (Builder $query, string $role): Builder => $query->where(fn (Builder $roles): Builder => $roles
                ->whereHas('rolesVigentes', fn (Builder $assigned): Builder => $assigned->where('roles.codigo', $role))
                ->orWhereHas('rolesPorSedeVigentes', fn (Builder $assigned): Builder => $assigned->where('roles.codigo', $role))))
            ->when($filters['ciudad'], fn (Builder $query, string $city): Builder => $query->whereHas('customer', fn (Builder $customerQuery): Builder => $customerQuery->where('ciudad', $city)))
            ->when($filters['fecha'] === 'hoy', fn (Builder $query): Builder => $query->whereDate('creado_en', now()->toDateString()))
            ->when($filters['fecha'] === 'ultimos_30', fn (Builder $query): Builder => $query->where('creado_en', '>=', now()->subDays(30)))
            ->when($filters['fecha'] === 'ultimos_90', fn (Builder $query): Builder => $query->where('creado_en', '>=', now()->subDays(90)))
            ->when($filters['estado'] !== null && Schema::hasColumn('usuarios', 'activo'), fn (Builder $query): Builder => $query->where('activo', $filters['estado'] === 'activo'));
    }

    /**
     * Obtiene la distribución de usuarios por rol.
     *
     * @return Collection Resultado de la operación.
     */
    private function distribucionRoles(): Collection
    {
        $global = DB::table('usuarios_roles as asignacion')
            ->join('roles', 'roles.id', '=', 'asignacion.rol_id')
            ->where('roles.activo', true)
            ->whereIn('roles.alcance_permitido', ['empresa', 'propio'])
            ->where('asignacion.activo', true)
            ->where(fn ($query) => $query->whereNull('asignacion.vigente_hasta')->orWhere('asignacion.vigente_hasta', '>', now()))
            ->select('roles.codigo as rol', 'asignacion.usuario_id');

        $sites = DB::table('usuarios_sedes_roles as asignacion')
            ->join('roles', 'roles.id', '=', 'asignacion.rol_id')
            ->join('sedes', 'sedes.id', '=', 'asignacion.sede_id')
            ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
            ->where('roles.activo', true)
            ->where('roles.alcance_permitido', 'sede')
            ->where('sedes.activa', true)
            ->where('empresas.activo', true)
            ->where('asignacion.activo', true)
            ->where(fn ($query) => $query->whereNull('asignacion.vigente_hasta')->orWhere('asignacion.vigente_hasta', '>', now()))
            ->select('roles.codigo as rol', 'asignacion.usuario_id');

        return DB::query()->fromSub($global->union($sites), 'asignaciones_vigentes')
            ->select('rol')->selectRaw('COUNT(*) as total')->groupBy('rol')->orderByDesc('total')->get();
    }

    /**
     * Prepara los datos visuales de cada cuenta sin ejecutar transformaciones en Blade.
     *
     * @param  Collection<int, User>  $accounts
     * @return array<int, array{initials:string, roles:string, avatarIndex:int, roleIndex:int}>
     */
    private function prepararPresentacionUsuarios(Collection $accounts): array
    {
        $presentation = [];

        foreach ($accounts->values() as $index => $account) {
            $presentation[$account->getKey()] = [
                'initials' => $this->inicialesPara($account->nombre),
                'roles' => $this->formatRoles($account),
                'avatarIndex' => $index % 5,
                'roleIndex' => $index % 7,
            ];
        }

        return $presentation;
    }

    /**
     * Prepara la distribución de roles para que la vista solo la represente.
     *
     * @param  Collection<int, object>  $distribution
     * @return array<int, object{rol:string, total:int}>
     */
    private function prepararDistribucionRoles(Collection $distribution): array
    {
        return $distribution->values()->map(function (object $item): object {
            $total = (int) $item->total;

            return (object) [
                'rol' => (string) $item->rol,
                'total' => $total,
            ];
        })->all();
    }

    /** @param array{buscar:?string,rol:?string,estado:?string,ciudad:?string,fecha:?string,por_pagina:int} $filters */
    private function hasFilters(array $filters): bool
    {
        return filled($filters['buscar'])
            || filled($filters['rol'])
            || filled($filters['estado'])
            || filled($filters['ciudad'])
            || filled($filters['fecha']);
    }

    private function formatRoles(User $account): string
    {
        return $account->rolesVigentes
            ->concat($account->rolesPorSedeVigentes)
            ->pluck('nombre')
            ->unique()
            ->join(', ') ?: 'Sin rol vigente';
    }

    private function inicialesPara(?string $name): string
    {
        return mb_strtoupper(mb_substr(trim((string) $name), 0, 1));
    }

    /** @return array{buscar:?string,rol:?string,estado:?string,ciudad:?string,fecha:?string,por_pagina:int} */
    private function filtros(Request $request): array
    {
        $search = trim($request->string('buscar')->toString());
        $role = trim($request->string('rol')->toString());
        $status = trim($request->string('estado')->toString());
        $city = trim($request->string('ciudad')->toString());
        $date = trim($request->string('fecha')->toString());
        $perPage = $request->integer('por_pagina', 10);

        return [
            'buscar' => $search !== '' ? $search : null,
            'rol' => in_array($role, $this->codigosRoles(), true) ? $role : null,
            'estado' => in_array($status, ['activo', 'inactivo'], true) ? $status : null,
            'ciudad' => $city !== '' ? $city : null,
            'fecha' => in_array($date, ['hoy', 'ultimos_30', 'ultimos_90'], true) ? $date : null,
            'por_pagina' => in_array($perPage, [10, 20, 50], true) ? $perPage : 10,
        ];
    }

    /** @return array<int,string> */
    private function codigosRoles(): array
    {
        if (Schema::hasTable('roles')) {
            $configuredRoles = DB::table('roles')->where('activo', true)->orderBy('codigo')->pluck('codigo')->all();

            return $configuredRoles;
        }

        return [];
    }

    /** @param array<int,string> $codigosRoles
     * @return array<string,string>
     */
    private function roleLabels(array $codigosRoles): array
    {
        return Schema::hasTable('roles')
            ? DB::table('roles')->whereIn('codigo', $codigosRoles)->pluck('nombre', 'codigo')->all()
            : [];
    }

    /** @param array<int,string> $codigosRoles
     * @return array<string,string>
     */
    private function roleScopes(array $codigosRoles): array
    {
        return Schema::hasTable('roles')
            ? DB::table('roles')->whereIn('codigo', $codigosRoles)->pluck('alcance_permitido', 'codigo')->all()
            : [];
    }
}
