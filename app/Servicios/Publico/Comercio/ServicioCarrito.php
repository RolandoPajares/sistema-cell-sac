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
        // Limpia identificadores y cantidades antes de usar el carrito en el checkout.
        // Agrupa en $normalizado los datos que se utilizaran en el siguiente paso.
        $normalizado = [];

        // Recorre los elementos recibidos para validar o procesar cada registro.
        // Recorre cada linea para conservar solo productos y cantidades validas.
        foreach ($carrito as $productoId => $linea) {
            // Guarda en $id el resultado necesario para continuar la operacion.
            $id = filter_var($productoId, FILTER_VALIDATE_INT);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($id === false || $id < 1) {
                continue;
            }

            // Guarda en $cantidad el resultado necesario para continuar la operacion.
            $cantidad = is_array($linea)
                ? filter_var($linea['cantidad'] ?? $linea['quantity'] ?? null, FILTER_VALIDATE_INT)
                : filter_var($linea, FILTER_VALIDATE_INT);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
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
