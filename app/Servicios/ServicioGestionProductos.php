<?php

namespace App\Servicios;

use App\Contratos\Productos\AlcanceProductos;
use App\Contratos\Productos\AuditoriaProductos;
use App\Models\Catalogo\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Define la responsabilidad de la clase ServicioGestionProductos dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ServicioGestionProductos
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly AlcanceProductos $alcance,
        private readonly ServicioInventario $inventario,
        private readonly ServicioCatalogo $catalogo,
        private readonly AuditoriaProductos $auditoria,
    ) {}

    /** @param array<string, mixed> $datos */
    public function crear(array $datos, ?int $proveedorId, User $usuario, string $ip): Producto
    {
        return DB::transaction(function () use ($datos, $proveedorId, $usuario, $ip): Producto {
            $producto = Producto::create($datos + [
                'activo' => true,
                'creado_en' => now(),
                ...$this->alcance->atributosDeAlcance($usuario, 'productos'),
            ]);
            $producto->suppliers()->sync($proveedorId !== null ? [$proveedorId] : []);
            $this->inventario->registerNewProduct($producto, $usuario);
            $this->catalogo->sincronizarImagenPortada($producto);
            $this->auditoria->registrar($usuario->id, 'product.created', 'productos', $producto->id, null, $producto->only([
                'marca', 'nombre', 'categoria_id', 'precio', 'existencias', 'url_imagen',
            ]), $ip);

            return $producto;
        }, 3);
    }

    /** @param array<string, mixed> $datos @param array<string, mixed> $datosValidados @param array<string, mixed> $antes */
    public function actualizar(Producto $producto, array $datos, array $datosValidados, array $antes, User $usuario, string $ip): void
    {
        DB::transaction(function () use ($producto, $datos, $datosValidados, $antes, $usuario, $ip): void {
            $producto->update($datos);
            if (array_key_exists('proveedor_id', $datosValidados)) {
                $producto->suppliers()->sync($datosValidados['proveedor_id'] ? [$datosValidados['proveedor_id']] : []);
            }
            $this->catalogo->sincronizarImagenPortada($producto);
            $this->auditoria->registrar($usuario->id, 'product.updated', 'productos', $producto->id, $antes, $producto->only(array_keys($antes)), $ip);
        }, 3);
    }
}
