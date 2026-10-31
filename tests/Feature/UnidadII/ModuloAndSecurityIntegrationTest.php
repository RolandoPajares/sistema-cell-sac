<?php

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Cotizacion;
use App\Models\Comercio\Pedido;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use App\Models\Marketing\Campania;
use App\Models\User;
use App\Servicios\ServicioCompras;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

/**
 * @param  array<string, mixed>  $attributes
 */
function productoParaIntegracionUnidadDos(array $attributes = []): Producto
{
    $category = Categoria::factory()->create();

    return Producto::factory()->create($attributes + [
        'categoria_id' => $category->id,
        'categoria' => $category->nombre,
    ]);
}

it('completes the administrator category lifecycle and records audit events', function (): void {
    $administrator = User::factory()->administrator()->create();

    $this->actingAs($administrator)
        ->post(route('management.categories.store'), [
            'nombre' => 'Accesorios',
            'descripcion' => 'Accesorios para dispositivos.',
        ])
        ->assertRedirect();

    $category = Categoria::query()->where('nombre', 'Accesorios')->firstOrFail();

    $this->assertDatabaseHas('registros_auditoria', [
        'accion' => 'category.created',
        'entidad' => 'categorias',
        'entidad_id' => $category->id,
    ]);

    $this->actingAs($administrator)
        ->put(route('management.categories.update', $category), [
            'nombre' => 'Accesorios premium',
            'descripcion' => 'Accesorios actualizados.',
        ])
        ->assertRedirect();

    $this->actingAs($administrator)
        ->delete(route('management.categories.deactivate', $category))
        ->assertRedirect();

    $this->assertDatabaseHas('categorias', [
        'id' => $category->id,
        'nombre' => 'Accesorios premium',
        'activo' => 0,
    ]);
});

it('completes the administrator client lifecycle', function (): void {
    $administrator = User::factory()->administrator()->create();

    $this->actingAs($administrator)
        ->post(route('management.customers.store'), [
            'tipo' => 'minorista',
            'documento' => 'DNI-10001',
            'razon_social' => null,
            'nombre_contacto' => 'Cliente Integración',
            'correo' => 'cliente.integracion@example.test',
            'telefono' => '999111222',
            'ciudad' => 'Lima',
        ])
        ->assertRedirect();

    $customer = Cliente::query()->where('documento', 'DNI-10001')->firstOrFail();

    $this->actingAs($administrator)
        ->put(route('management.customers.update', $customer), [
            'tipo' => 'mayorista',
            'documento' => 'DNI-10001',
            'razon_social' => 'Empresa Integración',
            'nombre_contacto' => 'Cliente Actualizado',
            'correo' => 'cliente.actualizado@example.test',
            'telefono' => '999333444',
            'ciudad' => 'Arequipa',
        ])
        ->assertRedirect();

    $this->actingAs($administrator)
        ->delete(route('management.customers.deactivate', $customer))
        ->assertRedirect();

    $this->assertDatabaseHas('clientes', [
        'id' => $customer->id,
        'tipo' => 'mayorista',
        'activo' => 0,
    ]);
});

it('completes the administrator product lifecycle', function (): void {
    $administrator = User::factory()->administrator()->create();
    $category = Categoria::factory()->create(['nombre' => 'Telefonía']);

    $this->actingAs($administrator)
        ->post(route('management.products.store'), [
            'marca' => 'Samsung',
            'nombre' => 'Producto Integración',
            'categoria_id' => $category->id,
            'precio' => 700,
            'precio_oferta' => 650,
            'url_imagen' => null,
            'descripcion' => 'Producto de prueba de integración.',
            'existencias' => 5,
        ])
        ->assertRedirect();

    $product = Producto::query()->where('nombre', 'Producto Integración')->firstOrFail();

    $this->actingAs($administrator)
        ->put(route('management.products.update', $product), [
            'marca' => 'Samsung',
            'nombre' => 'Producto Integración actualizado',
            'categoria_id' => $category->id,
            'precio' => 750,
            'precio_oferta' => 700,
            'url_imagen' => null,
            'descripcion' => 'Producto actualizado.',
            'existencias' => 4,
        ])
        ->assertRedirect();

    $this->actingAs($administrator)
        ->delete(route('management.products.deactivate', $product))
        ->assertRedirect();

    $this->assertDatabaseHas('productos', [
        'id' => $product->id,
        'nombre' => 'Producto Integración actualizado',
        'activo' => 0,
    ]);
});

it('creates an administrator-managed wholesale user and customer profile atomically', function (): void {
    $administrator = User::factory()->administrator()->create();

    $this->actingAs($administrator)
        ->post(route('management.users.store'), [
            'nombre' => 'Usuario Mayorista',
            'correo' => 'mayorista.integracion@example.test',
            'rol' => 'cliente_mayorista',
            'password' => 'IntegracionSegura123',
            'documento' => 'RUC-20001',
            'razon_social' => 'Empresa Mayorista',
            'telefono' => '999555666',
            'ciudad' => 'Lima',
        ])
        ->assertRedirect();

    $user = User::query()->where('correo', 'mayorista.integracion@example.test')->firstOrFail();

    expect(Hash::check('IntegracionSegura123', $user->password))->toBeTrue();
    $this->assertDatabaseHas('clientes', [
        'usuario_id' => $user->id,
        'tipo' => 'mayorista',
        'documento' => 'RUC-20001',
    ]);
});

it('completes the supplier lifecycle for purchasing roles', function (): void {
    $purchasing = User::factory()->create(['rol' => 'compras_logistica']);

    $this->actingAs($purchasing)
        ->post(route('management.suppliers.store'), [
            'nombre' => 'Proveedor Integración',
            'ruc' => 'RUC-30001',
            'correo' => 'proveedor.integracion@example.test',
            'telefono' => '999777888',
            'ciudad' => 'Lima',
        ])
        ->assertRedirect();

    $supplier = Proveedor::query()->where('ruc', 'RUC-30001')->firstOrFail();

    $this->actingAs($purchasing)
        ->put(route('management.suppliers.update', $supplier), [
            'nombre' => 'Proveedor Actualizado',
            'ruc' => 'RUC-30001',
            'correo' => 'proveedor.actualizado@example.test',
            'telefono' => '999777999',
            'ciudad' => 'Cusco',
        ])
        ->assertRedirect();

    $this->actingAs($purchasing)
        ->delete(route('management.suppliers.deactivate', $supplier))
        ->assertRedirect();

    $this->assertDatabaseHas('proveedores', [
        'id' => $supplier->id,
        'nombre' => 'Proveedor Actualizado',
        'activo' => 0,
    ]);
});

it('adjusts inventory and rejects a movement that would create negative stock', function (): void {
    $administrator = User::factory()->administrator()->create();
    $product = productoParaIntegracionUnidadDos(['existencias' => 2]);

    $this->actingAs($administrator)
        ->post(route('management.inventory.adjust', $product), [
            'tipo_movimiento' => 'entrada',
            'cantidad' => 3,
            'notas' => 'Entrada de integración.',
        ])
        ->assertRedirect();

    expect((int) $product->fresh()->existencias)->toBe(5);
    $this->assertDatabaseHas('movimientos_inventario', [
        'producto_id' => $product->id,
        'tipo_movimiento' => 'entrada',
        'cantidad' => 3,
    ]);

    $this->actingAs($administrator)
        ->post(route('management.inventory.adjust', $product), [
            'tipo_movimiento' => 'salida',
            'cantidad' => 6,
            'notas' => 'Salida superior al stock.',
        ])
        ->assertUnprocessable();

    expect((int) $product->fresh()->existencias)->toBe(5);
});

it('receives a purchase, creates details and increments inventory', function (): void {
    $purchasing = User::factory()->create(['rol' => 'compras_logistica']);
    $supplier = Proveedor::create([
        'nombre' => 'Proveedor Compra',
        'ruc' => 'RUC-40001',
        'correo' => 'compra@example.test',
        'activo' => true,
        'creado_en' => now(),
    ]);
    $product = productoParaIntegracionUnidadDos(['existencias' => 1, 'precio' => 30]);

    $this->actingAs($purchasing)
        ->post(route('management.purchases.store'), [
            'proveedor_id' => $supplier->id,
            'fecha_compra' => now()->toDateString(),
            'items' => [[
                'producto_id' => $product->id,
                'cantidad' => 2,
                'costo_unitario' => 20,
            ]],
        ])
        ->assertRedirect();

    $purchase = Compra::query()->where('proveedor_id', $supplier->id)->firstOrFail();

    expect((float) $purchase->total)->toBe(40.0)
        ->and((int) $product->fresh()->existencias)->toBe(3);
    $this->assertDatabaseHas('compra_detalles', [
        'compra_id' => $purchase->id,
        'producto_id' => $product->id,
        'cantidad' => 2,
    ]);
    $this->assertDatabaseHas('movimientos_inventario', [
        'producto_id' => $product->id,
        'tipo_movimiento' => 'entrada',
        'cantidad' => 2,
    ]);
});

it('rolls back a purchase when a later detail cannot be found', function (): void {
    $purchasing = User::factory()->create(['rol' => 'compras_logistica']);
    $supplier = Proveedor::create([
        'nombre' => 'Proveedor Rollback',
        'ruc' => 'RUC-40002',
        'correo' => 'rollback@example.test',
        'activo' => true,
        'creado_en' => now(),
    ]);
    $product = productoParaIntegracionUnidadDos(['existencias' => 4]);

    expect(fn (): Compra => app(ServicioCompras::class)->receive([
        'proveedor_id' => $supplier->id,
        'fecha_compra' => now()->toDateString(),
        'items' => [
            ['producto_id' => $product->id, 'cantidad' => 1, 'costo_unitario' => 10],
            ['producto_id' => 999999, 'cantidad' => 1, 'costo_unitario' => 10],
        ],
    ], $purchasing, null))->toThrow(ModelNotFoundException::class);

    expect(Compra::query()->count())->toBe(0)
        ->and((int) $product->fresh()->existencias)->toBe(4);
});

it('cancels an order and restores its inventory', function (): void {
    $administrator = User::factory()->administrator()->create();
    $customer = User::factory()->create(['rol' => 'cliente_minorista']);
    $product = productoParaIntegracionUnidadDos(['existencias' => 0]);
    $order = Pedido::create([
        'usuario_id' => $customer->id,
        'total' => 500,
        'subtotal' => 500,
        'descuento' => 0,
        'estado' => 'Pendiente',
        'creado_en' => now(),
    ]);
    $order->details()->create([
        'producto_id' => $product->id,
        'cantidad' => 1,
        'precio_unitario' => 500,
    ]);

    $this->actingAs($administrator)
        ->patch(route('orders.status', $order), ['estado' => 'Cancelado'])
        ->assertRedirect();

    expect($order->fresh()->estado)->toBe('Cancelado')
        ->and((int) $product->fresh()->existencias)->toBe(1);
    $this->assertDatabaseHas('movimientos_inventario', [
        'producto_id' => $product->id,
        'tipo_movimiento' => 'entrada',
        'cantidad' => 1,
    ]);
});

it('creates a wholesale quotation with details and ownership', function (): void {
    $wholesaleCustomer = User::factory()->create(['rol' => 'cliente_mayorista']);
    $customer = Cliente::create([
        'usuario_id' => $wholesaleCustomer->id,
        'tipo' => 'mayorista',
        'documento' => 'RUC-50001',
        'razon_social' => 'Cliente Cotización',
        'nombre_contacto' => 'Contacto Mayorista',
        'correo' => $wholesaleCustomer->correo,
        'activo' => true,
        'creado_en' => now(),
    ]);
    $product = productoParaIntegracionUnidadDos(['precio' => 100]);

    $this->actingAs($wholesaleCustomer)
        ->post(route('quotations.store'), [
            'cliente_id' => $customer->id,
            'items' => [['producto_id' => $product->id, 'cantidad' => 2]],
            'notas' => 'Cotización de integración.',
        ])
        ->assertRedirect();

    $quotation = Cotizacion::query()->where('cliente_id', $customer->id)->firstOrFail();

    expect((float) $quotation->total)->toBe(200.0);
    $this->assertDatabaseHas('cotizacion_detalles', [
        'cotizacion_id' => $quotation->id,
        'producto_id' => $product->id,
        'cantidad' => 2,
    ]);
});

it('creates campaigns, coupons and customer segments for marketing', function (): void {
    $marketing = User::factory()->create(['rol' => 'marketing']);
    $dates = [
        'inicia_en' => now()->addDay()->toDateTimeString(),
        'finaliza_en' => now()->addDays(5)->toDateTimeString(),
    ];

    $this->actingAs($marketing)
        ->post(route('management.campaigns.store'), [
            'nombre' => 'Campaña Integración',
            'ubicacion' => 'emergente',
            'titulo' => 'Oferta de integración',
            'descripcion' => 'Campaña de prueba.',
            'texto_boton' => 'Ver oferta',
            'url_boton' => 'catalog.index',
            'url_imagen' => null,
            'precio_anterior' => 100,
            'precio_oferta' => 80,
            ...$dates,
        ])
        ->assertRedirect();

    $campaign = Campania::query()->where('nombre', 'Campaña Integración')->firstOrFail();

    $this->actingAs($marketing)
        ->put(route('management.campaigns.update', $campaign), [
            'nombre' => 'Campaña Integración actualizada',
            'ubicacion' => 'lateral',
            'titulo' => 'Oferta actualizada',
            'descripcion' => 'Campaña actualizada.',
            'texto_boton' => 'Comprar',
            'url_boton' => 'catalog.index',
            'url_imagen' => null,
            'precio_anterior' => 120,
            'precio_oferta' => 90,
            ...$dates,
        ])
        ->assertRedirect();

    $this->actingAs($marketing)
        ->delete(route('management.campaigns.deactivate', $campaign))
        ->assertRedirect();

    $this->actingAs($marketing)
        ->post(route('management.coupons.store'), [
            'codigo' => 'marketing10',
            'tipo_descuento' => 'porcentaje',
            'valor' => 10,
            'minimo_compra' => 50,
            'limite_usos' => 5,
            ...$dates,
        ])
        ->assertRedirect();

    $this->actingAs($marketing)
        ->post(route('management.segments.store'), [
            'nombre' => 'Clientes Integración',
            'roles' => ['cliente_minorista', 'cliente_mayorista'],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('campanas_publicitarias', [
        'id' => $campaign->id,
        'nombre' => 'Campaña Integración actualizada',
        'activo' => 0,
    ]);
    $this->assertDatabaseHas('cupones', ['codigo' => 'MARKETING10', 'usos' => 0]);
    $this->assertDatabaseHas('segmentos_clientes', ['nombre' => 'Clientes Integración']);
});

it('supports multiple suppliers for one product through the Eloquent pivot', function (): void {
    $product = productoParaIntegracionUnidadDos();
    $firstSupplier = Proveedor::create([
        'nombre' => 'Proveedor Pivot 1',
        'ruc' => 'RUC-60001',
        'correo' => 'pivot1@example.test',
        'activo' => true,
        'creado_en' => now(),
    ]);
    $secondSupplier = Proveedor::create([
        'nombre' => 'Proveedor Pivot 2',
        'ruc' => 'RUC-60002',
        'correo' => 'pivot2@example.test',
        'activo' => true,
        'creado_en' => now(),
    ]);

    $product->suppliers()->attach([$firstSupplier->id, $secondSupplier->id]);

    expect($product->fresh()->suppliers)->toHaveCount(2)
        ->and($firstSupplier->fresh()->products)->toHaveCount(1)
        ->and($secondSupplier->fresh()->products)->toHaveCount(1);
});

it('enforces the role matrix for protected module entry points', function (): void {
    $administrator = User::factory()->administrator()->create();
    $this->actingAs($administrator)->get(route('management.products'))->assertOk();

    $matrix = [
        'management.products' => ['administrador'],
        'management.customers' => ['administrador'],
        'management.campaigns' => ['administrador', 'marketing'],
        'management.inventory' => ['administrador', 'compras_logistica', 'ventas_minoristas'],
        'management.purchases' => ['administrador', 'compras_logistica'],
        'management.suppliers' => ['administrador', 'compras_logistica'],
        'management.quotations' => ['administrador'],
        'management.segments' => ['administrador', 'marketing'],
        'management.reports' => ['administrador', 'marketing'],
        'quotations.index' => ['administrador', 'ventas_mayoristas', 'cliente_mayorista'],
    ];

    foreach ($matrix as $routeName => $allowedRoles) {
        foreach (User::ROLES as $role) {
            $user = User::factory()->create(['rol' => $role]);
            $response = $this->actingAs($user)->get(route($routeName));

            if (in_array($role, $allowedRoles, true)) {
                $response->assertOk();
            } else {
                $response->assertForbidden();
            }
        }
    }
});

it('rejects unauthorized product writes before persistence', function (): void {
    $marketing = User::factory()->create(['rol' => 'marketing']);
    $category = Categoria::factory()->create();

    $this->actingAs($marketing)
        ->post(route('management.products.store'), [
            'marca' => 'No autorizado',
            'nombre' => 'Producto rechazado',
            'categoria_id' => $category->id,
            'precio' => 100,
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('productos', ['nombre' => 'Producto rechazado']);
});
