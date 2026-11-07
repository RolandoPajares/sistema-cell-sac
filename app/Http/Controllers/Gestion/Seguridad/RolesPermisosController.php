<?php

namespace App\Http\Controllers\Gestion\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarPermisosRolRequest;
use App\Http\Requests\ActualizarRolRequest;
use App\Http\Requests\GuardarRolRequest;
use App\Models\Seguridad\Rol;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioDelegacion;
use App\Servicios\ServicioPlantillasRbac;
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
            ? Rol::query()->withCount(['usuarios', 'permisos'])->when($search !== '', fn ($query) => $query->where(fn ($nested) => $nested->where('nombre', 'like', '%'.$search.'%')->orWhere('descripcion', 'like', '%'.$search.'%')->orWhere('codigo', 'like', '%'.$search.'%')))->when(in_array($status, ['activo', 'inactivo'], true), fn ($query) => $query->where('activo', $status === 'activo'))->orderBy('es_sistema', 'desc')->orderBy('nombre')->get()
            : collect();
        $permissionCount = $ready ? DB::table('permisos')->where('activo', true)->count() : 0;

        return view('gestion.administracion.roles-permisos.listar', compact('ready', 'roles', 'permissionCount', 'search', 'status'));
    }

    /**
     * Ejecuta la operación matrix definida por la clase RolesPermisosController.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function matrix(ServicioAutorizacion $autorizacion, ServicioPlantillasRbac $plantillas): View
    {
        abort_unless($autorizacion->allowsGlobal(request()->user(), 'gestion.seguridad.consultar'), 403);
        $ready = $autorizacion->schemaAvailable();
        $roles = $ready ? Rol::query()->where('activo', true)->with(['permisos' => fn ($query) => $query->orderBy('modulo_id')->orderBy('accion')])->orderBy('nombre')->get() : collect();
        $selectedRoleId = (int) request()->integer('rol', (int) $roles->first()?->getKey());
        $selectedRole = $roles->firstWhere('id', $selectedRoleId) ?: $roles->first();
        $permissions = $ready ? $this->permissionCatalog() : collect();
        $recommendedPermissionIds = $ready ? $plantillas->permissionIdsFor($selectedRole?->codigo) : [];
        $previewItems = $ready && $selectedRole ? $this->previewCatalog((int) $selectedRole->getKey()) : collect();
        $permissionCount = $ready ? DB::table('permisos')->where('activo', true)->count() : 0;
        $moduleCount = $ready ? DB::table('modulos')->where('habilitado', true)->count() : 0;
        $screenCount = $ready ? DB::table('pantallas')->where('habilitada', true)->count() : 0;
        $assignedPermissionCount = $ready ? DB::table('roles_permisos')->join('roles', 'roles.id', '=', 'roles_permisos.rol_id')->where('roles.activo', true)->distinct()->count(DB::raw('CONCAT(roles_permisos.rol_id, ":", roles_permisos.permiso_id, ":", roles_permisos.alcance)')) : 0;

        return view('gestion.administracion.roles-permisos.matriz', compact('ready', 'roles', 'selectedRole', 'permissions', 'permissionCount', 'moduleCount', 'screenCount', 'assignedPermissionCount', 'recommendedPermissionIds', 'previewItems'));
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
    private function permissionCatalog()
    {
        return DB::table('permisos')->join('modulos', 'modulos.id', '=', 'permisos.modulo_id')
            ->leftJoin('pantallas', 'pantallas.id', '=', 'permisos.pantalla_id')
            ->where('permisos.activo', true)
            ->orderBy('modulos.orden')->orderBy('permisos.accion')->orderBy('permisos.nombre')
            ->get(['permisos.id', 'permisos.codigo', 'permisos.nombre', 'permisos.accion', 'permisos.pantalla_id', 'pantallas.nombre as pantalla_nombre', 'modulos.nombre as modulo_nombre', 'modulos.codigo as modulo_codigo'])
            ->groupBy('modulo_codigo');
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
            abort_unless($request->user()->isRole('administrador'), 403);
        }

        $role = DB::transaction(fn (): Rol => Rol::query()->create([
            ...$data,
            'es_sistema' => false,
            'activo' => true,
        ]));

        $auditoria->record($request->user()->getKey(), 'crear_rol', 'roles', $role->getKey(), null, ['codigo' => $role->codigo, 'alcance' => $role->alcance_permitido], $request->ip());

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
    ): RedirectResponse {
        $permissionIds = collect($request->validated('permisos', []))->map(static fn (mixed $id): int => (int) $id)->unique()->values()->all();
        $scope = $request->validated('alcance');
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.actualizar'), 403);
        abort_unless($delegacion->puedeDelegarPermisos($request->user(), $permissionIds, $scope), 403, 'No puedes delegar permisos o alcances superiores a los que posees.');

        if ($scope === 'empresa') {
            abort_unless($autorizacion->hasRole($request->user(), 'administrador'), 403, 'Solo un administrador global puede conceder alcance empresarial.');
        }
        abort_unless($scope === $role->alcance_permitido, 422, 'El alcance del permiso debe coincidir con el alcance permitido del rol.');

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

        if (in_array($role->codigo, ['cliente_mayorista', 'cliente_minorista'], true)) {
            $concedeOperacionAdministrativa = DB::table('permisos')
                ->whereIn('id', $permissionIds)
                ->where('codigo', 'gestion.pedidos.crear')
                ->exists();
            abort_unless(! $concedeOperacionAdministrativa, 422, 'Los roles de cliente no pueden recibir el permiso administrativo de crear pedidos.');
        }

        abort_if($role->codigo === 'administrador', 422, 'El rol global del administrador es reservado y no puede perder privilegios.');

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

        return back()->with('status', 'Los privilegios del rol fueron actualizados.');
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
        abort_if($role->codigo === 'administrador', 422, 'El rol del administrador está reservado y no puede editarse.');
        $data = $request->validated();
        abort_unless($data['alcance_permitido'] !== 'empresa' || $request->user()->isRole('administrador'), 403, 'Solo un administrador global puede conceder alcance empresarial.');
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
        abort_if($role->codigo === 'administrador', 422, 'El rol del administrador está reservado y no puede desactivarse.');
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
}
