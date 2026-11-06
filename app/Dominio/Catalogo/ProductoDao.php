<?php

namespace App\Dominio\Catalogo;

use Closure;

/**
 * Define la responsabilidad de la clase ProductoDao dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
interface ProductoDao
{
    /** @param Closure(self): mixed $operacion */
    public function enTransaccion(Closure $operacion): mixed;

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $id  Parámetro recibido por la operación.
     * @param  mixed  $incluirVariantes  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function buscarPorId(int $id, bool $incluirVariantes = true): ?ProductoEntidad;

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $producto  Parámetro recibido por la operación.
     * @return ProductoEntidad Resultado de la operación.
     */
    public function crear(ProductoEntidad $producto): ProductoEntidad;

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $producto  Parámetro recibido por la operación.
     * @return ProductoEntidad Resultado de la operación.
     */
    public function actualizar(ProductoEntidad $producto): ProductoEntidad;

    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @param  mixed  $variante  Parámetro recibido por la operación.
     * @return VarianteProductoEntidad Resultado de la operación.
     */
    public function crearVariante(VarianteProductoEntidad $variante): VarianteProductoEntidad;

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $variante  Parámetro recibido por la operación.
     * @return VarianteProductoEntidad Resultado de la operación.
     */
    public function actualizarVariante(VarianteProductoEntidad $variante): VarianteProductoEntidad;

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $id  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    public function eliminarVariante(int $id): void;

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $id  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    public function eliminar(int $id): void;

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $productoId  Parámetro recibido por la operación.
     * @param  mixed  $rutaImagen  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    public function sincronizarImagenPortada(int $productoId, string $rutaImagen): void;
}
