<?php

namespace App\Http\Controllers\Gestion\Administracion\Seguridad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestion\Administracion\Seguridad\AsignarRolUsuarioRequest;
use App\Models\Organizacion\Sede;
use App\Models\Seguridad\Rol;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\Administracion\Seguridad\ServicioDelegacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase AsignacionRolesController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class AsignacionRolesController extends Controller
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
        // Prepara en $users la consulta que obtiene o modifica los registros necesarios.
        $users = $ready ? User::query()->with(['roles', 'rolesPorSede'])->orderBy('nombre')->paginate(30) : collect();
        // Prepara en $roles la consulta que obtiene o modifica los registros necesarios.
        $roles = $ready ? Rol::query()->where('activo', true)->orderBy('nombre')->get() : collect();
        // Prepara en $sites la consulta que obtiene o modifica los registros necesarios.
        $sites = $ready ? Sede::query()->where('activa', true)->orderBy('nombre')->get() : collect();

        // Agrupa en $assignmentPresentation los datos que se utilizaran en la siguiente operacion.
        $assignmentPresentation = [];
        // Recorre $users as $user y procesa cada elemento de la coleccion.
        foreach ($users as $user) {
            $activeGlobal = $user->roles->filter(static fn (Rol $role): bool => (bool) $role->pivot->activo);
            $activeSite = $user->rolesPorSede->filter(static fn (Rol $role): bool => (bool) $role->pivot->activo);
            $assignmentPresentation[$user->getKey()] = [
                'activeGlobal' => $activeGlobal,
                'activeSite' => $activeSite,
                'currentGlobal' => $activeGlobal->pluck('id')->all(),
                'currentSite' => $activeSite->pluck('id')->all(),
            ];
        }

        // Devuelve la vista 'gestion.administracion.seguridad.asignaciones-delegaciones.listar' con los datos preparados por este controlador.
        return view('gestion.administracion.seguridad.asignaciones-delegaciones.listar', compact('ready', 'users', 'roles', 'sites', 'assignmentPresentation'));
    }

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @return mixed Resultado de la operación.
     */
    public function store(
        AsignarRolUsuarioRequest $request,
        User $user,
        ServicioAutorizacion $autorizacion,
        ServicioDelegacion $delegacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $data = $request->validated();
        // Prepara en $role la consulta que obtiene o modifica los registros necesarios.
        $role = Rol::query()->findOrFail($data['rol_id']);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($delegacion->puedeAdministrarUsuario($request->user(), $user), 403, 'No puedes administrar una cuenta con privilegios superiores a los tuyos.');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($delegacion->puedeAsignarRol($request->user(), $role), 403, 'El rol solicitado concede privilegios superiores a los que puedes delegar.');

        // Comprueba la condicion $role->alcance_permitido === 'sede' antes de ejecutar este bloque.
        if ($role->alcance_permitido === 'sede') {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($data['alcance'] === 'sede', 422, 'Este rol debe asignarse dentro de una sede.');
        } else {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($data['alcance'] === 'empresa', 422, 'Este rol se asigna a nivel de empresa o cuenta propia.');
        }

        // Comprueba la condicion $data['alcance'] === 'empresa' antes de ejecutar este bloque.
        if ($data['alcance'] === 'empresa') {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless(! $role->es_sistema || $autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.consultar'), 403);
        } else {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless(Schema::hasTable('usuarios_sedes_roles'), 503, 'La asignación por sede requiere la migración RBAC.');
            // Obtiene en $siteId la informacion de la solicitud o del usuario actual.
            $siteId = app(ServicioAutorizacion::class)->operationSite($request->user())['sede_id'];
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if($siteId === null, 422, 'No existe una sede interna autorizada para asignar este rol.');
            // Prepara en $site la consulta que obtiene o modifica los registros necesarios.
            $site = Sede::query()->whereKey($siteId)->where('activa', true)->firstOrFail();
            $data['sede_id'] = (int) $site->getKey();
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($autorizacion->allowsRecord($request->user(), 'gestion.seguridad.asignar', (int) $site->empresa_id, (int) $site->id), 403);
            // Comprueba la condicion ! $autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.consultar') antes de ejecutar este bloque.
            if (! $autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.consultar')) {
                // Prepara en $canManageSite la consulta que obtiene o modifica los registros necesarios.
                $canManageSite = DB::table('usuarios_sedes_roles')
                    ->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
                    ->where('usuarios_sedes_roles.usuario_id', $request->user()->getKey())
                    ->where('usuarios_sedes_roles.sede_id', $data['sede_id'])
                    ->where('usuarios_sedes_roles.activo', true)
                    ->where('roles.activo', true)
                    ->where(fn ($query) => $query->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now()))
                    ->exists();
                // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                abort_unless($canManageSite, 403, 'No puedes asignar roles en una sede a la que no tienes acceso.');
            }
        }

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($data, $role, $user, $request, $auditoria): void {
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            // Guarda en $before el resultado necesario para continuar esta operacion.
            $before = $this->instantaneaAsignacion($user);
            // Agrupa en $assignment los datos que se utilizaran en la siguiente operacion.
            $assignment = [
                'asignado_por_usuario_id' => $request->user()->getKey(),
                'asignado_en' => now(),
                'vigente_hasta' => $data['vigente_hasta'] ?? null,
                'activo' => true,
            ];

            // Comprueba la condicion $data['alcance'] === 'sede' antes de ejecutar este bloque.
            if ($data['alcance'] === 'sede') {
                // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
                DB::table('usuarios_sedes_roles')->updateOrInsert(
                    ['usuario_id' => $user->getKey(), 'sede_id' => (int) $data['sede_id'], 'rol_id' => $role->getKey()],
                    $assignment,
                );
            } else {
                // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
                DB::table('usuarios_roles')->updateOrInsert(
                    ['usuario_id' => $user->getKey(), 'rol_id' => $role->getKey()],
                    $assignment,
                );
            }

            $auditoria->record($request->user()->getKey(), 'asignar_rol_usuario', 'usuarios', $user->getKey(), ['asignaciones' => $before], ['asignaciones' => $this->instantaneaAsignacion($user)], $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'El rol fue asignado al usuario.');
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @return mixed Resultado de la operación.
     */
    public function sync(
        Request $request,
        User $user,
        ServicioAutorizacion $autorizacion,
        ServicioDelegacion $delegacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($autorizacion->allowsGlobal($request->user(), 'gestion.seguridad.asignar'), 403);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($delegacion->puedeAdministrarUsuario($request->user(), $user), 403, 'No puedes administrar una cuenta con privilegios superiores a los tuyos.');

        // Guarda en $validated el resultado necesario para continuar esta operacion.
        $validated = $request->validate([
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['integer', 'distinct', Rule::exists('roles', 'id')->where('activo', true)],
        ]);
        $selectedIds = collect($validated['roles'] ?? [])->map(static fn (mixed $id): int => (int) $id)->unique()->values();
        // Prepara en $selectedRoles la consulta que obtiene o modifica los registros necesarios.
        $selectedRoles = Rol::query()->whereIn('id', $selectedIds)->get()->keyBy('id');

        // Recorre $selectedRoles as $role y procesa cada elemento de la coleccion.
        foreach ($selectedRoles as $role) {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($delegacion->puedeAsignarRol($request->user(), $role), 403, 'El rol solicitado concede privilegios superiores a los que puedes delegar.');
        }

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($request, $user, $selectedRoles, $delegacion, $auditoria): void {
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            // Guarda en $before el resultado necesario para continuar esta operacion.
            $before = $this->instantaneaAsignacion($user);
            // Guarda en $siteId el resultado necesario para continuar esta operacion.
            $siteId = null;
            // Guarda en $site el resultado necesario para continuar esta operacion.
            $site = null;

            // Comprueba la condicion $selectedRoles->contains(fn (Rol $role): bool => $role->alcance_permitido === 'sede') antes de ejecutar este bloque.
            if ($selectedRoles->contains(fn (Rol $role): bool => $role->alcance_permitido === 'sede')) {
                // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                abort_unless(Schema::hasTable('usuarios_sedes_roles'), 503, 'La asignación por sede requiere la migración RBAC.');
                // Obtiene en $siteId la informacion de la solicitud o del usuario actual.
                $siteId = app(ServicioAutorizacion::class)->operationSite($request->user())['sede_id'];
                // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                abort_if($siteId === null, 422, 'No existe una sede interna autorizada para asignar roles de sede.');
                // Prepara en $site la consulta que obtiene o modifica los registros necesarios.
                $site = Sede::query()->whereKey($siteId)->where('activa', true)->firstOrFail();
                // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                abort_unless(app(ServicioAutorizacion::class)->allowsRecord($request->user(), 'gestion.seguridad.asignar', (int) $site->empresa_id, (int) $site->id), 403);
            }

            $globalIds = $selectedRoles->filter(fn (Rol $role): bool => $role->alcance_permitido !== 'sede')->keys()->all();
            $siteIds = $selectedRoles->filter(fn (Rol $role): bool => $role->alcance_permitido === 'sede')->keys()->all();
            // Prepara en $existingGlobal la consulta que obtiene o modifica los registros necesarios.
            $existingGlobal = DB::table('usuarios_roles')->where('usuario_id', $user->getKey())->where('activo', true)->get();
            // Prepara en $existingSite la consulta que obtiene o modifica los registros necesarios.
            $existingSite = DB::table('usuarios_sedes_roles')->where('usuario_id', $user->getKey())->where('activo', true)->get();

            // Recorre $existingGlobal as $assignment y procesa cada elemento de la coleccion.
            foreach ($existingGlobal as $assignment) {
                // Comprueba la condicion ! in_array((int) $assignment->rol_id, $globalIds, true) antes de ejecutar este bloque.
                if (! in_array((int) $assignment->rol_id, $globalIds, true)) {
                    // Prepara en $role la consulta que obtiene o modifica los registros necesarios.
                    $role = Rol::query()->findOrFail($assignment->rol_id);
                    // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                    abort_unless($delegacion->puedeAsignarRol($request->user(), $role), 403);
                    // Comprueba la condicion $role->es_sistema antes de ejecutar este bloque.
                    if ($role->es_sistema) {
                        $delegacion->bloquearAdministradoresGlobales();
                        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                        abort_if($delegacion->esUltimoAdministradorGlobal($user), 422, 'No se puede retirar el último administrador global autorizado.');
                    }
                    // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
                    DB::table('usuarios_roles')->where('usuario_id', $user->getKey())->where('rol_id', $role->getKey())->update(['activo' => false]);
                }
            }
            // Recorre $existingSite as $assignment y procesa cada elemento de la coleccion.
            foreach ($existingSite as $assignment) {
                // Comprueba la condicion $siteId === null || (int) $assignment->sede_id !== (int) $siteId || ! in_array((int) $assignment->rol_id, $siteIds, true) antes de ejecutar este bloque.
                if ($siteId === null || (int) $assignment->sede_id !== (int) $siteId || ! in_array((int) $assignment->rol_id, $siteIds, true)) {
                    // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
                    DB::table('usuarios_sedes_roles')->where('usuario_id', $user->getKey())->where('sede_id', $assignment->sede_id)->where('rol_id', $assignment->rol_id)->update(['activo' => false]);
                }
            }

            // Recorre $selectedRoles as $role y procesa cada elemento de la coleccion.
            foreach ($selectedRoles as $role) {
                $attributes = ['asignado_por_usuario_id' => $request->user()->getKey(), 'asignado_en' => now(), 'vigente_hasta' => null, 'activo' => true];
                // Comprueba la condicion $role->alcance_permitido === 'sede' antes de ejecutar este bloque.
                if ($role->alcance_permitido === 'sede') {
                    // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
                    DB::table('usuarios_sedes_roles')->updateOrInsert(['usuario_id' => $user->getKey(), 'sede_id' => (int) $site->getKey(), 'rol_id' => $role->getKey()], $attributes);
                } else {
                    // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
                    DB::table('usuarios_roles')->updateOrInsert(['usuario_id' => $user->getKey(), 'rol_id' => $role->getKey()], $attributes);
                }
            }

            $auditoria->record($request->user()->getKey(), 'sincronizar_roles_usuario', 'usuarios', $user->getKey(), ['asignaciones' => $before], ['asignaciones' => $this->instantaneaAsignacion($user)], $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Los roles del usuario fueron actualizados.');
    }

    /**
     * Ejecuta la operación revokeGlobal definida por la clase AsignacionRolesController.
     *
     * @return mixed Resultado de la operación.
     */
    public function revokeGlobal(
        Request $request,
        User $user,
        Rol $role,
        ServicioDelegacion $delegacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        return $this->revocarAsignacion($request, $user, $role, null, $delegacion, $auditoria);
    }

    /**
     * Ejecuta la operación revokeAtSite definida por la clase AsignacionRolesController.
     *
     * @return mixed Resultado de la operación.
     */
    public function revokeAtSite(
        Request $request,
        User $user,
        Rol $role,
        Sede $site,
        ServicioDelegacion $delegacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        return $this->revocarAsignacion($request, $user, $role, $site, $delegacion, $auditoria);
    }

    /**
     * Revoca una asignación de rol respetando su alcance.
     *
     * @return mixed Resultado de la operación.
     */
    private function revocarAsignacion(
        Request $request,
        User $user,
        Rol $role,
        ?Sede $site,
        ServicioDelegacion $delegacion,
        RegistradorAuditoria $auditoria,
    ): RedirectResponse {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(app(ServicioAutorizacion::class)->allowsGlobal($request->user(), 'gestion.seguridad.revocar'), 403);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($delegacion->puedeAdministrarUsuario($request->user(), $user), 403, 'No puedes administrar una cuenta con privilegios superiores a los tuyos.');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($delegacion->puedeAsignarRol($request->user(), $role), 403, 'No puedes administrar una asignación con privilegios superiores a los tuyos.');
        // Comprueba la condicion $site !== null antes de ejecutar este bloque.
        if ($site !== null) {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless(app(ServicioAutorizacion::class)->allowsRecord($request->user(), 'gestion.seguridad.revocar', (int) $site->empresa_id, (int) $site->id), 403);
        }

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($request, $user, $role, $site, $delegacion, $auditoria): void {
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            // Guarda en $before el resultado necesario para continuar esta operacion.
            $before = $this->instantaneaAsignacion($user);
            // Comprueba la condicion $role->es_sistema && $site === null antes de ejecutar este bloque.
            if ($role->es_sistema && $site === null) {
                $delegacion->bloquearAdministradoresGlobales();
                // Detiene la solicitud si se cumple la validacion de seguridad indicada.
                abort_if($delegacion->esUltimoAdministradorGlobal($user), 422, 'No se puede retirar el último administrador global autorizado.');
            }

            // Comprueba la condicion $site instanceof Sede antes de ejecutar este bloque.
            if ($site instanceof Sede) {
                $updated = DB::table('usuarios_sedes_roles')->where('usuario_id', $user->getKey())->where('rol_id', $role->getKey())->where('sede_id', $site->getKey())->where('activo', true)->update(['activo' => false]);
            } else {
                $updated = DB::table('usuarios_roles')->where('usuario_id', $user->getKey())->where('rol_id', $role->getKey())->where('activo', true)->update(['activo' => false]);
            }
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if($updated !== 1, 422, 'La asignación no existe o ya está revocada.');

            $auditoria->record($request->user()->getKey(), 'revocar_rol_usuario', 'usuarios', $user->getKey(), ['asignaciones' => $before], ['asignaciones' => $this->instantaneaAsignacion($user)], $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'La asignación fue revocada y conservada para auditoría.');
    }

    /** @return array<int,array<string,mixed>> */
    private function instantaneaAsignacion(User $user): array
    {
        // Prepara en $global la consulta que obtiene o modifica los registros necesarios.
        $global = DB::table('usuarios_roles')->join('roles', 'roles.id', '=', 'usuarios_roles.rol_id')
            ->where('usuarios_roles.usuario_id', $user->getKey())
            ->get(['roles.codigo', 'usuarios_roles.activo', 'usuarios_roles.vigente_hasta'])
            ->map(static fn ($row): array => ['rol' => $row->codigo, 'sede_id' => null, 'activo' => (bool) $row->activo, 'vigente_hasta' => $row->vigente_hasta]);
        // Prepara en $sites la consulta que obtiene o modifica los registros necesarios.
        $sites = DB::table('usuarios_sedes_roles')->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
            ->where('usuarios_sedes_roles.usuario_id', $user->getKey())
            ->get(['roles.codigo', 'usuarios_sedes_roles.sede_id', 'usuarios_sedes_roles.activo', 'usuarios_sedes_roles.vigente_hasta'])
            ->map(static fn ($row): array => ['rol' => $row->codigo, 'sede_id' => (int) $row->sede_id, 'activo' => (bool) $row->activo, 'vigente_hasta' => $row->vigente_hasta]);

        return $global->concat($sites)->values()
            ->all();
    }
}
