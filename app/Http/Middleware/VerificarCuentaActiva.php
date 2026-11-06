<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Define la responsabilidad de la clase VerificarCuentaActiva dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class VerificarCuentaActiva
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user !== null && Schema::hasColumn('usuarios', 'activo')) {
            $activeStatus = $user->getAttribute('activo');
            $isActive = $activeStatus === null || (bool) $activeStatus;
            $isLocked = $user->getAttribute('bloqueado_hasta') !== null
                && now()->lessThan($user->getAttribute('bloqueado_hasta'));

            if (! $isActive || $isLocked) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['correo' => 'La cuenta está inactiva o temporalmente bloqueada.']);
            }
        }

        return $next($request);
    }
}
