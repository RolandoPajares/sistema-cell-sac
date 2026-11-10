<?php

namespace App\Servicios\Gestion\Comercial\Compras;

use App\Models\Catalogo\Producto;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ServicioCompras dentro de su m�dulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioCompras
{
    private const MAX_CENTIMOS = 999999999999;

    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operaci�n.
     */
    public function __construct(
        private readonly ServicioInventario $inventario,
        private readonly RegistradorAuditoria $auditoria,
        private readonly ServicioAutorizacion $autorizacion,
    ) {}

    /**
     * Registra la informaci�n recibida y coordina su persistencia.
     *
     * @param  mixed  $datos  Par�metro recibido por la operaci�n.
     * @param  mixed  $actor  Par�metro recibido por la operaci�n.
     * @param  mixed  $token  Par�metro recibido por la operaci�n.
     * @param  mixed  $ip  Par�metro recibido por la operaci�n.
     * @return Compra Resultado de la operaci�n.
     */
    public function registrar(array $datos, User $actor, string $token, ?string $ip): Compra
    {
        $this->autorizacion->assertAllowed($actor, 'gestion.compras.crear');
        abort_unless(Schema::hasColumn('registros_auditoria', 'identificador_universal_evento'), 503, 'No est� disponible la protecci�n contra compras duplicadas.');
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/', $token) === 1, 422, 'La solicitud de compra no es v�lida.');
        $ubicacion = $this->autorizacion->operationLocation($actor);
        abort_unless($ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null, 503, 'No hay un almac�n autorizado disponible.');
        abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.compras.crear', $ubicacion['empresa_id'], $ubicacion['sede_id']), 403);
        $hash = hash('sha256', 'compra:'.$actor->getKey().':'.$token);
        $evento = substr($hash, 0, 8).'-'.substr($hash, 8, 4).'-4'.substr($hash, 13, 3).'-a'.substr($hash, 17, 3).'-'.substr($hash, 20, 12);

        return DB::transaction(function () use ($datos, $actor, $ip, $ubicacion, $evento): Compra {
            DB::table('empresas')->where('id', $ubicacion['empresa_id'])->lockForUpdate()->firstOrFail();
            $previo = DB::table('registros_auditoria')->where('identificador_universal_evento', $evento)->lockForUpdate()->first();
            if ($previo !== null) {
                abort_unless($previo->accion === 'compra.registrada' && $previo->entidad === 'compras' && (int) $previo->usuario_id === (int) $actor->id, 409, 'La solicitud ya fue utilizada.');
                $compra = Compra::query()->whereKey($previo->entidad_id)->lockForUpdate()->firstOrFail();
                abort_unless((int) $compra->empresa_id === $ubicacion['empresa_id'] && (int) $compra->sede_id === $ubicacion['sede_id']
                    && (int) $compra->almacen_id === $ubicacion['almacen_id'] && (int) $compra->proveedor_id === (int) $datos['proveedor_id']
                    && $compra->fecha_compra->toDateString() === $datos['fecha_compra'], 409, 'La solicitud ya corresponde a otra compra.');
                $anteriores = $compra->details()->orderBy('producto_id')->get()->map(fn ($linea): array => [(int) $linea->producto_id, $linea->variante_id === null ? null : (int) $linea->variante_id, (int) $linea->cantidad, $this->centimos((string) $linea->costo_unitario)])->all();
                $solicitados = collect($datos['items'])->sortBy('producto_id')->map(fn (array $linea): array => [(int) $linea['producto_id'], isset($linea['variante_id']) ? (int) $linea['variante_id'] : null, (int) $linea['cantidad'], $this->centimos((string) $linea['costo_unitario'])])->values()->all();
                abort_unless($anteriores === $solicitados, 409, 'La solicitud ya corresponde a otros productos o costos.');

                return $compra;
            }

            $proveedor = Proveedor::query()->whereKey($datos['proveedor_id'])->where('empresa_id', $ubicacion['empresa_id'])->where('activo', true)->first();
            abort_unless($proveedor !== null, 422, 'El proveedor no est� activo en la empresa autorizada.');
            $items = collect($datos['items'])->sortBy('producto_id')->values();
            abort_if($items->isEmpty() || $items->count() > 100 || $items->pluck('producto_id')->unique()->count() !== $items->count(), 422, 'Los productos de la compra no son v�lidos.');
            $productos = Producto::query()->whereIn('id', $items->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $lineas = [];
            $total = 0;
            foreach ($items as $item) {
                $producto = $productos->get((int) $item['producto_id']);
                $cantidad = filter_var($item['cantidad'], FILTER_VALIDATE_INT);
                abort_unless($producto instanceof Producto && $producto->activo && (int) $producto->empresa_id === $ubicacion['empresa_id'], 422, 'Un producto no est� activo en la empresa autorizada.');
                abort_if($cantidad === false || $cantidad < 1 || $cantidad > 1000, 422, 'La cantidad no es v�lida.');
                $variantesProducto = DB::table('variantes_productos')->where('producto_id', $producto->id);
                $varianteId = isset($item['variante_id']) && $item['variante_id'] !== '' ? (int) $item['variante_id'] : null;
                if ((clone $variantesProducto)->exists()) {
                    abort_unless($varianteId !== null, 422, 'Selecciona la variante que se comprará.');
                    $varianteValida = (clone $variantesProducto)->where('id', $varianteId)->where('estado', 'activo')->exists();
                    abort_unless($varianteValida, 422, 'La variante seleccionada no está activa o no pertenece al producto.');
                } else {
                    abort_unless($varianteId === null, 422, 'El producto seleccionado no tiene variantes activas.');
                }
                $costo = $this->centimos((string) $item['costo_unitario']);
                $descuento = $this->centimos(number_format((float) ($item['descuento'] ?? 0), 2, '.', ''));
                $impuesto = $this->centimos(number_format((float) ($item['impuesto'] ?? 0), 2, '.', ''));
                $importe = ($costo * $cantidad) - $descuento + $impuesto;
                abort_if($importe < 0, 422, 'El descuento no puede superar el importe de la l�nea.');
                abort_if($importe > self::MAX_CENTIMOS - $total, 422, 'La compra supera el importe permitido.');
                $total += $importe;
                $linea = ['producto_id' => $producto->id, 'variante_id' => $varianteId, 'cantidad' => $cantidad, 'costo_unitario' => $this->importe($costo)];
                if (Schema::hasColumn('compra_detalles', 'descuento')) {
                    $linea['descuento'] = $this->importe($descuento);
                    $linea['impuesto'] = $this->importe($impuesto);
                    $linea['total_linea'] = $this->importe($importe);
                }
                $lineas[] = $linea;
            }
            $compraDatos = [...$ubicacion, 'proveedor_id' => $proveedor->id, 'creado_por_usuario_id' => $actor->id,
                'total' => $this->importe($total), 'estado' => 'Pendiente', 'fecha_compra' => $datos['fecha_compra'], 'activo' => true, 'creado_en' => now()];
            if (Schema::hasColumn('compras', 'numero_referencia')) {
                $compraDatos['numero_referencia'] = $datos['numero_referencia'] ?? null;
                $compraDatos['moneda'] = $datos['moneda'] ?? 'PEN';
            }
            $compra = Compra::create($compraDatos);
            foreach ($lineas as $linea) {
                $compra->details()->create($linea);
            }
            $this->auditoria->record($actor->id, 'compra.registrada', 'compras', $compra->id, null,
                ['proveedor_id' => $proveedor->id, 'total' => $compra->total, 'items' => count($lineas)], $ip,
                scope: ['empresa_id' => $ubicacion['empresa_id'], 'sede_id' => $ubicacion['sede_id']], eventId: $evento);

            return $compra;
        }, 3);
    }

    /**
     * Ejecuta la operaci�n aprobar definida por la clase ServicioCompras.
     *
     * @param  mixed  $compra  Par�metro recibido por la operaci�n.
     * @param  mixed  $actor  Par�metro recibido por la operaci�n.
     * @param  mixed  $ip  Par�metro recibido por la operaci�n.
     * @return Compra Resultado de la operaci�n.
     */
    /**
     * Actualiza una compra pendiente sin crear un nuevo encabezado ni afectar inventario.
     *
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Compra $compra, array $datos, User $actor, ?string $ip): Compra
    {
        $this->autorizacion->assertAllowed($actor, 'gestion.compras.actualizar');

        return DB::transaction(function () use ($compra, $datos, $actor, $ip): Compra {
            $bloqueada = Compra::query()->whereKey($compra->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.compras.actualizar', (int) $bloqueada->empresa_id, (int) $bloqueada->sede_id), 403);
            abort_unless($bloqueada->activo && $bloqueada->estado === 'Pendiente', 409, 'Solo se pueden editar compras pendientes.');
            abort_if(DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->where('documento_id', (string) $bloqueada->id)->exists(), 409, 'La compra ya afect� inventario y requiere un procedimiento de ajuste.');
            if (Schema::hasTable('pagos')) {
                abort_if(DB::table('pagos')->where('compra_id', $bloqueada->id)->exists(), 409, 'La compra tiene pagos asociados y no puede editarse directamente.');
            }
            if (Schema::hasTable('cuentas_pagar')) {
                abort_if(DB::table('cuentas_pagar')->where('compra_id', $bloqueada->id)->exists(), 409, 'La compra tiene una cuenta por pagar asociada y requiere conciliaci�n.');
            }

            $proveedor = Proveedor::query()->whereKey($datos['proveedor_id'])->where('empresa_id', $bloqueada->empresa_id)->where('activo', true)->first();
            abort_unless($proveedor !== null, 422, 'El proveedor no est� activo en la empresa de la compra.');
            $items = collect($datos['items'])->sortBy('producto_id')->values();
            abort_if($items->isEmpty() || $items->count() > 100 || $items->pluck('producto_id')->unique()->count() !== $items->count(), 422, 'Los productos de la compra no son v�lidos.');
            $productos = Producto::query()->whereIn('id', $items->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $lineas = [];
            $total = 0;
            foreach ($items as $item) {
                $producto = $productos->get((int) $item['producto_id']);
                $cantidad = filter_var($item['cantidad'], FILTER_VALIDATE_INT);
                abort_unless($producto instanceof Producto && $producto->activo && (int) $producto->empresa_id === (int) $bloqueada->empresa_id, 422, 'Un producto no est� activo en la empresa de la compra.');
                abort_if($cantidad === false || $cantidad < 1 || $cantidad > 1000, 422, 'La cantidad no es v�lida.');
                $variantesProducto = DB::table('variantes_productos')->where('producto_id', $producto->id);
                $varianteId = isset($item['variante_id']) && $item['variante_id'] !== '' ? (int) $item['variante_id'] : null;
                if ((clone $variantesProducto)->exists()) {
                    abort_unless($varianteId !== null && (clone $variantesProducto)->where('id', $varianteId)->where('estado', 'activo')->exists(), 422, 'La variante seleccionada no es v�lida.');
                } else {
                    abort_unless($varianteId === null, 422, 'El producto seleccionado no tiene variantes activas.');
                }
                $costo = $this->centimos((string) $item['costo_unitario']);
                $descuento = $this->centimos(number_format((float) ($item['descuento'] ?? 0), 2, '.', ''));
                $impuesto = $this->centimos(number_format((float) ($item['impuesto'] ?? 0), 2, '.', ''));
                $importe = ($costo * $cantidad) - $descuento + $impuesto;
                abort_if($importe < 0, 422, 'El descuento no puede superar el importe de la l�nea.');
                abort_if($importe > self::MAX_CENTIMOS - $total, 422, 'La compra supera el importe permitido.');
                $total += $importe;
                $linea = ['producto_id' => $producto->id, 'variante_id' => $varianteId, 'cantidad' => $cantidad, 'costo_unitario' => $this->importe($costo)];
                if (Schema::hasColumn('compra_detalles', 'descuento')) {
                    $linea['descuento'] = $this->importe($descuento);
                    $linea['impuesto'] = $this->importe($impuesto);
                    $linea['total_linea'] = $this->importe($importe);
                }
                $lineas[] = $linea;
            }

            $anteriores = ['proveedor_id' => $bloqueada->proveedor_id, 'fecha_compra' => $bloqueada->fecha_compra?->toDateString(), 'total' => $bloqueada->total, 'items' => $bloqueada->details()->orderBy('id')->get()->map(fn ($item): array => $item->only(['producto_id', 'variante_id', 'cantidad', 'costo_unitario', 'descuento', 'impuesto', 'total_linea']))->all()];
            $actualizacion = ['proveedor_id' => $proveedor->id, 'fecha_compra' => $datos['fecha_compra'], 'total' => $this->importe($total)];
            if (Schema::hasColumn('compras', 'numero_referencia')) {
                $actualizacion['numero_referencia'] = $datos['numero_referencia'] ?? null;
                $actualizacion['moneda'] = $datos['moneda'] ?? 'PEN';
            }
            if (Schema::hasColumn('compras', 'actualizado_en')) {
                $actualizacion['actualizado_en'] = now();
            }
            $bloqueada->update($actualizacion);
            $bloqueada->details()->delete();
            foreach ($lineas as $linea) {
                $bloqueada->details()->create($linea);
            }
            $nuevos = ['proveedor_id' => $proveedor->id, 'fecha_compra' => $datos['fecha_compra'], 'total' => $bloqueada->total, 'items' => $lineas];
            $this->auditoria->record($actor->id, 'compra.actualizada', 'compras', $bloqueada->id, $anteriores, $nuevos, $ip,
                scope: ['empresa_id' => (int) $bloqueada->empresa_id, 'sede_id' => (int) $bloqueada->sede_id]);

            return $bloqueada->fresh(['supplier', 'details.product', 'details.variante']);
        }, 3);
    }

    public function aprobar(Compra $compra, User $actor, ?string $ip): Compra
    {
        return $this->cambiarEstado($compra, $actor, $ip, false);
    }

    /**
     * Ejecuta la operaci�n recibir definida por la clase ServicioCompras.
     *
     * @param  mixed  $compra  Par�metro recibido por la operaci�n.
     * @param  mixed  $actor  Par�metro recibido por la operaci�n.
     * @param  mixed  $ip  Par�metro recibido por la operaci�n.
     * @return Compra Resultado de la operaci�n.
     */
    public function recibir(Compra $compra, User $actor, ?string $ip): Compra
    {
        return $this->cambiarEstado($compra, $actor, $ip, true);
    }

    /**
     * Ejecuta la operaci�n anular definida por la clase ServicioCompras.
     *
     * @param  mixed  $compra  Par�metro recibido por la operaci�n.
     * @param  mixed  $actor  Par�metro recibido por la operaci�n.
     * @param  mixed  $ip  Par�metro recibido por la operaci�n.
     * @return Compra Resultado de la operaci�n.
     */
    public function anular(Compra $compra, User $actor, ?string $ip): Compra
    {
        $this->autorizacion->assertAllowed($actor, 'gestion.compras.anular');

        return DB::transaction(function () use ($compra, $actor, $ip): Compra {
            $bloqueada = Compra::query()->whereKey($compra->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.compras.anular', (int) $bloqueada->empresa_id, (int) $bloqueada->sede_id), 403);
            abort_unless($bloqueada->activo && $bloqueada->estado === 'Pendiente', 409, 'Solo se pueden anular compras pendientes que a�n no hayan sido aprobadas o recibidas.');
            abort_if(DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->where('documento_id', (string) $bloqueada->id)->exists(), 409, 'La compra ya tiene movimientos de inventario y requiere conciliaci�n.');
            $bloqueada->update(['activo' => false, 'estado' => 'Cancelada']);
            $this->auditoria->record($actor->id, 'compra.anulada', 'compras', $bloqueada->id,
                ['activo' => true, 'estado' => 'Pendiente'], ['activo' => false, 'estado' => 'Cancelada'], $ip,
                scope: ['empresa_id' => (int) $bloqueada->empresa_id, 'sede_id' => (int) $bloqueada->sede_id]);

            return $bloqueada;
        }, 3);
    }

    /**
     * @param  array<int, array{detalle_compra_id:int|string,cantidad:int|string,identificadores?:string|null}>  $items
     */
    public function registrarRecepcionParcial(Compra $compra, array $items, ?string $observaciones, string $token, User $actor, ?string $ip): Compra
    {
        $this->autorizacion->assertAllowed($actor, 'gestion.compras.recibir');
        abort_unless(Schema::hasColumn('registros_auditoria', 'identificador_universal_evento'), 503, 'No esta disponible la proteccion contra recepciones duplicadas.');
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/', $token) === 1, 422, 'La solicitud de recepcion no es valida.');

        $ubicacion = $this->autorizacion->operationLocation($actor);
        abort_unless($ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null, 503, 'No hay un almacen autorizado disponible.');
        abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.compras.recibir', $ubicacion['empresa_id'], $ubicacion['sede_id']), 403);

        $hash = hash('sha256', 'recepcion-compra:'.$actor->getKey().':'.$compra->getKey().':'.$token);
        $evento = substr($hash, 0, 8).'-'.substr($hash, 8, 4).'-4'.substr($hash, 13, 3).'-a'.substr($hash, 17, 3).'-'.substr($hash, 20, 12);
        $itemsNormalizados = collect($items)
            ->map(function (array $item): array {
                $cantidad = (int) $item['cantidad'];
                $identificadores = $this->normalizarIdentificadores((string) ($item['identificadores'] ?? ''));
                abort_if($cantidad === 0 && $identificadores !== [], 422, 'No registres identificadores cuando la cantidad recibida sea cero.');

                return [
                    'detalle_compra_id' => (int) $item['detalle_compra_id'],
                    'cantidad' => $cantidad,
                    'identificadores' => $identificadores,
                ];
            })
            ->filter(fn (array $item): bool => $item['cantidad'] > 0)
            ->sortBy('detalle_compra_id')
            ->values()
            ->all();
        abort_if($itemsNormalizados === [], 422, 'Indica al menos una cantidad recibida mayor que cero.');
        abort_if(collect($itemsNormalizados)->pluck('detalle_compra_id')->unique()->count() !== count($itemsNormalizados), 422, 'Un detalle fue enviado mas de una vez.');
        $itemsParaAuditoria = array_map(static fn (array $item): array => [
            'detalle_compra_id' => $item['detalle_compra_id'],
            'cantidad' => $item['cantidad'],
            'identificadores_cantidad' => count($item['identificadores']),
            'identificadores_hash' => hash('sha256', json_encode($item['identificadores'], JSON_THROW_ON_ERROR)),
        ], $itemsNormalizados);

        try {
            return DB::transaction(function () use ($compra, $itemsNormalizados, $itemsParaAuditoria, $observaciones, $actor, $ip, $ubicacion, $evento): Compra {
                $bloqueada = Compra::query()->whereKey($compra->getKey())->lockForUpdate()->firstOrFail();
                $previo = DB::table('registros_auditoria')->where('identificador_universal_evento', $evento)->lockForUpdate()->first();

                if ($previo !== null) {
                    abort_unless($previo->accion === 'compra.recepcion_parcial' && $previo->entidad === 'compras'
                        && (int) $previo->usuario_id === (int) $actor->id && (int) $previo->entidad_id === (int) $bloqueada->id, 409, 'La solicitud de recepcion ya fue utilizada.');
                    $datosPrevios = json_decode((string) $previo->valores_nuevos, true);
                    abort_unless(is_array($datosPrevios) && ($datosPrevios['items'] ?? null) === $itemsParaAuditoria, 409, 'La solicitud ya corresponde a otras cantidades o identificadores.');

                    return $bloqueada;
                }

                abort_unless((int) $bloqueada->empresa_id === $ubicacion['empresa_id'] && (int) $bloqueada->sede_id === $ubicacion['sede_id']
                    && (int) $bloqueada->almacen_id === $ubicacion['almacen_id'], 409, 'La recepcion requiere el almacen original de la compra.');
                abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.compras.recibir', (int) $bloqueada->empresa_id, (int) $bloqueada->sede_id), 403);
                abort_unless($bloqueada->activo && $bloqueada->estado === 'Aprobada', 409, 'La compra debe estar aprobada y pendiente de recepcion.');
                abort_unless(DB::table('registros_auditoria')->where('accion', 'compra.registrada')->where('entidad', 'compras')->where('entidad_id', $bloqueada->id)->exists(), 409, 'La compra requiere conciliacion antes de continuar.');

                $lineas = $bloqueada->details()->orderBy('id')->lockForUpdate()->get();
                abort_if($lineas->isEmpty() || $lineas->pluck('producto_id')->unique()->count() !== $lineas->count(), 409, 'Los detalles de compra requieren conciliacion.');
                $lineasPorId = $lineas->keyBy('id');
                $recibidasAntes = $this->cantidadesRecibidasPorDetalle($bloqueada, $lineas);
                foreach ($lineas as $linea) {
                    abort_if((int) ($recibidasAntes[$linea->id] ?? 0) > (int) $linea->cantidad, 409, 'El inventario excede la cantidad comprada.');
                }
                $productos = Producto::query()->whereIn('id', $lineas->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                foreach ($itemsNormalizados as $item) {
                    $linea = $lineasPorId->get($item['detalle_compra_id']);
                    abort_unless($linea !== null, 422, 'Un detalle no pertenece a esta compra.');
                    $cantidadPendiente = (int) $linea->cantidad - (int) ($recibidasAntes[$linea->id] ?? 0);
                    abort_if($item['cantidad'] > $cantidadPendiente, 422, 'La cantidad recibida supera lo pendiente para un producto.');
                }

                foreach ($itemsNormalizados as $item) {
                    $linea = $lineasPorId->get($item['detalle_compra_id']);
                    $producto = $productos->get((int) $linea->producto_id);
                    abort_unless($producto instanceof Producto && (int) $producto->empresa_id === (int) $bloqueada->empresa_id, 409, 'Un producto de la compra requiere conciliacion.');

                    $this->registrarEntradaDeCompra($bloqueada, $linea, $producto, $item['cantidad'], $observaciones, $actor, $item['identificadores']);
                }

                $recibidasDespues = $this->cantidadesRecibidasPorDetalle($bloqueada, $lineas);
                $completa = $lineas->every(fn ($linea): bool => (int) ($recibidasDespues[$linea->id] ?? 0) === (int) $linea->cantidad);
                if (! $completa) {
                    abort_unless(is_string($observaciones) && trim($observaciones) !== '', 422, 'Registra la discrepancia o el motivo de la recepcion parcial.');
                }

                if ($completa) {
                    $bloqueada->update(['estado' => 'Recibida']);
                }

                $this->comprobarRecepcion($bloqueada, $completa);
                $this->auditoria->record(
                    $actor->id,
                    'compra.recepcion_parcial',
                    'compras',
                    $bloqueada->id,
                    ['estado' => 'Aprobada', 'cantidades_recibidas' => $recibidasAntes],
                    ['estado' => $bloqueada->estado, 'items' => $itemsParaAuditoria, 'cantidades_recibidas' => $recibidasDespues],
                    $ip,
                    observations: $observaciones,
                    scope: ['empresa_id' => (int) $bloqueada->empresa_id, 'sede_id' => (int) $bloqueada->sede_id],
                    eventId: $evento,
                );

                return $bloqueada;
            }, 3);
        } catch (UniqueConstraintViolationException $excepcion) {
            abort(409, 'Uno de los IMEI o n�meros de serie ya est� registrado. No se guard� la recepci�n.');
        }
    }

    /**
     * Ejecuta la operaci�n cambiarEstado definida por la clase ServicioCompras.
     *
     * @param  mixed  $compra  Par�metro recibido por la operaci�n.
     * @param  mixed  $actor  Par�metro recibido por la operaci�n.
     * @param  mixed  $ip  Par�metro recibido por la operaci�n.
     * @param  mixed  $recibir  Par�metro recibido por la operaci�n.
     * @return Compra Resultado de la operaci�n.
     */
    private function cambiarEstado(Compra $compra, User $actor, ?string $ip, bool $recibir): Compra
    {
        $permiso = $recibir ? 'gestion.compras.recibir' : 'gestion.compras.aprobar';
        $this->autorizacion->assertAllowed($actor, $permiso);

        return DB::transaction(function () use ($compra, $actor, $ip, $recibir, $permiso): Compra {
            $bloqueada = Compra::query()->whereKey($compra->id)->lockForUpdate()->firstOrFail();
            abort_unless($this->autorizacion->allowsRecord($actor, $permiso, (int) $bloqueada->empresa_id, (int) $bloqueada->sede_id), 403);
            $ubicacion = $this->autorizacion->operationLocation($actor);
            abort_unless((int) $bloqueada->empresa_id === $ubicacion['empresa_id'] && (int) $bloqueada->sede_id === $ubicacion['sede_id']
                && (int) $bloqueada->almacen_id === $ubicacion['almacen_id'], 409, 'La operaci�n requiere el almac�n original de la compra.');
            $destino = $recibir ? 'Recibida' : 'Aprobada';
            if ($bloqueada->estado === $destino) {
                if ($recibir) {
                    $this->comprobarRecepcion($bloqueada, true);
                }

                return $bloqueada;
            }
            abort_unless($bloqueada->activo && $bloqueada->estado === ($recibir ? 'Aprobada' : 'Pendiente'), 409, 'El estado de la compra no permite esta operaci�n.');
            abort_unless(DB::table('registros_auditoria')->where('accion', 'compra.registrada')->where('entidad', 'compras')->where('entidad_id', $bloqueada->id)->exists(), 409, 'La compra requiere conciliaci�n antes de continuar.');
            if ($recibir) {
                $lineas = $bloqueada->details()->orderBy('id')->lockForUpdate()->get();
                abort_if($lineas->isEmpty() || $lineas->pluck('producto_id')->unique()->count() !== $lineas->count(), 409, 'Los detalles de compra requieren conciliaci�n.');
                $recibidas = $this->cantidadesRecibidasPorDetalle($bloqueada, $lineas);
                $productos = Producto::query()->whereIn('id', $lineas->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                foreach ($lineas as $linea) {
                    $producto = $productos->get((int) $linea->producto_id);
                    abort_unless($producto instanceof Producto && (int) $producto->empresa_id === $ubicacion['empresa_id'] && (int) $linea->cantidad > 0, 409, 'Un producto de la compra requiere conciliaci�n.');
                    $cantidadPendiente = (int) $linea->cantidad - (int) ($recibidas[$linea->id] ?? 0);
                    if ($cantidadPendiente > 0) {
                        $this->registrarEntradaDeCompra($bloqueada, $linea, $producto, $cantidadPendiente, null, $actor, []);
                    }
                }
                $this->comprobarRecepcion($bloqueada, true);
            }
            $anterior = $bloqueada->estado;
            $bloqueada->update(['estado' => $destino]);
            $this->auditoria->record($actor->id, $recibir ? 'compra.recibida' : 'compra.aprobada', 'compras', $bloqueada->id,
                ['estado' => $anterior], ['estado' => $destino], $ip,
                scope: ['empresa_id' => $ubicacion['empresa_id'], 'sede_id' => $ubicacion['sede_id']]);

            return $bloqueada;
        }, 3);
    }

    /**
     * Ejecuta la operaci�n comprobarRecepcion definida por la clase ServicioCompras.
     *
     * @param  mixed  $compra  Par�metro recibido por la operaci�n.
     * @param  mixed  $completa  Par�metro recibido por la operaci�n.
     * @return void Resultado de la operaci�n.
     */
    private function comprobarRecepcion(Compra $compra, bool $completa): void
    {
        $lineas = $compra->details()->get(['id', 'producto_id', 'variante_id', 'cantidad']);
        abort_unless($lineas->isNotEmpty(), 409, 'La compra no tiene detalles para recibir.');
        $recibidas = $this->cantidadesRecibidasPorDetalle($compra, $lineas);

        foreach ($lineas as $linea) {
            $cantidadRecibida = (int) ($recibidas[$linea->id] ?? 0);
            abort_if($cantidadRecibida > (int) $linea->cantidad, 409, 'El inventario excede la cantidad comprada.');
            if ($completa) {
                abort_unless($cantidadRecibida === (int) $linea->cantidad, 409, 'La recepci�n no coincide con las cantidades compradas.');
            }
        }
    }

    /**
     * @return array<int, int>
     */
    private function cantidadesRecibidasPorDetalle(Compra $compra, Collection $lineas): array
    {
        $lineasPorClave = $lineas->keyBy(fn ($linea): string => (int) $linea->producto_id.':'.($linea->variante_id === null ? 'base' : (int) $linea->variante_id));
        abort_if($lineasPorClave->count() !== $lineas->count(), 409, 'La compra tiene detalles duplicados de producto o variante.');

        $movimientos = DB::table('movimientos_inventario')
            ->where('documento_tipo', 'compra')
            ->where('documento_id', (string) $compra->id)
            ->where('tipo_movimiento', 'entrada')
            ->where('empresa_id', $compra->empresa_id)
            ->where('sede_id', $compra->sede_id)
            ->where('almacen_id', $compra->almacen_id)
            ->get(['producto_id', 'variante_id', 'cantidad']);
        $cantidadesPorDetalle = [];

        foreach ($movimientos as $movimiento) {
            $clave = (int) $movimiento->producto_id.':'.($movimiento->variante_id === null ? 'base' : (int) $movimiento->variante_id);
            $linea = $lineasPorClave->get($clave);
            abort_unless($linea !== null, 409, 'Existe una entrada de inventario que no corresponde a un detalle de esta compra.');
            abort_if((float) $movimiento->cantidad <= 0 || (float) $movimiento->cantidad !== floor((float) $movimiento->cantidad), 409, 'Una entrada de compra requiere conciliaci�n.');
            $cantidadesPorDetalle[$linea->id] = ($cantidadesPorDetalle[$linea->id] ?? 0) + (int) $movimiento->cantidad;
        }

        return $cantidadesPorDetalle;
    }

    /** @param array<int, array{imei:?string,numero_serie:?string}> $identificadores */
    private function registrarEntradaDeCompra(
        Compra $compra,
        object $linea,
        Producto $producto,
        int $cantidad,
        ?string $observaciones,
        User $actor,
        array $identificadores,
    ): void {
        $variantes = DB::table('variantes_productos')->where('producto_id', $producto->id);
        $varianteId = $linea->variante_id === null ? null : (int) $linea->variante_id;
        $controlaSerie = false;

        if ((clone $variantes)->exists()) {
            abort_unless($varianteId !== null, 409, 'La compra requiere conciliar la variante del producto antes de recibir.');
            $variante = (clone $variantes)->where('id', $varianteId)->where('estado', 'activo')->first(['controla_serie']);
            abort_unless($variante !== null, 409, 'La variante de compra no existe, no pertenece al producto o est� inactiva.');
            $controlaSerie = (bool) $variante->controla_serie;
        } else {
            abort_unless($varianteId === null, 409, 'La compra referencia una variante que ya no existe.');
        }

        if ($controlaSerie) {
            abort_unless(count($identificadores) === $cantidad, 422, 'Registra un IMEI o n�mero de serie por cada unidad serializada recibida.');
            abort_unless(Schema::hasColumns('dispositivos', ['producto_id', 'variante_id', 'imei', 'numero_serie', 'detalle_compra_id', 'almacen_actual_id', 'estado', 'fecha_ingreso'])
                && Schema::hasColumns('movimientos_dispositivos', ['dispositivo_id', 'movimiento_inventario_id', 'estado_anterior', 'estado_nuevo', 'creado_en'])
                && Schema::hasColumns('detalles_compras_dispositivos', ['detalle_compra_id', 'dispositivo_id']),
                503, 'El esquema de trazabilidad de IMEI no est� disponible.');
        } else {
            abort_if($identificadores !== [], 422, 'Este producto no requiere IMEI ni n�mero de serie.');
            abort_if(Schema::hasTable('dispositivos') && DB::table('dispositivos')->where('producto_id', $producto->id)->where('variante_id', $varianteId)->exists(), 409,
                'La variante ya tiene equipos serializados; revisa la configuraci�n de control de serie antes de recibir.');
        }

        $notas = 'Ingreso por compra #'.$compra->id.'; detalle #'.$linea->id;
        if (is_string($observaciones) && trim($observaciones) !== '') {
            $notas .= '. '.$observaciones;
        }

        $movimiento = $this->inventario->changeLockedProductInWarehouse(
            $producto,
            $varianteId,
            'entrada',
            $cantidad,
            mb_substr($notas, 0, 500),
            $actor,
            (int) $compra->empresa_id,
            (int) $compra->sede_id,
            (int) $compra->almacen_id,
            'compra',
            (string) $compra->id,
            true,
        );

        if (! $controlaSerie) {
            return;
        }

        abort_unless($movimiento !== null, 409, 'No se pudo crear el movimiento de entrada que respalda los dispositivos.');

        foreach ($identificadores as $identificador) {
            try {
                $dispositivoId = DB::table('dispositivos')->insertGetId([
                    'producto_id' => $producto->id,
                    'variante_id' => $varianteId,
                    'imei' => $identificador['imei'],
                    'numero_serie' => $identificador['numero_serie'],
                    'detalle_compra_id' => $linea->id,
                    'almacen_actual_id' => $compra->almacen_id,
                    'estado' => 'disponible',
                    'fecha_ingreso' => now(),
                ]);
            } catch (UniqueConstraintViolationException $excepcion) {
                abort(409, 'Uno de los IMEI o n�meros de serie ya est� registrado.');
            }

            DB::table('detalles_compras_dispositivos')->insert([
                'detalle_compra_id' => $linea->id,
                'dispositivo_id' => $dispositivoId,
            ]);
            DB::table('movimientos_dispositivos')->insert([
                'dispositivo_id' => $dispositivoId,
                'movimiento_inventario_id' => $movimiento->id,
                'estado_anterior' => null,
                'estado_nuevo' => 'disponible',
                'creado_en' => now(),
            ]);
        }
    }

    /** @return array<int, array{imei:?string,numero_serie:?string}> */
    private function normalizarIdentificadores(string $texto): array
    {
        $lineas = preg_split('/\R/u', trim($texto), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $identificadores = [];
        $vistos = [];

        foreach ($lineas as $linea) {
            $imei = null;
            $numeroSerie = null;

            foreach (explode('|', trim($linea)) as $parte) {
                $parte = trim($parte);
                if (preg_match('/\AIMEI:([0-9]{15})\z/i', $parte, $coincidencia) === 1 && $imei === null) {
                    $imei = $coincidencia[1];
                    abort_unless($this->esImeiValido($imei), 422, 'El IMEI debe tener 15 d�gitos y un d�gito de control v�lido.');
                } elseif (preg_match('/\ASERIE:(.{1,100})\z/iu', $parte, $coincidencia) === 1 && $numeroSerie === null) {
                    $numeroSerie = trim($coincidencia[1]);
                    abort_if($numeroSerie === '', 422, 'El n�mero de serie no puede estar vac�o.');
                } else {
                    abort(422, 'Usa una l�nea por unidad con formato IMEI:15d�gitos, SERIE:valor o IMEI:15d�gitos|SERIE:valor.');
                }
            }

            abort_if($imei === null && $numeroSerie === null, 422, 'Cada unidad requiere un IMEI o n�mero de serie.');
            foreach ([['imei', $imei], ['serie', $numeroSerie]] as [$tipo, $valor]) {
                if ($valor === null) {
                    continue;
                }
                $clave = $tipo.':'.mb_strtolower($valor);
                abort_if(isset($vistos[$clave]), 422, 'No repitas un IMEI o n�mero de serie en la recepci�n.');
                $vistos[$clave] = true;
            }
            $identificadores[] = ['imei' => $imei, 'numero_serie' => $numeroSerie];
        }

        return $identificadores;
    }

    /**
     * Ejecuta la operaci�n esImeiValido definida por la clase ServicioCompras.
     *
     * @param  mixed  $imei  Par�metro recibido por la operaci�n.
     * @return bool Resultado de la operaci�n.
     */
    private function esImeiValido(string $imei): bool
    {
        if (preg_match('/\A[0-9]{15}\z/', $imei) !== 1) {
            return false;
        }

        $suma = 0;
        foreach (str_split(substr($imei, 0, 14)) as $indice => $digito) {
            $valor = (int) $digito;
            if ($indice % 2 === 1) {
                $valor *= 2;
                if ($valor > 9) {
                    $valor -= 9;
                }
            }
            $suma += $valor;
        }

        return (10 - ($suma % 10)) % 10 === (int) substr($imei, 14, 1);
    }

    /**
     * Ejecuta la operaci�n centimos definida por la clase ServicioCompras.
     *
     * @param  mixed  $valor  Par�metro recibido por la operaci�n.
     * @return int Resultado de la operaci�n.
     */
    private function centimos(string $valor): int
    {
        abort_unless(preg_match('/\A\d{1,8}(?:\.\d{1,2})?\z/', $valor) === 1, 422, 'El costo unitario no es v�lido.');
        [$entero, $decimal] = array_pad(explode('.', $valor, 2), 2, '');

        return (int) $entero * 100 + (int) str_pad($decimal, 2, '0');
    }

    /**
     * Procesa e incorpora la informaci�n importada.
     *
     * @param  mixed  $centimos  Par�metro recibido por la operaci�n.
     * @return string Resultado de la operaci�n.
     */
    private function importe(int $centimos): string
    {
        return intdiv($centimos, 100).'.'.str_pad((string) ($centimos % 100), 2, '0', STR_PAD_LEFT);
    }
}
