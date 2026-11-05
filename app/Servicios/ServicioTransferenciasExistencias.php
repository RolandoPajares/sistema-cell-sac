<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Inventario\DetalleTransferenciaExistencia;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Inventario\TransferenciaExistencia;
use App\Models\Organizacion\Almacen;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ServicioTransferenciasExistencias
{
    public function __construct(
        private readonly ServicioAutorizacion $authorization,
        private readonly ServicioInventario $inventory,
        private readonly RegistradorAuditoria $audit,
    ) {}

    /** @return array<string, mixed> */
    public function pageData(User $actor, array $filters): array
    {
        $this->authorization->assertAllowed($actor, 'gestion.inventario.consultar');
        $location = $this->currentCompany($actor);
        $warehouses = Almacen::query()->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
            ->where('sedes.empresa_id', $location['empresa_id'])->where('sedes.activa', true)->where('almacenes.activo', true)
            ->orderBy('sedes.nombre')->orderBy('almacenes.nombre')
            ->get(['almacenes.id', 'almacenes.sede_id', 'almacenes.codigo', 'almacenes.nombre', 'sedes.nombre as sede_nombre']);
        $sourceWarehouses = $location['sede_id'] === null
            ? $warehouses
            : $warehouses->where('sede_id', $location['sede_id'])->values();
        $transfers = TransferenciaExistencia::query()->with([
            'sedeOrigen:id,nombre', 'sedeDestino:id,nombre', 'almacenOrigen:id,nombre', 'almacenDestino:id,nombre',
            'solicitante:id,nombre', 'aprobador:id,nombre', 'detalles.producto:id,nombre,marca', 'detalles.variante:id,nombre_color,codigo_sku',
        ])->where('empresa_id', $location['empresa_id'])
            ->when($location['sede_id'] !== null, fn (Builder $query): Builder => $query->where(fn (Builder $scope): Builder => $scope
                ->where('sede_origen_id', $location['sede_id'])->orWhere('sede_destino_id', $location['sede_id'])))
            ->when($filters['estado'] ?? null, fn (Builder $query, string $status): Builder => $query->where('estado', $status))
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search): Builder {
                return $query->where(function (Builder $matching) use ($search): void {
                    $matching->where('codigo', 'like', '%'.$search.'%')
                        ->orWhereHas('detalles.producto', fn (Builder $products): Builder => $products->where('nombre', 'like', '%'.$search.'%'));
                });
            })->orderByDesc('id')->paginate(12)->withQueryString();
        $products = DB::table('existencias_almacen as stock')->join('productos', 'productos.id', '=', 'stock.producto_id')
            ->leftJoin('variantes_productos', 'variantes_productos.id', '=', 'stock.variante_id')
            ->where('stock.empresa_id', $location['empresa_id'])->where('productos.activo', true)
            ->select(['productos.id as producto_id', 'productos.nombre', 'productos.marca', 'stock.variante_id', 'variantes_productos.nombre_color', 'variantes_productos.codigo_sku'])
            ->distinct()->orderBy('productos.nombre')->get();
        $transferIds = $transfers->getCollection()->modelKeys();
        $movements = $transferIds === [] ? collect() : MovimientoInventario::query()->with(['product:id,nombre', 'user:id,nombre', 'almacen:id,nombre'])
            ->whereIn('documento_tipo', ['transferencia_despacho', 'transferencia_recepcion', 'transferencia_retorno'])
            ->whereIn('documento_id', array_map('strval', $transferIds))->orderBy('creado_en')->get()->groupBy('documento_id');
        $counts = TransferenciaExistencia::query()->where('empresa_id', $location['empresa_id'])
            ->when($location['sede_id'] !== null, fn (Builder $query): Builder => $query->where(fn (Builder $scope): Builder => $scope
                ->where('sede_origen_id', $location['sede_id'])->orWhere('sede_destino_id', $location['sede_id'])))
            ->selectRaw('estado, COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado');

        return [
            'empresa_id' => $location['empresa_id'], 'sede_id' => $location['sede_id'], 'warehouses' => $warehouses, 'source_warehouses' => $sourceWarehouses,
            'products' => $products, 'transfers' => $transfers, 'movements' => $movements,
            'metrics' => [
                'solicitadas' => (int) ($counts['solicitada'] ?? 0), 'en_transito' => (int) ($counts['en tránsito'] ?? 0),
                'recepcion_parcial' => (int) ($counts['recibida parcialmente'] ?? 0), 'pendientes_cierre' => (int) ($counts['recibida totalmente'] ?? 0),
            ],
            'supports_return_reconciliation' => Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_devuelta'),
            'supports_difference_reconciliation' => Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_devuelta')
                && Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_conciliada'),
        ];
    }

    /** @param array<string, mixed> $filters
     * @return Builder<TransferenciaExistencia>
     */
    public function exportQuery(User $actor, array $filters): Builder
    {
        $this->authorization->assertAllowed($actor, 'gestion.inventario.exportar');
        $location = $this->currentCompany($actor);

        return $this->transferQuery($location, $filters)->orderBy('id');
    }

    /** @param array<string, mixed> $data */
    public function request(array $data, User $actor, ?string $ip): TransferenciaExistencia
    {
        $this->authorization->assertAllowed($actor, 'gestion.inventario.transferir');
        $requestEventId = $this->eventId(0, $actor->getKey(), 'request', $data['clave_idempotencia']);
        $source = $this->warehouseContext($actor, (int) $data['almacen_origen_id'], 'gestion.inventario.transferir');
        $destination = $this->warehouseContext($actor, (int) $data['almacen_destino_id'], null, $source['empresa_id']);
        abort_if($source['almacen_id'] === $destination['almacen_id'], 422, 'Los almacenes de origen y destino deben ser distintos.');

        return DB::transaction(function () use ($data, $actor, $ip, $source, $destination, $requestEventId): TransferenciaExistencia {
            $existingEvent = DB::table('registros_auditoria')->where('identificador_universal_evento', $requestEventId)->first(['entidad_id']);
            if ($existingEvent !== null) {
                return TransferenciaExistencia::query()->with('detalles')->findOrFail($existingEvent->entidad_id);
            }
            $transfer = TransferenciaExistencia::query()->create([
                'empresa_id' => $source['empresa_id'], 'sede_origen_id' => $source['sede_id'], 'almacen_origen_id' => $source['almacen_id'],
                'sede_destino_id' => $destination['sede_id'], 'almacen_destino_id' => $destination['almacen_id'],
                'codigo' => $this->newCode($source['empresa_id']), 'estado' => 'solicitada',
                'solicitado_por_usuario_id' => $actor->getKey(), 'creado_en' => now(),
            ]);
            foreach ($data['items'] as $item) {
                $product = Producto::query()->whereKey($item['producto_id'])->where('empresa_id', $source['empresa_id'])->where('activo', true)->first();
                abort_unless($product !== null, 422, 'El producto no pertenece a la empresa o no esta activo.');
                $variantId = isset($item['variante_id']) && $item['variante_id'] !== '' ? (int) $item['variante_id'] : null;
                $this->assertProductVariant($product->getKey(), $variantId);
                DetalleTransferenciaExistencia::query()->create([
                    'transferencia_id' => $transfer->getKey(), 'producto_id' => $product->getKey(), 'variante_id' => $variantId,
                    'cantidad_solicitada' => (int) $item['cantidad'], 'cantidad_enviada' => 0, 'cantidad_recibida' => 0,
                ]);
            }
            $this->audit->record($actor->getKey(), 'transfer.requested', 'transferencias_existencias', $transfer->getKey(), null, [
                'estado' => 'solicitada', 'codigo' => $transfer->codigo, 'empresa_id' => $transfer->empresa_id,
                'sede_origen_id' => $transfer->sede_origen_id, 'almacen_origen_id' => $transfer->almacen_origen_id,
                'sede_destino_id' => $transfer->sede_destino_id, 'almacen_destino_id' => $transfer->almacen_destino_id,
                'items' => collect($data['items'])->map(fn (array $item): array => [
                    'producto_id' => (int) $item['producto_id'], 'variante_id' => $item['variante_id'] ?? null,
                    'cantidad_solicitada' => (int) $item['cantidad'],
                ])->all(),
            ], $ip, observations: $data['observaciones'] ?? null, scope: ['empresa_id' => $source['empresa_id'], 'sede_id' => $source['sede_id']], eventId: $requestEventId);

            return $transfer->load('detalles.producto', 'detalles.variante');
        }, 3);
    }

    public function approve(TransferenciaExistencia $transfer, User $actor, string $key, ?string $ip): TransferenciaExistencia
    {
        $this->authorization->assertAllowed($actor, 'gestion.inventario.aprobar');

        return $this->transition($transfer, $actor, 'approve', $key, $ip, function (TransferenciaExistencia $locked) use ($actor): array {
            abort_unless($locked->estado === 'solicitada', 409, 'Solo se pueden aprobar transferencias solicitadas.');
            $this->authorizeTransferSite($actor, $locked, 'sede_origen_id', 'gestion.inventario.aprobar');
            $this->assertWarehouseStillActive($locked);
            foreach ($locked->detalles()->orderBy('producto_id')->orderBy('variante_clave')->lockForUpdate()->get() as $detail) {
                Producto::query()->whereKey($detail->producto_id)->lockForUpdate()->firstOrFail();
                $balance = $this->balance($locked, $detail, (int) $locked->almacen_origen_id, true);
                $available = (int) floor((float) $balance->cantidad_fisica - (float) $balance->cantidad_reservada);
                abort_if($available < (int) $detail->cantidad_solicitada, 409, 'No hay existencias disponibles para aprobar todos los productos solicitados.');
                if ($this->isSerialized($detail)) {
                    abort_if($this->availableDeviceQuery($detail, (int) $locked->almacen_origen_id)->count() < (int) $detail->cantidad_solicitada, 409, 'Faltan IMEI o numeros de serie disponibles en el almacen de origen.');
                }
            }
            $before = ['estado' => $locked->estado];
            $locked->estado = 'aprobada';
            $locked->aprobado_por_usuario_id = $actor->getKey();
            $locked->save();

            return [$before, ['estado' => $locked->estado, 'aprobado_por_usuario_id' => $actor->getKey()], null];
        });
    }

    /** @param array<string, mixed> $data */
    public function dispatch(TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip): TransferenciaExistencia
    {
        $this->authorization->assertAllowed($actor, 'gestion.inventario.transferir');

        return $this->transition($transfer, $actor, 'dispatch', $data['clave_idempotencia'], $ip, function (TransferenciaExistencia $locked) use ($data, $actor, $ip): array {
            abort_unless(in_array($locked->estado, ['aprobada', 'en tránsito', 'recibida parcialmente'], true), 409, 'La transferencia no esta disponible para despacho.');
            $this->authorizeTransferSite($actor, $locked, 'sede_origen_id', 'gestion.inventario.transferir');
            $this->assertWarehouseStillActive($locked);
            $details = $this->requestedDetails($locked, $data['items'] ?? []);
            $before = ['estado' => $locked->estado];
            foreach ($details as [$detail, $operation]) {
                $product = Producto::query()->whereKey($detail->producto_id)->lockForUpdate()->firstOrFail();
                $quantity = $operation['cantidad'];
                abort_if((int) $detail->cantidad_enviada + $quantity > (int) $detail->cantidad_solicitada, 422, 'El despacho supera la cantidad autorizada.');
                $movement = $this->inventory->changeLockedProductInWarehouse($product, $detail->variante_id === null ? null : (int) $detail->variante_id,
                    'salida', $quantity, 'Despacho de transferencia '.$locked->codigo, $actor, (int) $locked->empresa_id,
                    (int) $locked->sede_origen_id, (int) $locked->almacen_origen_id, 'transferencia_despacho', (string) $locked->getKey());
                if ($this->isSerialized($detail)) {
                    $devices = $this->findAvailableDevices($detail, (int) $locked->almacen_origen_id, $operation['series']);
                    abort_unless($devices->count() === $quantity, 422, 'Identifica un IMEI o numero de serie distinto por cada unidad despachada.');
                    foreach ($devices as $device) {
                        $this->changeDevice($device, $movement, 'disponible', 'en_transito', null);
                    }
                } else {
                    abort_if($operation['series'] !== [], 422, 'El detalle no requiere IMEI o numero de serie.');
                }
                $detail->cantidad_enviada = (int) $detail->cantidad_enviada + $quantity;
                $detail->save();
            }
            $beforeInTransit = ['estado' => $locked->estado];
            $locked->estado = 'despachada';
            $locked->enviado_en ??= now();
            $locked->save();
            $this->audit->record($actor->getKey(), 'transfer.dispatched', 'transferencias_existencias', $locked->getKey(), $beforeInTransit,
                ['estado' => 'despachada', 'enviado_en' => $locked->enviado_en?->toISOString(), 'empresa_id' => $locked->empresa_id,
                    'sede_origen_id' => $locked->sede_origen_id, 'almacen_origen_id' => $locked->almacen_origen_id], $ip,
                scope: ['empresa_id' => (int) $locked->empresa_id, 'sede_id' => (int) $locked->sede_origen_id],
                eventId: $this->eventId($locked->getKey(), $actor->getKey(), 'dispatch_stage', $data['clave_idempotencia']));
            $locked->estado = 'en tránsito';
            $locked->save();

            return [$before, [
                'estado' => $locked->estado, 'enviado_en' => $locked->enviado_en?->toISOString(),
                'cantidades' => array_map(fn (array $entry): array => [
                    'detalle_id' => $entry[0]->getKey(), 'cantidad_despachada' => $entry[1]['cantidad'],
                    'cantidad_enviada_acumulada' => (int) $entry[0]->cantidad_enviada,
                ], $details),
            ], null];
        });
    }

    /** @param array<string, mixed> $data */
    public function receive(TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip): TransferenciaExistencia
    {
        $this->authorization->assertAllowed($actor, 'gestion.inventario.transferir');

        return $this->transition($transfer, $actor, 'receive', $data['clave_idempotencia'], $ip, function (TransferenciaExistencia $locked) use ($data, $actor): array {
            abort_unless(in_array($locked->estado, ['en tránsito', 'recibida parcialmente'], true), 409, 'La transferencia no tiene unidades disponibles para recepcion.');
            $this->authorizeTransferSite($actor, $locked, 'sede_destino_id', 'gestion.inventario.transferir');
            $this->assertWarehouseStillActive($locked);
            $details = $this->requestedDetails($locked, $data['items'] ?? []);
            $before = ['estado' => $locked->estado];
            foreach ($details as [$detail, $operation]) {
                $product = Producto::query()->whereKey($detail->producto_id)->lockForUpdate()->firstOrFail();
                $quantity = $operation['cantidad'];
                $remaining = (int) $detail->cantidad_enviada - (int) $detail->cantidad_recibida
                    - $this->returnedQuantity($detail) - $this->reconciledQuantity($detail);
                abort_if($quantity > $remaining, 422, 'La recepcion supera las unidades que siguen en transito.');
                $movement = $this->inventory->changeLockedProductInWarehouse($product, $detail->variante_id === null ? null : (int) $detail->variante_id,
                    'entrada', $quantity, 'Recepcion de transferencia '.$locked->codigo, $actor, (int) $locked->empresa_id,
                    (int) $locked->sede_destino_id, (int) $locked->almacen_destino_id, 'transferencia_recepcion', (string) $locked->getKey(), true);
                if ($this->isSerialized($detail)) {
                    $devices = $this->findTransitDevices($locked, $detail, $operation['series']);
                    abort_unless($devices->count() === $quantity, 422, 'Identifica cada IMEI o numero de serie recibido.');
                    foreach ($devices as $device) {
                        $this->changeDevice($device, $movement, 'en_transito', 'disponible', (int) $locked->almacen_destino_id);
                    }
                } else {
                    abort_if($operation['series'] !== [], 422, 'El detalle no requiere IMEI o numero de serie.');
                }
                $detail->cantidad_recibida = (int) $detail->cantidad_recibida + $quantity;
                $detail->save();
            }
            $locked->load('detalles');
            $allDispatched = $locked->detalles->every(fn (DetalleTransferenciaExistencia $detail): bool => (int) $detail->cantidad_enviada === (int) $detail->cantidad_solicitada);
            $allReceived = $locked->detalles->every(fn (DetalleTransferenciaExistencia $detail): bool => (int) $detail->cantidad_recibida === (int) $detail->cantidad_enviada);
            $receivedAny = $locked->detalles->sum(fn (DetalleTransferenciaExistencia $detail): int => (int) $detail->cantidad_recibida) > 0;
            if ($allDispatched && $allReceived) {
                $locked->estado = 'recibida totalmente';
                $locked->recibido_en = now();
            } elseif ($receivedAny) {
                $locked->estado = 'recibida parcialmente';
                $locked->recibido_en = now();
            } else {
                $locked->estado = 'en tránsito';
            }
            $locked->save();

            return [$before, [
                'estado' => $locked->estado, 'recibido_en' => $locked->recibido_en?->toISOString(),
                'cantidades' => array_map(fn (array $entry): array => [
                    'detalle_id' => $entry[0]->getKey(), 'cantidad_recibida' => $entry[1]['cantidad'],
                    'cantidad_recibida_acumulada' => (int) $entry[0]->cantidad_recibida,
                ], $details),
            ], null];
        });
    }

    /** @param array<string, mixed> $data */
    public function returnToOrigin(TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip): TransferenciaExistencia
    {
        $this->authorization->assertAllowed($actor, 'gestion.inventario.transferir');
        abort_unless(Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_devuelta'), 503, 'La conciliacion requiere autorizar y ejecutar la migracion independiente de transferencias.');

        return $this->transition($transfer, $actor, 'return', $data['clave_idempotencia'], $ip, function (TransferenciaExistencia $locked) use ($data, $actor): array {
            abort_unless(in_array($locked->estado, ['en tránsito', 'recibida parcialmente'], true), 409, 'No hay unidades pendientes para devolver al origen.');
            $this->authorizeTransferSite($actor, $locked, 'sede_origen_id', 'gestion.inventario.transferir');
            $this->assertWarehouseStillActive($locked);
            $details = $this->requestedDetails($locked, $data['items'] ?? []);
            $reason = trim((string) ($data['motivo'] ?? ''));
            abort_if($reason === '', 422, 'Documenta el motivo de la devolucion al origen.');
            $before = ['estado' => $locked->estado];
            foreach ($details as [$detail, $operation]) {
                $quantity = $operation['cantidad'];
                $remaining = (int) $detail->cantidad_enviada - (int) $detail->cantidad_recibida
                    - (int) $detail->cantidad_devuelta - $this->reconciledQuantity($detail);
                abort_if($quantity > $remaining, 422, 'La cantidad devuelta supera las unidades pendientes en transito.');
                $product = Producto::query()->whereKey($detail->producto_id)->lockForUpdate()->firstOrFail();
                $movement = $this->inventory->changeLockedProductInWarehouse($product, $detail->variante_id === null ? null : (int) $detail->variante_id,
                    'entrada', $quantity, 'Reversion de transferencia '.$locked->codigo.': '.$reason, $actor, (int) $locked->empresa_id,
                    (int) $locked->sede_origen_id, (int) $locked->almacen_origen_id, 'transferencia_retorno', (string) $locked->getKey());
                if ($this->isSerialized($detail)) {
                    $devices = $this->findTransitDevices($locked, $detail, $operation['series']);
                    abort_unless($devices->count() === $quantity, 422, 'Identifica cada IMEI o numero de serie devuelto.');
                    foreach ($devices as $device) {
                        $this->changeDevice($device, $movement, 'en_transito', 'disponible', (int) $locked->almacen_origen_id);
                    }
                } else {
                    abort_if($operation['series'] !== [], 422, 'El detalle no requiere IMEI o numero de serie.');
                }
                $detail->cantidad_devuelta = (int) $detail->cantidad_devuelta + $quantity;
                $detail->save();
            }

            return [$before, [
                'estado' => $locked->estado, 'motivo' => $reason,
                'cantidades' => array_map(fn (array $entry): array => [
                    'detalle_id' => $entry[0]->getKey(), 'cantidad_devuelta' => $entry[1]['cantidad'],
                    'cantidad_devuelta_acumulada' => (int) $entry[0]->cantidad_devuelta,
                ], $details),
            ], $reason];
        });
    }

    /** @param array<string, mixed> $data */
    public function reject(TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip): TransferenciaExistencia
    {
        $this->authorization->assertAllowed($actor, 'gestion.inventario.aprobar');

        return $this->finishBeforeDispatch($transfer, $data, $actor, $ip, 'rechazada');
    }

    /** @param array<string, mixed> $data */
    public function reconcileDifference(TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip): TransferenciaExistencia
    {
        $this->authorization->assertAllowed($actor, 'gestion.inventario.transferir');
        abort_unless(Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_devuelta')
            && Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_conciliada'), 503,
            'La conciliacion cuantificada requiere autorizar y ejecutar la migracion independiente de transferencias.');

        return $this->transition($transfer, $actor, 'reconcile', $data['clave_idempotencia'], $ip, function (TransferenciaExistencia $locked) use ($data, $actor): array {
            abort_unless(in_array($locked->estado, ['en tránsito', 'recibida parcialmente'], true), 409, 'No hay diferencias de despacho pendientes para conciliar.');
            $this->authorizeTransferSite($actor, $locked, 'sede_destino_id', 'gestion.inventario.transferir');
            $details = $this->requestedDetails($locked, $data['items'] ?? []);
            $reason = trim((string) ($data['motivo'] ?? ''));
            abort_unless(mb_strlen($reason) >= 5 && mb_strlen($reason) <= 500, 422, 'Documenta la causa de la diferencia con al menos cinco caracteres.');
            $before = ['estado' => $locked->estado];
            foreach ($details as [$detail, $operation]) {
                $quantity = $operation['cantidad'];
                $remaining = (int) $detail->cantidad_enviada - (int) $detail->cantidad_recibida
                    - (int) $detail->cantidad_devuelta - (int) $detail->cantidad_conciliada;
                abort_if($quantity > $remaining, 422, 'La cantidad conciliada supera las unidades que siguen sin recibirse ni devolverse.');
                if ($this->isSerialized($detail)) {
                    $devices = $this->findTransitDevices($locked, $detail, $operation['series']);
                    abort_unless($devices->count() === $quantity, 422, 'Identifica cada IMEI o numero de serie de la diferencia.');
                    foreach ($devices as $device) {
                        $movementId = DB::table('movimientos_dispositivos')
                            ->where('dispositivo_id', $device->id)->where('estado_nuevo', 'en_transito')
                            ->orderByDesc('id')->value('movimiento_inventario_id');
                        abort_unless(is_numeric($movementId) && DB::table('movimientos_inventario')->where('id', $movementId)
                            ->where('documento_tipo', 'transferencia_despacho')->where('documento_id', (string) $locked->getKey())->exists(),
                            409, 'La trazabilidad del dispositivo requiere conciliacion manual.');
                        DB::table('dispositivos')->where('id', $device->id)->where('estado', 'en_transito')
                            ->update(['estado' => 'discrepancia_transferencia', 'almacen_actual_id' => null]);
                        DB::table('movimientos_dispositivos')->insert([
                            'dispositivo_id' => $device->id, 'movimiento_inventario_id' => $movementId,
                            'estado_anterior' => 'en_transito', 'estado_nuevo' => 'discrepancia_transferencia', 'creado_en' => now(),
                        ]);
                    }
                } else {
                    abort_if($operation['series'] !== [], 422, 'El detalle no requiere IMEI o numero de serie.');
                }
                $detail->cantidad_conciliada = (int) $detail->cantidad_conciliada + $quantity;
                $detail->save();
            }

            return [$before, [
                'estado' => $locked->estado, 'motivo' => $reason,
                'cantidades' => array_map(fn (array $entry): array => [
                    'detalle_id' => $entry[0]->getKey(), 'cantidad_conciliada' => $entry[1]['cantidad'],
                    'cantidad_conciliada_acumulada' => (int) $entry[0]->cantidad_conciliada,
                ], $details),
            ], $reason];
        });
    }

    /** @param array<string, mixed> $data */
    public function cancel(TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip): TransferenciaExistencia
    {
        $this->authorization->assertAllowed($actor, 'gestion.inventario.transferir');

        return $this->finishBeforeDispatch($transfer, $data, $actor, $ip, 'cancelada');
    }

    /** @param array<string, mixed> $data */
    public function close(TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip): TransferenciaExistencia
    {
        $this->authorization->assertAllowed($actor, 'gestion.inventario.transferir');

        return $this->transition($transfer, $actor, 'close', $data['clave_idempotencia'], $ip, function (TransferenciaExistencia $locked) use ($actor, $data): array {
            abort_unless(in_array($locked->estado, ['en tránsito', 'recibida totalmente', 'recibida parcialmente'], true), 409, 'La transferencia no esta lista para cierre.');
            $this->authorizeTransferSite($actor, $locked, 'sede_destino_id', 'gestion.inventario.transferir');
            $locked->load('detalles');
            $unresolvedTransit = $locked->detalles->contains(fn (DetalleTransferenciaExistencia $detail): bool => (int) $detail->cantidad_enviada !== (int) $detail->cantidad_recibida
                    + $this->returnedQuantity($detail) + $this->reconciledQuantity($detail));
            abort_unless(! $unresolvedTransit, 409, 'Hay unidades enviadas que siguen en transito; recibe, devuelve o concilia cada diferencia.');
            $notDispatched = (int) $locked->detalles->sum(fn (DetalleTransferenciaExistencia $detail): int => max(0, (int) $detail->cantidad_solicitada - (int) $detail->cantidad_enviada));
            $returned = (int) $locked->detalles->sum(fn (DetalleTransferenciaExistencia $detail): int => $this->returnedQuantity($detail));
            $reconciled = (int) $locked->detalles->sum(fn (DetalleTransferenciaExistencia $detail): int => $this->reconciledQuantity($detail));
            $reason = trim((string) ($data['motivo'] ?? ''));
            if ($notDispatched > 0 || $returned > 0 || $reconciled > 0) {
                abort_unless(mb_strlen($reason) >= 5 && mb_strlen($reason) <= 500, 422, 'Documenta las diferencias antes de cerrar la transferencia.');
            }
            $before = ['estado' => $locked->estado];
            $locked->estado = 'cerrada';
            $locked->save();
            $lines = $locked->detalles->map(fn (DetalleTransferenciaExistencia $detail): array => [
                'detalle_id' => $detail->getKey(), 'producto_id' => (int) $detail->producto_id,
                'cantidad_solicitada' => (int) $detail->cantidad_solicitada, 'cantidad_enviada' => (int) $detail->cantidad_enviada,
                'cantidad_recibida' => (int) $detail->cantidad_recibida, 'cantidad_devuelta' => $this->returnedQuantity($detail),
                'cantidad_conciliada' => $this->reconciledQuantity($detail),
            ])->values()->all();

            return [$before, [
                'estado' => $locked->estado, 'motivo' => $reason ?: null,
                'unidades_no_despachadas' => $notDispatched, 'unidades_devueltas' => $returned,
                'unidades_conciliadas' => $reconciled, 'detalles' => $lines,
            ], $reason ?: null];
        });
    }

    /** @param array<string, mixed> $data */
    private function finishBeforeDispatch(TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip, string $targetStatus): TransferenciaExistencia
    {
        $event = $targetStatus === 'rechazada' ? 'reject' : 'cancel';

        return $this->transition($transfer, $actor, $event, $data['clave_idempotencia'], $ip, function (TransferenciaExistencia $locked) use ($actor, $data, $targetStatus): array {
            abort_unless(in_array($locked->estado, ['solicitada', 'aprobada'], true), 409, 'La transferencia no puede rechazarse ni cancelarse despues del despacho.');
            $permission = $targetStatus === 'rechazada' ? 'gestion.inventario.aprobar' : 'gestion.inventario.transferir';
            $this->authorizeTransferSite($actor, $locked, 'sede_origen_id', $permission);
            $reason = trim((string) ($data['motivo'] ?? ''));
            abort_if($reason === '', 422, 'Indica el motivo del rechazo o cancelacion.');
            $before = ['estado' => $locked->estado];
            $locked->estado = $targetStatus;
            $locked->save();

            return [$before, ['estado' => $targetStatus, 'motivo' => $reason], $reason];
        });
    }

    /** @param callable(TransferenciaExistencia):array{0:array<string,mixed>,1:array<string,mixed>,2:?string} $operation */
    private function transition(TransferenciaExistencia $transfer, User $actor, string $action, string $key, ?string $ip, callable $operation): TransferenciaExistencia
    {
        return DB::transaction(function () use ($transfer, $actor, $action, $key, $ip, $operation): TransferenciaExistencia {
            $eventId = $this->eventId($transfer->getKey(), $actor->getKey(), $action, $key);
            if (DB::table('registros_auditoria')->where('identificador_universal_evento', $eventId)->exists()) {
                return TransferenciaExistencia::query()->with('detalles')->findOrFail($transfer->getKey());
            }
            $locked = TransferenciaExistencia::query()->whereKey($transfer->getKey())->lockForUpdate()->firstOrFail();
            if (DB::table('registros_auditoria')->where('identificador_universal_evento', $eventId)->exists()) {
                return $locked->load('detalles');
            }
            $siteColumn = in_array($action, ['receive', 'reconcile', 'close'], true) ? 'sede_destino_id' : 'sede_origen_id';
            $permission = in_array($action, ['approve', 'reject'], true) ? 'gestion.inventario.aprobar' : 'gestion.inventario.transferir';
            $this->authorizeTransferSite($actor, $locked, $siteColumn, $permission);
            [$before, $after, $observations] = $operation($locked);
            $this->audit->record($actor->getKey(), 'transfer.'.$action, 'transferencias_existencias', $locked->getKey(), $before,
                $after + ['empresa_id' => $locked->empresa_id, 'sede_origen_id' => $locked->sede_origen_id,
                    'almacen_origen_id' => $locked->almacen_origen_id, 'sede_destino_id' => $locked->sede_destino_id,
                    'almacen_destino_id' => $locked->almacen_destino_id], $ip, observations: $observations,
                scope: ['empresa_id' => (int) $locked->empresa_id, 'sede_id' => (int) $locked->{$siteColumn}], eventId: $eventId);

            return $locked->load('detalles');
        }, 3);
    }

    /** @return array{empresa_id:int,sede_id:int,almacen_id:int} */
    private function warehouseContext(User $actor, int $warehouseId, ?string $permission, ?int $companyId = null): array
    {
        $warehouse = DB::table('almacenes')->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
            ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')->where('almacenes.id', $warehouseId)
            ->where('almacenes.activo', true)->where('sedes.activa', true)->where('empresas.activo', true)
            ->first(['empresas.id as empresa_id', 'sedes.id as sede_id', 'almacenes.id as almacen_id']);
        abort_unless($warehouse !== null, 422, 'El almacen indicado no esta habilitado.');
        $companyId ??= (int) $warehouse->empresa_id;
        abort_unless((int) $warehouse->empresa_id === $companyId, 422, 'El almacen no pertenece a la empresa de la transferencia.');
        if ($permission !== null) {
            abort_unless($this->authorization->allowsRecord($actor, $permission, $companyId, (int) $warehouse->sede_id), 403, 'No tienes permiso sobre la sede seleccionada.');
        }

        return ['empresa_id' => $companyId, 'sede_id' => (int) $warehouse->sede_id, 'almacen_id' => (int) $warehouse->almacen_id];
    }

    /** @return array{empresa_id:int,sede_id:?int} */
    private function currentCompany(User $actor): array
    {
        $permission = 'gestion.inventario.consultar';
        if ($this->authorization->allowsGlobal($actor, $permission)) {
            $companies = DB::table('empresas')->where('activo', true)->limit(2)->pluck('id');
            abort_unless($companies->count() === 1, 403);

            return ['empresa_id' => (int) $companies->first(), 'sede_id' => null];
        }
        $siteId = $this->authorization->activeSiteIdFor($actor);
        abort_unless($siteId !== null, 403, 'Selecciona una sede autorizada para consultar transferencias.');
        $site = DB::table('sedes')->where('id', $siteId)->where('activa', true)->first(['empresa_id']);
        abort_unless($site !== null && $this->authorization->allowsRecord($actor, $permission, (int) $site->empresa_id, $siteId), 403);

        return ['empresa_id' => (int) $site->empresa_id, 'sede_id' => $siteId];
    }

    /** @param array{empresa_id:int,sede_id:?int} $location
     * @param  array<string, mixed>  $filters
     * @return Builder<TransferenciaExistencia>
     */
    private function transferQuery(array $location, array $filters): Builder
    {
        return TransferenciaExistencia::query()->with([
            'sedeOrigen:id,nombre', 'sedeDestino:id,nombre', 'almacenOrigen:id,nombre', 'almacenDestino:id,nombre',
            'solicitante:id,nombre', 'aprobador:id,nombre', 'detalles.producto:id,nombre,marca', 'detalles.variante:id,nombre_color,codigo_sku',
        ])->where('empresa_id', $location['empresa_id'])
            ->when($location['sede_id'] !== null, fn (Builder $query): Builder => $query->where(fn (Builder $scope): Builder => $scope
                ->where('sede_origen_id', $location['sede_id'])->orWhere('sede_destino_id', $location['sede_id'])))
            ->when($filters['estado'] ?? null, fn (Builder $query, string $status): Builder => $query->where('estado', $status))
            ->when($filters['buscar'] ?? null, function (Builder $query, string $search): Builder {
                return $query->where(function (Builder $matching) use ($search): void {
                    $matching->where('codigo', 'like', '%'.$search.'%')
                        ->orWhereHas('detalles.producto', fn (Builder $products): Builder => $products->where('nombre', 'like', '%'.$search.'%'));
                });
            });
    }

    private function authorizeTransferSite(User $actor, TransferenciaExistencia $transfer, string $siteColumn, string $permission): void
    {
        abort_unless($this->authorization->allowsRecord($actor, $permission, (int) $transfer->empresa_id, (int) $transfer->{$siteColumn}), 403, 'No tienes permiso para operar transferencias en esta sede.');
    }

    private function assertWarehouseStillActive(TransferenciaExistencia $transfer): void
    {
        foreach ([['almacen_origen_id', 'sede_origen_id'], ['almacen_destino_id', 'sede_destino_id']] as [$warehouseColumn, $siteColumn]) {
            $valid = DB::table('almacenes')->join('sedes', 'sedes.id', '=', 'almacenes.sede_id')
                ->where('almacenes.id', $transfer->{$warehouseColumn})->where('almacenes.sede_id', $transfer->{$siteColumn})
                ->where('sedes.empresa_id', $transfer->empresa_id)->where('almacenes.activo', true)->where('sedes.activa', true)->exists();
            abort_unless($valid, 409, 'Una sede o almacen de la transferencia ya no esta activo.');
        }
    }

    private function balance(TransferenciaExistencia $transfer, DetalleTransferenciaExistencia $detail, int $warehouseId, bool $lock): object
    {
        $siteId = $warehouseId === (int) $transfer->almacen_origen_id ? $transfer->sede_origen_id : $transfer->sede_destino_id;
        $query = DB::table('existencias_almacen')->where('empresa_id', $transfer->empresa_id)->where('sede_id', $siteId)
            ->where('almacen_id', $warehouseId)->where('producto_id', $detail->producto_id);
        if ($detail->variante_id === null) {
            $query->whereNull('variante_id');
        } else {
            $query->where('variante_id', $detail->variante_id);
        }
        if ($lock) {
            $query->lockForUpdate();
        }
        $balance = $query->first(['cantidad_fisica', 'cantidad_reservada']);
        abort_unless($balance !== null, 409, 'No existe saldo para el producto o variante en el almacen.');

        return $balance;
    }

    private function assertProductVariant(int $productId, ?int $variantId): void
    {
        if ($variantId !== null) {
            abort_unless(DB::table('variantes_productos')->where('id', $variantId)->where('producto_id', $productId)->exists(), 422, 'La variante no pertenece al producto seleccionado.');
        }
    }

    private function isSerialized(DetalleTransferenciaExistencia $detail): bool
    {
        if ($detail->variante_id !== null && (bool) DB::table('variantes_productos')->where('id', $detail->variante_id)->value('controla_serie')) {
            return true;
        }

        return DB::table('dispositivos')->where('producto_id', $detail->producto_id)
            ->when($detail->variante_id === null, fn ($query) => $query->whereNull('variante_id'), fn ($query) => $query->where('variante_id', $detail->variante_id))->exists();
    }

    private function availableDeviceQuery(DetalleTransferenciaExistencia $detail, int $warehouseId): QueryBuilder
    {
        return DB::table('dispositivos')->where('producto_id', $detail->producto_id)
            ->when($detail->variante_id === null, fn ($query) => $query->whereNull('variante_id'), fn ($query) => $query->where('variante_id', $detail->variante_id))
            ->where('almacen_actual_id', $warehouseId)->where('estado', 'disponible');
    }

    /** @return Collection<int, object> */
    private function findAvailableDevices(DetalleTransferenciaExistencia $detail, int $warehouseId, array $serials): Collection
    {
        $this->assertUniqueSerials($serials);
        abort_unless($serials !== [], 422, 'Indica los IMEI o numeros de serie que se despacharan.');

        return $this->availableDeviceQuery($detail, $warehouseId)->lockForUpdate()
            ->where(function (QueryBuilder $query) use ($serials): void {
                $query->whereIn('imei', $serials)->orWhereIn('numero_serie', $serials);
            })->get();
    }

    /** @return Collection<int, object> */
    private function findTransitDevices(TransferenciaExistencia $transfer, DetalleTransferenciaExistencia $detail, array $serials): Collection
    {
        $this->assertUniqueSerials($serials);
        abort_unless($serials !== [], 422, 'Indica los IMEI o numeros de serie que se procesaran.');

        return DB::table('dispositivos')->join('movimientos_dispositivos', 'movimientos_dispositivos.dispositivo_id', '=', 'dispositivos.id')
            ->join('movimientos_inventario', 'movimientos_inventario.id', '=', 'movimientos_dispositivos.movimiento_inventario_id')
            ->where('dispositivos.producto_id', $detail->producto_id)
            ->when($detail->variante_id === null, fn ($query) => $query->whereNull('dispositivos.variante_id'), fn ($query) => $query->where('dispositivos.variante_id', $detail->variante_id))
            ->where('dispositivos.estado', 'en_transito')->where('movimientos_inventario.documento_tipo', 'transferencia_despacho')
            ->where('movimientos_inventario.documento_id', (string) $transfer->getKey())
            ->where(function (QueryBuilder $query) use ($serials): void {
                $query->whereIn('dispositivos.imei', $serials)->orWhereIn('dispositivos.numero_serie', $serials);
            })->distinct()->lockForUpdate()->get('dispositivos.*');
    }

    private function changeDevice(object $device, MovimientoInventario $movement, string $oldStatus, string $newStatus, ?int $warehouseId): void
    {
        abort_unless($device->estado === $oldStatus, 409, 'El IMEI o numero de serie ya fue procesado por otra operacion.');
        DB::table('dispositivos')->where('id', $device->id)->update([
            'estado' => $newStatus, 'almacen_actual_id' => $warehouseId, 'fecha_salida' => $newStatus === 'en_transito' ? now() : null,
        ]);
        DB::table('movimientos_dispositivos')->insert([
            'dispositivo_id' => $device->id, 'movimiento_inventario_id' => $movement->getKey(),
            'estado_anterior' => $oldStatus, 'estado_nuevo' => $newStatus, 'creado_en' => now(),
        ]);
    }

    /** @return array<int, array{0:DetalleTransferenciaExistencia,1:array{cantidad:int,series:array<int,string>}}> */
    private function requestedDetails(TransferenciaExistencia $transfer, array $items): array
    {
        abort_if($items === [], 422, 'Selecciona al menos un producto para procesar.');
        $details = $transfer->detalles()->whereIn('id', collect($items)->pluck('detalle_id'))->lockForUpdate()->get()->keyBy('id');
        abort_unless($details->count() === count($items), 422, 'Uno o mas detalles no pertenecen a esta transferencia.');
        $result = [];
        foreach ($items as $item) {
            $detail = $details->get((int) $item['detalle_id']);
            abort_unless($detail !== null, 422, 'Uno o mas detalles no pertenecen a esta transferencia.');
            $series = preg_split('/[\r\n,;]+/', (string) ($item['series'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $result[] = [$detail, ['cantidad' => (int) $item['cantidad'], 'series' => array_values(array_map('trim', $series))]];
        }

        return $result;
    }

    /** @param array<int, string> $serials */
    private function assertUniqueSerials(array $serials): void
    {
        $serials = array_values(array_filter(array_map('trim', $serials), static fn (string $serial): bool => $serial !== ''));
        abort_if(count($serials) !== count(array_unique($serials)), 422, 'No repitas IMEI o numeros de serie.');
    }

    private function returnedQuantity(DetalleTransferenciaExistencia $detail): int
    {
        return Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_devuelta') ? (int) $detail->cantidad_devuelta : 0;
    }

    private function reconciledQuantity(DetalleTransferenciaExistencia $detail): int
    {
        return Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_conciliada') ? (int) $detail->cantidad_conciliada : 0;
    }

    private function newCode(int $companyId): string
    {
        do {
            $code = 'TRF-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (DB::table('transferencias_existencias')->where('empresa_id', $companyId)->where('codigo', $code)->exists());

        return $code;
    }

    private function eventId(int $transferId, int $actorId, string $action, string $key): string
    {
        $hash = substr(hash('sha256', implode('|', [$transferId, $actorId, $action, Str::lower($key)])), 0, 32);
        $hash[12] = '5';
        $hash[16] = dechex((hexdec($hash[16]) & 0x3) | 0x8);

        return substr($hash, 0, 8).'-'.substr($hash, 8, 4).'-'.substr($hash, 12, 4).'-'.substr($hash, 16, 4).'-'.substr($hash, 20, 12);
    }
}
