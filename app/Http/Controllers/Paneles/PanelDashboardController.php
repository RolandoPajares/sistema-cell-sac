<?php

namespace App\Http\Controllers\Paneles;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Servicios\ServicioPaneles;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase PanelDashboardController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
abstract class PanelDashboardController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $panelService  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(protected readonly ServicioPaneles $panelService) {}

    /**
     * Ejecuta la operación __invoke definida por la clase PanelDashboardController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    final public function __invoke(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        return view($this->viewName(), $this->viewData($request, $user));
    }

    /**
     * Ejecuta la operación role definida por la clase PanelDashboardController.
     *
     * @return string Resultado de la operación.
     */
    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @return string Resultado de la operación.
     */
    abstract protected function viewName(): string;

    /**
     * @return array<string, mixed>
     */
    protected function viewData(Request $request, User $user): array
    {
        return $this->panelService->roleDashboard(
            $user,
            (array) $request->session()->get('cart', []),
        );
    }
}
