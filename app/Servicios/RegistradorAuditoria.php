<?php

namespace App\Servicios;

use App\Models\Auditoria\RegistroAuditoria;

class RegistradorAuditoria
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function record(?int $userId, string $action, string $entity, ?int $entityId, ?array $before, ?array $after, ?string $ip): void
    {
        RegistroAuditoria::create([
            'usuario_id' => $userId,
            'accion' => $action,
            'entidad' => $entity,
            'entidad_id' => $entityId,
            'valores_anteriores' => $before,
            'valores_nuevos' => $after,
            'direccion_ip' => $ip,
            'creado_en' => now(),
        ]);
    }
}
