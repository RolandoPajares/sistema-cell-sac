<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function create(array $data, User $user): Quotation
    {
        return DB::transaction(function () use ($data, $user): Quotation {
            $customerId = $data['cliente_id'] ?? Customer::whereBelongsTo($user, 'user')->where('tipo', 'mayorista')->value('id');
            abort_unless($customerId, 422, 'El usuario debe tener un perfil de cliente mayorista.');

            $customer = Customer::findOrFail($customerId);
            abort_unless(in_array($user->rol, ['administrador', 'ventas_mayoristas'], true) || $customer->usuario_id === $user->id, 403);

            $items = collect($data['items']);
            $products = Product::whereIn('id', $items->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $total = 0.0;
            $lines = [];

            foreach ($items as $item) {
                $product = $products->get((int) $item['producto_id']);
                abort_unless($product instanceof Product && $product->activo, 422, 'Producto no disponible.');
                $price = (float) $product->effective_price;
                $total += $price * (int) $item['cantidad'];
                $lines[] = [$product->id, (int) $item['cantidad'], $price];
            }

            $quotation = Quotation::create([
                'cliente_id' => $customer->id,
                'creado_por' => $user->id,
                'total' => $total,
                'estado' => 'Borrador',
                'notas' => $data['notas'] ?? null,
                'activo' => true,
                'creado_en' => now(),
            ]);

            foreach ($lines as [$productId, $quantity, $price]) {
                $quotation->details()->create(['producto_id' => $productId, 'cantidad' => $quantity, 'precio_unitario' => $price]);
            }

            return $quotation;
        }, 3);
    }
}
