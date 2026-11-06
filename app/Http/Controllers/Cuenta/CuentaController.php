<?php

namespace App\Http\Controllers\Cuenta;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paneles\ResolutorDashboard;
use App\Models\User;
use Illuminate\Http\Request;
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
    public function __construct(private readonly ResolutorDashboard $dashboardResolver) {}

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

        if ($user->isRole('administrador')) {
            $user->loadMissing('customer');

            return view('paneles.administrador.perfil.editar', [
                'role' => 'administrador',
            ]);
        }

        return ($this->dashboardResolver->for($user))($request);
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
    public function profile(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $user->loadMissing('customer');

        $profileViews = [
            'administrador' => 'paneles.administrador.perfil.mostrar',
            'compras_logistica' => 'paneles.compras-logistica.perfil.mostrar',
            'ventas_mayoristas' => 'paneles.ventas-mayoristas.perfil.mostrar',
            'ventas_minoristas' => 'paneles.ventas-minoristas.perfil.mostrar',
            'marketing' => 'paneles.marketing.perfil.mostrar',
            'cliente_mayorista' => 'paneles.cliente-mayorista.perfil.mostrar',
            'cliente_minorista' => 'paneles.cliente-minorista.perfil.mostrar',
        ];

        return view($profileViews[$user->rol] ?? $profileViews['cliente_minorista'], [
            'role' => $user->rol,
        ]);
    }
}
