<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * Ejecuta la operación principal asignada al componente.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $next  Parámetro recibido por la operación.
     * @param  mixed  $permissions  Parámetro recibido por la operación.
     * @return Response Resultado de la operación.
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
