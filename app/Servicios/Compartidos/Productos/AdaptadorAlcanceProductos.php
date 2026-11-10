<?php

namespace App\Servicios\Compartidos\Productos;

use App\Contratos\Productos\AlcanceProductos;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;

/**
 * Define la responsabilidad de la clase AdaptadorAlcanceProductos dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class AdaptadorAlcanceProductos implements AlcanceProductos
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    /**
     * Ejecuta la operación atributosDeAlcance definida por la clase AdaptadorAlcanceProductos.
     *
     * @param  mixed  $usuario  Parámetro recibido por la operación.
     * @param  mixed  $tabla  Parámetro recibido por la operación.
     * @return array Resultado de la operación.
     */
    public function atributosDeAlcance(User $usuario, string $tabla): array
    {
        return $this->autorizacion->operationScopeAttributes($usuario, $tabla);
    }
}
