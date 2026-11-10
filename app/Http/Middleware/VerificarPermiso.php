<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Define la responsabilidad de la clase VerificarPermiso dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class VerificarPermiso
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    /**
     * Autoriza la solicitud mediante permisos explícitos o mediante el permiso de su ruta.
     *
     * Rechaza con HTTP 403 las solicitudes sin usuario autenticado, sin permisos declarados
     * o que no superan alguna comprobación RBAC. Cuando autoriza, deja el permiso efectivo
     * en los atributos de la solicitud para que las capas posteriores lo reutilicen.
     *
     * @param  Request  $request  Solicitud HTTP que se va a autorizar.
     * @param  Closure  $next  Continuación del pipeline HTTP.
     * @param  string  ...$permissions  Códigos de permiso o el marcador especial `ruta`.
     * @return Response Respuesta producida por el siguiente middleware o controlador.
     *
     * @throws HttpException Si la autorización falla.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        if ($permissions === ['ruta']) {
            abort_unless($this->autorizacion->allowsRoute($request), 403);
            $request->attributes->set('permiso_contexto', $this->autorizacion->permissionForRoute($request));
        } else {
            abort_unless($permissions !== [], 403);
            foreach ($permissions as $permission) {
                abort_unless($this->autorizacion->allows($user, $permission), 403);
            }
            $request->attributes->set('permiso_contexto', $permissions[0]);
        }

        return $next($request);
    }
}
