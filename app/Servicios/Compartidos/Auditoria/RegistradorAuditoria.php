<?php

namespace App\Servicios\Compartidos\Auditoria;

use App\Models\Auditoria\RegistroAuditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Define la responsabilidad de la clase RegistradorAuditoria dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class RegistradorAuditoria
{
    /** @var array<string,bool> */
    private array $columnCache = [];

    /**
     * @param  array<string,mixed>|null  $before
     * @param  array<string,mixed>|null  $after
     * @param  array{empresa_id?:int,sede_id?:int}|null  $scope
     */
    public function record(
        ?int $userId,
        string $action,
        string $entity,
        ?int $entityId,
        ?array $before,
        ?array $after,
        ?string $ip,
        string $result = 'exito',
        ?string $observations = null,
        ?array $scope = null,
        ?string $eventId = null,
    ): void {
        $request = request();
        $result = match ($result) {
            'exito', 'exitoso' => 'exito',
            'fallo', 'fallido', 'denegado' => 'fallo',
            'revertido' => 'revertido',
            default => 'exito',
        };
        $sessionSiteId = $request->hasSession() ? $request->session()->get('sede_actual_id') : null;
        $payloadSiteId = $scope['sede_id'] ?? $after['sede_id'] ?? null;
        $siteId = $this->ubicacionEntidad($entity, $entityId, 'sede_id')
            ?? (is_numeric($payloadSiteId) ? (int) $payloadSiteId : null)
            ?? (is_numeric($sessionSiteId) ? (int) $sessionSiteId : null);
        $companyId = $scope['empresa_id'] ?? $this->ubicacionEntidad($entity, $entityId, 'empresa_id');
        if ($companyId === null && $siteId !== null && Schema::hasTable('sedes')) {
            $siteCompanyId = DB::table('sedes')->where('id', $siteId)->value('empresa_id');
            $companyId = is_numeric($siteCompanyId) ? (int) $siteCompanyId : null;
        }
        if ($companyId === null && Schema::hasTable('empresas')) {
            $companyId = DB::table('empresas')->where('codigo', 'TECHCELL')->value('id');
        }
        $context = [
            'usuario_id' => $userId,
            'accion' => Str::limit($action, 80, ''),
            'entidad' => Str::limit($entity, 80, ''),
            'entidad_id' => $entityId,
            'valores_anteriores' => $this->datosSeguros($before),
            'valores_nuevos' => $this->datosSeguros($after),
            'direccion_ip' => $ip,
            'creado_en' => now(),
        ];

        if ($this->tieneColumna('identificador_universal_evento')) {
            $context['identificador_universal_evento'] = $eventId ?? (string) Str::uuid();
        } elseif ($this->tieneColumna('evento_uuid')) {
            $context['evento_uuid'] = (string) Str::uuid();
        }
        if ($this->tieneColumna('rol_codigo')) {
            $context['rol_codigo'] = Str::limit((string) ($request->attributes->get('rol_contexto') ?: $request->user()?->rol), 40, '') ?: null;
        } elseif ($this->tieneColumna('rol_contexto')) {
            $context['rol_contexto'] = Str::limit((string) ($request->attributes->get('rol_contexto') ?: $request->user()?->rol), 64, '') ?: null;
        }
        if ($this->tieneColumna('empresa_id')) {
            $context['empresa_id'] = $companyId;
        }
        if ($this->tieneColumna('sede_id')) {
            $context['sede_id'] = $siteId;
        }
        $permission = $request->attributes->get('permiso_contexto');
        $moduleCode = is_string($permission) && preg_match('/^gestion\.([a-z0-9_]+)/', $permission, $matches) === 1
                ? $matches[1]
                : $this->moduloParaEntidad($entity);
        if ($this->tieneColumna('modulo')) {
            $context['modulo'] = $moduleCode;
        } elseif ($this->tieneColumna('modulo_codigo')) {
            $context['modulo_codigo'] = $moduleCode;
        }
        if ($this->tieneColumna('resultado')) {
            $context['resultado'] = $result;
        }
        if ($this->tieneColumna('origen')) {
            $context['origen'] = 'laravel';
        }
        if ($this->tieneColumna('agente_usuario')) {
            $context['agente_usuario'] = Str::limit((string) $request->userAgent(), 500, '');
        }
        if ($this->tieneColumna('correlacion_id')) {
            $correlationId = (string) $request->header('X-Request-ID', '');
            $context['correlacion_id'] = preg_match('/^[A-Za-z0-9._-]{1,64}$/', $correlationId) === 1 ? $correlationId : null;
        }
        if ($this->tieneColumna('observaciones')) {
            $context['observaciones'] = $observations === null ? null : Str::limit($observations, 1000, '');
        } elseif ($this->tieneColumna('observacion')) {
            $context['observacion'] = $observations === null ? null : Str::limit($observations, 1000, '');
        }
        if ($this->tieneColumna('tabla_afectada')) {
            $context['tabla_afectada'] = Str::limit($entity, 100, '');
        }
        if ($this->tieneColumna('identificador_entidad')) {
            $context['identificador_entidad'] = $entityId === null ? null : (string) $entityId;
        }
        if ($this->tieneColumna('ocurrido_en')) {
            $context['ocurrido_en'] = now();
        }
        if ($this->tieneColumna('cambios')) {
            $context['cambios'] = $this->cambios($this->datosSeguros($before), $this->datosSeguros($after));
        }

        RegistroAuditoria::query()->create($context);
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $column  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function tieneColumna(string $column): bool
    {
        return $this->columnCache[$column] ??= Schema::hasColumn('registros_auditoria', $column);
    }

    /** @param array<string,mixed>|null $payload
     * @return array<string,mixed>|null
     */
    private function datosSeguros(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        $safe = [];
        foreach ($payload as $key => $value) {
            if (preg_match('/password|token|secret|credential|clave|correo|email|telefono|documento|dni|ruc|tarjeta|cuenta_bancaria|cookie|session/i', (string) $key) === 1) {
                continue;
            }

            if (is_array($value)) {
                $safe[$key] = $this->datosSeguros($value);
            } elseif (is_scalar($value) || $value === null) {
                $safe[$key] = $value;
            }
        }

        return $safe;
    }

    /**
     * Ejecuta la operación moduleForEntity definida por la clase RegistradorAuditoria.
     *
     * @param  mixed  $entity  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    private function moduloParaEntidad(string $entity): ?string
    {
        return match ($entity) {
            'users', 'roles', 'permisos', 'menus', 'elementos_menu', 'pantallas' => 'seguridad',
            'productos', 'producto_variantes', 'categorias' => 'productos',
            'pedidos', 'detalle_pedidos', 'cotizaciones' => 'ventas',
            'compras', 'compra_detalles', 'proveedores' => 'compras',
            'movimientos_inventario', 'almacenes', 'transferencias_existencias', 'detalles_transferencias_existencias' => 'inventario',
            'registros_auditoria' => 'auditoria',
            default => null,
        };
    }

    /**
     * Obtiene la ubicación empresarial de la entidad auditada.
     *
     * @param  mixed  $entity  Parámetro recibido por la operación.
     * @param  mixed  $entityId  Parámetro recibido por la operación.
     * @param  mixed  $column  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    private function ubicacionEntidad(string $entity, ?int $entityId, string $column): ?int
    {
        $auditableTables = [
            'pedidos', 'compras', 'cotizaciones', 'movimientos_inventario',
            'devoluciones_garantias', 'ventas', 'transferencias_stock', 'transferencias_existencias',
        ];

        if ($entityId === null || ! in_array($entity, $auditableTables, true) || ! Schema::hasTable($entity) || ! Schema::hasColumn($entity, $column)) {
            return null;
        }

        $value = DB::table($entity)->where('id', $entityId)->value($column);

        return is_numeric($value) ? (int) $value : null;
    }

    /** @param array<string,mixed>|null $before
     * @param  array<string,mixed>|null  $after
     * @return array<string,array{anterior:mixed,nuevo:mixed}>|null
     */
    private function cambios(?array $before, ?array $after): ?array
    {
        if ($before === null && $after === null) {
            return null;
        }

        $changes = [];
        foreach (array_unique(array_merge(array_keys($before ?? []), array_keys($after ?? []))) as $key) {
            $previous = $before[$key] ?? null;
            $next = $after[$key] ?? null;

            if ($previous !== $next) {
                $changes[(string) $key] = ['anterior' => $previous, 'nuevo' => $next];
            }
        }

        return $changes;
    }
}
