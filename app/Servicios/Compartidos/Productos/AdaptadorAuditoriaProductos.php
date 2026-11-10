<?php

namespace App\Servicios\Compartidos\Productos;

use App\Contratos\Productos\AuditoriaProductos;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;

/**
 * Define la responsabilidad de la clase AdaptadorAuditoriaProductos dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class AdaptadorAuditoriaProductos implements AuditoriaProductos
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $auditoria  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly RegistradorAuditoria $auditoria) {}

    /** @param array<string, mixed>|null $antes @param array<string, mixed>|null $despues */
    public function registrar(?int $usuarioId, string $accion, string $entidad, ?int $entidadId, ?array $antes, ?array $despues, ?string $ip): void
    {
        $this->auditoria->record($usuarioId, $accion, $entidad, $entidadId, $antes, $despues, $ip);
    }
}
