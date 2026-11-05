<?php

namespace App\Dominio\Catalogo;

final readonly class ProductoEntidad
{
    /** @param list<VarianteProductoEntidad> $variantes */
    public function __construct(
        public ?int $id,
        public string $marca,
        public string $nombre,
        public int $categoriaId,
        public string $categoria,
        public string $precio,
        public ?string $precioOferta,
        public bool $activo,
        public ?int $empresaId,
        public array $variantes = [],
    ) {}
}
