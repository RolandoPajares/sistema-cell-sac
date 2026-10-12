<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function adjust(Product $product, string $movementType, int $quantity, string $notes, User $user): InventoryMovement
    {
        return DB::transaction(function () use ($product, $movementType, $quantity, $notes, $user): InventoryMovement {
            $lockedProduct = Product::whereKey($product->getKey())->lockForUpdate()->firstOrFail();

            return $this->changeLockedProduct($lockedProduct, $movementType, $quantity, $notes, $user);
        }, 3);
    }

    public function changeLockedProduct(Product $product, string $movementType, int $quantity, string $notes, User $user): InventoryMovement
    {
        $previousStock = (int) $product->existencias;
        $nextStock = match ($movementType) {
            'entrada' => $previousStock + $quantity,
            'salida' => $previousStock - $quantity,
            'ajuste' => $quantity,
            default => throw new \InvalidArgumentException('Tipo de movimiento inválido.'),
        };

        abort_if($nextStock < 0, 422, 'La salida supera el stock disponible.');

        $product->update(['existencias' => $nextStock]);

        return InventoryMovement::create([
            'producto_id' => $product->id,
            'usuario_id' => $user->id,
            'tipo_movimiento' => $movementType,
            'cantidad' => $movementType === 'ajuste' ? $nextStock - $previousStock : $quantity,
            'notas' => $notes,
            'creado_en' => now(),
        ]);
    }
}
