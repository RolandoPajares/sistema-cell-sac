<?php

namespace App\Http\Middleware;

use App\Servicios\ServicioAutorizacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Define la responsabilidad de la clase AsegurarRol dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AsegurarRol
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    /**
     * Ejecuta la operación principal asignada al componente.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $next  Parámetro recibido por la operación.
     * @param  mixed  $roles  Parámetro recibido por la operación.
     * @return Response Resultado de la operación.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $roleContext = $user === null ? null : collect($roles)->first(
            fn (string $role): bool => $this->autorizacion->hasRole($user, $role),
        );
        abort_unless($roleContext !== null, 403);
        $request->attributes->set('rol_contexto', $roleContext);

        return $next($request);
    }
}
