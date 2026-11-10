<?php

namespace App\Http\Controllers\Cuenta\Perfil;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cuenta\Perfil\ActualizarAvatarRequest;
use App\Http\Requests\Cuenta\Perfil\ActualizarContrasenaRequest;
use App\Http\Requests\Cuenta\Perfil\ActualizarPerfilRequest;
use App\Http\Requests\Cuenta\Perfil\ActualizarPreferenciasRequest;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Notificaciones\ServicioNotificaciones;
use App\Servicios\Cuenta\Perfil\ServicioPerfil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase CuentaAdministracionController dentro de su m�dulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CuentaAdministracionController extends Controller
{
    /**
     * Actualiza la informaci�n existente con los datos recibidos.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @param  mixed  $audit  Par�metro recibido por la operaci�n.
     * @return RedirectResponse Resultado de la operaci�n.
     */
    public function updateProfile(ActualizarPerfilRequest $request, RegistradorAuditoria $audit, ServicioPerfil $perfil): RedirectResponse
    {
        $user = $request->user();
        $fields = $perfil->fieldsFor($user);
        $dynamicValues = $request->validated('perfil', []);
        $before = ['campos' => array_values(array_filter(['nombre', 'correo'], fn (string $field): bool => $user->getAttribute($field) !== ($field === 'correo' ? mb_strtolower($request->validated($field)) : $request->validated($field))))];
        DB::transaction(function () use ($user, $request, $audit, $before, $perfil, $fields, $dynamicValues): void {
            $user->update([
                'nombre' => $request->validated('nombre'),
                'correo' => mb_strtolower($request->validated('correo')),
            ]);
            $perfil->saveValues($user, $fields, $dynamicValues);
            $audit->record($user->getKey(), 'actualizar_perfil', 'usuarios', $user->getKey(), $before, ['campos' => $before['campos'], 'campos_dinamicos' => array_keys($dynamicValues)], $request->ip());
        });

        return back()->with('status', 'Perfil actualizado.');
    }

    /**
     * Actualiza la informaci�n existente con los datos recibidos.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @param  mixed  $audit  Par�metro recibido por la operaci�n.
     * @return RedirectResponse Resultado de la operaci�n.
     */
    public function updatePassword(ActualizarContrasenaRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $user = $request->user();
        DB::transaction(function () use ($request, $user, $audit): void {
            $user->update(['password' => Hash::make($request->validated('password'))]);
            if (Schema::hasTable('sesiones_usuarios')) {
                DB::table('sesiones_usuarios')->where('usuario_id', $user->getKey())->whereNull('revocada_en')
                    ->update(['revocada_en' => now(), 'actualizado_en' => now()]);
            }
            $audit->record($user->getKey(), 'actualizar_contrasena', 'usuarios', $user->getKey(), null, ['cambio' => true], $request->ip());
        });
        $request->session()->regenerate();

        return back()->with('status', 'Contrase�a actualizada.');
    }

    /**
     * Ejecuta la operaci�n security definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @return View Resultado de la operaci�n.
     */
    public function security(Request $request): View
    {
        $sessionsAvailable = Schema::hasTable('sesiones_usuarios');
        $sessions = $sessionsAvailable
            ? DB::table('sesiones_usuarios')->where('usuario_id', $request->user()->getKey())->orderByDesc('ultimo_acceso')->limit(20)->get()
            : collect();

        return view('cuenta.perfil.seguridad', array_merge(
            compact('sessions', 'sessionsAvailable'),
            $this->profileNavigation($request->user()),
        ));
    }

    /**
     * Ejecuta la operaci�n sessions definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @return View Resultado de la operaci�n.
     */
    public function sessions(Request $request): View
    {
        $available = Schema::hasTable('sesiones_usuarios');
        $currentHash = $request->hasSession() ? hash('sha256', $request->session()->getId()) : null;
        $sessions = $available
            ? DB::table('sesiones_usuarios')->where('usuario_id', $request->user()->getKey())->orderByDesc('ultimo_acceso')->paginate(20)->through(function (object $session) use ($currentHash): object {
                $session->dispositivo = Str::limit($session->agente_usuario ?: 'Agente no informado', 100);
                $session->ultimo_acceso_formateado = Carbon::parse($session->ultimo_acceso)->format('d/m/Y H:i');
                $session->estado = $session->revocada_en
                    ? 'Revocada'
                    : (hash_equals((string) $session->resumen_identificador, (string) $currentHash) ? 'Sesi�n actual' : 'Activa');
                $session->puede_revocarse = $session->revocada_en === null;

                return $session;
            })
            : collect();

        return view('cuenta.perfil.sesiones', array_merge(
            compact('sessions', 'available', 'currentHash'),
            $this->profileNavigation($request->user()),
        ));
    }

    /**
     * Ejecuta la operaci�n revokeSession definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @param  mixed  $sessionId  Par�metro recibido por la operaci�n.
     * @param  mixed  $audit  Par�metro recibido por la operaci�n.
     * @return RedirectResponse Resultado de la operaci�n.
     */
    public function revokeSession(Request $request, int $sessionId, RegistradorAuditoria $audit): RedirectResponse
    {
        abort_unless(Schema::hasTable('sesiones_usuarios'), 503, 'La auditor�a de sesiones requiere la migraci�n de seguridad.');
        $session = DB::table('sesiones_usuarios')->where('id', $sessionId)->where('usuario_id', $request->user()->getKey())->first();
        abort_if($session === null, 404);

        DB::transaction(function () use ($sessionId, $request, $audit): void {
            DB::table('sesiones_usuarios')->where('id', $sessionId)->update(['revocada_en' => now(), 'actualizado_en' => now()]);
            $audit->record($request->user()->getKey(), 'revocar_sesion', 'sesiones_usuarios', $sessionId, ['activa' => true], ['revocada' => true], $request->ip());
        });

        if (hash_equals((string) $session->resumen_identificador, hash('sha256', $request->session()->getId()))) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Sesi�n cerrada correctamente.');
        }

        return back()->with('status', 'La sesi�n seleccionada fue revocada.');
    }

    /**
     * Ejecuta la operaci�n preferences definida por la clase CuentaAdministracionController.
     *
     * @return View Resultado de la operaci�n.
     */
    public function preferences(Request $request): View
    {
        $preferencesAvailable = Schema::hasColumn('usuarios', 'preferencias');
        $preferences = (array) ($request->user()->preferencias ?? []);

        return view('cuenta.perfil.preferencias', array_merge(
            compact('preferencesAvailable', 'preferences'),
            $this->profileNavigation($request->user()),
        ));
    }

    /**
     * Actualiza la informaci�n existente con los datos recibidos.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @param  mixed  $audit  Par�metro recibido por la operaci�n.
     * @return RedirectResponse Resultado de la operaci�n.
     */
    public function updatePreferences(ActualizarPreferenciasRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        abort_unless(Schema::hasColumn('usuarios', 'preferencias'), 503, 'Las preferencias requieren la migraci�n de seguridad de cuenta.');
        $user = $request->user();
        $before = array_keys($request->validated());
        DB::transaction(function () use ($user, $request, $audit, $before): void {
            $user->update(['preferencias' => array_replace((array) $user->preferencias, $request->validated())]);
            $audit->record($user->getKey(), 'actualizar_preferencias', 'usuarios', $user->getKey(), ['campos' => $before], ['campos' => $before], $request->ip());
        });

        return back()->with('status', 'Preferencias guardadas.');
    }

    /**
     * Actualiza la informaci�n existente con los datos recibidos.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @param  mixed  $audit  Par�metro recibido por la operaci�n.
     * @return RedirectResponse Resultado de la operaci�n.
     */
    public function updateAvatar(ActualizarAvatarRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        abort_unless(Schema::hasColumn('usuarios', 'foto_perfil'), 503, 'La fotograf�a de perfil requiere la migraci�n de seguridad de cuenta.');
        $user = $request->user();
        $previousPath = $user->foto_perfil;
        $newPath = $request->file('foto_perfil')->storePublicly('avatars', 'public');

        try {
            DB::transaction(function () use ($user, $newPath, $request, $audit): void {
                $user->update(['foto_perfil' => $newPath]);
                $audit->record($user->getKey(), 'actualizar_fotografia_perfil', 'usuarios', $user->getKey(), null, ['fotografia_actualizada' => true], $request->ip());
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($newPath);
            throw $exception;
        }

        if (is_string($previousPath) && str_starts_with($previousPath, 'avatars/')) {
            Storage::disk('public')->delete($previousPath);
        }

        return back()->with('status', 'Fotograf�a de perfil actualizada.');
    }

    /**
     * Ejecuta la operaci�n activity definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @return View Resultado de la operaci�n.
     */
    public function deleteAvatar(Request $request, RegistradorAuditoria $audit): RedirectResponse
    {
        abort_unless(Schema::hasColumn('usuarios', 'foto_perfil'), 503, 'La fotografia de perfil requiere la migracion de seguridad de cuenta.');
        $user = $request->user();
        $previousPath = $user->foto_perfil;

        DB::transaction(function () use ($user, $request, $audit): void {
            $user->update(['foto_perfil' => null]);
            $audit->record($user->getKey(), 'eliminar_fotografia_perfil', 'usuarios', $user->getKey(), ['fotografia_actualizada' => true], ['fotografia_eliminada' => true], $request->ip());
        });

        if (is_string($previousPath) && str_starts_with($previousPath, 'avatars/')) {
            Storage::disk('public')->delete($previousPath);
        }

        return back()->with('status', 'Fotografia de perfil eliminada.');
    }

    public function activity(Request $request): View
    {
        $available = Schema::hasTable('registros_auditoria');
        $activity = $available
            ? RegistroAuditoria::query()->where('usuario_id', $request->user()->getKey())->latest('creado_en')->paginate(25)
            : collect();

        return view('cuenta.perfil.actividad', array_merge(
            compact('activity', 'available'),
            $this->profileNavigation($request->user()),
        ));
    }

    /**
     * Ejecuta la operaci�n notifications definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @param  mixed  $servicio  Par�metro recibido por la operaci�n.
     * @return View Resultado de la operaci�n.
     */
    public function notifications(Request $request, ServicioNotificaciones $servicio): View
    {
        $available = Schema::hasTable('notificaciones');
        $notificaciones = $available ? $servicio->listarDelUsuario($request->user()) : collect();

        return view('cuenta.perfil.notificaciones', array_merge(
            compact('notificaciones', 'available'),
            $this->profileNavigation($request->user()),
        ));
    }

    /**
     * Ejecuta la operaci�n markNotificationRead definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @param  mixed  $notificationId  Par�metro recibido por la operaci�n.
     * @param  mixed  $servicio  Par�metro recibido por la operaci�n.
     * @return RedirectResponse Resultado de la operaci�n.
     */
    public function markNotificationRead(Request $request, string $notificationId, ServicioNotificaciones $servicio): RedirectResponse
    {
        abort_unless(Schema::hasTable('notificaciones'), 503, 'La tabla de notificaciones no est� disponible.');
        $marcadaComoLeida = $servicio->marcarComoLeida($request->user(), $notificationId, $request->ip());

        return back()->with('status', $marcadaComoLeida
            ? 'Notificaci�n marcada como le�da.'
            : 'La notificaci�n ya estaba le�da.');
    }

    /**
     * Prepara las rutas de navegación compartidas por las pantallas de cuenta.
     *
     * @return array{profileHomeRoute:string}
     */
    private function profileNavigation(User $user): array
    {
        return [
            'profileHomeRoute' => $user->can('gestion.seguridad.consultar')
                ? 'management.account'
                : 'account.profile.show',
        ];
    }
}
