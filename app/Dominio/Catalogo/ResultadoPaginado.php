<?php

namespace App\Dominio\Catalogo;

use InvalidArgumentException;

/**
 * Resultado inmutable de una consulta paginada del catálogo.
 *
 * @template T
 */
final readonly class ResultadoPaginado
{
    /** @param list<T> $items */
    public function __construct(
        public array $items,
        public int $total,
        public int $porPagina,
        public int $pagina,
    ) {
        if ($this->porPagina < 1 || $this->pagina < 1 || $this->total < 0) {
            throw new InvalidArgumentException('Los valores de paginación no son válidos.');
        }
    }

    public function ultimaPagina(): int
    {
        return max(1, (int) ceil($this->total / $this->porPagina));
    }
}
