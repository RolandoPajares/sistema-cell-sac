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
        // Obtiene el usuario autenticado para asociar la sesion con su cuenta.
        $user = $request->user();

        // Omite el registro si no hay usuario, tabla de sesiones o sesion HTTP disponible.
        if ($user === null || ! Schema::hasTable('sesiones_usuarios') || ! $request->hasSession()) {
            return $next($request);
        }

        // Obtiene el identificador de la sesion y crea un resumen seguro para almacenarlo.
        $sessionId = $request->session()->getId();
        $sessionHash = hash('sha256', $sessionId);

        // Busca si la sesion actual existe y si fue revocada por el sistema o por el usuario.
        $session = DB::table('sesiones_usuarios')->where('resumen_identificador', $sessionHash)->first();

        // Cierra la sesion si el registro fue marcado como revocado.
        if ($session !== null && $session->revocada_en !== null) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['correo' => 'La sesión ya no está activa. Inicia sesión nuevamente.']);
        }

        // Crea o actualiza el registro de sesion con usuario, IP, navegador y fecha de acceso.
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
