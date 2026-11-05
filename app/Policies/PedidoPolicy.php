<?php

namespace App\Policies;

use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Servicios\ServicioAutorizacion;

class PedidoPolicy
{
    public function view(User $user, Pedido $order): bool
    {
        $autorizacion = app(ServicioAutorizacion::class);

        $canAccessRecord = $autorizacion->allowsRecord(
            $user,
            'gestion.pedidos.consultar',
            is_numeric($order->empresa_id) ? (int) $order->empresa_id : null,
            is_numeric($order->sede_id) ? (int) $order->sede_id : null,
            is_numeric($order->usuario_id) ? (int) $order->usuario_id : null,
        );

        if (! $canAccessRecord) {
            return false;
        }

        if ((int) $order->usuario_id === (int) $user->getKey()) {
            return true;
        }

        if ($autorizacion->allowsGlobal($user, 'gestion.pedidos.consultar')) {
            return true;
        }

        return $order->user !== null
            && (($autorizacion->hasRole($order->user, 'cliente_mayorista') && $autorizacion->hasRole($user, 'ventas_mayoristas'))
                || ($autorizacion->hasRole($order->user, 'cliente_minorista') && $autorizacion->hasRole($user, 'ventas_minoristas')));
    }

    public function updateStatus(User $user, Pedido $order): bool
    {
        $autorizacion = app(ServicioAutorizacion::class);

        return $autorizacion->allowsRecord(
            $user,
            'gestion.pedidos.actualizar',
            is_numeric($order->empresa_id) ? (int) $order->empresa_id : null,
            is_numeric($order->sede_id) ? (int) $order->sede_id : null,
        );
    }
}
