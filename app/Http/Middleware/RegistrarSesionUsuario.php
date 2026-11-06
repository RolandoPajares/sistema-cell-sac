<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Define la responsabilidad de la clase RegistrarSesionUsuario dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class RegistrarSesionUsuario
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null || ! Schema::hasTable('sesiones_usuarios') || ! $request->hasSession()) {
            return $next($request);
        }

        $sessionId = $request->session()->getId();
        $sessionHash = hash('sha256', $sessionId);
        $session = DB::table('sesiones_usuarios')->where('resumen_identificador', $sessionHash)->first();

        if ($session !== null && $session->revocada_en !== null) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['correo' => 'La sesión ya no está activa. Inicia sesión nuevamente.']);
        }

        DB::table('sesiones_usuarios')->updateOrInsert(
            ['resumen_identificador' => $sessionHash],
            [
                'usuario_id' => $user->getKey(),
                'direccion_ip' => $request->ip(),
                'agente_usuario' => mb_substr((string) $request->userAgent(), 0, 1000),
                'ultimo_acceso' => now(),
                'revocada_en' => null,
                'actualizado_en' => now(),
                'creado_en' => $session === null ? now() : $session->creado_en,
            ],
        );

        return $next($request);
    }
}
