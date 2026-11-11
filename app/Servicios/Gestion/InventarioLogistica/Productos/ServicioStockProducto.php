<?php

namespace App\Servicios\Gestion\InventarioLogistica\Productos;

use App\Models\Catalogo\Producto;
use App\Models\User;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use Illuminate\Support\Facades\DB;

final class ServicioStockProducto
{
    public function __construct(
        private readonly ServicioInventario $inventario,
    ) {}

    public function sincronizar(Producto $producto, int $stockObjetivo, User $usuario, string $motivo): void
    {
        abort_if($stockObjetivo < 0, 422, 'El stock no puede ser negativo.');
        abort_if(DB::table('variantes_productos')->where('producto_id', $producto->getKey())->exists(), 422, 'Este producto administra stock por variante. Ajusta las existencias desde sus variantes.');

        $stockActual = (int) DB::table('existencias_almacen')
            ->where('empresa_id', $producto->empresa_id)
            ->where('producto_id', $producto->getKey())
            ->whereNull('variante_id')
            ->sum('cantidad_fisica');
        $diferencia = $stockObjetivo - $stockActual;

        if ($diferencia === 0) {
            return;
        }

        $this->inventario->cambiarProductoBloqueado(
            $producto,
            $diferencia > 0 ? 'entrada' : 'salida',
            abs($diferencia),
            $motivo,
            $usuario,
        );
    }
}
