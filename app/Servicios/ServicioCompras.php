<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServicioCompras
{
    private const MAX_CENTIMOS = 999999999999;

    public function __construct(
        private readonly ServicioInventario $inventario,
        private readonly RegistradorAuditoria $auditoria,
        private readonly ServicioAutorizacion $autorizacion,
    ) {}

    public function registrar(array $datos, User $actor, string $token, ?string $ip): Compra
    {
        $this->autorizacion->assertAllowed($actor, 'gestion.compras.crear');
        abort_unless(Schema::hasColumn('registros_auditoria', 'identificador_universal_evento'), 503, 'No está disponible la protección contra compras duplicadas.');
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/', $token) === 1, 422, 'La solicitud de compra no es válida.');
        $ubicacion = $this->autorizacion->operationLocation($actor);
        abort_unless($ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null, 503, 'No hay un almacén autorizado disponible.');
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
            abort_unless($proveedor !== null, 422, 'El proveedor no está activo en la empresa autorizada.');
            $items = collect($datos['items'])->sortBy('producto_id')->values();
            abort_if($items->isEmpty() || $items->count() > 100 || $items->pluck('producto_id')->unique()->count() !== $items->count(), 422, 'Los productos de la compra no son válidos.');
            $productos = Producto::query()->whereIn('id', $items->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $lineas = [];
            $total = 0;
            foreach ($items as $item) {
                $producto = $productos->get((int) $item['producto_id']);
                $cantidad = filter_var($item['cantidad'], FILTER_VALIDATE_INT);
                abort_unless($producto instanceof Producto && $producto->activo && (int) $producto->empresa_id === $ubicacion['empresa_id'], 422, 'Un producto no está activo en la empresa autorizada.');
                abort_if($cantidad === false || $cantidad < 1 || $cantidad > 1000, 422, 'La cantidad no es válida.');
                $variantesProducto = DB::table('variantes_productos')->where('producto_id', $producto->id);
                $varianteId = isset($item['variante_id']) && $item['variante_id'] !== '' ? (int) $item['variante_id'] : null;
                if ((clone $variantesProducto)->exists()) {
                    abort_unless($varianteId !== null, 422, 'Selecciona la variante que se comprarÃ¡.');
                    $varianteValida = (clone $variantesProducto)->where('id', $varianteId)->where('estado', 'activo')->exists();
                    abort_unless($varianteValida, 422, 'La variante seleccionada no estÃ¡ activa o no pertenece al producto.');
                } else {
                    abort_unless($varianteId === null, 422, 'El producto seleccionado no tiene variantes activas.');
                }
                $costo = $this->centimos((string) $item['costo_unitario']);
                $importe = $costo * $cantidad;
                abort_if($importe > self::MAX_CENTIMOS - $total, 422, 'La compra supera el importe permitido.');
                $total += $importe;
                $lineas[] = ['producto_id' => $producto->id, 'variante_id' => $varianteId, 'cantidad' => $cantidad, 'costo_unitario' => $this->importe($costo)];
            }
            $compra = Compra::create([
                ...$ubicacion, 'proveedor_id' => $proveedor->id, 'creado_por_usuario_id' => $actor->id,
                'total' => $this->importe($total), 'estado' => 'Pendiente', 'fecha_compra' => $datos['fecha_compra'],
                'activo' => true, 'creado_en' => now(),
            ]);
            foreach ($lineas as $linea) {
                $compra->details()->create($linea);
            }
            $this->auditoria->record($actor->id, 'compra.registrada', 'compras', $compra->id, null,
                ['proveedor_id' => $proveedor->id, 'total' => $compra->total, 'items' => count($lineas)], $ip,
                scope: ['empresa_id' => $ubicacion['empresa_id'], 'sede_id' => $ubicacion['sede_id']], eventId: $evento);

            return $compra;
        }, 3);
    }

    public function aprobar(Compra $compra, User $actor, ?string $ip): Compra
    {
        return $this->cambiarEstado($compra, $actor, $ip, false);
    }

    public function recibir(Compra $compra, User $actor, ?string $ip): Compra
    {
        return $this->cambiarEstado($compra, $actor, $ip, true);
    }

    /**
     * @param  array<int, array{detalle_compra_id:int|string,cantidad:int|string}>  $items
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
            ->map(fn (array $item): array => ['detalle_compra_id' => (int) $item['detalle_compra_id'], 'cantidad' => (int) $item['cantidad']])
            ->filter(fn (array $item): bool => $item['cantidad'] > 0)
            ->sortBy('detalle_compra_id')
            ->values()
            ->all();
        abort_if($itemsNormalizados === [], 422, 'Indica al menos una cantidad recibida mayor que cero.');
        abort_if(collect($itemsNormalizados)->pluck('detalle_compra_id')->unique()->count() !== count($itemsNormalizados), 422, 'Un detalle fue enviado mas de una vez.');

        return DB::transaction(function () use ($compra, $itemsNormalizados, $observaciones, $actor, $ip, $ubicacion, $evento): Compra {
            $bloqueada = Compra::query()->whereKey($compra->getKey())->lockForUpdate()->firstOrFail();
            $previo = DB::table('registros_auditoria')->where('identificador_universal_evento', $evento)->lockForUpdate()->first();

            if ($previo !== null) {
                abort_unless($previo->accion === 'compra.recepcion_parcial' && $previo->entidad === 'compras'
                    && (int) $previo->usuario_id === (int) $actor->id && (int) $previo->entidad_id === (int) $bloqueada->id, 409, 'La solicitud de recepcion ya fue utilizada.');
                $datosPrevios = json_decode((string) $previo->valores_nuevos, true);
                abort_unless(is_array($datosPrevios) && ($datosPrevios['items'] ?? null) === $itemsNormalizados, 409, 'La solicitud ya corresponde a otras cantidades.');

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

                $this->registrarEntradaDeCompra($bloqueada, $linea, $producto, $item['cantidad'], $observaciones, $actor);
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
                ['estado' => $bloqueada->estado, 'items' => $itemsNormalizados, 'cantidades_recibidas' => $recibidasDespues],
                $ip,
                observations: $observaciones,
                scope: ['empresa_id' => (int) $bloqueada->empresa_id, 'sede_id' => (int) $bloqueada->sede_id],
                eventId: $evento,
            );

            return $bloqueada;
        }, 3);
    }

    private function cambiarEstado(Compra $compra, User $actor, ?string $ip, bool $recibir): Compra
    {
        $permiso = $recibir ? 'gestion.compras.recibir' : 'gestion.compras.aprobar';
        $this->autorizacion->assertAllowed($actor, $permiso);

        return DB::transaction(function () use ($compra, $actor, $ip, $recibir, $permiso): Compra {
            $bloqueada = Compra::query()->whereKey($compra->id)->lockForUpdate()->firstOrFail();
            abort_unless($this->autorizacion->allowsRecord($actor, $permiso, (int) $bloqueada->empresa_id, (int) $bloqueada->sede_id), 403);
            $ubicacion = $this->autorizacion->operationLocation($actor);
            abort_unless((int) $bloqueada->empresa_id === $ubicacion['empresa_id'] && (int) $bloqueada->sede_id === $ubicacion['sede_id']
                && (int) $bloqueada->almacen_id === $ubicacion['almacen_id'], 409, 'La operación requiere el almacén original de la compra.');
            $destino = $recibir ? 'Recibida' : 'Aprobada';
            if ($bloqueada->estado === $destino) {
                if ($recibir) {
                    $this->comprobarRecepcion($bloqueada, true);
                }

                return $bloqueada;
            }
            abort_unless($bloqueada->activo && $bloqueada->estado === ($recibir ? 'Aprobada' : 'Pendiente'), 409, 'El estado de la compra no permite esta operación.');
            abort_unless(DB::table('registros_auditoria')->where('accion', 'compra.registrada')->where('entidad', 'compras')->where('entidad_id', $bloqueada->id)->exists(), 409, 'La compra requiere conciliación antes de continuar.');
            if ($recibir) {
                $lineas = $bloqueada->details()->orderBy('id')->lockForUpdate()->get();
                abort_if($lineas->isEmpty() || $lineas->pluck('producto_id')->unique()->count() !== $lineas->count(), 409, 'Los detalles de compra requieren conciliación.');
                $recibidas = $this->cantidadesRecibidasPorDetalle($bloqueada, $lineas);
                $productos = Producto::query()->whereIn('id', $lineas->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                foreach ($lineas as $linea) {
                    $producto = $productos->get((int) $linea->producto_id);
                    abort_unless($producto instanceof Producto && (int) $producto->empresa_id === $ubicacion['empresa_id'] && (int) $linea->cantidad > 0, 409, 'Un producto de la compra requiere conciliación.');
                    $cantidadPendiente = (int) $linea->cantidad - (int) ($recibidas[$linea->id] ?? 0);
                    if ($cantidadPendiente > 0) {
                        $this->registrarEntradaDeCompra($bloqueada, $linea, $producto, $cantidadPendiente, null, $actor);
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

    private function comprobarRecepcion(Compra $compra, bool $completa): void
    {
        $lineas = $compra->details()->get(['id', 'producto_id', 'variante_id', 'cantidad']);
        abort_unless($lineas->isNotEmpty(), 409, 'La compra no tiene detalles para recibir.');
        $recibidas = $this->cantidadesRecibidasPorDetalle($compra, $lineas);

        foreach ($lineas as $linea) {
            $cantidadRecibida = (int) ($recibidas[$linea->id] ?? 0);
            abort_if($cantidadRecibida > (int) $linea->cantidad, 409, 'El inventario excede la cantidad comprada.');
            if ($completa) {
                abort_unless($cantidadRecibida === (int) $linea->cantidad, 409, 'La recepción no coincide con las cantidades compradas.');
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
            abort_if((float) $movimiento->cantidad <= 0 || (float) $movimiento->cantidad !== floor((float) $movimiento->cantidad), 409, 'Una entrada de compra requiere conciliación.');
            $cantidadesPorDetalle[$linea->id] = ($cantidadesPorDetalle[$linea->id] ?? 0) + (int) $movimiento->cantidad;
        }

        return $cantidadesPorDetalle;
    }

    private function registrarEntradaDeCompra(Compra $compra, object $linea, Producto $producto, int $cantidad, ?string $observaciones, User $actor): void
    {
        abort_if(Schema::hasTable('dispositivos') && DB::table('dispositivos')->where('producto_id', $producto->id)->exists(), 409, 'La recepción de equipos serializados requiere sus identificadores.');
        $variantes = DB::table('variantes_productos')->where('producto_id', $producto->id);
        $varianteId = $linea->variante_id === null ? null : (int) $linea->variante_id;

        if ((clone $variantes)->exists()) {
            abort_unless($varianteId !== null, 409, 'La compra requiere conciliar la variante del producto antes de recibir.');
            $variante = (clone $variantes)->where('id', $varianteId)->where('estado', 'activo')->first(['controla_serie']);
            abort_unless($variante !== null, 409, 'La variante de compra no existe, no pertenece al producto o está inactiva.');
            abort_if((bool) $variante->controla_serie, 409, 'La recepción de equipos serializados requiere sus identificadores.');
        } else {
            abort_unless($varianteId === null, 409, 'La compra referencia una variante que ya no existe.');
        }

        $notas = 'Ingreso por compra #'.$compra->id.'; detalle #'.$linea->id;
        if (is_string($observaciones) && trim($observaciones) !== '') {
            $notas .= '. '.$observaciones;
        }

        $this->inventario->changeLockedProductInWarehouse(
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
    }

    private function centimos(string $valor): int
    {
        abort_unless(preg_match('/\A\d{1,8}(?:\.\d{1,2})?\z/', $valor) === 1, 422, 'El costo unitario no es válido.');
        [$entero, $decimal] = array_pad(explode('.', $valor, 2), 2, '');

        return (int) $entero * 100 + (int) str_pad($decimal, 2, '0');
    }

    private function importe(int $centimos): string
    {
        return intdiv($centimos, 100).'.'.str_pad((string) ($centimos % 100), 2, '0', STR_PAD_LEFT);
    }
}
