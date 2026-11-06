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
            return view('paneles.administrador.perfil.editar', [
                'role' => 'administrador',
            ]);
        }

        return ($this->dashboardResolver->for($user))($request);
    }
}
