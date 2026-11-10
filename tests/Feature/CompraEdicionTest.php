<?php

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Compras\Compra;
use App\Models\Compras\DetalleCompra;
use App\Models\Compras\Proveedor;
use Tests\SpanishAccessFixture;

it('actualiza una compra pendiente sin duplicar su encabezado ni sus detalles', function (): void {
    $actor = SpanishAccessFixture::user('administrador');
    $scope = SpanishAccessFixture::organization();
    $supplier = Proveedor::create(['nombre' => 'Proveedor de edición', 'ruc' => 'RUC-EDICION-01', 'correo' => 'edicion@example.test', 'activo' => true, 'creado_en' => now(), 'empresa_id' => $scope['empresa_id']]);
    $category = Categoria::factory()->create(['empresa_id' => $scope['empresa_id']]);
    $firstProduct = Producto::factory()->create(['empresa_id' => $scope['empresa_id'], 'categoria_id' => $category->id]);
    $secondProduct = Producto::factory()->create(['empresa_id' => $scope['empresa_id'], 'categoria_id' => $category->id]);
    $purchase = Compra::create([
        'proveedor_id' => $supplier->id,
        'creado_por_usuario_id' => $actor->id,
        'empresa_id' => $scope['empresa_id'],
        'sede_id' => $scope['sede_id'],
        'almacen_id' => $scope['almacen_id'],
        'total' => 20,
        'estado' => 'Pendiente',
        'fecha_compra' => '2026-11-01',
        'activo' => true,
        'creado_en' => now(),
    ]);
    DetalleCompra::create(['compra_id' => $purchase->id, 'producto_id' => $firstProduct->id, 'cantidad' => 1, 'costo_unitario' => 20]);

    $this->actingAs($actor)->put(route('management.purchases.update', $purchase), [
        'proveedor_id' => $supplier->id,
        'fecha_compra' => '2026-11-05',
        'moneda' => 'PEN',
        'items' => [[
            'producto_id' => $secondProduct->id,
            'cantidad' => 2,
            'costo_unitario' => '15.00',
            'descuento' => '2.00',
            'impuesto' => '1.00',
        ]],
    ])->assertRedirect();

    expect(Compra::query()->count())->toBe(1)
        ->and($purchase->fresh()->fecha_compra->toDateString())->toBe('2026-11-05')
        ->and((float) $purchase->fresh()->total)->toBe(29.0);
    $this->assertDatabaseHas('detalles_compras', ['compra_id' => $purchase->id, 'producto_id' => $secondProduct->id, 'cantidad' => 2]);
    $this->assertDatabaseMissing('detalles_compras', ['compra_id' => $purchase->id, 'producto_id' => $firstProduct->id]);
    $this->assertDatabaseHas('registros_auditoria', ['accion' => 'compra.actualizada', 'entidad' => 'compras', 'entidad_id' => $purchase->id]);
});
