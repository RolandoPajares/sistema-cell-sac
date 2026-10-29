<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ServicioVentas
{
    private const MAX_ORDER_TOTAL_CENTS = 9999999999;

    public function __construct(
        private readonly ServicioInventario $inventoryService,
        private readonly RegistradorAuditoria $auditLogger,
    ) {}

    /**
     * @param  array<int, array{producto_id:int,cantidad:int}>  $items
     */
    public function register(User $customer, array $items, User $actor, string $saleType, string $saleDate, ?string $notes, ?string $ip): Pedido
    {
        return DB::transaction(function () use ($customer, $items, $actor, $saleType, $saleDate, $notes, $ip): Pedido {
            $preparedItems = $this->prepareItems($items);
            $products = $this->lockedProducts($preparedItems);
            $totals = $this->calculateTotals($preparedItems, $products);
            $order = Pedido::create([
                'usuario_id' => $customer->id,
                'subtotal' => $totals['subtotal_cents'] / 100,
                'descuento' => 0,
                'cupon_codigo' => null,
                'total' => $totals['subtotal_cents'] / 100,
                'estado' => 'Pendiente',
                'creado_en' => $this->dateTime($saleDate),
            ]);

            $this->replaceDetailsAndDecreaseStock($order, $preparedItems, $products, $actor);
            $this->auditLogger->record($actor->id, 'sale.created', 'pedidos', $order->id, null, [
                'usuario_id' => $customer->id,
                'tipo_venta' => $saleType,
                'total' => $order->total,
                'items' => count($preparedItems),
                'observaciones' => $notes,
            ], $ip);

            return $order;
        }, 3);
    }

    /**
     * @param  array<int, array{producto_id:int,cantidad:int}>  $items
     */
    public function update(Pedido $order, User $customer, array $items, User $actor, string $saleType, string $saleDate, ?string $notes, ?string $ip): Pedido
    {
        return DB::transaction(function () use ($order, $customer, $items, $actor, $saleType, $saleDate, $notes, $ip): Pedido {
            $lockedOrder = Pedido::query()->with('details')->lockForUpdate()->findOrFail($order->id);
            abort_if($lockedOrder->estado === 'Cancelado', 422, 'Una venta cancelada no puede editarse.');
            $before = [
                'usuario_id' => $lockedOrder->usuario_id,
                'total' => $lockedOrder->total,
                'items' => $lockedOrder->details->count(),
            ];
            $preparedItems = $this->prepareItems($items);

            foreach ($lockedOrder->details as $detail) {
                $product = Producto::query()->lockForUpdate()->findOrFail($detail->producto_id);
                $this->inventoryService->changeLockedProduct($product, 'entrada', (int) $detail->cantidad, 'Reversión por edición de venta #'.$lockedOrder->id, $actor);
            }

            $products = $this->lockedProducts($preparedItems);
            $totals = $this->calculateTotals($preparedItems, $products);
            $discountCents = min($totals['subtotal_cents'], (int) round((float) $lockedOrder->descuento * 100));
            $lockedOrder->update([
                'usuario_id' => $customer->id,
                'subtotal' => $totals['subtotal_cents'] / 100,
                'descuento' => $discountCents / 100,
                'total' => ($totals['subtotal_cents'] - $discountCents) / 100,
                'creado_en' => $this->dateTime($saleDate),
            ]);
            $lockedOrder->details()->delete();
            $this->replaceDetailsAndDecreaseStock($lockedOrder, $preparedItems, $products, $actor);
            $this->auditLogger->record($actor->id, 'sale.updated', 'pedidos', $lockedOrder->id, $before, [
                'usuario_id' => $lockedOrder->usuario_id,
                'tipo_venta' => $saleType,
                'total' => $lockedOrder->total,
                'items' => count($preparedItems),
                'observaciones' => $notes,
            ], $ip);

            return $lockedOrder;
        }, 3);
    }

    /**
     * @param  array<int, array{producto_id:int,cantidad:int}>  $items
     * @return array<int, array{producto_id:int,cantidad:int}>
     */
    private function prepareItems(array $items): array
    {
        return collect($items)
            ->map(static fn (array $item): array => [
                'producto_id' => (int) $item['producto_id'],
                'cantidad' => (int) $item['cantidad'],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{producto_id:int,cantidad:int}>  $items
     * @return Collection<int, Producto>
     */
    private function lockedProducts(array $items): Collection
    {
        $productIds = collect($items)->pluck('producto_id')->sort()->values();
        $products = Producto::query()
            ->whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item['producto_id']);
            abort_unless($product instanceof Producto && $product->activo, 422, 'Uno de los productos no está disponible.');
            abort_if($item['cantidad'] > (int) $product->existencias, 422, 'Stock insuficiente para '.$product->nombre.'.');
        }

        return $products;
    }

    /**
     * @param  array<int, array{producto_id:int,cantidad:int}>  $items
     * @param  Collection<int, Producto>  $products
     * @return array{subtotal_cents:int}
     */
    private function calculateTotals(array $items, Collection $products): array
    {
        $subtotalCents = 0;

        foreach ($items as $item) {
            $product = $products->get($item['producto_id']);
            $priceCents = (int) round((float) $product->effective_price * 100, 0, PHP_ROUND_HALF_UP);
            $lineCents = $priceCents * $item['cantidad'];
            abort_if($lineCents < 0 || $subtotalCents > self::MAX_ORDER_TOTAL_CENTS - $lineCents, 422, 'La venta supera el total permitido.');
            $subtotalCents += $lineCents;
        }

        return ['subtotal_cents' => $subtotalCents];
    }

    /**
     * @param  array<int, array{producto_id:int,cantidad:int}>  $items
     * @param  Collection<int, Producto>  $products
     */
    private function replaceDetailsAndDecreaseStock(Pedido $order, array $items, Collection $products, User $actor): void
    {
        foreach ($items as $item) {
            $product = $products->get($item['producto_id']);
            $order->details()->create([
                'producto_id' => $product->id,
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $product->effective_price,
            ]);
            $this->inventoryService->changeLockedProduct($product, 'salida', $item['cantidad'], 'Salida por venta #'.$order->id, $actor);
        }
    }

    private function dateTime(string $saleDate): CarbonImmutable
    {
        return CarbonImmutable::parse($saleDate)->setTimeFrom(now());
    }
}
