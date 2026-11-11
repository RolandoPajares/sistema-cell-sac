<?php

namespace App\Servicios\Gestion\InventarioLogistica\Productos;

use App\Contratos\Productos\AlcanceProductos;
use App\Contratos\Productos\AuditoriaProductos;
use App\Models\Catalogo\Producto;
use App\Models\User;
use App\Servicios\Compartidos\Catalogo\ServicioCatalogo;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use Illuminate\Support\Facades\DB;

final class ServicioGestionProductos
{
    public function __construct(
        private readonly AlcanceProductos $alcance,
        private readonly ServicioInventario $inventario,
        private readonly ServicioCatalogo $catalogo,
        private readonly AuditoriaProductos $auditoria,
        private readonly ServicioStockProducto $stock,
    ) {}

    /** @param array<string, mixed> $datos */
    public function crear(array $datos, ?int $proveedorId, User $usuario, string $ip): Producto
    {
        return DB::transaction(function () use ($datos, $proveedorId, $usuario, $ip): Producto {
            $stockInicial = (int) ($datos['existencias'] ?? 0);
            unset($datos['existencias']);

            $producto = Producto::create($datos + [
                'activo' => true,
                'creado_en' => now(),
                ...$this->alcance->atributosDeAlcance($usuario, 'productos'),
            ]);
            $producto->suppliers()->sync($proveedorId !== null ? [$proveedorId] : []);
            $this->inventario->registrarProductoNuevo($producto, $usuario);
            $this->stock->sincronizar($producto, $stockInicial, $usuario, 'Stock inicial registrado desde el formulario de producto.');
            $this->catalogo->sincronizarImagenPortada($producto);
            $this->auditoria->registrar($usuario->id, 'product.created', 'productos', $producto->id, null, $producto->only([
                'marca', 'nombre', 'categoria_id', 'precio', 'existencias', 'url_imagen',
            ]), $ip);

            return $producto;
        }, 3);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  array<string, mixed>  $datosValidados
     * @param  array<string, mixed>  $antes
     */
    public function actualizar(Producto $producto, array $datos, array $datosValidados, array $antes, User $usuario, string $ip): void
    {
        DB::transaction(function () use ($producto, $datos, $datosValidados, $antes, $usuario, $ip): void {
            $stockObjetivo = array_key_exists('existencias', $datosValidados) ? (int) $datosValidados['existencias'] : null;
            unset($datos['existencias']);

            $producto->update($datos);
            if ($stockObjetivo !== null) {
                $this->stock->sincronizar($producto, $stockObjetivo, $usuario, 'Stock actualizado desde el formulario de producto.');
            }
            if (array_key_exists('proveedor_id', $datosValidados)) {
                $producto->suppliers()->sync($datosValidados['proveedor_id'] ? [$datosValidados['proveedor_id']] : []);
            }
            $this->catalogo->sincronizarImagenPortada($producto);
            $this->auditoria->registrar($usuario->id, 'product.updated', 'productos', $producto->id, $antes, $producto->only(array_keys($antes)), $ip);
        }, 3);
    }
}
