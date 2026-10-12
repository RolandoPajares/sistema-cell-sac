<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<int|string, int|string>  $cart
     */
    public function checkout(User $user, array $cart, ?string $couponCode, ?string $ip): Order
    {
        return DB::transaction(function () use ($user, $cart, $couponCode, $ip): Order {
            $products = Product::query()
                ->whereIn('id', array_keys($cart))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $items = [];
            $subtotalCents = 0;

            foreach ($cart as $id => $rawQuantity) {
                $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT);
                abort_if($quantity === false || $quantity < 1 || $quantity > 1000, 422, 'Cantidad de carrito inválida.');

                $product = $products->get((int) $id);
                abort_unless($product instanceof Product && $product->activo, 422, 'Un producto dejó de estar disponible.');
                abort_if($quantity > (int) $product->existencias, 422, 'Stock insuficiente para '.$product->nombre.'.');

                $priceCents = (int) round((float) $product->effective_price * 100, 0, PHP_ROUND_HALF_UP);
                $lineCents = $priceCents * $quantity;
                abort_if($lineCents < 0 || $subtotalCents > 9999999999 - $lineCents, 422, 'El pedido supera el total permitido.');

                $subtotalCents += $lineCents;
                $items[] = [$product, $quantity, $priceCents];
            }

            $discountCents = $this->discountFor($couponCode, $subtotalCents);
            $order = Order::create([
                'usuario_id' => $user->id,
                'subtotal' => $subtotalCents / 100,
                'descuento' => $discountCents / 100,
                'cupon_codigo' => $couponCode,
                'total' => ($subtotalCents - $discountCents) / 100,
                'estado' => 'Pendiente',
                'creado_en' => now(),
            ]);

            foreach ($items as [$product, $quantity, $priceCents]) {
                $order->details()->create([
                    'producto_id' => $product->id,
                    'cantidad' => $quantity,
                    'precio_unitario' => $priceCents / 100,
                ]);
                $this->inventoryService->changeLockedProduct($product, 'salida', $quantity, 'Salida por pedido #'.$order->id, $user);
            }

            $this->auditLogger->record($user->id, 'order.created', 'pedidos', $order->id, null, ['total' => $order->total, 'items' => count($items)], $ip);

            return $order;
        }, 3);
    }

    public function updateStatus(Order $order, User $user, string $status, ?string $ip): void
    {
        DB::transaction(function () use ($order, $user, $status, $ip): void {
            $lockedOrder = Order::with('details')->whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_if($lockedOrder->estado === 'Cancelado' && $status !== 'Cancelado', 422, 'Un pedido cancelado no puede reabrirse.');

            $before = $lockedOrder->estado;
            if ($status === 'Cancelado' && $before !== 'Cancelado') {
                foreach ($lockedOrder->details as $line) {
                    $product = Product::whereKey($line->producto_id)->lockForUpdate()->firstOrFail();
                    $this->inventoryService->changeLockedProduct($product, 'entrada', (int) $line->cantidad, 'Reingreso por cancelación del pedido #'.$lockedOrder->id, $user);
                }
            }

            $lockedOrder->update(['estado' => $status]);
            $this->auditLogger->record($user->id, 'order.status_changed', 'pedidos', $lockedOrder->id, ['estado' => $before], ['estado' => $status], $ip);
        }, 3);
    }

    private function discountFor(?string $couponCode, int $subtotalCents): int
    {
        if ($couponCode === null || $couponCode === '') {
            return 0;
        }

        $coupon = Coupon::where('codigo', $couponCode)->lockForUpdate()->first();
        abort_unless($coupon && $coupon->activo && $coupon->inicia_en <= now() && $coupon->finaliza_en >= now(), 422, 'El cupón venció o fue desactivado.');
        abort_if($coupon->limite_usos !== null && $coupon->usos >= $coupon->limite_usos, 422, 'El cupón agotó sus usos.');
        abort_if($subtotalCents < (int) round((float) $coupon->minimo_compra * 100), 422, 'El pedido no alcanza el mínimo del cupón.');

        $discountCents = $coupon->tipo_descuento === 'porcentaje'
            ? (int) round($subtotalCents * ((float) $coupon->valor / 100), 0, PHP_ROUND_HALF_UP)
            : (int) round((float) $coupon->valor * 100, 0, PHP_ROUND_HALF_UP);

        $coupon->increment('usos');

        return min($discountCents, $subtotalCents);
    }
}
