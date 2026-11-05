<?php

namespace App\Servicios;

use App\Contratos\Productos\AlcanceProductos;
use App\Models\User;

final class AdaptadorAlcanceProductos implements AlcanceProductos
{
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    public function atributosDeAlcance(User $usuario, string $tabla): array
    {
        return $this->autorizacion->operationScopeAttributes($usuario, $tabla);
    }
}
