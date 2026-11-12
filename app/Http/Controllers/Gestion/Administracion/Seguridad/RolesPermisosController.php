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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($autorizacion->allowsGlobal(request()->user(), 'gestion.seguridad.consultar'), 403);
        // Guarda en $ready el resultado necesario para continuar esta operacion.
        $ready = $autorizacion->schemaAvailable();
        // Obtiene en $search la informacion de la solicitud o del usuario actual.
        $search = trim(request()->string('buscar')->toString());
        // Obtiene en $status la informacion de la solicitud o del usuario actual.
        $status = request()->string('estado')->toString();
        // Guarda en $roles el resultado necesario para continuar esta operacion.
        $roles = $ready
            ? Rol::query()->with(['camposPerfil', 'permisos'])->withCount(['usuarios', 'permisos'])->when($search !== '', fn ($query) => $query->where(fn ($nested) => $nested->where('nombre', 'like', '%'.$search.'%')->orWhere('descripcion', 'like', '%'.$search.'%')->orWhere('codigo', 'like', '%'.$search.'%')))->when(in_array($status, ['activo', 'inactivo'], true), fn ($query) => $query->where('activo', $status === 'activo'))->orderBy('es_sistema', 'desc')->orderBy('nombre')->get()
            : collect();
        // Guarda en $permissionCount el resultado necesario para continuar esta operacion.
        $permissionCount = $ready ? DB::table('permisos')->where('activo', true)->count() : 0;
        // Guarda en $profileFields el resultado necesario para continuar esta operacion.
        $profileFields = Schema::hasTable('perfil_campos')
            ? PerfilCampo::query()->with('roles')->orderBy('orden')->orderBy('etiqueta')->get()
            : collect();

        // Devuelve la vista 'gestion.administracion.seguridad.roles-permisos.listar' con los datos preparados por este controlador.
        return view('gestion.administracion.seguridad.roles-permisos.listar', compact('ready', 'roles', 'permissionCount', 'search', 'status', 'profileFields'));
    }

    public function storeProfileField(CrearCampoPerfilRequest $request, RegistradorAuditoria $auditoria): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $data = $request->validated();
        // Prepara en $field la consulta que obtiene o modifica los registros necesarios.
        $field = DB::transaction(function () use ($data, $request, $auditoria): PerfilCampo {
            // Prepara en $field la consulta que obtiene o modifica los registros necesarios.
            $field = PerfilCampo::query()->create([
                ...$data,
                'opciones' => $data['tipo'] === 'select' ? ($data['opciones'] ?? []) : null,
                'orden' => $data['orden'] ?? 0,
                'activo' => $data['activo'] ?? true,
            ]);

            $auditoria->record($request->user()->getKey(), 'crear_campo_perfil', 'perfil_campos', $field->getKey(), null, $field->only(['codigo', 'tipo']), $request->ip());

            return $field;
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Campo adicional de perfil creado.');
    }

    public function updateRoleProfileFields(ActualizarCamposPerfilRolRequest $request, Rol $role, RegistradorAuditoria $auditoria): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $configs = collect($request->validated('campos', []))->keyBy(fn (array $config): int => (int) $config['id']);
        // Prepara en $fields la consulta que obtiene o modifica los registros necesarios.
        $fields = PerfilCampo::query()->whereIn('id', $configs->keys())->get();

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($role, $fields, $configs, $request, $auditoria): void {
            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::table('roles_perfil_campos')->where('rol_id', $role->getKey())->delete();
            // Recorre $fields as $field y procesa cada elemento de la coleccion.
            foreach ($fields as $field) {
                // Guarda en $config el resultado necesario para continuar esta operacion.
                $config = $configs->get($field->getKey(), []);
                // Guarda en $visible el resultado necesario para continuar esta operacion.
                $visible = (bool) ($config['visible'] ?? false);
                // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
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

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($autorizacion->allowsGlobal(request()->user(), 'gestion.seguridad.consultar'), 403);
        // Guarda en $ready el resultado necesario para continuar esta operacion.
        $ready = $autorizacion->schemaAvailable();
        $roles = $ready ? Rol::query()->where('activo', true)->with(['permisos' => fn ($query) => $query->where('permisos.activo', true)->orderBy('modulo_id')->orderBy('accion')])->orderBy('nombre')->get() : collect();
        // Obtiene en $selectedRoleId la informacion de la solicitud o del usuario actual.
        $selectedRoleId = (int) request()->integer('rol', (int) $roles->first()?->getKey());
        // Guarda en $selectedRole el resultado necesario para continuar esta operacion.
        $selectedRole = $roles->firstWhere('id', $selectedRoleId) ?: $roles->first();
        // Guarda en $matrixModules el resultado necesario para continuar esta operacion.
        $matrixModules = $ready ? $this->modulosMatriz() : collect();
        // Guarda en $matrixGroups el resultado necesario para continuar esta operacion.
        $matrixGroups = $ready ? $this->gruposMatriz($matrixModules) : collect();
        // Guarda en $previewItems el resultado necesario para continuar esta operacion.
        $previewItems = $ready && $selectedRole ? $this->catalogoVistaPrevia((int) $selectedRole->getKey()) : collect();
        $allPermissions = $matrixModules->flatMap(fn (array $module): array => array_merge(
            $module['permissions']->all(),
            $module['screens']->flatMap(fn (array $screen): array => $screen['permissions']->all())->all(),
        ));
        // Guarda en $actions el resultado necesario para continuar esta operacion.
        $actions = $allPermissions->pluck('accion')->filter()->unique()->sort()->values();
        $selectedPermissionIds = $selectedRole?->permisos->pluck('id')->map(fn ($id): int => (int) $id)->all() ?? [];
        // Guarda en $permissionCount el resultado necesario para continuar esta operacion.
        $permissionCount = $ready ? DB::table('permisos')->where('activo', true)->count() : 0;
        // Guarda en $moduleCount el resultado necesario para continuar esta operacion.
        $moduleCount = $ready ? DB::table('modulos')->where('habilitado', true)->count() : 0;
        // Guarda en $screenCount el resultado necesario para continuar esta operacion.
        $screenCount = $ready ? DB::table('pantallas')
            ->join('modulos', 'modulos.id', '=', 'pantallas.modulo_id')
            ->where('pantallas.habilitada', true)
            ->where('modulos.habilitado', true)
            ->count() : 0;
        // Guarda en $assignedPermissionCount el resultado necesario para continuar esta operacion.
        $assignedPermissionCount = $ready ? DB::table('roles_permisos')
            ->join('roles', 'roles.id', '=', 'roles_permisos.rol_id')
            ->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
            ->where('roles.activo', true)
            ->where('permisos.activo', true)
            ->count() : 0;

        // Devuelve la vista 'gestion.administracion.seguridad.matriz-privilegios.listar' con los datos preparados por este controlador.
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
    private function catalogoVistaPrevia(int $roleId): Collection
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
    private function modulosMatriz(): Collection
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
    private function gruposMatriz(Collection $modules): Collection
    {
        // Prepara en $menuId la consulta que obtiene o modifica los registros necesarios.
        $menuId = DB::table('menus')
            ->where('codigo', 'administrador.sidebar')
            ->where('contexto', 'sidebar')
            ->where('habilitado', true)
            ->value('id');

        // Comprueba la condicion $menuId === null antes de ejecutar este bloque.
        if ($menuId === null) {
            return collect();
        }

        // Prepara en $rootItems la consulta que obtiene o modifica los registros necesarios.
        $rootItems = DB::table('elementos_menu')
            ->where('menu_id', $menuId)
            ->whereNull('padre_id')
            ->where('habilitado', true)
            ->orderBy('orden')
            ->get(['id', 'codigo', 'etiqueta', 'tipo']);
        // Guarda en $moduleById el resultado necesario para continuar esta operacion.
        $moduleById = $modules->keyBy('id');
        // Prepara en $menuEntries la consulta que obtiene o modifica los registros necesarios.
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
            // Guarda en $items el resultado necesario para continuar esta operacion.
            $items = $menuEntries->get((int) $root->id, collect())
                ->map(static function (object $item) use ($moduleById): ?array {
                    // Guarda en $module el resultado necesario para continuar esta operacion.
                    $module = $moduleById->get((int) $item->modulo_id);
                    // Comprueba la condicion $module === null antes de ejecutar este bloque.
                    if ($module === null) {
                        return null;
                    }

                    // Guarda en $screen el resultado necesario para continuar esta operacion.
                    $screen = $module['screens']->firstWhere('id', (int) $item->pantalla_id);
                    // Comprueba la condicion $screen === null antes de ejecutar este bloque.
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasTable('roles'), 503, 'La migración RBAC todavía no está aplicada.');
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $data = $request->validated();

        // Comprueba la condicion $data['alcance_permitido'] === 'empresa' antes de ejecutar este bloque.
        if ($data['alcance_permitido'] === 'empresa') {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($request->user()->can('gestion.seguridad.consultar'), 403);
        }

        // Prepara en $role la consulta que obtiene o modifica los registros necesarios.
        $role = DB::transaction(function () use ($data, $request, $auditoria): Rol {
            // Prepara en $role la consulta que obtiene o modifica los registros necesarios.
            $role = Rol::query()->create([
                ...$data,
                'es_sistema' => false,
                'activo' => true,
            ]);

            $auditoria->record($request->user()->getKey(), 'crear_rol', 'roles', $role->getKey(), null, ['codigo' => $role->codigo, 'alcance' => $role->alcance_permitido], $request->ip());

            return $role;
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $permissionIds = collect($request->validated('permisos', []))->map(static fn (mixed $id): int => (int) $id)->unique()->values()->all();
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $scope = $request->validated('alcance');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($delegacion->puedeDelegarPermisos($request->user(), $permissionIds, $scope), 403, 'No puedes delegar permisos o alcances superiores a los que posees.');

        // Comprueba la condicion $scope === 'empresa' antes de ejecutar este bloque.
        if ($scope === 'empresa') {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.consultar'), 403, 'Solo un administrador global puede conceder alcance empresarial.');
        }
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($scope === $role->alcance_permitido, 422, 'El alcance del permiso debe coincidir con el alcance permitido del rol.');

        // Comprueba la condicion $role->alcance_permitido === 'propio' && DB::table('permisos' antes de ejecutar este bloque.
        if ($role->alcance_permitido === 'propio' && DB::table('permisos')
            ->whereIn('id', $permissionIds)
            ->where('codigo', 'like', 'gestion.%')
            ->exists()) {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort(422, 'Los roles de alcance propio no pueden recibir permisos administrativos.');
        }

        // Prepara en $protectedPermissionIds la consulta que obtiene o modifica los registros necesarios.
        $protectedPermissionIds = DB::table('permisos')
            ->join('modulos', 'modulos.id', '=', 'permisos.modulo_id')
            ->where('modulos.codigo', 'seguridad')
            ->where('permisos.activo', true)
            ->pluck('permisos.id')
            ->map(static fn (mixed $id): int => (int) $id);
        // Prepara en $currentProtectedPermissionIds la consulta que obtiene o modifica los registros necesarios.
        $currentProtectedPermissionIds = DB::table('roles_permisos')
            ->where('rol_id', $role->getKey())
            ->whereIn('permiso_id', $protectedPermissionIds)
            ->pluck('permiso_id')
            ->map(static fn (mixed $id): int => (int) $id);
        // Guarda en $submittedProtectedPermissionIds el resultado necesario para continuar esta operacion.
        $submittedProtectedPermissionIds = collect($permissionIds)->intersect($protectedPermissionIds);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($submittedProtectedPermissionIds->diff($currentProtectedPermissionIds)->isEmpty(), 422, 'El módulo Seguridad y Delegación está protegido y no se puede modificar desde esta matriz.');
        // Guarda en $permissionIds el resultado necesario para continuar esta operacion.
        $permissionIds = collect($permissionIds)
            ->diff($protectedPermissionIds)
            ->merge($currentProtectedPermissionIds)
            ->unique()
            ->values()
            ->all();

        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if(
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->esRolSeguridadGlobal($role) && $delegacion->esUltimoAdministradorGlobal($request->user()),
            422,
            'No se puede modificar el último acceso administrativo global.'
        );

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($role, $permissionIds, $scope, $request, $auditoria): void {
            // Prepara en $role la consulta que obtiene o modifica los registros necesarios.
            $role = Rol::query()->whereKey($role->getKey())->lockForUpdate()->firstOrFail();
            $before = $role->permisos()->get()->map(fn ($permission): array => [
                'codigo' => $permission->codigo,
                'alcance' => $permission->pivot->alcance,
            ])->values()->all();
            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::table('roles_permisos')->where('rol_id', $role->getKey())->delete();

            // Recorre $permissionIds as $permissionId y procesa cada elemento de la coleccion.
            foreach ($permissionIds as $permissionId) {
                // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
                DB::table('roles_permisos')->insert([
                    'rol_id' => $role->getKey(),
                    'permiso_id' => $permissionId,
                    'alcance' => $scope,
                    'creado_en' => now(),
                ]);
            }

            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->sincronizarMenusDelRol((int) $role->getKey());

            // Prepara en $after la consulta que obtiene o modifica los registros necesarios.
            $after = DB::table('roles_permisos')->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
                ->where('roles_permisos.rol_id', $role->getKey())
                ->get(['permisos.codigo', 'roles_permisos.alcance'])->map(static fn ($row): array => ['codigo' => $row->codigo, 'alcance' => $row->alcance])->all();
            $auditoria->record($request->user()->getKey(), 'actualizar_permisos_rol', 'roles', $role->getKey(), ['permisos' => $before], ['permisos' => $after], $request->ip());
        });

        // Guarda en $message el resultado necesario para continuar esta operacion.
        $message = 'Los privilegios del rol fueron actualizados.';

        // Comprueba la condicion $request->expectsJson() antes de ejecutar este bloque.
        if ($request->expectsJson()) {
            // Devuelve al navegador el resultado HTTP producido por la instruccion siguiente.
            return response()->json(['message' => $message]);
        }

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $data = $request->validated();
        $permissionIds = collect($data['permisos'] ?? [])->map(static fn (mixed $id): int => (int) $id)->unique()->values()->all();
        // Guarda en $scope el resultado necesario para continuar esta operacion.
        $scope = (string) $data['alcance'];
        // Prepara en $screen la consulta que obtiene o modifica los registros necesarios.
        $screen = DB::table('pantallas')
            ->join('modulos', 'modulos.id', '=', 'pantallas.modulo_id')
            ->where('pantallas.id', $data['pantalla_id'])
            ->where('pantallas.modulo_id', $data['modulo_id'])
            ->where('pantallas.habilitada', true)
            ->where('modulos.habilitado', true)
            ->first(['pantallas.id', 'pantallas.modulo_id', 'modulos.codigo as modulo_codigo']);

        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($screen !== null, 422, 'La pantalla no pertenece al módulo seleccionado.');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($scope === $role->alcance_permitido, 422, 'El alcance del permiso debe coincidir con el alcance permitido del rol.');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($delegacion->puedeDelegarPermisos($request->user(), $permissionIds, $scope), 403, 'No puedes delegar permisos o alcances superiores a los que posees.');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($scope !== 'empresa' || $autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.consultar'), 403, 'Solo un administrador global puede conceder alcance empresarial.');

        // Comprueba la condicion $role->alcance_permitido === 'propio' && DB::table('permisos')->whereIn('id', $permissionIds)->where('codigo', 'like', 'gestion.%')->exists() antes de ejecutar este bloque.
        if ($role->alcance_permitido === 'propio' && DB::table('permisos')->whereIn('id', $permissionIds)->where('codigo', 'like', 'gestion.%')->exists()) {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort(422, 'Los roles de alcance propio no pueden recibir permisos administrativos.');
        }

        // Prepara en $screenPermissionIds la consulta que obtiene o modifica los registros necesarios.
        $screenPermissionIds = DB::table('permisos')
            ->where('modulo_id', $screen->modulo_id)
            ->where('pantalla_id', $screen->id)
            ->where('activo', true)
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(collect($permissionIds)->diff($screenPermissionIds)->isEmpty(), 422, 'Uno o más permisos no pertenecen a la pantalla seleccionada.');

        // Prepara en $protectedPermissionIds la consulta que obtiene o modifica los registros necesarios.
        $protectedPermissionIds = DB::table('permisos')
            ->join('modulos', 'modulos.id', '=', 'permisos.modulo_id')
            ->where('modulos.codigo', 'seguridad')
            ->where('permisos.pantalla_id', $screen->id)
            ->where('permisos.activo', true)
            ->pluck('permisos.id')
            ->map(static fn (mixed $id): int => (int) $id);
        // Prepara en $currentProtectedPermissionIds la consulta que obtiene o modifica los registros necesarios.
        $currentProtectedPermissionIds = DB::table('roles_permisos')
            ->where('rol_id', $role->getKey())
            ->where('alcance', $scope)
            ->whereIn('permiso_id', $protectedPermissionIds)
            ->pluck('permiso_id')
            ->map(static fn (mixed $id): int => (int) $id);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(collect($permissionIds)->intersect($protectedPermissionIds)->diff($currentProtectedPermissionIds)->isEmpty(), 422, 'El módulo Seguridad y Delegación está protegido.');
        // Guarda en $permissionIds el resultado necesario para continuar esta operacion.
        $permissionIds = collect($permissionIds)->diff($protectedPermissionIds)->merge($currentProtectedPermissionIds)->unique()->values()->all();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if($this->esRolSeguridadGlobal($role) && $delegacion->esUltimoAdministradorGlobal($request->user()), 422, 'No se puede modificar el último acceso administrativo global.');

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($role, $screen, $screenPermissionIds, $permissionIds, $scope, $request, $auditoria): void {
            // Prepara en $lockedRole la consulta que obtiene o modifica los registros necesarios.
            $lockedRole = Rol::query()->whereKey($role->getKey())->lockForUpdate()->firstOrFail();
            // Prepara en $before la consulta que obtiene o modifica los registros necesarios.
            $before = DB::table('roles_permisos')->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
                ->where('roles_permisos.rol_id', $lockedRole->getKey())->where('roles_permisos.alcance', $scope)
                ->whereIn('roles_permisos.permiso_id', $screenPermissionIds)->get(['permisos.codigo', 'roles_permisos.alcance'])
                ->map(static fn (object $row): array => ['codigo' => $row->codigo, 'alcance' => $row->alcance])->values()->all();
            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::table('roles_permisos')->where('rol_id', $lockedRole->getKey())->where('alcance', $scope)->whereIn('permiso_id', $screenPermissionIds)->delete();
            // Recorre $permissionIds as $permissionId y procesa cada elemento de la coleccion.
            foreach ($permissionIds as $permissionId) {
                // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
                DB::table('roles_permisos')->insert(['rol_id' => $lockedRole->getKey(), 'permiso_id' => $permissionId, 'alcance' => $scope, 'creado_en' => now()]);
            }
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->sincronizarMenusDelRol((int) $lockedRole->getKey());
            // Prepara en $after la consulta que obtiene o modifica los registros necesarios.
            $after = DB::table('roles_permisos')->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
                ->where('roles_permisos.rol_id', $lockedRole->getKey())->where('roles_permisos.alcance', $scope)
                ->whereIn('roles_permisos.permiso_id', $screenPermissionIds)->get(['permisos.codigo', 'roles_permisos.alcance'])
                ->map(static fn (object $row): array => ['codigo' => $row->codigo, 'alcance' => $row->alcance])->values()->all();
            $auditoria->record($request->user()->getKey(), 'actualizar_permisos_pantalla', 'pantallas', $screen->id, ['permisos' => $before], ['permisos' => $after], $request->ip());
        });

        // Devuelve al navegador el resultado HTTP producido por la instruccion siguiente.
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if(
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->esRolSeguridadGlobal($role) && $delegacion->esUltimoAdministradorGlobal($request->user()),
            422,
            'No se puede editar el último acceso administrativo global.'
        );
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $data = $request->validated();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($data['alcance_permitido'] !== 'empresa' || $request->user()->can('gestion.seguridad.consultar'), 403, 'Solo un administrador global puede conceder alcance empresarial.');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($delegacion->puedeDelegarPermisos($request->user(), $role->permisos()->pluck('permisos.id')->all(), $data['alcance_permitido']), 403, 'El nuevo alcance excede tus privilegios delegables.');

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($role, $data, $request, $auditoria): void {
            // Prepara en $locked la consulta que obtiene o modifica los registros necesarios.
            $locked = Rol::query()->lockForUpdate()->findOrFail($role->getKey());
            // Comprueba la condicion $locked->alcance_permitido !== $data['alcance_permitido'] antes de ejecutar este bloque.
            if ($locked->alcance_permitido !== $data['alcance_permitido']) {
                // Prepara en $hasGlobalAssignments la consulta que obtiene o modifica los registros necesarios.
                $hasGlobalAssignments = DB::table('usuarios_roles')
                    ->where('rol_id', $locked->getKey())->where('activo', true)->exists();
                // Guarda en $hasSiteAssignments el resultado necesario para continuar esta operacion.
                $hasSiteAssignments = Schema::hasTable('usuarios_sedes_roles') && DB::table('usuarios_sedes_roles')
                    ->where('rol_id', $locked->getKey())->where('activo', true)->exists();
                // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                abort_if($hasGlobalAssignments || $hasSiteAssignments, 409, 'No puedes cambiar el alcance de un rol con asignaciones activas; revócalas primero.');
            }
            // Guarda en $before el resultado necesario para continuar esta operacion.
            $before = $locked->only(['nombre', 'descripcion', 'alcance_permitido']);
            $locked->update($data);
            $auditoria->record($request->user()->getKey(), 'actualizar_rol', 'roles', $locked->getKey(), $before, $locked->only(['nombre', 'descripcion', 'alcance_permitido']), $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if(
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->esRolSeguridadGlobal($role) && $delegacion->esUltimoAdministradorGlobal($request->user()),
            422,
            'No se puede desactivar el último acceso administrativo global.'
        );
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(app(ServicioAutorizacion::class)->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        // Guarda en $activo el resultado necesario para continuar esta operacion.
        $activo = $request->boolean('activo');

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($role, $activo, $request, $delegacion, $auditoria): void {
            // Prepara en $locked la consulta que obtiene o modifica los registros necesarios.
            $locked = Rol::query()->lockForUpdate()->findOrFail($role->getKey());
            // Guarda en $hasGlobalAssignments el resultado necesario para continuar esta operacion.
            $hasGlobalAssignments = $locked->usuarios()->wherePivot('activo', true)->exists();
            // Guarda en $hasSiteAssignments el resultado necesario para continuar esta operacion.
            $hasSiteAssignments = Schema::hasTable('usuarios_sedes_roles') && DB::table('usuarios_sedes_roles')
                ->where('rol_id', $locked->getKey())->where('activo', true)->exists();
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if(! $activo && ($hasGlobalAssignments || $hasSiteAssignments), 409, 'Revoca primero las asignaciones activas de este rol.');
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($delegacion->puedeDelegarPermisos($request->user(), $locked->permisos()->pluck('permisos.id')->all(), $locked->alcance_permitido), 403);
            $before = ['activo' => (bool) $locked->activo];
            $locked->update(['activo' => $activo]);
            $auditoria->record($request->user()->getKey(), $activo ? 'reactivar_rol' : 'desactivar_rol', 'roles', $locked->getKey(), $before, ['activo' => $activo], $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', $activo ? 'El rol fue reactivado.' : 'El rol fue desactivado.');
    }

    private function esRolSeguridadGlobal(Rol $role): bool
    {
        return $role->permisos()
            ->where('permisos.codigo', 'gestion.seguridad.actualizar')
            ->wherePivot('alcance', 'empresa')
            ->exists();
    }

    /**
     * Vincula al rol los menús que contienen opciones habilitadas para sus permisos vigentes.
     *
     * La asociación es deliberadamente aditiva: roles_menus también contiene asignaciones
     * administrativas explícitas y el servicio del menú filtra cada opción con autorización
     * efectiva. La clave primaria rol/menú evita duplicados sin conceder permisos adicionales.
     */
    private function sincronizarMenusDelRol(int $roleId): void
    {
        // Prepara en $permissionIds la consulta que obtiene o modifica los registros necesarios.
        $permissionIds = DB::table('roles_permisos as asignacion')
            ->join('permisos', 'permisos.id', '=', 'asignacion.permiso_id')
            ->where('asignacion.rol_id', $roleId)
            ->where('permisos.activo', true)
            ->pluck('asignacion.permiso_id')
            ->map(static fn (mixed $permissionId): int => (int) $permissionId)
            ->unique()
            ->values();

        // Comprueba la condicion $permissionIds->isEmpty() antes de ejecutar este bloque.
        if ($permissionIds->isEmpty()) {
            return;
        }

        // Prepara en $menuIds la consulta que obtiene o modifica los registros necesarios.
        $menuIds = DB::table('elementos_menu as elemento')
            ->join('menus', 'menus.id', '=', 'elemento.menu_id')
            ->whereIn('elemento.permiso_id', $permissionIds->all())
            ->where('elemento.habilitado', true)
            ->where('menus.habilitado', true)
            ->where('menus.contexto', 'sidebar')
            ->distinct()
            ->pluck('elemento.menu_id')
            ->map(static fn (mixed $menuId): int => (int) $menuId);

        // Recorre $menuIds as $menuId y procesa cada elemento de la coleccion.
        foreach ($menuIds as $menuId) {
            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::table('roles_menus')->insertOrIgnore([
                'rol_id' => $roleId,
                'menu_id' => $menuId,
                'creado_en' => now(),
            ]);
        }
    }
}
