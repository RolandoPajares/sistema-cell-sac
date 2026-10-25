<?php

namespace App\Policies;

use App\Models\Catalogo\Producto;
use App\Models\User;

class ProductoPolicy
{
    public function update(User $user, Producto $product): bool
    {
        return $user->isRole('administrador');
    }

    public function delete(User $user, Producto $product): bool
    {
        return $user->isRole('administrador');
    }
}
