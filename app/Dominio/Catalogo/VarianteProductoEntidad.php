<?php

namespace App\Dominio\Catalogo;

final readonly class VarianteProductoEntidad
{
    public function __construct(
        public ?int $id,
        public int $productoId,
        public ?string $codigoSku,
        public ?string $codigoBarras,
        public string $nombreColor,
        public ?string $codigoColor,
        public int $existencias,
        public bool $controlaSerie,
        public string $estado,
    ) {}
}
