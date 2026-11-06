<?php

namespace App\Policies;

use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Servicios\ServicioAutorizacion;

/**
 * Define la responsabilidad de la clase PedidoPolicy dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class PedidoPolicy
{
    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @param  mixed  $order  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
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

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @param  mixed  $order  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
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
