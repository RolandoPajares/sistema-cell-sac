<?php

namespace App\Dominio\Catalogo;

use Closure;

/**
 * Contrato de persistencia para productos y variantes de catálogo.
 */
interface ProductoDao
{
    /**
     * Ejecuta una operación del DAO dentro de una transacción controlada.
     *
     * @param Closure(self): mixed $operacion Callback que recibe la implementación del DAO.
     * @return mixed Resultado producido por el callback.
     */
    public function enTransaccion(Closure $operacion): mixed;

    /**
     * Busca un producto y opcionalmente carga sus variantes asociadas.
     *
     * @param int $id Identificador del producto.
     * @param bool $incluirVariantes Indica si deben incluirse las variantes.
     * @return ProductoEntidad|null Entidad encontrada o null si no existe.
     */
    public function buscarPorId(int $id, bool $incluirVariantes = true): ?ProductoEntidad;

    /**
     * Inserta un producto validado en el almacenamiento.
     *
     * @param ProductoEntidad $producto Datos del producto.
     * @return ProductoEntidad Producto persistido.
     */
    public function crear(ProductoEntidad $producto): ProductoEntidad;

    /**
     * Actualiza un producto identificado y devuelve su representación persistida.
     *
     * @param ProductoEntidad $producto Producto con nuevos datos.
     * @return ProductoEntidad Producto actualizado.
     */
    public function actualizar(ProductoEntidad $producto): ProductoEntidad;

    /**
     * Inserta una variante vinculada a un producto existente.
     *
     * @param VarianteProductoEntidad $variante Datos de la variante.
     * @return VarianteProductoEntidad Variante persistida.
     */
    public function crearVariante(VarianteProductoEntidad $variante): VarianteProductoEntidad;

    /**
     * Actualiza los datos persistibles de una variante existente.
     *
     * @param VarianteProductoEntidad $variante Variante con identificador.
     * @return VarianteProductoEntidad Variante actualizada.
     */
    public function actualizarVariante(VarianteProductoEntidad $variante): VarianteProductoEntidad;

    /**
     * Elimina una variante por identificador dentro de una transacción.
     *
     * @param int $id Identificador de la variante.
     */
    public function eliminarVariante(int $id): void;

    /**
     * Elimina un producto por identificador dentro de una transacción.
     *
     * @param int $id Identificador del producto.
     */
    public function eliminar(int $id): void;

    /**
     * Sincroniza una ruta de imagen y la posiciona como portada cuando corresponde.
     *
     * @param int $productoId Identificador del producto.
     * @param string $rutaImagen Ruta de la imagen.
     */
    public function sincronizarImagenPortada(int $productoId, string $rutaImagen): void;
}
