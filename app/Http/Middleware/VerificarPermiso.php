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
        // Obtiene el usuario autenticado asociado a la solicitud actual.
        $user = $request->user();

        // Rechaza la solicitud si no existe un usuario valido para comprobar permisos.
        abort_unless($user instanceof User, 403);

        // Cuando la ruta usa el marcador 'ruta', consulta automaticamente el permiso configurado para esa URL.
        if ($permissions === ['ruta']) {
            // Detiene la solicitud si el usuario no tiene permiso para acceder a la ruta solicitada.
            abort_unless($this->autorizacion->allowsRoute($request), 403);

            // Guarda el permiso resuelto en la solicitud para que el controlador y la auditoria puedan reutilizarlo.
            $request->attributes->set('permiso_contexto', $this->autorizacion->permissionForRoute($request));
        } else {
            // Rechaza la configuracion del middleware cuando no se indico ningun permiso explicito.
            abort_unless($permissions !== [], 403);

            // Comprueba uno por uno los permisos declarados en la ruta.
            foreach ($permissions as $permission) {
                // Verifica si el usuario actual puede ejecutar el permiso que se esta recorriendo.
                abort_unless($this->autorizacion->allows($user, $permission), 403);
            }

            // Guarda el primer permiso autorizado como contexto para las capas posteriores.
            $request->attributes->set('permiso_contexto', $permissions[0]);
        }

        // Continua el recorrido de la solicitud hacia el siguiente middleware o controlador.
        return $next($request);
    }
}
