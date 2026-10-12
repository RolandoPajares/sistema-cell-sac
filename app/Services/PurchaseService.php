<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array{proveedor_id:int, fecha_compra:string, items:array<int, array{producto_id:int, cantidad:int, costo_unitario:numeric-string|float|int}>}  $data
     */
    public function receive(array $data, User $user, ?string $ip): Purchase
    {
        return DB::transaction(function () use ($data, $user, $ip): Purchase {
            $items = collect($data['items']);
            $total = $items->sum(fn (array $item): float => (float) $item['cantidad'] * (float) $item['costo_unitario']);
            $purchase = Purchase::create([
                'proveedor_id' => $data['proveedor_id'],
                'creado_por' => $user->id,
                'total' => $total,
                'estado' => 'Recibida',
                'fecha_compra' => $data['fecha_compra'],
                'activo' => true,
                'creado_en' => now(),
            ]);

            foreach ($items as $item) {
                $product = Product::whereKey($item['producto_id'])->lockForUpdate()->firstOrFail();
                $purchase->details()->create([
                    'producto_id' => $product->id,
                    'cantidad' => $item['cantidad'],
                    'costo_unitario' => $item['costo_unitario'],
                ]);
                $this->inventoryService->changeLockedProduct($product, 'entrada', (int) $item['cantidad'], 'Ingreso por compra #'.$purchase->id, $user);
            }

            $this->auditLogger->record($user->id, 'purchase.received', 'compras', $purchase->id, null, ['proveedor_id' => $data['proveedor_id'], 'items' => $items->count()], $ip);

            return $purchase;
        }, 3);
    }
}
