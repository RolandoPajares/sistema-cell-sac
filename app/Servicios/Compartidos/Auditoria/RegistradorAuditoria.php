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
        // Normaliza la accion y guarda los valores anteriores y nuevos del evento.
        // Guarda en $request el resultado necesario para continuar la operacion.
        $request = request();
        // Guarda en $result el resultado necesario para continuar la operacion.
        $result = match ($result) {
            'exito', 'exitoso' => 'exito',
            'fallo', 'fallido', 'denegado' => 'fallo',
            'revertido' => 'revertido',
            default => 'exito',
        };
        // Guarda en $sessionSiteId el resultado de la consulta ejecutada.
        $sessionSiteId = $request->hasSession() ? $request->session()->get('sede_actual_id') : null;
        // Guarda en $payloadSiteId el resultado necesario para continuar la operacion.
        $payloadSiteId = $scope['sede_id'] ?? $after['sede_id'] ?? null;
        // Guarda en $siteId el resultado necesario para continuar la operacion.
        $siteId = $this->ubicacionEntidad($entity, $entityId, 'sede_id')
            ?? (is_numeric($payloadSiteId) ? (int) $payloadSiteId : null)
            ?? (is_numeric($sessionSiteId) ? (int) $sessionSiteId : null);
        // Guarda en $companyId el resultado necesario para continuar la operacion.
        $companyId = $scope['empresa_id'] ?? $this->ubicacionEntidad($entity, $entityId, 'empresa_id');
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($companyId === null && $siteId !== null && Schema::hasTable('sedes')) {
            // Prepara en $siteCompanyId la consulta que obtiene o modifica los datos necesarios.
            // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
            $siteCompanyId = DB::table('sedes')->where('id', $siteId)->value('empresa_id');
            // Guarda en $companyId el resultado necesario para continuar la operacion.
            $companyId = is_numeric($siteCompanyId) ? (int) $siteCompanyId : null;
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($companyId === null && Schema::hasTable('empresas')) {
            // Prepara en $companyId la consulta que obtiene o modifica los datos necesarios.
            $companyId = DB::table('empresas')->where('codigo', 'TECHCELL')->value('id');
        }
        // Agrupa en $context los datos que se utilizaran en el siguiente paso.
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

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('identificador_universal_evento')) {
            $context['identificador_universal_evento'] = $eventId ?? (string) Str::uuid();
        } elseif ($this->tieneColumna('evento_uuid')) {
            $context['evento_uuid'] = (string) Str::uuid();
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('rol_codigo')) {
            $context['rol_codigo'] = Str::limit((string) ($request->attributes->get('rol_contexto') ?: $request->user()?->rol), 40, '') ?: null;
        } elseif ($this->tieneColumna('rol_contexto')) {
            $context['rol_contexto'] = Str::limit((string) ($request->attributes->get('rol_contexto') ?: $request->user()?->rol), 64, '') ?: null;
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('empresa_id')) {
            $context['empresa_id'] = $companyId;
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('sede_id')) {
            $context['sede_id'] = $siteId;
        }
        // Guarda en $permission el resultado de la consulta ejecutada.
        $permission = $request->attributes->get('permiso_contexto');
        $moduleCode = is_string($permission) && preg_match('/^gestion\.([a-z0-9_]+)/', $permission, $matches) === 1
                ? $matches[1]
                : $this->moduloParaEntidad($entity);
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('modulo')) {
            $context['modulo'] = $moduleCode;
        } elseif ($this->tieneColumna('modulo_codigo')) {
            $context['modulo_codigo'] = $moduleCode;
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('resultado')) {
            $context['resultado'] = $result;
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('origen')) {
            $context['origen'] = 'laravel';
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('agente_usuario')) {
            $context['agente_usuario'] = Str::limit((string) $request->userAgent(), 500, '');
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('correlacion_id')) {
            // Guarda en $correlationId el resultado necesario para continuar la operacion.
            $correlationId = (string) $request->header('X-Request-ID', '');
            $context['correlacion_id'] = preg_match('/^[A-Za-z0-9._-]{1,64}$/', $correlationId) === 1 ? $correlationId : null;
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('observaciones')) {
            $context['observaciones'] = $observations === null ? null : Str::limit($observations, 1000, '');
        } elseif ($this->tieneColumna('observacion')) {
            $context['observacion'] = $observations === null ? null : Str::limit($observations, 1000, '');
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('tabla_afectada')) {
            $context['tabla_afectada'] = Str::limit($entity, 100, '');
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('identificador_entidad')) {
            $context['identificador_entidad'] = $entityId === null ? null : (string) $entityId;
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($this->tieneColumna('ocurrido_en')) {
            $context['ocurrido_en'] = now();
        }
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
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
        // Comprueba si la columna existe para mantener compatibilidad con el esquema actual.
        return $this->columnCache[$column] ??= Schema::hasColumn('registros_auditoria', $column);
    }

    /** @param array<string,mixed>|null $payload
     * @return array<string,mixed>|null
     */
    private function datosSeguros(?array $payload): ?array
    {
        // Filtra datos sensibles antes de almacenarlos en auditoria.
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($payload === null) {
            return null;
        }

        // Agrupa en $safe los datos que se utilizaran en el siguiente paso.
        $safe = [];
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($payload as $key => $value) {
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (preg_match('/password|token|secret|credential|clave|correo|email|telefono|documento|dni|ruc|tarjeta|cuenta_bancaria|cookie|session/i', (string) $key) === 1) {
                continue;
            }

            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
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
        // Determina el modulo funcional al que pertenece la entidad auditada.
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
        // Obtiene empresa o sede desde la entidad afectada cuando es posible.
        // Agrupa en $auditableTables los datos que se utilizaran en el siguiente paso.
        $auditableTables = [
            'pedidos', 'compras', 'cotizaciones', 'movimientos_inventario',
            'devoluciones_garantias', 'ventas', 'transferencias_stock', 'transferencias_existencias',
        ];

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($entityId === null || ! in_array($entity, $auditableTables, true) || ! Schema::hasTable($entity) || ! Schema::hasColumn($entity, $column)) {
            return null;
        }

        // Prepara en $value la consulta que obtiene o modifica los datos necesarios.
        $value = DB::table($entity)->where('id', $entityId)->value($column);

        return is_numeric($value) ? (int) $value : null;
    }

    /** @param array<string,mixed>|null $before
     * @param  array<string,mixed>|null  $after
     * @return array<string,array{anterior:mixed,nuevo:mixed}>|null
     */
    private function cambios(?array $before, ?array $after): ?array
    {
        // Compara estados y devuelve un resumen de los campos modificados.
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($before === null && $after === null) {
            return null;
        }

        // Agrupa en $changes los datos que se utilizaran en el siguiente paso.
        $changes = [];
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach (array_unique(array_merge(array_keys($before ?? []), array_keys($after ?? []))) as $key) {
            // Guarda en $previous el resultado necesario para continuar la operacion.
            $previous = $before[$key] ?? null;
            // Guarda en $next el resultado necesario para continuar la operacion.
            $next = $after[$key] ?? null;

            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($previous !== $next) {
                $changes[(string) $key] = ['anterior' => $previous, 'nuevo' => $next];
            }
        }

        return $changes;
    }
}
