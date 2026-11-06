<?php

namespace App\Http\Controllers\Cuenta;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarAvatarRequest;
use App\Http\Requests\ActualizarContrasenaRequest;
use App\Http\Requests\ActualizarPerfilRequest;
use App\Http\Requests\ActualizarPreferenciasRequest;
use App\Models\Auditoria\RegistroAuditoria;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioNotificaciones;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase CuentaAdministracionController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CuentaAdministracionController extends Controller
{
    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function updateProfile(ActualizarPerfilRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        $user = $request->user();
        $before = ['campos' => array_values(array_filter(['nombre', 'correo'], fn (string $field): bool => $user->getAttribute($field) !== ($field === 'correo' ? mb_strtolower($request->validated($field)) : $request->validated($field))))];
        DB::transaction(function () use ($user, $request, $audit, $before): void {
            $user->update([
                'nombre' => $request->validated('nombre'),
                'correo' => mb_strtolower($request->validated('correo')),
            ]);
            $audit->record($user->getKey(), 'actualizar_perfil', 'usuarios', $user->getKey(), $before, ['campos' => $before['campos']], $request->ip());
        });

        return back()->with('status', 'Perfil actualizado.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
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

        return back()->with('status', 'Contraseña actualizada.');
    }

    /**
     * Ejecuta la operación security definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function security(Request $request): View
    {
        $sessionsAvailable = Schema::hasTable('sesiones_usuarios');
        $sessions = $sessionsAvailable
            ? DB::table('sesiones_usuarios')->where('usuario_id', $request->user()->getKey())->orderByDesc('ultimo_acceso')->limit(20)->get()
            : collect();

        return view('paneles.administrador.perfil.seguridad', compact('sessions', 'sessionsAvailable'));
    }

    /**
     * Ejecuta la operación sessions definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function sessions(Request $request): View
    {
        $available = Schema::hasTable('sesiones_usuarios');
        $currentHash = $request->hasSession() ? hash('sha256', $request->session()->getId()) : null;
        $sessions = $available
            ? DB::table('sesiones_usuarios')->where('usuario_id', $request->user()->getKey())->orderByDesc('ultimo_acceso')->paginate(20)
            : collect();

        return view('paneles.administrador.perfil.sesiones', compact('sessions', 'available', 'currentHash'));
    }

    /**
     * Ejecuta la operación revokeSession definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $sessionId  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function revokeSession(Request $request, int $sessionId, RegistradorAuditoria $audit): RedirectResponse
    {
        abort_unless(Schema::hasTable('sesiones_usuarios'), 503, 'La auditoría de sesiones requiere la migración de seguridad.');
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

            return redirect()->route('login')->with('status', 'Sesión cerrada correctamente.');
        }

        return back()->with('status', 'La sesión seleccionada fue revocada.');
    }

    /**
     * Ejecuta la operación preferences definida por la clase CuentaAdministracionController.
     *
     * @return View Resultado de la operación.
     */
    public function preferences(): View
    {
        $preferencesAvailable = Schema::hasColumn('usuarios', 'preferencias');

        return view('paneles.administrador.perfil.preferencias', compact('preferencesAvailable'));
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function updatePreferences(ActualizarPreferenciasRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        abort_unless(Schema::hasColumn('usuarios', 'preferencias'), 503, 'Las preferencias requieren la migración de seguridad de cuenta.');
        $user = $request->user();
        $before = array_keys($request->validated());
        DB::transaction(function () use ($user, $request, $audit, $before): void {
            $user->update(['preferencias' => array_replace((array) $user->preferencias, $request->validated())]);
            $audit->record($user->getKey(), 'actualizar_preferencias', 'usuarios', $user->getKey(), ['campos' => $before], ['campos' => $before], $request->ip());
        });

        return back()->with('status', 'Preferencias guardadas.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $audit  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function updateAvatar(ActualizarAvatarRequest $request, RegistradorAuditoria $audit): RedirectResponse
    {
        abort_unless(Schema::hasColumn('usuarios', 'foto_perfil'), 503, 'La fotografía de perfil requiere la migración de seguridad de cuenta.');
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

        return back()->with('status', 'Fotografía de perfil actualizada.');
    }

    /**
     * Ejecuta la operación activity definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function activity(Request $request): View
    {
        $available = Schema::hasTable('registros_auditoria');
        $activity = $available
            ? RegistroAuditoria::query()->where('usuario_id', $request->user()->getKey())->latest('creado_en')->paginate(25)
            : collect();

        return view('paneles.administrador.perfil.actividad', compact('activity', 'available'));
    }

    /**
     * Ejecuta la operación notifications definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $servicio  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function notifications(Request $request, ServicioNotificaciones $servicio): View
    {
        $available = Schema::hasTable('notificaciones');
        $notificaciones = $available ? $servicio->listarDelUsuario($request->user()) : collect();

        return view('paneles.administrador.perfil.notificaciones', compact('notificaciones', 'available'));
    }

    /**
     * Ejecuta la operación markNotificationRead definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $notificationId  Parámetro recibido por la operación.
     * @param  mixed  $servicio  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function markNotificationRead(Request $request, string $notificationId, ServicioNotificaciones $servicio): RedirectResponse
    {
        abort_unless(Schema::hasTable('notificaciones'), 503, 'La tabla de notificaciones no está disponible.');
        $marcadaComoLeida = $servicio->marcarComoLeida($request->user(), $notificationId, $request->ip());

        return back()->with('status', $marcadaComoLeida
            ? 'Notificación marcada como leída.'
            : 'La notificación ya estaba leída.');
    }
}
