<?php

namespace App\Servicios;

final class ServicioCarrito
{
    /**
     * @param  array<int|string, mixed>  $carrito
     * @return array<int, array{cantidad:int,variante_id:?int}>
     */
    public function normalizar(array $carrito): array
    {
        $normalizado = [];

        foreach ($carrito as $productoId => $linea) {
            $id = filter_var($productoId, FILTER_VALIDATE_INT);
            if ($id === false || $id < 1) {
                continue;
            }

            $cantidad = is_array($linea)
                ? filter_var($linea['cantidad'] ?? $linea['quantity'] ?? null, FILTER_VALIDATE_INT)
                : filter_var($linea, FILTER_VALIDATE_INT);
            if ($cantidad === false || $cantidad < 1) {
                continue;
            }

            $varianteId = is_array($linea) && isset($linea['variante_id']) && $linea['variante_id'] !== ''
                ? filter_var($linea['variante_id'], FILTER_VALIDATE_INT)
                : null;

            $normalizado[(int) $id] = [
                'cantidad' => (int) $cantidad,
                'variante_id' => $varianteId === false ? null : ($varianteId === null ? null : (int) $varianteId),
            ];
        }

        return $normalizado;
    }
}
