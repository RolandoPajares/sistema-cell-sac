<?php

namespace App\Http\Controllers\Gestion\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\AsignarRolUsuarioRequest;
use App\Models\Organizacion\Sede;
use App\Models\Seguridad\Rol;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioDelegacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

final class AsignacionRolesController extends Controller
{
    public function index(ServicioAutorizacion $autorizacion): View
    {
        abort_unless($autorizacion->allowsGlobal(request()->user(), 'gestion.seguridad.consultar'), 403);
        $ready = $autorizacion->schemaAvailable();
        $users = $ready ? User::query()->with(['roles', 'rolesPorSede'])->orderBy('nombre')->paginate(30) : collect();
        $roles = $ready ? Rol::query()->where('activo', true)->orderBy('nombre')->get() : collect();
        $sites = $ready ? Sede::query()->where('activa', true)->orderBy('nombre')->get() : collect();

        return view('gestion.roles-permisos.asignaciones', compact('ready', 'users', 'roles', 'sites'));
    }

    public function store(
        AsignarRolUsuarioRequest $request,
        User $user,
        ServicioAutorizacion $autorizacion,
        ServicioDelegacion $delegacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        $data = $request->validated();
        $role = Rol::query()->findOrFail($data['rol_id']);
        abort_unless($delegacion->puedeAdministrarUsuario($request->user(), $user), 403, 'No puedes administrar una cuenta con privilegios superiores a los tuyos.');
        abort_unless($delegacion->puedeAsignarRol($request->user(), $role), 403, 'El rol solicitado concede privilegios superiores a los que puedes delegar.');

        if ($role->alcance_permitido === 'sede') {
            abort_unless($data['alcance'] === 'sede', 422, 'Este rol debe asignarse dentro de una sede.');
        } else {
            abort_unless($data['alcance'] === 'empresa', 422, 'Este rol se asigna a nivel de empresa o cuenta propia.');
        }

        if ($data['alcance'] === 'empresa') {
            abort_unless($role->codigo !== 'administrador' || $autorizacion->hasRole($request->user(), 'administrador'), 403);
        } else {
            abort_unless(Schema::hasTable('usuarios_sedes_roles'), 503, 'La asignación por sede requiere la migración RBAC.');
            $siteId = app(ServicioAutorizacion::class)->operationSite($request->user())['sede_id'];
            abort_if($siteId === null, 422, 'No existe una sede interna autorizada para asignar este rol.');
            $site = Sede::query()->whereKey($siteId)->where('activa', true)->firstOrFail();
            $data['sede_id'] = (int) $site->getKey();
            abort_unless($autorizacion->allowsRecord($request->user(), 'gestion.seguridad.asignar', (int) $site->empresa_id, (int) $site->id), 403);
            if (! $autorizacion->hasRole($request->user(), 'administrador')) {
                $canManageSite = DB::table('usuarios_sedes_roles')
                    ->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
                    ->where('usuarios_sedes_roles.usuario_id', $request->user()->getKey())
                    ->where('usuarios_sedes_roles.sede_id', $data['sede_id'])
                    ->where('usuarios_sedes_roles.activo', true)
                    ->where('roles.activo', true)
                    ->where(fn ($query) => $query->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now()))
                    ->exists();
                abort_unless($canManageSite, 403, 'No puedes asignar roles en una sede a la que no tienes acceso.');
            }
        }

        DB::transaction(function () use ($data, $role, $user, $request, $auditoria): void {
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            $before = $this->assignmentSnapshot($user);
            $assignment = [
                'asignado_por_usuario_id' => $request->user()->getKey(),
                'asignado_en' => now(),
                'vigente_hasta' => $data['vigente_hasta'] ?? null,
                'activo' => true,
            ];

            if ($data['alcance'] === 'sede') {
                DB::table('usuarios_sedes_roles')->updateOrInsert(
                    ['usuario_id' => $user->getKey(), 'sede_id' => (int) $data['sede_id'], 'rol_id' => $role->getKey()],
                    $assignment,
                );
            } else {
                DB::table('usuarios_roles')->updateOrInsert(
                    ['usuario_id' => $user->getKey(), 'rol_id' => $role->getKey()],
                    $assignment,
                );
            }

            $auditoria->record($request->user()->getKey(), 'asignar_rol_usuario', 'usuarios', $user->getKey(), ['asignaciones' => $before], ['asignaciones' => $this->assignmentSnapshot($user)], $request->ip());
        });

        return back()->with('status', 'El rol fue asignado al usuario.');
    }

    public function revokeGlobal(
        Request $request,
        User $user,
        Rol $role,
        ServicioDelegacion $delegacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        return $this->revokeAssignment($request, $user, $role, null, $delegacion, $auditoria);
    }

    public function revokeAtSite(
        Request $request,
        User $user,
        Rol $role,
        Sede $site,
        ServicioDelegacion $delegacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        return $this->revokeAssignment($request, $user, $role, $site, $delegacion, $auditoria);
    }

    private function revokeAssignment(
        Request $request,
        User $user,
        Rol $role,
        ?Sede $site,
        ServicioDelegacion $delegacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        abort_unless(app(ServicioAutorizacion::class)->allowsGlobal($request->user(), 'gestion.seguridad.revocar'), 403);
        abort_unless($delegacion->puedeAdministrarUsuario($request->user(), $user), 403, 'No puedes administrar una cuenta con privilegios superiores a los tuyos.');
        abort_unless($delegacion->puedeAsignarRol($request->user(), $role), 403, 'No puedes administrar una asignación con privilegios superiores a los tuyos.');
        if ($site !== null) {
            abort_unless(app(ServicioAutorizacion::class)->allowsRecord($request->user(), 'gestion.seguridad.revocar', (int) $site->empresa_id, (int) $site->id), 403);
        }

        DB::transaction(function () use ($request, $user, $role, $site, $delegacion, $auditoria): void {
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            $before = $this->assignmentSnapshot($user);
            if ($role->codigo === 'administrador' && $site === null) {
                $delegacion->bloquearAdministradoresGlobales();
                abort_if($delegacion->esUltimoAdministradorGlobal($user), 422, 'No se puede retirar el último administrador global autorizado.');
            }

            if ($site instanceof Sede) {
                $updated = DB::table('usuarios_sedes_roles')->where('usuario_id', $user->getKey())->where('rol_id', $role->getKey())->where('sede_id', $site->getKey())->where('activo', true)->update(['activo' => false]);
            } else {
                $updated = DB::table('usuarios_roles')->where('usuario_id', $user->getKey())->where('rol_id', $role->getKey())->where('activo', true)->update(['activo' => false]);
            }
            abort_if($updated !== 1, 422, 'La asignación no existe o ya está revocada.');

            $auditoria->record($request->user()->getKey(), 'revocar_rol_usuario', 'usuarios', $user->getKey(), ['asignaciones' => $before], ['asignaciones' => $this->assignmentSnapshot($user)], $request->ip());
        });

        return back()->with('status', 'La asignación fue revocada y conservada para auditoría.');
    }

    /** @return array<int,array<string,mixed>> */
    private function assignmentSnapshot(User $user): array
    {
        $global = DB::table('usuarios_roles')->join('roles', 'roles.id', '=', 'usuarios_roles.rol_id')
            ->where('usuarios_roles.usuario_id', $user->getKey())
            ->get(['roles.codigo', 'usuarios_roles.activo', 'usuarios_roles.vigente_hasta'])
            ->map(static fn ($row): array => ['rol' => $row->codigo, 'sede_id' => null, 'activo' => (bool) $row->activo, 'vigente_hasta' => $row->vigente_hasta]);
        $sites = DB::table('usuarios_sedes_roles')->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
            ->where('usuarios_sedes_roles.usuario_id', $user->getKey())
            ->get(['roles.codigo', 'usuarios_sedes_roles.sede_id', 'usuarios_sedes_roles.activo', 'usuarios_sedes_roles.vigente_hasta'])
            ->map(static fn ($row): array => ['rol' => $row->codigo, 'sede_id' => (int) $row->sede_id, 'activo' => (bool) $row->activo, 'vigente_hasta' => $row->vigente_hasta]);

        return $global->concat($sites)->values()
            ->all();
    }
}
