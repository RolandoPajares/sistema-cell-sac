<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Venta;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServicioVentas
{
    private const MAX_CENTIMOS = 999999999999999;

    public function __construct(
        private readonly ServicioInventario $inventario,
        private readonly RegistradorAuditoria $auditoria,
        private readonly ServicioAutorizacion $autorizacion,
    ) {}

    /** @param array{cliente_id:int,tipo_venta:string,fecha_venta:string,observaciones:?string,items:array<int,array{producto_id:int,cantidad:int}>} $datos */
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

    private function centimos(string $precio): int
    {
        abort_unless(preg_match('/\A\d{1,8}(?:\.\d{1,2})?\z/', $precio) === 1, 422, 'El precio del producto no es válido.');
        [$entero, $decimal] = array_pad(explode('.', $precio, 2), 2, '');

        return (int) $entero * 100 + (int) str_pad($decimal, 2, '0');
    }

    private function importe(int $centimos): string
    {
        return intdiv($centimos, 100).'.'.str_pad((string) ($centimos % 100), 2, '0', STR_PAD_LEFT);
    }
}
