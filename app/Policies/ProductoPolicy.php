<?php

namespace App\Policies;

use App\Models\Catalogo\Producto;
use App\Models\User;
use App\Servicios\ServicioAutorizacion;

class ProductoPolicy
{
    public function update(User $user, Producto $product): bool
    {
        return app(ServicioAutorizacion::class)->allowsRecord(
            $user,
            'gestion.productos.actualizar',
            is_numeric($product->empresa_id) ? (int) $product->empresa_id : null,
            null,
        );
    }

    public function delete(User $user, Producto $product): bool
    {
        return app(ServicioAutorizacion::class)->allowsRecord(
            $user,
            'gestion.productos.eliminar',
            is_numeric($product->empresa_id) ? (int) $product->empresa_id : null,
            null,
        );
    }
}
