<?php

namespace App\Servicios\Gestion\Comercial\Ventas;

use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\DetalleVenta;
use App\Models\Comercio\Venta;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Define la responsabilidad de la clase ServicioVentas dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioVentas
{
    private const MAX_CENTIMOS = 999999999999999;

    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioInventario $inventario,
        private readonly RegistradorAuditoria $auditoria,
        private readonly ServicioAutorizacion $autorizacion,
    ) {}

    /**
     * Registra una venta presencial idempotente y descuenta existencias dentro de una transacción.
     *
     * Verifica autorización, esquema, token, ubicación, cliente, productos activos, cantidades
     * y restricciones de serialización. Bloquea los registros críticos, crea la venta y detalles,
     * registra movimientos de inventario y escribe la auditoría antes de confirmar.
     *
     * @param  array{cliente_id:int,tipo_venta:string,fecha_venta:string,observaciones:?string,items:array<int,array{producto_id:int,cantidad:int}>}  $datos  Datos validados de la venta.
     * @param  User  $actor  Usuario que registra la operación.
     * @param  string  $token  Token idempotente de la solicitud.
     * @param  ?string  $ip  Dirección IP para auditoría.
     * @return Venta Venta confirmada o registro idempotente existente.
     *
     * @throws HttpException Si falla autorización, validación, disponibilidad o consistencia.
     */
    public function registrarFisica(array $datos, User $actor, string $token, ?string $ip): Venta
    {
        $this->autorizacion->assertAllowed($actor, 'gestion.ventas.crear');
        abort_unless(Schema::hasColumns('ventas', ['canal', 'observaciones']), 503, 'La migración de canal y observaciones está pendiente de autorización y aplicación.');
        abort_unless($datos['tipo_venta'] === 'fisica' && preg_match('/\A[a-f0-9]{64}\z/', $token) === 1, 422, 'La solicitud de venta no es válida.');
        $ubicacion = $this->autorizacion->operationLocation($actor);
        abort_unless($ubicacion['empresa_id'] !== null && $ubicacion['sede_id'] !== null && $ubicacion['almacen_id'] !== null, 503, 'No hay un almacén autorizado disponible.');
        abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.ventas.crear', $ubicacion['empresa_id'], $ubicacion['sede_id']), 403);

        return DB::transaction(function () use ($datos, $actor, $token, $ip, $ubicacion): Venta {
            DB::table('empresas')->where('id', $ubicacion['empresa_id'])->lockForUpdate()->firstOrFail();
            $codigo = 'V-'.substr(hash('sha256', $actor->getKey().':'.$token), 0, 40);
            $existente = Venta::query()->where('empresa_id', $ubicacion['empresa_id'])->where('codigo', $codigo)->lockForUpdate()->first();
            if ($existente !== null) {
                abort_unless((int) $existente->usuario_id === (int) $actor->getKey()
                    && (int) $existente->sede_id === $ubicacion['sede_id']
                    && (int) $existente->almacen_id === $ubicacion['almacen_id']
                    && (int) $existente->cliente_id === (int) $datos['cliente_id']
                    && $existente->canal === 'presencial'
                    && $existente->observaciones === ($datos['observaciones'] ?? null)
                    && $existente->fecha_venta->toDateString() === $datos['fecha_venta'], 409, 'La solicitud ya corresponde a otra venta.');
                $anteriores = $existente->detalles()->orderBy('producto_id')->get(['producto_id', 'cantidad'])->mapWithKeys(fn ($linea): array => [(int) $linea->producto_id => (int) $linea->cantidad])->all();
                $solicitados = collect($datos['items'])->sortBy('producto_id')->mapWithKeys(fn (array $linea): array => [(int) $linea['producto_id'] => (int) $linea['cantidad']])->all();
                abort_unless($anteriores === $solicitados, 409, 'La solicitud ya corresponde a otros productos o cantidades.');

                return $existente;
            }

            $cliente = Cliente::query()->whereKey($datos['cliente_id'])->where('empresa_id', $ubicacion['empresa_id'])->where('activo', true)->first();
            abort_unless($cliente !== null, 422, 'El cliente no está activo en la empresa autorizada.');
            $items = collect($datos['items'])->sortBy('producto_id')->values();
            abort_if($items->isEmpty() || $items->count() > 100 || $items->pluck('producto_id')->unique()->count() !== $items->count(), 422, 'Los productos de la venta no son válidos.');
            $productos = Producto::query()->whereIn('id', $items->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $lineas = [];
            $totalCentimos = 0;
            foreach ($items as $item) {
                $cantidad = filter_var($item['cantidad'], FILTER_VALIDATE_INT);
                $producto = $productos->get((int) $item['producto_id']);
                abort_unless($producto instanceof Producto && $producto->activo && (int) $producto->empresa_id === $ubicacion['empresa_id'], 422, 'Un producto no está disponible en la empresa.');
                abort_if($cantidad === false || $cantidad < 1 || $cantidad > 1000, 422, 'La cantidad de venta no es válida.');
                abort_if(Schema::hasTable('dispositivos') && DB::table('dispositivos')->where('producto_id', $producto->id)->exists(), 409, 'La venta de equipos serializados requiere seleccionar sus identificadores.');
                $precio = $this->centimos($producto->effective_price);
                $importe = $precio * $cantidad;
                abort_if($importe > self::MAX_CENTIMOS - $totalCentimos, 422, 'La venta supera el importe permitido.');
                $totalCentimos += $importe;
                $lineas[] = [$producto, $cantidad, $precio, $importe];
            }

            $venta = Venta::create([
                ...$ubicacion, 'cliente_id' => $cliente->id, 'usuario_id' => $actor->id, 'codigo' => $codigo,
                'estado' => 'confirmada', 'moneda' => 'PEN', 'canal' => 'presencial',
                'observaciones' => $datos['observaciones'] ?? null,
                'subtotal' => $this->importe($totalCentimos), 'total' => $this->importe($totalCentimos),
                'fecha_venta' => CarbonImmutable::parse($datos['fecha_venta'])->setTimeFrom(now()),
            ]);
            foreach ($lineas as [$producto, $cantidad, $precio, $importe]) {
                $movimiento = $this->inventario->changeLockedProduct($producto, 'salida', $cantidad, 'Salida por venta #'.$venta->id, $actor);
                $movimiento->update(['documento_tipo' => 'venta', 'documento_id' => (string) $venta->id]);
                $variante = $movimiento->variante_id === null ? null : DB::table('variantes_productos')->where('id', $movimiento->variante_id)->first(['codigo_sku', 'controla_serie']);
                abort_if($variante !== null && $variante->controla_serie, 409, 'La venta de equipos serializados requiere seleccionar sus identificadores.');
                $sku = $variante?->codigo_sku;
                $venta->detalles()->create([
                    'producto_id' => $producto->id, 'variante_id' => $movimiento->variante_id,
                    'descripcion_historica' => $producto->nombre, 'sku_historico' => $sku,
                    'cantidad' => $cantidad, 'precio_unitario' => $this->importe($precio), 'total_linea' => $this->importe($importe),
                ]);
            }
            $this->auditoria->record($actor->id, 'venta.confirmada', 'ventas', $venta->id, null,
                ['cliente_id' => $cliente->id, 'total' => $venta->total, 'moneda' => 'PEN', 'canal' => 'presencial', 'items' => count($lineas)], $ip,
                scope: ['empresa_id' => $ubicacion['empresa_id'], 'sede_id' => $ubicacion['sede_id']]);

            return $venta;
        }, 3);
    }

    /**
     * Anula una venta presencial confirmada y repone sus salidas de inventario.
     *
     * Comprueba motivo, autorización, pagos, documentos y devoluciones; bloquea la venta,
     * sus detalles, movimientos, productos y saldos, actualiza existencias, cambia el estado
     * a anulada y registra el evento de auditoría dentro de la misma transacción.
     *
     * @param  Venta  $venta  Venta que se desea anular.
     * @param  User  $actor  Usuario que solicita la anulación.
     * @param  string  $motivo  Motivo de entre 5 y 500 caracteres.
     * @param  ?string  $ip  Dirección IP para auditoría.
     * @return Venta Venta anulada o ya anulada.
     *
     * @throws HttpException Si no se cumplen las reglas de anulación.
     */
    public function anular(Venta $venta, User $actor, string $motivo, ?string $ip): Venta
    {
        $motivo = trim($motivo);
        abort_unless(mb_strlen($motivo) >= 5 && mb_strlen($motivo) <= 500, 422, 'El motivo de anulación no es válido.');
        $this->autorizacion->assertAllowed($actor, 'gestion.ventas.anular');
        foreach (['ventas', 'detalles_ventas', 'movimientos_inventario', 'existencias_almacen', 'variantes_productos', 'pagos', 'documentos_electronicos', 'devoluciones_garantias', 'dispositivos'] as $tabla) {
            abort_unless(Schema::hasTable($tabla), 503, 'Faltan datos para verificar la anulación segura.');
        }
        abort_unless(Schema::hasColumn('ventas', 'canal'), 503, 'Falta el canal de la venta.');

        return DB::transaction(function () use ($venta, $actor, $motivo, $ip): Venta {
            $bloqueada = Venta::query()->whereKey($venta->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.ventas.anular', (int) $bloqueada->empresa_id, (int) $bloqueada->sede_id), 403);
            if ($bloqueada->estado === 'anulada') {
                return $bloqueada;
            }
            abort_unless($bloqueada->estado === 'confirmada' && $bloqueada->canal === 'presencial' && $bloqueada->pedido_id === null, 409, 'Solo se pueden anular ventas físicas confirmadas sin pedido asociado.');
            $ubicacion = $this->autorizacion->operationLocation($actor);
            abort_unless((int) $bloqueada->empresa_id === $ubicacion['empresa_id'] && (int) $bloqueada->sede_id === $ubicacion['sede_id'] && (int) $bloqueada->almacen_id === $ubicacion['almacen_id'], 409, 'La anulación requiere el almacén original de la venta.');
            foreach (['pagos', 'documentos_electronicos', 'devoluciones_garantias'] as $tabla) {
                abort_if(DB::table($tabla)->where('venta_id', $bloqueada->id)->lockForUpdate()->first() !== null, 409, 'La venta tiene pagos, comprobantes o devoluciones y requiere revisión.');
            }

            $lineas = $bloqueada->detalles()->orderBy('producto_id')->lockForUpdate()->get();
            abort_if($lineas->isEmpty() || $lineas->pluck('producto_id')->unique()->count() !== $lineas->count(), 409, 'Los detalles de la venta requieren conciliación.');
            $salidas = DB::table('movimientos_inventario')->where('documento_tipo', 'venta')->where('documento_id', (string) $bloqueada->id)
                ->where('tipo_movimiento', 'salida')->lockForUpdate()->get();
            abort_unless($salidas->count() === $lineas->count(), 409, 'Las salidas originales requieren conciliación.');
            $productos = Producto::query()->whereIn('id', $lineas->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($lineas as $linea) {
                $cantidad = (string) $linea->cantidad;
                abort_unless(preg_match('/\A[1-9][0-9]*(?:\.0{1,6})?\z/', $cantidad) === 1 && (float) $cantidad <= 1000, 409, 'La cantidad de la venta requiere conciliación.');
                $salida = $salidas->where('producto_id', $linea->producto_id)->first();
                abort_unless($salida !== null && (float) $salida->cantidad === (float) $cantidad
                    && (int) $salida->empresa_id === (int) $bloqueada->empresa_id && (int) $salida->sede_id === (int) $bloqueada->sede_id
                    && (int) $salida->almacen_id === (int) $bloqueada->almacen_id
                    && (string) ($salida->variante_id ?? '') === (string) ($linea->variante_id ?? ''), 409, 'La salida original no coincide con el detalle.');
                $producto = $productos->get((int) $linea->producto_id);
                abort_unless($producto instanceof Producto, 409, 'Falta un producto de la venta.');
                abort_if(DB::table('dispositivos')->where('producto_id', $producto->id)->exists(), 409, 'La anulación de equipos serializados requiere trazabilidad individual.');
                abort_if($linea->variante_id !== null && DB::table('variantes_productos')->where('id', $linea->variante_id)->where('controla_serie', true)->exists(), 409, 'La anulación de equipos serializados requiere trazabilidad individual.');
                $saldos = DB::table('existencias_almacen')->where('empresa_id', $bloqueada->empresa_id)->where('sede_id', $bloqueada->sede_id)
                    ->where('almacen_id', $bloqueada->almacen_id)->where('producto_id', $producto->id)->lockForUpdate()->get();
                abort_unless($saldos->count() === 1 && (string) ($saldos->first()->variante_id ?? '') === (string) ($linea->variante_id ?? ''), 409, 'El saldo del almacén requiere conciliación.');
                $movimiento = $this->inventario->changeLockedProduct($producto, 'entrada', (int) $cantidad, 'Reposición por anulación de venta #'.$bloqueada->id, $actor);
                $movimiento->update(['documento_tipo' => 'anulacion_venta', 'documento_id' => (string) $bloqueada->id]);
            }
            $bloqueada->update(['estado' => 'anulada']);
            $this->auditoria->record($actor->id, 'venta.anulada', 'ventas', $bloqueada->id, ['estado' => 'confirmada'],
                ['estado' => 'anulada', 'motivo' => $motivo], $ip,
                scope: ['empresa_id' => (int) $bloqueada->empresa_id, 'sede_id' => (int) $bloqueada->sede_id]);

            return $bloqueada;
        }, 3);
    }

    /** @param array{cliente_id:int,fecha_venta:string,observaciones:?string,items:array<int,array{producto_id:int,cantidad:int}>} $datos */
    public function actualizarBorrador(Venta $venta, array $datos, User $actor, ?string $ip): Venta
    {
        $this->autorizacion->assertAllowed($actor, 'gestion.ventas.actualizar');
        foreach (['ventas', 'detalles_ventas', 'existencias_almacen', 'movimientos_inventario', 'pagos', 'documentos_electronicos', 'devoluciones_garantias'] as $tabla) {
            abort_unless(Schema::hasTable($tabla), 503, 'Faltan tablas necesarias para editar la venta de forma segura.');
        }

        return DB::transaction(function () use ($venta, $datos, $actor, $ip): Venta {
            $ventaBloqueada = Venta::query()->whereKey($venta->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.ventas.actualizar', (int) $ventaBloqueada->empresa_id, (int) $ventaBloqueada->sede_id), 403);
            abort_unless($ventaBloqueada->estado === 'borrador' && $ventaBloqueada->pedido_id === null, 409, 'Solo se pueden editar ventas en borrador sin pedido asociado.');
            abort_unless($ventaBloqueada->canal === 'presencial', 409, 'La edicion de ventas vinculadas a otros canales requiere su flujo correspondiente.');
            abort_if((float) $ventaBloqueada->descuento !== 0.0 || (float) $ventaBloqueada->impuestos !== 0.0, 409, 'La venta tiene descuentos o impuestos que requieren sus reglas de edicion.');
            $ubicacion = $this->autorizacion->operationLocation($actor);
            abort_unless((int) $ventaBloqueada->empresa_id === $ubicacion['empresa_id'] && (int) $ventaBloqueada->sede_id === $ubicacion['sede_id']
                && (int) $ventaBloqueada->almacen_id === $ubicacion['almacen_id'], 409, 'La edicion requiere operar desde la ubicacion original de la venta.');
            if (Schema::hasColumns('movimientos_inventario', ['documento_tipo', 'documento_id'])) {
                abort_if(DB::table('movimientos_inventario')->where('documento_tipo', 'venta')->where('documento_id', (string) $ventaBloqueada->id)->exists(), 409, 'La venta ya tiene movimientos y no puede editarse.');
            }
            foreach (['pagos', 'documentos_electronicos', 'devoluciones_garantias'] as $tabla) {
                if (Schema::hasColumn($tabla, 'venta_id')) {
                    abort_if(DB::table($tabla)->where('venta_id', $ventaBloqueada->id)->exists(), 409, 'La venta ya tiene registros asociados y no puede editarse.');
                }
            }
            $detallesAnteriores = $ventaBloqueada->detalles()->lockForUpdate()->get()->keyBy('producto_id');
            abort_if($detallesAnteriores->contains(fn ($detalle): bool => $detalle->variante_id !== null), 409, 'Las ventas con variantes requieren el flujo de edicion por variante.');

            $cliente = Cliente::query()->whereKey($datos['cliente_id'])->where('empresa_id', $ventaBloqueada->empresa_id)->where('activo', true)->first();
            abort_unless($cliente !== null, 422, 'El cliente no esta activo en la empresa de la venta.');
            $items = collect($datos['items'])->sortBy('producto_id')->values();
            abort_if($items->isEmpty() || $items->pluck('producto_id')->unique()->count() !== $items->count(), 422, 'Los productos de la venta no son validos.');
            $productos = Producto::query()->whereIn('id', $items->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $totalCentimos = 0;
            $lineas = [];

            foreach ($items as $item) {
                $producto = $productos->get((int) $item['producto_id']);
                abort_unless($producto instanceof Producto && $producto->activo && (int) $producto->empresa_id === (int) $ventaBloqueada->empresa_id, 422, 'Un producto no esta disponible en la empresa de la venta.');
                $cantidad = (int) $item['cantidad'];
                $saldo = DB::table('existencias_almacen')->where('empresa_id', $ventaBloqueada->empresa_id)->where('sede_id', $ventaBloqueada->sede_id)
                    ->where('almacen_id', $ventaBloqueada->almacen_id)->where('producto_id', $producto->id)->lockForUpdate()->get();
                abort_unless($saldo->count() === 1 && $saldo->first()->variante_id === null, 409, 'La existencia del producto no tiene un saldo unico compatible con la edicion.');
                abort_if($cantidad > floor((float) $saldo->first()->cantidad_fisica - (float) $saldo->first()->cantidad_reservada), 422, 'La existencia disponible no alcanza para la cantidad solicitada.');
                abort_if(DB::table('dispositivos')->where('producto_id', $producto->id)->exists(), 409, 'Los productos serializados requieren seleccionar sus identificadores antes de editarse.');
                $precioHistorico = $detallesAnteriores->get($producto->id)?->precio_unitario;
                $precioCentimos = $this->centimos((string) ($precioHistorico ?? $producto->effective_price));
                $importeCentimos = $precioCentimos * $cantidad;
                abort_if($importeCentimos > self::MAX_CENTIMOS - $totalCentimos, 422, 'La venta supera el importe permitido.');
                $totalCentimos += $importeCentimos;
                $lineas[] = [$producto, $cantidad, $precioCentimos, $importeCentimos];
            }

            $antes = ['cliente_id' => $ventaBloqueada->cliente_id, 'fecha_venta' => $ventaBloqueada->fecha_venta?->toDateString(), 'total' => $ventaBloqueada->total];
            $ventaBloqueada->update([
                'cliente_id' => $cliente->id,
                'fecha_venta' => CarbonImmutable::parse($datos['fecha_venta'])->setTimeFrom($ventaBloqueada->fecha_venta ?? now()),
                'observaciones' => $datos['observaciones'] ?? null,
                'subtotal' => $this->importe($totalCentimos),
                'descuento' => '0.00',
                'impuestos' => '0.00',
                'total' => $this->importe($totalCentimos),
            ]);
            $ventaBloqueada->detalles()->delete();
            foreach ($lineas as [$producto, $cantidad, $precioCentimos, $importeCentimos]) {
                $ventaBloqueada->detalles()->create([
                    'producto_id' => $producto->id,
                    'descripcion_historica' => $producto->nombre,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $this->importe($precioCentimos),
                    'total_linea' => $this->importe($importeCentimos),
                ]);
            }
            $this->auditoria->record($actor->id, 'venta.borrador_actualizado', 'ventas', $ventaBloqueada->id, $antes,
                ['cliente_id' => $cliente->id, 'fecha_venta' => $ventaBloqueada->fecha_venta?->toDateString(), 'total' => $ventaBloqueada->total], $ip,
                scope: ['empresa_id' => (int) $ventaBloqueada->empresa_id, 'sede_id' => (int) $ventaBloqueada->sede_id]);

            return $ventaBloqueada->load('detalles');
        }, 3);
    }

    /** @param array<int|string, array<int, int|string>> $dispositivosPorDetalle */
    public function confirmarBorradorCotizacion(Venta $venta, User $actor, ?string $ip, array $dispositivosPorDetalle = []): Venta
    {
        $this->autorizacion->assertAllowed($actor, 'gestion.ventas.crear');
        abort_unless(Schema::hasColumns('ventas', ['pedido_id', 'canal'])
            && Schema::hasColumns('detalles_ventas', ['detalle_pedido_id', 'variante_id'])
            && Schema::hasColumns('movimientos_inventario', ['documento_tipo', 'documento_id', 'variante_id'])
            && Schema::hasColumns('dispositivos', ['id', 'producto_id', 'variante_id', 'almacen_actual_id', 'estado', 'fecha_salida'])
            && Schema::hasColumns('movimientos_dispositivos', ['dispositivo_id', 'movimiento_inventario_id', 'estado_anterior', 'estado_nuevo'])
            && Schema::hasColumns('detalles_ventas_dispositivos', ['detalle_venta_id', 'dispositivo_id']),
            503, 'El esquema no permite confirmar de forma segura una venta vinculada a cotizacion.');

        return DB::transaction(function () use ($venta, $actor, $ip, $dispositivosPorDetalle): Venta {
            $ventaBloqueada = Venta::query()->whereKey($venta->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($this->autorizacion->allowsRecord($actor, 'gestion.ventas.crear', (int) $ventaBloqueada->empresa_id, (int) $ventaBloqueada->sede_id), 403);

            $detalles = $ventaBloqueada->detalles()->orderBy('id')->lockForUpdate()->get();
            abort_unless($ventaBloqueada->pedido_id !== null && $ventaBloqueada->canal === 'presencial', 409, 'Solo se confirma desde esta accion una venta en borrador vinculada a pedido presencial.');
            abort_unless($detalles->isNotEmpty() && $detalles->every(fn (DetalleVenta $detalle): bool => $detalle->detalle_pedido_id !== null)
                && $detalles->pluck('detalle_pedido_id')->unique()->count() === $detalles->count(), 409, 'Los detalles de la venta no mantienen un vinculo unico con el pedido.');
            abort_if(array_diff(array_map('intval', array_keys($dispositivosPorDetalle)), $detalles->modelKeys()) !== [], 422, 'Se enviaron dispositivos para detalles ajenos a esta venta.');
            $identificadoresEnviados = collect($dispositivosPorDetalle)->flatten()->map(fn ($id): int => (int) $id);
            abort_if($identificadoresEnviados->count() !== $identificadoresEnviados->unique()->count(), 422, 'No puedes asociar el mismo dispositivo a más de un detalle de la venta.');

            $pedido = DB::table('pedidos')->where('id', $ventaBloqueada->pedido_id)->lockForUpdate()->first();
            abort_unless($pedido !== null
                && (int) $pedido->empresa_id === (int) $ventaBloqueada->empresa_id
                && (int) $pedido->sede_id === (int) $ventaBloqueada->sede_id
                && (int) $pedido->almacen_id === (int) $ventaBloqueada->almacen_id
                && $pedido->canal === 'presencial' && $pedido->tipo_venta === 'fisica',
                409, 'El pedido no coincide con el alcance o el tipo de venta de la cotizacion.');
            abort_unless(DB::table('almacenes')->where('id', $ventaBloqueada->almacen_id)
                ->where('sede_id', $ventaBloqueada->sede_id)->where('activo', true)->where('permite_venta', true)->exists(),
                409, 'El almacen original ya no esta habilitado para ventas.');

            if ($ventaBloqueada->estado === 'confirmada') {
                abort_unless($this->movimientosDeVentaCoinciden($ventaBloqueada, $detalles), 409, 'La venta ya confirmada requiere conciliacion de movimientos; no se repetira el descuento.');
                abort_unless($this->dispositivosDeVentaCoinciden($detalles, $dispositivosPorDetalle), 409, 'Los dispositivos asociados no coinciden con la confirmacion original.');

                return $ventaBloqueada;
            }
            abort_unless($ventaBloqueada->estado === 'borrador' && $pedido->estado === 'Pendiente', 409, 'La venta o el pedido ya no estan en un estado confirmable.');

            $detallesPedido = DB::table('detalles_pedidos')->where('pedido_id', $pedido->id)
                ->whereIn('id', $detalles->pluck('detalle_pedido_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            abort_unless($detallesPedido->count() === $detalles->count(), 409, 'Faltan detalles del pedido original.');
            $productos = Producto::query()->whereIn('id', $detalles->pluck('producto_id')->unique())->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($detalles as $detalle) {
                $detallePedido = $detallesPedido->get((int) $detalle->detalle_pedido_id);
                abort_unless($detallePedido !== null
                    && (int) $detallePedido->producto_id === (int) $detalle->producto_id
                    && (string) ($detallePedido->variante_id ?? '') === (string) ($detalle->variante_id ?? '')
                    && (float) $detallePedido->cantidad === (float) $detalle->cantidad,
                    409, 'Un detalle de venta no coincide con su detalle de pedido.');
                $producto = $productos->get((int) $detalle->producto_id);
                abort_unless($producto instanceof Producto && $producto->activo && (int) $producto->empresa_id === (int) $ventaBloqueada->empresa_id, 409, 'Un producto de la venta no esta activo en la empresa autorizada.');
                abort_unless(preg_match('/\A[1-9][0-9]*(?:\.0{1,6})?\z/', (string) $detalle->cantidad) === 1 && (float) $detalle->cantidad <= 1000, 409, 'Una cantidad de venta requiere conciliacion.');
                $varianteId = $detalle->variante_id === null ? null : (int) $detalle->variante_id;
                $varianteSerializada = false;
                if ($varianteId !== null) {
                    $variante = DB::table('variantes_productos')->where('id', $varianteId)->where('producto_id', $producto->id)
                        ->where('estado', 'activo')->lockForUpdate()->first(['controla_serie']);
                    abort_unless($variante !== null, 409, 'Una variante ya no esta activa o no corresponde al producto.');
                    $varianteSerializada = (bool) $variante->controla_serie;
                }
                $dispositivos = array_values(array_unique(array_map('intval', $dispositivosPorDetalle[$detalle->getKey()] ?? [])));
                $requiereDispositivos = $varianteSerializada || DB::table('dispositivos')->where('producto_id', $producto->id)
                    ->when($varianteId === null, fn ($consulta) => $consulta->whereNull('variante_id'), fn ($consulta) => $consulta->where('variante_id', $varianteId))->exists();
                if ($requiereDispositivos) {
                    abort_unless((float) $detalle->cantidad === floor((float) $detalle->cantidad)
                        && count($dispositivos) === (int) $detalle->cantidad, 422, 'Selecciona un IMEI o numero de serie disponible por cada unidad.');
                    abort_unless(count($dispositivosPorDetalle[$detalle->getKey()] ?? []) === count($dispositivos), 422, 'No repitas un dispositivo en el mismo detalle.');
                } else {
                    abort_if($dispositivos !== [], 422, 'El detalle no requiere IMEI ni numero de serie.');
                }

                $movimiento = $this->inventario->changeLockedProductInWarehouse(
                    $producto,
                    $varianteId,
                    'salida',
                    (int) $detalle->cantidad,
                    'Salida por confirmacion de venta #'.$ventaBloqueada->id,
                    $actor,
                    (int) $ventaBloqueada->empresa_id,
                    (int) $ventaBloqueada->sede_id,
                    (int) $ventaBloqueada->almacen_id,
                    'venta',
                    (string) $ventaBloqueada->id,
                );
                abort_unless($movimiento !== null, 409, 'No se pudo registrar el movimiento de inventario para un detalle.');
                if ($dispositivos !== []) {
                    $dispositivosBloqueados = DB::table('dispositivos')->whereIn('id', $dispositivos)->orderBy('id')->lockForUpdate()->get();
                    abort_unless($dispositivosBloqueados->count() === count($dispositivos), 409, 'Un dispositivo seleccionado ya no existe.');
                    foreach ($dispositivosBloqueados as $dispositivo) {
                        abort_unless((int) $dispositivo->producto_id === (int) $detalle->producto_id
                            && (string) ($dispositivo->variante_id ?? '') === (string) ($varianteId ?? '')
                            && (int) $dispositivo->almacen_actual_id === (int) $ventaBloqueada->almacen_id
                            && $dispositivo->estado === 'disponible', 409, 'Un IMEI o numero de serie ya no esta disponible en el almacen de la venta.');
                        DB::table('detalles_ventas_dispositivos')->insert([
                            'detalle_venta_id' => $detalle->getKey(), 'dispositivo_id' => $dispositivo->id,
                        ]);
                        $actualizados = DB::table('dispositivos')->where('id', $dispositivo->id)->where('estado', 'disponible')
                            ->where('almacen_actual_id', $ventaBloqueada->almacen_id)->update([
                                'estado' => 'vendido', 'almacen_actual_id' => null, 'fecha_salida' => now(),
                            ]);
                        abort_unless($actualizados === 1, 409, 'El dispositivo fue tomado por otra operacion; vuelve a consultar la disponibilidad.');
                        DB::table('movimientos_dispositivos')->insert([
                            'dispositivo_id' => $dispositivo->id, 'movimiento_inventario_id' => $movimiento->getKey(),
                            'estado_anterior' => 'disponible', 'estado_nuevo' => 'vendido', 'creado_en' => now(),
                        ]);
                    }
                }
            }

            $estadoAnterior = $ventaBloqueada->estado;
            $ventaBloqueada->update(['estado' => 'confirmada']);
            $this->auditoria->record($actor->id, 'venta.confirmada_desde_cotizacion', 'ventas', $ventaBloqueada->id,
                ['estado' => $estadoAnterior], ['estado' => 'confirmada', 'pedido_id' => $pedido->id, 'movimientos' => $detalles->count()], $ip,
                scope: ['empresa_id' => (int) $ventaBloqueada->empresa_id, 'sede_id' => (int) $ventaBloqueada->sede_id]);

            return $ventaBloqueada->load('detalles');
        }, 3);
    }

    /** @param Collection<int, DetalleVenta> $detalles */
    private function movimientosDeVentaCoinciden(Venta $venta, Collection $detalles): bool
    {
        $movimientos = DB::table('movimientos_inventario')
            ->where('documento_tipo', 'venta')->where('documento_id', (string) $venta->id)
            ->where('tipo_movimiento', 'salida')->lockForUpdate()->get();
        if ($movimientos->count() !== $detalles->count()) {
            return false;
        }

        foreach ($detalles as $detalle) {
            $coincide = $movimientos->first(fn (object $movimiento): bool => (int) $movimiento->producto_id === (int) $detalle->producto_id
                && (string) ($movimiento->variante_id ?? '') === (string) ($detalle->variante_id ?? '')
                && (float) $movimiento->cantidad === (float) $detalle->cantidad
                && (int) $movimiento->empresa_id === (int) $venta->empresa_id
                && (int) $movimiento->sede_id === (int) $venta->sede_id
                && (int) $movimiento->almacen_id === (int) $venta->almacen_id);
            if ($coincide === null) {
                return false;
            }
            $movimientos->forget($movimientos->search(fn (object $movimiento): bool => $movimiento->id === $coincide->id));
        }

        return $movimientos->isEmpty();
    }

    /** @param Collection<int, DetalleVenta> $detalles
     * @param  array<int|string, array<int, int|string>>  $dispositivosPorDetalle
     */
    private function dispositivosDeVentaCoinciden(Collection $detalles, array $dispositivosPorDetalle): bool
    {
        foreach ($detalles as $detalle) {
            $asociados = DB::table('detalles_ventas_dispositivos')->where('detalle_venta_id', $detalle->getKey())
                ->orderBy('dispositivo_id')->pluck('dispositivo_id')->map(fn ($id): int => (int) $id)->all();
            $solicitados = array_values(array_unique(array_map('intval', $dispositivosPorDetalle[$detalle->getKey()] ?? [])));
            sort($solicitados);
            if ($asociados !== $solicitados) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ejecuta la operación centimos definida por la clase ServicioVentas.
     *
     * @param  mixed  $precio  Parámetro recibido por la operación.
     * @return int Resultado de la operación.
     */
    private function centimos(string $precio): int
    {
        abort_unless(preg_match('/\A\d{1,8}(?:\.\d{1,2})?\z/', $precio) === 1, 422, 'El precio del producto no es válido.');
        [$entero, $decimal] = array_pad(explode('.', $precio, 2), 2, '');

        return (int) $entero * 100 + (int) str_pad($decimal, 2, '0');
    }

    /**
     * Procesa e incorpora la información importada.
     *
     * @param  mixed  $centimos  Parámetro recibido por la operación.
     * @return string Resultado de la operación.
     */
    private function importe(int $centimos): string
    {
        return intdiv($centimos, 100).'.'.str_pad((string) ($centimos % 100), 2, '0', STR_PAD_LEFT);
    }
}
