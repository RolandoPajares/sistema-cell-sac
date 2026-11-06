<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Comercio\Cupon;
use App\Models\Comercio\Pedido;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ServicioPedidos dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ServicioPedidos
{
    private const MAX_ITEM_QUANTITY = 1000;

    private const MAX_ORDER_TOTAL_CENTS = 9999999999;

    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioInventario $inventoryService,
        private readonly RegistradorAuditoria $auditLogger,
        private readonly ServicioAutorizacion $authorization,
    ) {}

    /**
     * @param  array<int|string, int|string>  $cart
     */
    public function checkout(User $user, array $cart, ?string $couponCode, ?string $ip, string $checkoutToken): Pedido
    {
        $this->authorization->assertAllowed($user, 'gestion.pedidos.crear');
        abort_if($cart === [] || preg_match('/^[a-f0-9]{64}$/', $checkoutToken) !== 1, 422, 'La solicitud de compra no es válida.');

        return DB::transaction(function () use ($user, $cart, $couponCode, $ip, $checkoutToken): Pedido {
            $location = $this->authorization->operationLocation($user);
            abort_unless($location['empresa_id'] !== null && $location['sede_id'] !== null && $location['almacen_id'] !== null, 503, 'No hay un almacén autorizado disponible.');
            $tokenHash = hash('sha256', $user->getKey().':'.$checkoutToken);
            DB::table('carritos')->upsert([[
                'empresa_id' => $location['empresa_id'],
                'sede_id' => $location['sede_id'],
                'usuario_id' => $user->getKey(),
                'resumen_token' => $tokenHash,
                'estado' => 'activo',
            ]], ['resumen_token'], ['resumen_token']);
            $checkout = DB::table('carritos')->where('resumen_token', $tokenHash)->lockForUpdate()->first();
            abort_unless($checkout !== null && (int) $checkout->usuario_id === (int) $user->getKey()
                && (int) $checkout->empresa_id === $location['empresa_id'] && (int) $checkout->sede_id === $location['sede_id'], 409, 'La solicitud de compra pertenece a otro contexto.');
            if ($checkout->pedido_id !== null) {
                return Pedido::query()->findOrFail($checkout->pedido_id);
            }
            $products = Producto::query()
                ->whereIn('id', array_keys($cart))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $disponibilidad = $this->inventoryService->disponibilidadParaOperacion($user, array_keys($cart));
            $items = [];
            $subtotalCents = 0;

            foreach ($cart as $id => $rawQuantity) {
                $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT);
                abort_if($quantity === false || $quantity < 1 || $quantity > self::MAX_ITEM_QUANTITY, 422, 'Cantidad de carrito inválida.');

                $product = $products->get((int) $id);
                abort_unless($product instanceof Producto && $product->activo && array_key_exists((int) $id, $disponibilidad), 422, 'Un producto dejó de estar disponible.');
                abort_if($quantity > $disponibilidad[(int) $id], 422, 'Stock insuficiente en el almacén autorizado o faltan identificadores para '.$product->nombre.'.');

                $priceCents = (int) round((float) $product->effective_price * 100, 0, PHP_ROUND_HALF_UP);
                $lineCents = $priceCents * $quantity;
                abort_if($lineCents < 0 || $subtotalCents > self::MAX_ORDER_TOTAL_CENTS - $lineCents, 422, 'El pedido supera el total permitido.');

                $subtotalCents += $lineCents;
                $items[] = [$product, $quantity, $priceCents];
            }

            $descuentoCupon = $this->discountFor($couponCode, $subtotalCents, (int) $location['empresa_id']);
            $order = Pedido::create([
                'usuario_id' => $user->id,
                'canal' => 'ecommerce',
                'subtotal' => $subtotalCents / 100,
                'descuento' => $descuentoCupon['descuento_centimos'] / 100,
                'cupon_id' => $descuentoCupon['cupon_id'],
                'cupon_codigo' => $couponCode,
                'total' => ($subtotalCents - $descuentoCupon['descuento_centimos']) / 100,
                'estado' => 'Pendiente',
                'creado_en' => now(),
                ...$this->authorization->operationScopeAttributes($user, 'pedidos'),
            ]);

            foreach ($items as [$product, $quantity, $priceCents]) {
                $movimiento = $this->inventoryService->changeLockedProduct($product, 'salida', $quantity, 'Salida por pedido #'.$order->id, $user);
                $movimiento->update(['documento_tipo' => 'pedido', 'documento_id' => (string) $order->id]);
                $order->details()->create([
                    'producto_id' => $product->id,
                    'variante_id' => $movimiento->variante_id,
                    'cantidad' => $quantity,
                    'precio_unitario' => $priceCents / 100,
                ]);
            }

            DB::table('carritos')->where('id', $checkout->id)->update([
                'pedido_id' => $order->id,
                'estado' => 'convertido',
                'actualizado_en' => now(),
            ]);

            $this->auditLogger->record($user->id, 'order.created', 'pedidos', $order->id, null, ['total' => $order->total, 'items' => count($items)], $ip);

            return $order;
        }, 3);
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $order  Parámetro recibido por la operación.
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @param  mixed  $status  Parámetro recibido por la operación.
     * @param  mixed  $ip  Parámetro recibido por la operación.
     * @return void Resultado de la operación.
     */
    public function updateStatus(Pedido $order, User $user, string $status, ?string $ip): void
    {
        abort_unless($this->authorization->allowsRecord(
            $user,
            'gestion.pedidos.actualizar',
            is_numeric($order->empresa_id) ? (int) $order->empresa_id : null,
            is_numeric($order->sede_id) ? (int) $order->sede_id : null,
        ), 403);

        DB::transaction(function () use ($order, $user, $status, $ip): void {
            $lockedOrder = Pedido::with('details')->whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if($lockedOrder->estado === 'Cancelado' && $status !== 'Cancelado', 422, 'Un pedido cancelado no puede reabrirse.');

            $before = $lockedOrder->estado;
            if ($status === 'Cancelado' && $before !== 'Cancelado') {
                abort_if(in_array($before, ['Enviado', 'Entregado'], true), 409, 'El pedido ya fue enviado o entregado y requiere un proceso de devolución.');
                abort_if(Schema::hasTable('pagos') && DB::table('pagos')->where('pedido_id', $lockedOrder->id)->exists(), 409, 'El pedido tiene registros de pago que requieren conciliación.');
                abort_if(Schema::hasTable('ventas') && DB::table('ventas')->where('pedido_id', $lockedOrder->id)->exists(), 409, 'El pedido está vinculado a una venta.');
                abort_if(Schema::hasTable('devoluciones_garantias') && DB::table('devoluciones_garantias')->where('pedido_id', $lockedOrder->id)->exists(), 409, 'El pedido tiene una devolución o garantía abierta.');
                $location = $this->authorization->operationLocation($user);
                abort_unless($lockedOrder->almacen_id !== null && (int) $lockedOrder->almacen_id === $location['almacen_id'], 409, 'La reposición requiere operar desde el almacén original del pedido.');
                $salidas = DB::table('movimientos_inventario')->where('documento_tipo', 'pedido')
                    ->where('documento_id', (string) $lockedOrder->id)->where('tipo_movimiento', 'salida')
                    ->where('empresa_id', $lockedOrder->empresa_id)->where('sede_id', $lockedOrder->sede_id)
                    ->where('almacen_id', $lockedOrder->almacen_id)->get();
                abort_unless($salidas->count() === $lockedOrder->details->count(), 409, 'No se puede conciliar la salida de inventario de este pedido.');
                foreach ($lockedOrder->details as $line) {
                    $claveSalida = $salidas->search(fn (object $movimiento): bool => (int) $movimiento->producto_id === (int) $line->producto_id
                        && (float) $movimiento->cantidad === (float) $line->cantidad
                        && $movimiento->variante_id == $line->variante_id);
                    abort_unless($claveSalida !== false, 409, 'El detalle del pedido no coincide con su salida de inventario.');
                    $salidas->forget($claveSalida);
                    $product = Producto::whereKey($line->producto_id)->lockForUpdate()->firstOrFail();
                    $entrada = $this->inventoryService->changeLockedProduct($product, 'entrada', (int) $line->cantidad, 'Reingreso por cancelación del pedido #'.$lockedOrder->id, $user);
                    abort_unless($entrada->variante_id == $line->variante_id, 409, 'La variante del pedido no coincide con el saldo a reponer.');
                    $entrada->update(['documento_tipo' => 'pedido_cancelacion', 'documento_id' => (string) $lockedOrder->id]);
                }
            }

            $lockedOrder->update(['estado' => $status]);
            $this->auditLogger->record($user->id, 'order.status_changed', 'pedidos', $lockedOrder->id, ['estado' => $before], ['estado' => $status], $ip);
        }, 3);
    }

    /** @return array{descuento_centimos:int,cupon_id:?int} */
    private function discountFor(?string $couponCode, int $subtotalCents, int $empresaId): array
    {
        if ($couponCode === null || $couponCode === '') {
            return ['descuento_centimos' => 0, 'cupon_id' => null];
        }

        abort_unless(Schema::hasColumn('cupones', 'empresa_id'), 503,
            'Los cupones no están disponibles hasta configurar su empresa propietaria.');
        $coupon = Cupon::where('empresa_id', $empresaId)->where('codigo', $couponCode)->lockForUpdate()->first();
        abort_unless($coupon && $coupon->activo && $coupon->inicia_en <= now() && $coupon->finaliza_en >= now(), 422, 'El cupón venció o fue desactivado.');
        abort_if($coupon->limite_usos !== null && $coupon->usos >= $coupon->limite_usos, 422, 'El cupón agotó sus usos.');
        abort_if($subtotalCents < (int) round((float) $coupon->minimo_compra * 100), 422, 'El pedido no alcanza el mínimo del cupón.');

        $discountCents = $coupon->tipo_descuento === 'porcentaje'
            ? (int) round($subtotalCents * ((float) $coupon->valor / 100), 0, PHP_ROUND_HALF_UP)
            : (int) round((float) $coupon->valor * 100, 0, PHP_ROUND_HALF_UP);

        $coupon->increment('usos');

        return ['descuento_centimos' => min($discountCents, $subtotalCents), 'cupon_id' => (int) $coupon->getKey()];
    }
}
