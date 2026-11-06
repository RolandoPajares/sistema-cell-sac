<?php

namespace App\Contratos\Productos;

/**
 * Define la responsabilidad de la clase AuditoriaProductos dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
interface AuditoriaProductos
{
    /** @param array<string, mixed>|null $antes @param array<string, mixed>|null $despues */
    public function registrar(?int $usuarioId, string $accion, string $entidad, ?int $entidadId, ?array $antes, ?array $despues, ?string $ip): void;
}
