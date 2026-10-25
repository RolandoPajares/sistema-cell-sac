<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Cotizacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ServicioCotizaciones
{
    public function create(array $data, User $user): Cotizacion
    {
        return DB::transaction(function () use ($data, $user): Cotizacion {
            $customerId = $data['cliente_id'] ?? Cliente::whereBelongsTo($user, 'user')->where('tipo', 'mayorista')->value('id');
            abort_unless($customerId, 422, 'El usuario debe tener un perfil de cliente mayorista.');

            $customer = Cliente::findOrFail($customerId);
            abort_unless(in_array($user->rol, ['administrador', 'ventas_mayoristas'], true) || $customer->usuario_id === $user->id, 403);

            $items = collect($data['items']);
            $products = Producto::whereIn('id', $items->pluck('producto_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $total = 0.0;
            $lines = [];

            foreach ($items as $item) {
                $product = $products->get((int) $item['producto_id']);
                abort_unless($product instanceof Producto && $product->activo, 422, 'Producto no disponible.');
                $price = (float) $product->effective_price;
                $total += $price * (int) $item['cantidad'];
                $lines[] = [$product->id, (int) $item['cantidad'], $price];
            }

            $quotation = Cotizacion::create([
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
