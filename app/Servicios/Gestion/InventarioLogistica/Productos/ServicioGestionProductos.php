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
        // Crea el producto y registra sus datos iniciales dentro de una transaccion.
        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        // ORM: este servicio aplica ORM para consultar o modificar datos en la base de datos.
        return DB::transaction(function () use ($datos, $proveedorId, $usuario, $ip): Producto {
            // Guarda en $stockInicial el resultado necesario para continuar la operacion.
            $stockInicial = (int) ($datos['existencias'] ?? 0);
            unset($datos['existencias']);

            // Crea y guarda en $producto el registro gestionado por este servicio.
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
        // Actualiza el producto, sincroniza stock o proveedor y registra los cambios.
        // Ejecuta los cambios dentro de una transaccion para evitar datos incompletos.
        // Mantiene atomicas la actualizacion y la auditoria del producto.
        DB::transaction(function () use ($producto, $datos, $datosValidados, $antes, $usuario, $ip): void {
            // Guarda en $stockObjetivo el resultado necesario para continuar la operacion.
            $stockObjetivo = array_key_exists('existencias', $datosValidados) ? (int) $datosValidados['existencias'] : null;
            unset($datos['existencias']);

            $producto->update($datos);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($stockObjetivo !== null) {
                $this->stock->sincronizar($producto, $stockObjetivo, $usuario, 'Stock actualizado desde el formulario de producto.');
            }
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (array_key_exists('proveedor_id', $datosValidados)) {
                $producto->suppliers()->sync($datosValidados['proveedor_id'] ? [$datosValidados['proveedor_id']] : []);
            }
            $this->catalogo->sincronizarImagenPortada($producto);
            $this->auditoria->registrar($usuario->id, 'product.updated', 'productos', $producto->id, $antes, $producto->only(array_keys($antes)), $ip);
        }, 3);
    }
}
