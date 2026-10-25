<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Inventario\MovimientoInventario;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ServicioInventario
{
    public function adjust(Producto $product, string $movementType, int $quantity, string $notes, User $user): MovimientoInventario
    {
        return DB::transaction(function () use ($product, $movementType, $quantity, $notes, $user): MovimientoInventario {
            $lockedProduct = Producto::whereKey($product->getKey())->lockForUpdate()->firstOrFail();

            return $this->changeLockedProduct($lockedProduct, $movementType, $quantity, $notes, $user);
        }, 3);
    }

    public function changeLockedProduct(Producto $product, string $movementType, int $quantity, string $notes, User $user): MovimientoInventario
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

        return MovimientoInventario::create([
            'producto_id' => $product->id,
            'usuario_id' => $user->id,
            'tipo_movimiento' => $movementType,
            'cantidad' => $movementType === 'ajuste' ? $nextStock - $previousStock : $quantity,
            'notas' => $notes,
            'creado_en' => now(),
        ]);
    }
}
