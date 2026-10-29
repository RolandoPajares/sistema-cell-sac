<?php

namespace App\Policies;

use App\Models\Comercio\Pedido;
use App\Models\User;

class PedidoPolicy
{
    public function view(User $user, Pedido $order): bool
    {
        return $this->hasOrderAccess($user, $order);
    }

    public function updateStatus(User $user, Pedido $order): bool
    {
        return $this->hasOrderAccess($user, $order)
            && in_array($user->rol, ['administrador', 'ventas_minoristas', 'ventas_mayoristas'], true);
    }

    private function hasOrderAccess(User $user, Pedido $order): bool
    {
        if ($user->isRole('administrador') || $order->usuario_id === $user->id) {
            return true;
        }

        return ($user->isRole('ventas_minoristas') && $order->user?->rol === 'cliente_minorista')
            || ($user->isRole('ventas_mayoristas') && $order->user?->rol === 'cliente_mayorista');
    }
}
