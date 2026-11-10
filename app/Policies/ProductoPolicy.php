<?php

namespace App\Policies;

use App\Models\Catalogo\Producto;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;

/**
 * Define la responsabilidad de la clase ProductoPolicy dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ProductoPolicy
{
    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @param  mixed  $product  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    public function update(User $user, Producto $product): bool
    {
        return app(ServicioAutorizacion::class)->allowsRecord(
            $user,
            'gestion.productos.actualizar',
            is_numeric($product->empresa_id) ? (int) $product->empresa_id : null,
            null,
        );
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @param  mixed  $product  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
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
