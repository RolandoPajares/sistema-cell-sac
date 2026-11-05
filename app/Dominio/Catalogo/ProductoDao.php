<?php

namespace App\Dominio\Catalogo;

interface ProductoDao
{
    public function buscarPorId(int $id, bool $incluirVariantes = true): ?ProductoEntidad;

    public function crear(ProductoEntidad $producto): ProductoEntidad;

    public function actualizar(ProductoEntidad $producto): ProductoEntidad;

    public function crearVariante(VarianteProductoEntidad $variante): VarianteProductoEntidad;

    public function actualizarVariante(VarianteProductoEntidad $variante): VarianteProductoEntidad;

    public function eliminarVariante(int $id): void;

    public function eliminar(int $id): void;

    public function sincronizarImagenPortada(int $productoId, string $rutaImagen): void;
}
