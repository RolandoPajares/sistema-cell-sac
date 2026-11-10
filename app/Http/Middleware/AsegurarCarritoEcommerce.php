<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Autoriza el uso del carrito para clientes propios o usuarios con permiso de pedidos.
 */
final class AsegurarCarritoEcommerce
{
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    /**
     * Permite el carrito a clientes mayoristas/minoristas o a operadores autorizados.
     *
     * Los clientes se autorizan por su rol propio; los demás usuarios deben tener
     * `gestion.pedidos.crear`; el rol de cliente no recibe ese permiso administrativo.
     * No modifica sesión, inventario ni pedidos.
     *
     * @param  Request  $request  Solicitud autenticada del carrito.
     * @param  Closure(Request): Response  $next  Siguiente etapa del pipeline HTTP.
     * @return Response Respuesta producida por el controlador siguiente.
     *
     * @throws HttpException Si no existe una autorización válida.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $next($request);
        }

        $esCliente = $this->autorizacion->allows($user, 'gestion.cotizaciones.consultar')
            || $this->autorizacion->allows($user, 'gestion.pedidos.consultar');

        abort_unless($esCliente || $this->autorizacion->allows($user, 'gestion.pedidos.crear'), 403);

        return $next($request);
    }
}
