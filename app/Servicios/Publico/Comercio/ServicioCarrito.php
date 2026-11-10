<?php

namespace App\Servicios\Publico\Comercio;

final class ServicioCarrito
{
    /**
     * Normaliza las líneas del carrito a la estructura que consumen checkout y ventas.
     *
     * Acepta claves de producto convertibles a entero y líneas representadas como
     * cantidad simple o como arreglo con cantidad y variante. Descarta identificadores
     * y cantidades no enteros positivos, y conserva una variante válida cuando existe.
     * No persiste información ni modifica la sesión.
     *
     * @param  array<int|string, mixed>  $carrito
     * @return array<int, array{cantidad:int, variante_id:?int}> Líneas normalizadas por producto.
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
