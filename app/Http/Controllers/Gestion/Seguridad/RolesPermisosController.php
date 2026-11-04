<?php

namespace App\Http\Controllers\Gestion\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarPermisosRolRequest;
use App\Http\Requests\GuardarRolRequest;
use App\Models\Seguridad\Rol;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioDelegacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class RolesPermisosController extends Controller
{
    public function index(ServicioAutorizacion $autorizacion): View
    {
        abort_unless($autorizacion->allowsGlobal(request()->user(), 'gestion.seguridad.consultar'), 403);
        $ready = $autorizacion->schemaAvailable();
        $roles = $ready
            ? Rol::query()->with(['permisos' => fn ($query) => $query->orderBy('modulo_id')->orderBy('accion')])->withCount('usuarios')->orderBy('es_sistema', 'desc')->orderBy('nombre')->get()
            : collect();
        $modules = $ready ? DB::table('modulos')->where('habilitado', true)->orderBy('orden')->get() : collect();
        $permissions = $ready
            ? DB::table('permisos')->join('modulos', 'modulos.id', '=', 'permisos.modulo_id')
                ->where('permisos.activo', true)
                ->orderBy('modulos.orden')->orderBy('permisos.accion')->orderBy('permisos.nombre')
                ->get(['permisos.id', 'permisos.codigo', 'permisos.nombre', 'permisos.accion', 'modulos.nombre as modulo_nombre', 'modulos.codigo as modulo_codigo'])
                ->groupBy('modulo_codigo')
            : collect();
        $permissionCount = $ready ? DB::table('permisos')->where('activo', true)->count() : 0;

        return view('gestion.roles-permisos.listar', compact('ready', 'roles', 'modules', 'permissions', 'permissionCount'));
    }

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

        if ($role->codigo === 'administrador') {
            abort(422, 'El rol global del administrador es reservado y no puede perder privilegios.');
        }

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
}
