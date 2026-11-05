<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermiso
{
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

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
