<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ServicioGestionProductos
{
    public function __construct(
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioInventario $inventario,
        private readonly ServicioCatalogo $catalogo,
        private readonly RegistradorAuditoria $auditoria,
    ) {}

    /** @param array<string, mixed> $datos */
    public function crear(array $datos, ?int $proveedorId, User $usuario, string $ip): Producto
    {
        return DB::transaction(function () use ($datos, $proveedorId, $usuario, $ip): Producto {
            $producto = Producto::create($datos + [
                'activo' => true,
                'creado_en' => now(),
                ...$this->autorizacion->operationScopeAttributes($usuario, 'productos'),
            ]);
            $producto->suppliers()->sync($proveedorId !== null ? [$proveedorId] : []);
            $this->inventario->registerNewProduct($producto, $usuario);
            $this->catalogo->sincronizarImagenPortada($producto);
            $this->auditoria->record($usuario->id, 'product.created', 'productos', $producto->id, null, $producto->only([
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
            $this->auditoria->record($usuario->id, 'product.updated', 'productos', $producto->id, $antes, $producto->only(array_keys($antes)), $ip);
        }, 3);
    }
}
