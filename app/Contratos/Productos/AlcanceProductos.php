<?php

namespace App\Contratos\Productos;

use App\Models\User;

interface AlcanceProductos
{
    /** @return array<string, int> */
    public function atributosDeAlcance(User $usuario, string $tabla): array;
}
