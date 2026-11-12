<?php

namespace App\Http\Controllers\Cuenta\Perfil;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Servicios\Compartidos\Paneles\ServicioPaneles;
use App\Servicios\Cuenta\Perfil\ServicioPerfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase CuentaController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CuentaController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $dashboardResolver  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioPaneles $panelService) {}

    /**
     * Ejecuta la operación dashboard definida por la clase CuentaController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        return view('paneles.dashboard', $this->panelService->roleDashboard(
            $user,
            (array) $request->session()->get('cart', []),
        ));
    }

    /**
     * Muestra el perfil personal con la misma estructura para todos los roles.
     *
     * Las operaciones de actualización continúan delegándose a las rutas
     * existentes de cuenta; este método solo resuelve la vista correspondiente.
     *
     * @param  Request  $request  Solicitud autenticada de la cuenta actual.
     * @return View Vista de perfil correspondiente al rol del usuario.
     */
    public function profile(Request $request, ServicioPerfil $perfil): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $user->loadMissing('customer');
        $roles = $perfil->rolesPara($user);
        $dynamicFields = $perfil->camposPara($user);
        $dynamicValues = $perfil->valoresPara($user, $dynamicFields);
        $roleLabel = $roles->pluck('nombre')->filter()->unique()->implode(' · ') ?: 'Usuario autorizado';
        $functions = $perfil->funcionesPara($user);

        $isAdministrator = $user->can('gestion.seguridad.consultar');
        $preferences = (array) ($user->preferencias ?? []);
        $profile = Schema::hasTable('usuarios_perfiles') ? $user->perfil : null;
        $customer = $user->customer;
        $profileValues = [$user->nombre, $user->correo, $profile?->telefono, $profile?->documento, $user->foto_perfil, $preferences['idioma'] ?? null, ...array_values($dynamicValues)];
        $initials = mb_strtoupper(collect(preg_split('/\s+/', trim((string) $user->nombre)) ?: [])
            ->map(static fn (string $part): string => mb_substr($part, 0, 1))
            ->take(2)
            ->implode(''));

        return view('cuenta.perfil.mostrar', [
            'userName' => (string) $user->nombre,
            'user' => $user,
            'roleLabel' => $roleLabel,
            'roles' => $roles,
            'functions' => $functions,
            'dynamicFields' => $dynamicFields,
            'dynamicValues' => $dynamicValues,
            'isAdministrator' => $isAdministrator,
            'profileRoute' => $isAdministrator ? 'management.account.profile' : 'account.profile',
            'passwordRoute' => $isAdministrator ? 'management.account.password' : 'account.password',
            'accountHomeRoute' => $isAdministrator ? 'management.account' : 'dashboard',
            'preferences' => $preferences,
            'phone' => $profile?->telefono ?: 'No registrado',
            'document' => $profile?->documento ?: 'No registrado',
            'primarySite' => $perfil->sedePrincipalPara($user) ?: 'No registrada',
            'photoUrl' => $user->foto_perfil ? Storage::disk('public')->url($user->foto_perfil) : null,
            'avatarRoute' => 'management.account.avatar',
            'avatarDeleteRoute' => 'management.account.avatar.delete',
            'securityRoute' => 'management.account.security',
            'sessionsRoute' => 'management.account.sessions',
            'preferencesRoute' => 'management.account.preferences.update',
            'profileCompletion' => (int) round((count(array_filter($profileValues, static fn (mixed $value): bool => filled($value))) / count($profileValues)) * 100),
            'initials' => $initials,
        ]);
    }
}
