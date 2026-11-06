<?php

namespace App\Contratos\Productos;

use App\Models\User;

/**
 * Define la responsabilidad de la clase AlcanceProductos dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
interface AlcanceProductos
{
    /** @return array<string, int> */
    public function atributosDeAlcance(User $usuario, string $tabla): array;
}
