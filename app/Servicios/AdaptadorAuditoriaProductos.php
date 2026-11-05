<?php

namespace App\Servicios;

use App\Contratos\Productos\AuditoriaProductos;

final class AdaptadorAuditoriaProductos implements AuditoriaProductos
{
    public function __construct(private readonly RegistradorAuditoria $auditoria) {}

    /** @param array<string, mixed>|null $antes @param array<string, mixed>|null $despues */
    public function registrar(?int $usuarioId, string $accion, string $entidad, ?int $entidadId, ?array $antes, ?array $despues, ?string $ip): void
    {
        $this->auditoria->record($usuarioId, $accion, $entidad, $entidadId, $antes, $despues, $ip);
    }
}
