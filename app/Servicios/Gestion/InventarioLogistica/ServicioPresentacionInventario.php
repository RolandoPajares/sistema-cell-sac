<?php

namespace App\Servicios\Gestion\InventarioLogistica;

use App\Models\Catalogo\Producto;
use App\Models\Inventario\MovimientoInventario;
use Illuminate\Support\Str;

final class ServicioPresentacionInventario
{
    /**
     * @return array{image:?string,status:array{label:string,class:string},sku:string,movement:array{label:string,class:string,icon:string}}
     */
    public function producto(Producto $product, ?MovimientoInventario $movement): array
    {
        return [
            'image' => $this->imagen($product->url_imagen),
            'status' => $this->estadoStock((int) $product->existencias_alcance),
            'sku' => $product->variants->pluck('codigo_sku')->filter()->unique()->values()->implode(', ') ?: 'Sin SKU registrado',
            'movement' => $this->movimiento($movement),
        ];
    }

    /**
     * Prepara los datos derivados que necesita la tabla y el diálogo del catálogo.
     *
     * @return array{image:?string,images:array<int,?string>,stock:float,sku:string,status:string,status_class:string,stock_class:string}
     */
    public function productoCatalogo(Producto $product): array
    {
        $stock = (float) $product->existencias_alcance;
        $status = ! $product->activo
            ? 'Inactivo'
            : ($stock <= 0 ? 'Sin stock' : ($product->precio_oferta ? 'Oferta' : 'Activo'));

        return [
            'image' => $this->imagen($product->url_imagen),
            'images' => $product->variants
                ->flatMap(fn ($variant) => $variant->images->mapWithKeys(fn ($image): array => [$image->id => $this->imagen($image->ruta_imagen)]))
                ->all(),
            'stock' => $stock,
            'sku' => $product->variants->pluck('codigo_sku')->filter()->unique()->values()->implode(', ') ?: 'Sin SKU registrado',
            'status' => $status,
            'status_class' => match ($status) {
                'Sin stock' => 'danger',
                'Oferta' => 'warning',
                'Inactivo' => 'muted',
                default => 'success',
            },
            'stock_class' => $stock <= 0 ? 'danger' : 'success',
        ];
    }

    /**
     * @return array{label:string,class:string}
     */
    public function estadoStock(int $stock): array
    {
        return $stock > 0
            ? ['label' => 'Con existencias', 'class' => 'optimal']
            : ['label' => 'Sin existencias', 'class' => 'critical'];
    }

    /**
     * @return array{label:string,class:string,icon:string}
     */
    public function movimiento(?MovimientoInventario $movement): array
    {
        return match ($movement?->tipo_movimiento) {
            'entrada' => ['label' => 'Entrada', 'class' => 'entry', 'icon' => 'down'],
            'salida' => ['label' => 'Salida', 'class' => 'exit', 'icon' => 'up'],
            'ajuste' => ['label' => 'Ajuste', 'class' => 'adjustment', 'icon' => 'pencil'],
            default => ['label' => 'Sin movimientos', 'class' => 'empty', 'icon' => 'up'],
        };
    }

    /**
     * @return array{class:string,quantity:string,sign:string}
     */
    public function filaMovimiento(MovimientoInventario $movement): array
    {
        $quantity = $movement->tipo_movimiento === 'salida'
            ? -abs((float) $movement->cantidad)
            : (float) $movement->cantidad;

        return [
            'class' => $movement->tipo_movimiento === 'entrada' ? 'entry' : ($movement->tipo_movimiento === 'salida' ? 'exit' : 'adjustment'),
            'quantity' => rtrim(rtrim(number_format($quantity, 6, '.', ''), '0'), '.'),
            'sign' => $quantity >= 0 ? '+' : '',
        ];
    }

    public function imagen(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : asset(ltrim($path, '/'));
    }

    public function etiquetaHoy(): string
    {
        $months = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
        $today = now();

        return $today->format('d').' de '.$months[(int) $today->format('n')].', '.$today->format('Y');
    }
}
