<?php

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Comercio\Cupon;
use App\Models\Comercio\Pedido;
use App\Models\User;

function productForCellSac(array $attributes = []): Producto
{
    $category = Categoria::factory()->create();

    return Producto::factory()->create($attributes + [
        'categoria_id' => $category->id,
        'categoria' => $category->nombre,
        'nombre' => 'Galaxy test',
    ]);
}

it('registers public accounts only as retail customers', function () {
    $response = $this->post(route('register.store'), [
        'nombre' => 'Cliente Prueba',
        'correo' => 'cliente@example.test',
        'password' => 'PruebaSegura123',
        'password_confirmation' => 'PruebaSegura123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('users', ['correo' => 'cliente@example.test', 'rol' => 'cliente_minorista']);
});

it('filters the active Eloquent catalog and hides inactive products', function () {
    $active = productForCellSac();
    productForCellSac(['nombre' => 'Producto oculto', 'activo' => false]);

    $this->get(route('catalog.index', ['q' => 'Galaxy']))
        ->assertOk()
        ->assertSee('Galaxy test')
        ->assertDontSee('Producto oculto');

    $this->get(route('catalog.show', $active))->assertOk()->assertSee('Galaxy test');
});

it('creates a pending order and decrements stock atomically at checkout', function () {
    $user = User::factory()->create();
    $product = productForCellSac(['precio' => 500, 'existencias' => 4]);

    $this->actingAs($user)->post(route('cart.add', $product), ['cantidad' => 2])->assertRedirect();
    $this->actingAs($user)->post(route('checkout'))->assertRedirect();

    $this->assertDatabaseHas('pedidos', ['usuario_id' => $user->id, 'total' => 1000]);
    expect((int) $product->fresh()->existencias)->toBe(2)
        ->and(Pedido::count())->toBe(1);
});

it('applies a valid coupon once and records the discounted total', function () {
    $user = User::factory()->create();
    $product = productForCellSac(['precio' => 100, 'existencias' => 3]);
    $coupon = Cupon::create([
        'codigo' => 'SALE10',
        'tipo_descuento' => 'porcentaje',
        'valor' => 10,
        'minimo_compra' => 0,
        'limite_usos' => 1,
        'usos' => 0,
        'inicia_en' => now()->subHour(),
        'finaliza_en' => now()->addDay(),
        'activo' => true,
    ]);

    $this->actingAs($user)->withSession(['cart' => [$product->id => 1], 'coupon_code' => $coupon->codigo])
        ->post(route('checkout'))
        ->assertRedirect();

    $this->assertDatabaseHas('pedidos', ['subtotal' => 100, 'descuento' => 10, 'total' => 90]);
    expect((int) $coupon->fresh()->usos)->toBe(1);
});

it('forbids a customer from viewing another user order', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $product = productForCellSac();
    $order = Pedido::create(['usuario_id' => $owner->id, 'total' => 500, 'subtotal' => 500, 'descuento' => 0, 'estado' => 'Pendiente', 'creado_en' => now()]);
    $order->details()->create(['producto_id' => $product->id, 'cantidad' => 1, 'precio_unitario' => 500]);

    $this->actingAs($other)->get(route('orders.show', $order))->assertForbidden();
});

it('blocks non-admin roles from product administration', function () {
    $user = User::factory()->create(['rol' => 'marketing']);

    $this->actingAs($user)->get(route('management.products'))->assertForbidden();
});

it('does not disclose private order summaries to public assistant visitors', function () {
    $user = User::factory()->create(['rol' => 'cliente_minorista']);
    Pedido::create([
        'usuario_id' => $user->id,
        'total' => 500,
        'subtotal' => 500,
        'descuento' => 0,
        'estado' => 'Pendiente',
        'creado_en' => now(),
    ]);

    $response = $this->postJson(route('assistant.ask'), ['consulta' => '¿Cuántos pedidos pendientes hay?']);

    $response
        ->assertOk()
        ->assertJsonPath('respuesta.mensaje', 'Inicia sesion para consultar tus pedidos.')
        ->assertJsonMissing(['mensaje' => 'Hay 1 pedidos pendientes en el alcance de tu cuenta.']);
});

it('limits customer assistant order summaries to the authenticated customer', function () {
    $owner = User::factory()->create(['rol' => 'cliente_minorista']);
    $other = User::factory()->create(['rol' => 'cliente_minorista']);
    Pedido::create([
        'usuario_id' => $owner->id,
        'total' => 500,
        'subtotal' => 500,
        'descuento' => 0,
        'estado' => 'Pendiente',
        'creado_en' => now(),
    ]);
    Pedido::create([
        'usuario_id' => $other->id,
        'total' => 700,
        'subtotal' => 700,
        'descuento' => 0,
        'estado' => 'Pendiente',
        'creado_en' => now(),
    ]);

    $response = $this->actingAs($owner)->postJson(route('assistant.ask'), ['consulta' => 'pedidos pendientes']);

    $response
        ->assertOk()
        ->assertJsonPath('respuesta.mensaje', 'Hay 1 pedidos pendientes en el alcance de tu cuenta.');
});

it('preserves the seven supported roles', function () {
    expect(User::ROLES)->toHaveCount(7);
    foreach (['administrador', 'compras_logistica', 'ventas_mayoristas', 'ventas_minoristas', 'marketing', 'cliente_mayorista', 'cliente_minorista'] as $role) {
        expect(User::ROLES)->toContain($role);
    }
});
