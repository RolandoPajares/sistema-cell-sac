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
    public function updateProfile(ActualizarPerfilRequest $request, RegistradorAuditoria $audit, ServicioPerfil $perfil): RedirectResponse
    {
        // Obtiene en $user la informacion de la solicitud o del usuario actual.
        $user = $request->user();
        // Guarda en $fields el resultado necesario para continuar esta operacion.
        $fields = $perfil->camposPara($user);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $dynamicValues = $request->validated('perfil', []);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $before = ['campos' => array_values(array_filter(['nombre', 'correo'], fn (string $field): bool => $user->getAttribute($field) !== ($field === 'correo' ? mb_strtolower($request->validated($field)) : $request->validated($field))))];
        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($user, $request, $audit, $before, $perfil, $fields, $dynamicValues): void {
            $user->update([
                // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
                'nombre' => $request->validated('nombre'),
                // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
                'correo' => mb_strtolower($request->validated('correo')),
            ]);
            // Comprueba la condicion Schema::hasTable('usuarios_perfiles') antes de ejecutar este bloque.
            if (Schema::hasTable('usuarios_perfiles')) {
                $user->perfil()->updateOrCreate([], [
                    // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
                    'telefono' => $request->validated('telefono'),
                    // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
                    'documento' => $request->validated('documento'),
                ]);
            }
            $perfil->guardarValores($user, $fields, $dynamicValues);
            $audit->record($user->getKey(), 'actualizar_perfil', 'usuarios', $user->getKey(), $before, ['campos' => $before['campos'], 'campos_dinamicos' => array_keys($dynamicValues)], $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Obtiene en $user la informacion de la solicitud o del usuario actual.
        $user = $request->user();
        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($request, $user, $audit): void {
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            $user->update(['password' => Hash::make($request->validated('password'))]);
            // Comprueba la condicion Schema::hasTable('sesiones_usuarios') antes de ejecutar este bloque.
            if (Schema::hasTable('sesiones_usuarios')) {
                // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
                DB::table('sesiones_usuarios')->where('usuario_id', $user->getKey())->whereNull('revocada_en')
                    ->update(['revocada_en' => now(), 'actualizado_en' => now()]);
            }
            $audit->record($user->getKey(), 'actualizar_contrasena', 'usuarios', $user->getKey(), null, ['cambio' => true], $request->ip());
        });
        $request->session()->regenerate();

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Guarda en $sessionsAvailable el resultado necesario para continuar esta operacion.
        $sessionsAvailable = Schema::hasTable('sesiones_usuarios');
        // Guarda en $sessions el resultado necesario para continuar esta operacion.
        $sessions = $sessionsAvailable
            ? DB::table('sesiones_usuarios')->where('usuario_id', $request->user()->getKey())->orderByDesc('ultimo_acceso')->limit(20)->get()
            : collect();

        // Devuelve la vista 'cuenta.perfil.seguridad' con los datos preparados por este controlador.
        return view('cuenta.perfil.seguridad', array_merge(
            compact('sessions', 'sessionsAvailable'),
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->navegacionPerfil($request->user()),
        ));
    }

    /**
     * Ejecuta la operación sessions definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function sessions(Request $request): View
    {
        // Guarda en $available el resultado necesario para continuar esta operacion.
        $available = Schema::hasTable('sesiones_usuarios');
        // Guarda en $currentHash el resultado necesario para continuar esta operacion.
        $currentHash = $request->hasSession() ? hash('sha256', $request->session()->getId()) : null;
        // Guarda en $sessions el resultado necesario para continuar esta operacion.
        $sessions = $available
            ? DB::table('sesiones_usuarios')->where('usuario_id', $request->user()->getKey())->orderByDesc('ultimo_acceso')->paginate(20)->through(function (object $session) use ($currentHash): object {
                $session->dispositivo = Str::limit($session->agente_usuario ?: 'Agente no informado', 100);
                $session->ultimo_acceso_formateado = Carbon::parse($session->ultimo_acceso)->format('d/m/Y H:i');
                $session->estado = $session->revocada_en
                    ? 'Revocada'
                    : (hash_equals((string) $session->resumen_identificador, (string) $currentHash) ? 'Sesión actual' : 'Activa');
                $session->puede_revocarse = $session->revocada_en === null;

                return $session;
            })
            : collect();

        // Devuelve la vista 'cuenta.perfil.sesiones' con los datos preparados por este controlador.
        return view('cuenta.perfil.sesiones', array_merge(
            compact('sessions', 'available', 'currentHash'),
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->navegacionPerfil($request->user()),
        ));
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasTable('sesiones_usuarios'), 503, 'La auditoría de sesiones requiere la migración de seguridad.');
        // Prepara en $session la consulta que obtiene o modifica los registros necesarios.
        $session = DB::table('sesiones_usuarios')->where('id', $sessionId)->where('usuario_id', $request->user()->getKey())->first();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if($session === null, 404);

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($sessionId, $request, $audit): void {
            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::table('sesiones_usuarios')->where('id', $sessionId)->update(['revocada_en' => now(), 'actualizado_en' => now()]);
            $audit->record($request->user()->getKey(), 'revocar_sesion', 'sesiones_usuarios', $sessionId, ['activa' => true], ['revocada' => true], $request->ip());
        });

        // Comprueba la condicion hash_equals((string) $session->resumen_identificador, hash('sha256', $request->session()->getId())) antes de ejecutar este bloque.
        if (hash_equals((string) $session->resumen_identificador, hash('sha256', $request->session()->getId()))) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
            return redirect()->route('login')->with('status', 'Sesión cerrada correctamente.');
        }

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'La sesión seleccionada fue revocada.');
    }

    /**
     * Ejecuta la operación preferences definida por la clase CuentaAdministracionController.
     *
     * @return View Resultado de la operación.
     */
    public function preferences(Request $request): View
    {
        // Guarda en $preferencesAvailable el resultado necesario para continuar esta operacion.
        $preferencesAvailable = Schema::hasColumn('usuarios', 'preferencias');
        // Obtiene en $preferences la informacion de la solicitud o del usuario actual.
        $preferences = (array) ($request->user()->preferencias ?? []);

        // Devuelve la vista 'cuenta.perfil.preferencias' con los datos preparados por este controlador.
        return view('cuenta.perfil.preferencias', array_merge(
            compact('preferencesAvailable', 'preferences'),
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->navegacionPerfil($request->user()),
        ));
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasColumn('usuarios', 'preferencias'), 503, 'Las preferencias requieren la migración de seguridad de cuenta.');
        // Obtiene en $user la informacion de la solicitud o del usuario actual.
        $user = $request->user();
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $before = array_keys($request->validated());
        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($user, $request, $audit, $before): void {
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            $user->update(['preferencias' => array_replace((array) $user->preferencias, $request->validated())]);
            $audit->record($user->getKey(), 'actualizar_preferencias', 'usuarios', $user->getKey(), ['campos' => $before], ['campos' => $before], $request->ip());
        });

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasColumn('usuarios', 'foto_perfil'), 503, 'La fotografía de perfil requiere la migración de seguridad de cuenta.');
        // Obtiene en $user la informacion de la solicitud o del usuario actual.
        $user = $request->user();
        // Guarda en $previousPath el resultado necesario para continuar esta operacion.
        $previousPath = $user->foto_perfil;
        // Guarda en $newPath el resultado necesario para continuar esta operacion.
        $newPath = $request->file('foto_perfil')->storePublicly('avatars', 'public');

        // Ejecuta la operacion dentro de un bloque controlado para atender posibles excepciones.
        try {
            // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
            DB::transaction(function () use ($user, $newPath, $request, $audit): void {
                $user->update(['foto_perfil' => $newPath]);
                $audit->record($user->getKey(), 'actualizar_fotografia_perfil', 'usuarios', $user->getKey(), null, ['fotografia_actualizada' => true], $request->ip());
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($newPath);
            throw $exception;
        }

        // Comprueba la condicion is_string($previousPath) && str_starts_with($previousPath, 'avatars/') antes de ejecutar este bloque.
        if (is_string($previousPath) && str_starts_with($previousPath, 'avatars/')) {
            Storage::disk('public')->delete($previousPath);
        }

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Fotografía de perfil actualizada.');
    }

    /**
     * Ejecuta la operación activity definida por la clase CuentaAdministracionController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function deleteAvatar(Request $request, RegistradorAuditoria $audit): RedirectResponse
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasColumn('usuarios', 'foto_perfil'), 503, 'La fotografia de perfil requiere la migracion de seguridad de cuenta.');
        // Obtiene en $user la informacion de la solicitud o del usuario actual.
        $user = $request->user();
        // Guarda en $previousPath el resultado necesario para continuar esta operacion.
        $previousPath = $user->foto_perfil;

        // Ejecuta la consulta o modificacion de base de datos indicada en la instruccion siguiente.
        DB::transaction(function () use ($user, $request, $audit): void {
            $user->update(['foto_perfil' => null]);
            $audit->record($user->getKey(), 'eliminar_fotografia_perfil', 'usuarios', $user->getKey(), ['fotografia_actualizada' => true], ['fotografia_eliminada' => true], $request->ip());
        });

        // Comprueba la condicion is_string($previousPath) && str_starts_with($previousPath, 'avatars/') antes de ejecutar este bloque.
        if (is_string($previousPath) && str_starts_with($previousPath, 'avatars/')) {
            Storage::disk('public')->delete($previousPath);
        }

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Fotografia de perfil eliminada.');
    }

    public function activity(Request $request): View
    {
        // Guarda en $available el resultado necesario para continuar esta operacion.
        $available = Schema::hasTable('registros_auditoria');
        // Guarda en $activity el resultado necesario para continuar esta operacion.
        $activity = $available
            ? RegistroAuditoria::query()->where('usuario_id', $request->user()->getKey())->latest('creado_en')->paginate(25)
            : collect();

        // Devuelve la vista 'cuenta.perfil.actividad' con los datos preparados por este controlador.
        return view('cuenta.perfil.actividad', array_merge(
            compact('activity', 'available'),
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->navegacionPerfil($request->user()),
        ));
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
        // Guarda en $available el resultado necesario para continuar esta operacion.
        $available = Schema::hasTable('notificaciones');
        // Obtiene en $notificaciones la informacion de la solicitud o del usuario actual.
        $notificaciones = $available ? $servicio->listarDelUsuario($request->user()) : collect();

        // Devuelve la vista 'cuenta.perfil.notificaciones' con los datos preparados por este controlador.
        return view('cuenta.perfil.notificaciones', array_merge(
            compact('notificaciones', 'available'),
            // Solicita al servicio o dependencia la operacion indicada en esta instruccion.
            $this->navegacionPerfil($request->user()),
        ));
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasTable('notificaciones'), 503, 'La tabla de notificaciones no está disponible.');
        // Obtiene en $marcadaComoLeida la informacion de la solicitud o del usuario actual.
        $marcadaComoLeida = $servicio->marcarComoLeida($request->user(), $notificationId, $request->ip());

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', $marcadaComoLeida
            ? 'Notificación marcada como leída.'
            : 'La notificación ya estaba leída.');
    }

    /**
     * Prepara las rutas de navegación compartidas por las pantallas de cuenta.
     *
     * @return array{profileHomeRoute:string}
     */
    private function navegacionPerfil(User $user): array
    {
        return [
            'profileHomeRoute' => $user->can('gestion.seguridad.consultar')
                ? 'management.account.profile.show'
                : 'account.profile.show',
        ];
    }
}
