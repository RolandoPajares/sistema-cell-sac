<?php

namespace App\Http\Middleware;

use App\Servicios\ServicioAutorizacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AsegurarRol
{
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

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
