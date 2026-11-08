<?php

use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Policies\PedidoPolicy;
use App\Policies\ProductoPolicy;
use Tests\TestCase;

uses(TestCase::class);

/** @param array<string, mixed> $attributes */
function unsavedRbacUser(array $attributes): User
{
    $user = new User;
    $user->setRawAttributes($attributes);

    return $user;
}

function unsavedRbacProduct(int $id = 10): Producto
{
    $product = new Producto;
    $product->setRawAttributes(['id' => $id]);

    return $product;
}

/** @param array<string, mixed> $attributes */
function unsavedRbacOrder(array $attributes): Pedido
{
    $order = new Pedido;
    $order->setRawAttributes($attributes);

    return $order;
}

it('does not authorize an unsaved user from a legacy role attribute', function (): void {
    $user = unsavedRbacUser(['rol' => 'cualquier_codigo']);

    expect($user->can('gestion.seguridad.consultar'))->toBeFalse();
});

it('denies product writes when the RBAC catalog has no assignment', function (): void {
    $user = unsavedRbacUser(['rol' => 'cualquier_codigo']);
    $product = unsavedRbacProduct();
    $policy = new ProductoPolicy;

    expect($policy->update($user, $product))->toBeFalse()
        ->and($policy->delete($user, $product))->toBeFalse();
});

it('denies order access when the RBAC catalog has no assignment', function (): void {
    $owner = unsavedRbacUser(['id' => 1, 'rol' => 'cualquier_codigo']);
    $order = unsavedRbacOrder(['usuario_id' => 1]);
    $order->setRelation('user', $owner);

    expect((new PedidoPolicy)->view($owner, $order))->toBeFalse();
});
