<?php

namespace App\Contratos\Productos;

interface AuditoriaProductos
{
    /** @param array<string, mixed>|null $antes @param array<string, mixed>|null $despues */
    public function registrar(?int $usuarioId, string $accion, string $entidad, ?int $entidadId, ?array $antes, ?array $despues, ?string $ip): void;
}
