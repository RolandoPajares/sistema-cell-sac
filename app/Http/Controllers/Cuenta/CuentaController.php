<?php

namespace App\Http\Controllers\Cuenta;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Paneles\ResolutorDashboard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CuentaController extends Controller
{
    public function __construct(private readonly ResolutorDashboard $dashboardResolver) {}

    public function dashboard(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        if ($user->rol === 'administrador') {
            return view('paneles.administrador.perfil.editar', [
                'role' => $user->rol,
            ]);
        }

        return ($this->dashboardResolver->for($user))($request);
    }
}
