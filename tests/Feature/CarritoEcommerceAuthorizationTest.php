<?php

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use Illuminate\Support\Facades\DB;
use Tests\SpanishAccessFixture;

/**
 * Crea un producto activo con saldo disponible en el almacén de pruebas.
 */
function productoParaCarritoEcommerce(array $attributes = []): Producto
{
    $scope = SpanishAccessFixture::organization();
    $category = Categoria::factory()->create(['empresa_id' => $scope['empresa_id']]);
    $product = Producto::factory()->create($attributes + [
        'categoria_id' => $category->id,
        'categoria' => $category->nombre,
        'empresa_id' => $scope['empresa_id'],
        'activo' => true,
    ]);

    DB::table('existencias_almacen')->insert([
        'almacen_id' => $scope['almacen_id'],
        'producto_id' => $product->id,
        'empresa_id' => $scope['empresa_id'],
        'sede_id' => $scope['sede_id'],
        'cantidad_fisica' => $attributes['existencias'] ?? 4,
        'cantidad_reservada' => 0,
        'actualizado_en' => now(),
    ]);

    return $product;
}

it('permite agregar productos a clientes mayoristas y minoristas', function (): void {
    foreach (['cliente_mayorista', 'cliente_minorista'] as $role) {
        $customer = SpanishAccessFixture::user($role);
        $product = productoParaCarritoEcommerce();

        $this->actingAs($customer)
            ->post(route('cart.add', $product), ['cantidad' => 1])
            ->assertRedirect();

        expect(session('cart.'.$product->id.'.cantidad'))->toBe(1);
    }
});

it('mantiene el carrito aislado entre usuarios y permite modificarlo', function (): void {
    $owner = SpanishAccessFixture::user('cliente_minorista');
    $other = SpanishAccessFixture::user('cliente_minorista');
    $product = productoParaCarritoEcommerce();

    $this->actingAs($owner)
        ->post(route('cart.add', $product), ['cantidad' => 1])
        ->assertRedirect();
    $ownerCart = session('cart');

    $this->withSession(['cart' => []])->actingAs($other)
        ->get(route('cart.index'))
        ->assertOk()
        ->assertDontSee($product->nombre);

    $this->withSession(['cart' => $ownerCart])->actingAs($owner)
        ->patch(route('cart.update', $product), ['cantidad' => 2])
        ->assertRedirect();

    expect(session('cart.'.$product->id.'.cantidad'))->toBe(2);

    $this->post(route('cart.remove', $product))->assertRedirect();
    expect(session('cart'))->toBe([]);
});

it('permite carrito invitado y rechaza operadores sin permiso', function (): void {
    $product = productoParaCarritoEcommerce();

    $this->post(route('cart.add', $product), ['cantidad' => 1])->assertRedirect();

    $employee = SpanishAccessFixture::user('marketing');
    $this->actingAs($employee)
        ->postJson(route('cart.add', $product), ['cantidad' => 1])
        ->assertForbidden();
});

it('returns 422 for insufficient stock without writing the session', function (): void {
    $customer = SpanishAccessFixture::user('cliente_minorista');
    $product = productoParaCarritoEcommerce(['existencias' => 0]);

    $this->actingAs($customer)
        ->postJson(route('cart.add', $product), ['cantidad' => 1])
        ->assertStatus(422);

    expect(session('cart'))->toBeNull();
});

it('confirms an order for an authorized retail customer', function (): void {
    $customer = SpanishAccessFixture::user('cliente_minorista');
    $product = productoParaCarritoEcommerce();
    $token = bin2hex(random_bytes(32));

    $this->actingAs($customer)
        ->post(route('cart.add', $product), ['cantidad' => 1])
        ->assertRedirect();

    $this->withSession(['checkout_token' => $token])
        ->post(route('checkout'), ['checkout_token' => $token])
        ->assertRedirect();

    expect(Pedido::query()->where('usuario_id', $customer->id)->exists())->toBeTrue();
});

it('completes a guest checkout and exposes only tokenized tracking', function (): void {
    $product = productoParaCarritoEcommerce(['precio' => 120]);

    $this->post(route('cart.add', $product), ['cantidad' => 1])->assertRedirect();
    $token = bin2hex(random_bytes(32));
    $response = $this->withSession(['checkout_token' => $token])
        ->post(route('checkout'), [
            'checkout_token' => $token,
            'nombre' => 'Comprador Invitado',
            'correo' => 'invitado@example.test',
            'telefono' => '999999999',
            'direccion' => 'Av. Principal 123',
        ]);

    $response->assertRedirect();
    $order = Pedido::query()->whereNull('usuario_id')->latest('id')->firstOrFail();
    $this->get(route('orders.tracking', [$order, 'token' => $token]))->assertOk()->assertSee('Comprador Invitado');
    $this->get(route('orders.tracking', [$order, 'token' => str_repeat('a', 64)]))->assertNotFound();
});
