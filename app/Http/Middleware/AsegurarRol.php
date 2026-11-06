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
     * Permite continuar solo si el usuario posee uno de los roles requeridos por la ruta.
     *
     * Evalúa los roles recibidos en el orden declarado, guarda el primer rol coincidente
     * como contexto de la solicitud y devuelve HTTP 403 cuando no existe coincidencia.
     *
     * @param Request $request Solicitud HTTP que se va a autorizar.
     * @param Closure $next Continuación del pipeline HTTP.
     * @param string ...$roles Roles aceptados por la ruta.
     * @return Response Respuesta producida por el siguiente middleware o controlador.
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Si el usuario no tiene un rol requerido.
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
