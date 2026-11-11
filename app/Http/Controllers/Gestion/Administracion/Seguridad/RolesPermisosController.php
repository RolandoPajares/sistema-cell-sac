<?php

namespace App\Http\Controllers\Gestion\Administracion\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cuenta\Perfil\ActualizarCamposPerfilRolRequest;
use App\Http\Requests\Cuenta\Perfil\CrearCampoPerfilRequest;
use App\Http\Requests\Gestion\Administracion\Seguridad\ActualizarPermisosPantallaRequest;
use App\Http\Requests\Gestion\Administracion\Seguridad\ActualizarPermisosRolRequest;
use App\Http\Requests\Gestion\Administracion\Seguridad\ActualizarRolRequest;
use App\Http\Requests\Gestion\Administracion\Seguridad\GuardarRolRequest;
use App\Models\Cuenta\PerfilCampo;
use App\Models\Seguridad\Modulo;
use App\Models\Seguridad\Rol;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Administracion\Seguridad\ServicioDelegacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase RolesPermisosController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class RolesPermisosController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(ServicioAutorizacion $autorizacion): View
    {
        abort_unless($autorizacion->allowsGlobal(request()->user(), 'gestion.seguridad.consultar'), 403);
        $ready = $autorizacion->schemaAvailable();
        $search = trim(request()->string('buscar')->toString());
        $status = request()->string('estado')->toString();
        $roles = $ready
            ? Rol::query()->with(['camposPerfil', 'permisos'])->withCount(['usuarios', 'permisos'])->when($search !== '', fn ($query) => $query->where(fn ($nested) => $nested->where('nombre', 'like', '%'.$search.'%')->orWhere('descripcion', 'like', '%'.$search.'%')->orWhere('codigo', 'like', '%'.$search.'%')))->when(in_array($status, ['activo', 'inactivo'], true), fn ($query) => $query->where('activo', $status === 'activo'))->orderBy('es_sistema', 'desc')->orderBy('nombre')->get()
            : collect();
        $permissionCount = $ready ? DB::table('permisos')->where('activo', true)->count() : 0;
        $profileFields = Schema::hasTable('perfil_campos')
            ? PerfilCampo::query()->with('roles')->orderBy('orden')->orderBy('etiqueta')->get()
            : collect();

        return view('gestion.administracion.seguridad.roles-permisos.listar', compact('ready', 'roles', 'permissionCount', 'search', 'status', 'profileFields'));
    }

    public function storeProfileField(CrearCampoPerfilRequest $request, RegistradorAuditoria $auditoria): RedirectResponse
    {
        $data = $request->validated();
        $field = DB::transaction(function () use ($data, $request, $auditoria): PerfilCampo {
            $field = PerfilCampo::query()->create([
                ...$data,
                'opciones' => $data['tipo'] === 'select' ? ($data['opciones'] ?? []) : null,
                'orden' => $data['orden'] ?? 0,
                'activo' => $data['activo'] ?? true,
            ]);

            $auditoria->record($request->user()->getKey(), 'crear_campo_perfil', 'perfil_campos', $field->getKey(), null, $field->only(['codigo', 'tipo']), $request->ip());

            return $field;
        });

        return back()->with('status', 'Campo adicional de perfil creado.');
    }

    public function updateRoleProfileFields(ActualizarCamposPerfilRolRequest $request, Rol $role, RegistradorAuditoria $auditoria): RedirectResponse
    {
        $configs = collect($request->validated('campos', []))->keyBy(fn (array $config): int => (int) $config['id']);
        $fields = PerfilCampo::query()->whereIn('id', $configs->keys())->get();

        DB::transaction(function () use ($role, $fields, $configs, $request, $auditoria): void {
            DB::table('roles_perfil_campos')->where('rol_id', $role->getKey())->delete();
            foreach ($fields as $field) {
                $config = $configs->get($field->getKey(), []);
                $visible = (bool) ($config['visible'] ?? false);
                DB::table('roles_perfil_campos')->insert([
                    'rol_id' => $role->getKey(),
                    'perfil_campo_id' => $field->getKey(),
                    'visible' => $visible,
                    'editable' => $visible && (bool) ($config['editable'] ?? false),
                    'requerido' => $visible && (bool) ($config['requerido'] ?? false),
                    'creado_en' => now(),
                    'actualizado_en' => now(),
                ]);
            }

            $auditoria->record($request->user()->getKey(), 'configurar_campos_perfil_rol', 'roles', $role->getKey(), null, ['campos' => $fields->modelKeys()], $request->ip());
        });

        return back()->with('status', 'Campos de perfil del rol actualizados.');
    }

    /**
     * Ejecuta la operación matrix definida por la clase RolesPermisosController.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function matrix(ServicioAutorizacion $autorizacion): View
    {
        abort_unless($autorizacion->allowsGlobal(request()->user(), 'gestion.seguridad.consultar'), 403);
        $ready = $autorizacion->schemaAvailable();
        $roles = $ready ? Rol::query()->where('activo', true)->with(['permisos' => fn ($query) => $query->where('permisos.activo', true)->orderBy('modulo_id')->orderBy('accion')])->orderBy('nombre')->get() : collect();
        $selectedRoleId = (int) request()->integer('rol', (int) $roles->first()?->getKey());
        $selectedRole = $roles->firstWhere('id', $selectedRoleId) ?: $roles->first();
        $matrixModules = $ready ? $this->matrixModules() : collect();
        $matrixGroups = $ready ? $this->matrixGroups($matrixModules) : collect();
        $previewItems = $ready && $selectedRole ? $this->previewCatalog((int) $selectedRole->getKey()) : collect();
        $allPermissions = $matrixModules->flatMap(fn (array $module): array => array_merge(
            $module['permissions']->all(),
            $module['screens']->flatMap(fn (array $screen): array => $screen['permissions']->all())->all(),
        ));
        $actions = $allPermissions->pluck('accion')->filter()->unique()->sort()->values();
        $selectedPermissionIds = $selectedRole?->permisos->pluck('id')->map(fn ($id): int => (int) $id)->all() ?? [];
        $permissionCount = $ready ? DB::table('permisos')->where('activo', true)->count() : 0;
        $moduleCount = $ready ? DB::table('modulos')->where('habilitado', true)->count() : 0;
        $screenCount = $ready ? DB::table('pantallas')
            ->join('modulos', 'modulos.id', '=', 'pantallas.modulo_id')
            ->where('pantallas.habilitada', true)
            ->where('modulos.habilitado', true)
            ->count() : 0;
        $assignedPermissionCount = $ready ? DB::table('roles_permisos')
            ->join('roles', 'roles.id', '=', 'roles_permisos.rol_id')
            ->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
            ->where('roles.activo', true)
            ->where('permisos.activo', true)
            ->count() : 0;

        return view('gestion.administracion.seguridad.matriz-privilegios.listar', compact('ready', 'roles', 'selectedRole', 'matrixModules', 'matrixGroups', 'actions', 'selectedPermissionIds', 'permissionCount', 'moduleCount', 'screenCount', 'assignedPermissionCount', 'previewItems'));
    }

    /**
     * Obtiene los elementos de menú existentes para construir una vista previa real.
     *
     * La consulta no inventa rutas: solo devuelve pantallas y permisos registrados
     * en los menús asociados al rol seleccionado.
     *
     * @param  int  $roleId  Identificador del rol cuya navegación se previsualiza.
     * @return Collection<int, object> Elementos de menú catalogados.
     */
    private function previewCatalog(int $roleId): Collection
    {
        return DB::table('roles_menus')
            ->join('menus', 'menus.id', '=', 'roles_menus.menu_id')
            ->join('elementos_menu', 'elementos_menu.menu_id', '=', 'menus.id')
            ->leftJoin('pantallas', 'pantallas.id', '=', 'elementos_menu.pantalla_id')
            ->where('roles_menus.rol_id', $roleId)
            ->where('menus.contexto', 'sidebar')
            ->where('menus.habilitado', true)
            ->where('elementos_menu.habilitado', true)
            ->orderBy('elementos_menu.orden')
            ->get([
                'elementos_menu.id', 'elementos_menu.padre_id', 'elementos_menu.etiqueta',
                'elementos_menu.tipo', 'elementos_menu.permiso_id', 'pantallas.ruta',
            ]);
    }

    /**
     * Ejecuta la operación permissionCatalog definida por la clase RolesPermisosController.
     *
     * @return mixed Resultado de la operación.
     */
    private function matrixModules(): Collection
    {
        return Modulo::query()
            ->where('habilitado', true)
            ->with([
                'permisos' => fn ($query) => $query->where('activo', true)->whereNull('pantalla_id')->orderBy('accion')->orderBy('nombre'),
                'pantallas' => fn ($query) => $query->where('habilitada', true)->orderBy('nombre')->with([
                    'permisos' => fn ($permissionQuery) => $permissionQuery->where('activo', true)->orderBy('accion')->orderBy('nombre'),
                ]),
            ])
            ->orderBy('orden')->orderBy('nombre')->get()
            ->map(static fn (Modulo $module): array => [
                'id' => (int) $module->getKey(),
                'codigo' => (string) $module->codigo,
                'nombre' => (string) $module->nombre,
                'permissions' => $module->permisos->map(static fn ($permission): array => [
                    'id' => (int) $permission->getKey(), 'codigo' => (string) $permission->codigo,
                    'nombre' => (string) $permission->nombre, 'accion' => (string) $permission->accion,
                ])->values(),
                'screens' => $module->pantallas->map(static fn ($screen): array => [
                    'id' => (int) $screen->getKey(), 'codigo' => (string) $screen->codigo,
                    'nombre' => (string) $screen->nombre,
                    'permissions' => $screen->permisos->map(static fn ($permission): array => [
                        'id' => (int) $permission->getKey(), 'codigo' => (string) $permission->codigo,
                        'nombre' => (string) $permission->nombre, 'accion' => (string) $permission->accion,
                    ])->values(),
                ])->values(),
            ])->values();
    }

    /**
     * Agrupa los módulos según las secciones y pantallas del menú administrador registrado.
     *
     * @param  Collection<int, array<string, mixed>>  $modules
     * @return Collection<int, array{id: int, codigo: string, nombre: string, items: Collection<int, array<string, mixed>>}>
     */
    private function matrixGroups(Collection $modules): Collection
    {
        $menuId = DB::table('menus')
            ->where('codigo', 'administrador.sidebar')
            ->where('contexto', 'sidebar')
            ->where('habilitado', true)
            ->value('id');

        if ($menuId === null) {
            return collect();
        }

        $rootItems = DB::table('elementos_menu')
            ->where('menu_id', $menuId)
            ->whereNull('padre_id')
            ->where('habilitado', true)
            ->orderBy('orden')
            ->get(['id', 'codigo', 'etiqueta', 'tipo']);
        $moduleById = $modules->keyBy('id');
        $menuEntries = DB::table('elementos_menu as item')
            ->join('pantallas', 'pantallas.id', '=', 'item.pantalla_id')
            ->join('modulos', 'modulos.id', '=', 'pantallas.modulo_id')
            ->where('item.menu_id', $menuId)
            ->where('item.habilitado', true)
            ->where('pantallas.habilitada', true)
            ->where('modulos.habilitado', true)
            ->orderBy('item.orden')
            ->get([
                'item.id',
                'item.padre_id',
                'item.codigo',
                'item.etiqueta',
                'item.orden',
                'pantallas.id as pantalla_id',
                'pantallas.nombre as pantalla_nombre',
                'modulos.id as modulo_id',
            ])
            ->groupBy(static fn (object $item): int => (int) ($item->padre_id ?? $item->id));

        return $rootItems->map(static function (object $root) use ($menuEntries, $moduleById): array {
            $items = $menuEntries->get((int) $root->id, collect())
                ->map(static function (object $item) use ($moduleById): ?array {
                    $module = $moduleById->get((int) $item->modulo_id);
                    if ($module === null) {
                        return null;
                    }

                    $screen = $module['screens']->firstWhere('id', (int) $item->pantalla_id);
                    if ($screen === null) {
                        return null;
                    }

                    return [
                        'id' => (int) $item->id,
                        'codigo' => (string) $item->codigo,
                        'nombre' => (string) $item->etiqueta,
                        'screen_id' => (int) $screen['id'],
                        'screen_name' => (string) $screen['nombre'],
                        'module_id' => (int) $module['id'],
                        'module_code' => (string) $module['codigo'],
                        'module_name' => (string) $module['nombre'],
                        'permission_count' => $module['permissions']->count() + $screen['permissions']->count(),
                    ];
                })
                ->filter()
                ->values();

            return [
                'id' => (int) $root->id,
                'codigo' => (string) $root->codigo,
                'nombre' => (string) $root->etiqueta,
                'items' => $items,
            ];
        })->values();
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function store(GuardarRolRequest $request, RegistradorAuditoria $auditoria): RedirectResponse
    {
        abort_unless(Schema::hasTable('roles'), 503, 'La migración RBAC todavía no está aplicada.');
        $data = $request->validated();

        if ($data['alcance_permitido'] === 'empresa') {
            abort_unless($request->user()->can('gestion.seguridad.consultar'), 403);
        }

        $role = DB::transaction(function () use ($data, $request, $auditoria): Rol {
            $role = Rol::query()->create([
                ...$data,
                'es_sistema' => false,
                'activo' => true,
            ]);

            $auditoria->record($request->user()->getKey(), 'crear_rol', 'roles', $role->getKey(), null, ['codigo' => $role->codigo, 'alcance' => $role->alcance_permitido], $request->ip());

            return $role;
        });

        return back()->with('status', 'El rol '.$role->nombre.' fue creado sin privilegios iniciales.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @return mixed Resultado de la operación.
     */
    public function updatePermissions(
        ActualizarPermisosRolRequest $request,
        Rol $role,
        ServicioDelegacion $delegacion,
        ServicioAutorizacion $autorizacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse|JsonResponse {
        $permissionIds = collect($request->validated('permisos', []))->map(static fn (mixed $id): int => (int) $id)->unique()->values()->all();
        $scope = $request->validated('alcance');
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        abort_unless($delegacion->puedeDelegarPermisos($request->user(), $permissionIds, $scope), 403, 'No puedes delegar permisos o alcances superiores a los que posees.');

        if ($scope === 'empresa') {
            abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.consultar'), 403, 'Solo un administrador global puede conceder alcance empresarial.');
        }
        abort_unless($scope === $role->alcance_permitido, 422, 'El alcance del permiso debe coincidir con el alcance permitido del rol.');

        if ($role->alcance_permitido === 'propio' && DB::table('permisos')
            ->whereIn('id', $permissionIds)
            ->where('codigo', 'like', 'gestion.%')
            ->exists()) {
            abort(422, 'Los roles de alcance propio no pueden recibir permisos administrativos.');
        }

        $protectedPermissionIds = DB::table('permisos')
            ->join('modulos', 'modulos.id', '=', 'permisos.modulo_id')
            ->where('modulos.codigo', 'seguridad')
            ->where('permisos.activo', true)
            ->pluck('permisos.id')
            ->map(static fn (mixed $id): int => (int) $id);
        $currentProtectedPermissionIds = DB::table('roles_permisos')
            ->where('rol_id', $role->getKey())
            ->whereIn('permiso_id', $protectedPermissionIds)
            ->pluck('permiso_id')
            ->map(static fn (mixed $id): int => (int) $id);
        $submittedProtectedPermissionIds = collect($permissionIds)->intersect($protectedPermissionIds);
        abort_unless($submittedProtectedPermissionIds->diff($currentProtectedPermissionIds)->isEmpty(), 422, 'El módulo Seguridad y Delegación está protegido y no se puede modificar desde esta matriz.');
        $permissionIds = collect($permissionIds)
            ->diff($protectedPermissionIds)
            ->merge($currentProtectedPermissionIds)
            ->unique()
            ->values()
            ->all();

        abort_if(
            $this->esRolSeguridadGlobal($role) && $delegacion->esUltimoAdministradorGlobal($request->user()),
            422,
            'No se puede modificar el último acceso administrativo global.'
        );

        DB::transaction(function () use ($role, $permissionIds, $scope, $request, $auditoria): void {
            $role = Rol::query()->whereKey($role->getKey())->lockForUpdate()->firstOrFail();
            $before = $role->permisos()->get()->map(fn ($permission): array => [
                'codigo' => $permission->codigo,
                'alcance' => $permission->pivot->alcance,
            ])->values()->all();
            DB::table('roles_permisos')->where('rol_id', $role->getKey())->delete();

            foreach ($permissionIds as $permissionId) {
                DB::table('roles_permisos')->insert([
                    'rol_id' => $role->getKey(),
                    'permiso_id' => $permissionId,
                    'alcance' => $scope,
                    'creado_en' => now(),
                ]);
            }

            $after = DB::table('roles_permisos')->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
                ->where('roles_permisos.rol_id', $role->getKey())
                ->get(['permisos.codigo', 'roles_permisos.alcance'])->map(static fn ($row): array => ['codigo' => $row->codigo, 'alcance' => $row->alcance])->all();
            $auditoria->record($request->user()->getKey(), 'actualizar_permisos_rol', 'roles', $role->getKey(), ['permisos' => $before], ['permisos' => $after], $request->ip());
        });

        $message = 'Los privilegios del rol fueron actualizados.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('status', $message);
    }

    /**
     * Actualiza exclusivamente los permisos de una pantalla y alcance.
     *
     * @return JsonResponse Resultado persistido para el modal de la matriz.
     */
    public function updateScreenPermissions(
        ActualizarPermisosPantallaRequest $request,
        Rol $role,
        ServicioDelegacion $delegacion,
        ServicioAutorizacion $autorizacion,
        RegistradorAuditoria $auditoria,
    ): JsonResponse {
        $data = $request->validated();
        $permissionIds = collect($data['permisos'] ?? [])->map(static fn (mixed $id): int => (int) $id)->unique()->values()->all();
        $scope = (string) $data['alcance'];
        $screen = DB::table('pantallas')
            ->join('modulos', 'modulos.id', '=', 'pantallas.modulo_id')
            ->where('pantallas.id', $data['pantalla_id'])
            ->where('pantallas.modulo_id', $data['modulo_id'])
            ->where('pantallas.habilitada', true)
            ->where('modulos.habilitado', true)
            ->first(['pantallas.id', 'pantallas.modulo_id', 'modulos.codigo as modulo_codigo']);

        abort_unless($screen !== null, 422, 'La pantalla no pertenece al módulo seleccionado.');
        abort_unless($scope === $role->alcance_permitido, 422, 'El alcance del permiso debe coincidir con el alcance permitido del rol.');
        abort_unless($delegacion->puedeDelegarPermisos($request->user(), $permissionIds, $scope), 403, 'No puedes delegar permisos o alcances superiores a los que posees.');
        abort_unless($scope !== 'empresa' || $autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.consultar'), 403, 'Solo un administrador global puede conceder alcance empresarial.');

        if ($role->alcance_permitido === 'propio' && DB::table('permisos')->whereIn('id', $permissionIds)->where('codigo', 'like', 'gestion.%')->exists()) {
            abort(422, 'Los roles de alcance propio no pueden recibir permisos administrativos.');
        }

        $screenPermissionIds = DB::table('permisos')
            ->where('modulo_id', $screen->modulo_id)
            ->where('pantalla_id', $screen->id)
            ->where('activo', true)
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id);
        abort_unless(collect($permissionIds)->diff($screenPermissionIds)->isEmpty(), 422, 'Uno o más permisos no pertenecen a la pantalla seleccionada.');

        $protectedPermissionIds = DB::table('permisos')
            ->join('modulos', 'modulos.id', '=', 'permisos.modulo_id')
            ->where('modulos.codigo', 'seguridad')
            ->where('permisos.pantalla_id', $screen->id)
            ->where('permisos.activo', true)
            ->pluck('permisos.id')
            ->map(static fn (mixed $id): int => (int) $id);
        $currentProtectedPermissionIds = DB::table('roles_permisos')
            ->where('rol_id', $role->getKey())
            ->where('alcance', $scope)
            ->whereIn('permiso_id', $protectedPermissionIds)
            ->pluck('permiso_id')
            ->map(static fn (mixed $id): int => (int) $id);
        abort_unless(collect($permissionIds)->intersect($protectedPermissionIds)->diff($currentProtectedPermissionIds)->isEmpty(), 422, 'El módulo Seguridad y Delegación está protegido.');
        $permissionIds = collect($permissionIds)->diff($protectedPermissionIds)->merge($currentProtectedPermissionIds)->unique()->values()->all();
        abort_if($this->esRolSeguridadGlobal($role) && $delegacion->esUltimoAdministradorGlobal($request->user()), 422, 'No se puede modificar el último acceso administrativo global.');

        DB::transaction(function () use ($role, $screen, $screenPermissionIds, $permissionIds, $scope, $request, $auditoria): void {
            $lockedRole = Rol::query()->whereKey($role->getKey())->lockForUpdate()->firstOrFail();
            $before = DB::table('roles_permisos')->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
                ->where('roles_permisos.rol_id', $lockedRole->getKey())->where('roles_permisos.alcance', $scope)
                ->whereIn('roles_permisos.permiso_id', $screenPermissionIds)->get(['permisos.codigo', 'roles_permisos.alcance'])
                ->map(static fn (object $row): array => ['codigo' => $row->codigo, 'alcance' => $row->alcance])->values()->all();
            DB::table('roles_permisos')->where('rol_id', $lockedRole->getKey())->where('alcance', $scope)->whereIn('permiso_id', $screenPermissionIds)->delete();
            foreach ($permissionIds as $permissionId) {
                DB::table('roles_permisos')->insert(['rol_id' => $lockedRole->getKey(), 'permiso_id' => $permissionId, 'alcance' => $scope, 'creado_en' => now()]);
            }
            $after = DB::table('roles_permisos')->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
                ->where('roles_permisos.rol_id', $lockedRole->getKey())->where('roles_permisos.alcance', $scope)
                ->whereIn('roles_permisos.permiso_id', $screenPermissionIds)->get(['permisos.codigo', 'roles_permisos.alcance'])
                ->map(static fn (object $row): array => ['codigo' => $row->codigo, 'alcance' => $row->alcance])->values()->all();
            $auditoria->record($request->user()->getKey(), 'actualizar_permisos_pantalla', 'pantallas', $screen->id, ['permisos' => $before], ['permisos' => $after], $request->ip());
        });

        return response()->json([
            'message' => 'Los privilegios de la pantalla fueron actualizados.',
            'permission_ids' => DB::table('roles_permisos')->where('rol_id', $role->getKey())->pluck('permiso_id')->map(static fn (mixed $id): int => (int) $id)->values()->all(),
        ]);
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $role  Parámetro recibido por la operación.
     * @param  mixed  $delegacion  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function update(ActualizarRolRequest $request, Rol $role, ServicioDelegacion $delegacion, RegistradorAuditoria $auditoria): RedirectResponse
    {
        abort_if(
            $this->esRolSeguridadGlobal($role) && $delegacion->esUltimoAdministradorGlobal($request->user()),
            422,
            'No se puede editar el último acceso administrativo global.'
        );
        $data = $request->validated();
        abort_unless($data['alcance_permitido'] !== 'empresa' || $request->user()->can('gestion.seguridad.consultar'), 403, 'Solo un administrador global puede conceder alcance empresarial.');
        abort_unless($delegacion->puedeDelegarPermisos($request->user(), $role->permisos()->pluck('permisos.id')->all(), $data['alcance_permitido']), 403, 'El nuevo alcance excede tus privilegios delegables.');

        DB::transaction(function () use ($role, $data, $request, $auditoria): void {
            $locked = Rol::query()->lockForUpdate()->findOrFail($role->getKey());
            if ($locked->alcance_permitido !== $data['alcance_permitido']) {
                $hasGlobalAssignments = DB::table('usuarios_roles')
                    ->where('rol_id', $locked->getKey())->where('activo', true)->exists();
                $hasSiteAssignments = Schema::hasTable('usuarios_sedes_roles') && DB::table('usuarios_sedes_roles')
                    ->where('rol_id', $locked->getKey())->where('activo', true)->exists();
                abort_if($hasGlobalAssignments || $hasSiteAssignments, 409, 'No puedes cambiar el alcance de un rol con asignaciones activas; revócalas primero.');
            }
            $before = $locked->only(['nombre', 'descripcion', 'alcance_permitido']);
            $locked->update($data);
            $auditoria->record($request->user()->getKey(), 'actualizar_rol', 'roles', $locked->getKey(), $before, $locked->only(['nombre', 'descripcion', 'alcance_permitido']), $request->ip());
        });

        return back()->with('status', 'El rol fue actualizado.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $role  Parámetro recibido por la operación.
     * @param  mixed  $delegacion  Parámetro recibido por la operación.
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function updateStatus(Request $request, Rol $role, ServicioDelegacion $delegacion, RegistradorAuditoria $auditoria): RedirectResponse
    {
        abort_if(
            $this->esRolSeguridadGlobal($role) && $delegacion->esUltimoAdministradorGlobal($request->user()),
            422,
            'No se puede desactivar el último acceso administrativo global.'
        );
        abort_unless(app(ServicioAutorizacion::class)->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        $activo = $request->boolean('activo');

        DB::transaction(function () use ($role, $activo, $request, $delegacion, $auditoria): void {
            $locked = Rol::query()->lockForUpdate()->findOrFail($role->getKey());
            $hasGlobalAssignments = $locked->usuarios()->wherePivot('activo', true)->exists();
            $hasSiteAssignments = Schema::hasTable('usuarios_sedes_roles') && DB::table('usuarios_sedes_roles')
                ->where('rol_id', $locked->getKey())->where('activo', true)->exists();
            abort_if(! $activo && ($hasGlobalAssignments || $hasSiteAssignments), 409, 'Revoca primero las asignaciones activas de este rol.');
            abort_unless($delegacion->puedeDelegarPermisos($request->user(), $locked->permisos()->pluck('permisos.id')->all(), $locked->alcance_permitido), 403);
            $before = ['activo' => (bool) $locked->activo];
            $locked->update(['activo' => $activo]);
            $auditoria->record($request->user()->getKey(), $activo ? 'reactivar_rol' : 'desactivar_rol', 'roles', $locked->getKey(), $before, ['activo' => $activo], $request->ip());
        });

        return back()->with('status', $activo ? 'El rol fue reactivado.' : 'El rol fue desactivado.');
    }

    private function esRolSeguridadGlobal(Rol $role): bool
    {
        return $role->permisos()
            ->where('permisos.codigo', 'gestion.seguridad.actualizar')
            ->wherePivot('alcance', 'empresa')
            ->exists();
    }
}
