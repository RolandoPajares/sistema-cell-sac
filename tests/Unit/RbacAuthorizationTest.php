<?php

use App\Http\Middleware\AsegurarRol;
use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Policies\PedidoPolicy;
use App\Policies\ProductoPolicy;
use App\Soporte\PermisosRoles;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @param  array<string, mixed>  $attributes
 */
function unsavedUser(array $attributes): User
{
    $user = new User;
    $user->setRawAttributes($attributes);

    return $user;
}

function unsavedProduct(int $id = 10): Producto
{
    $product = new Producto;
    $product->setRawAttributes(['id' => $id]);

    return $product;
}

/**
 * @param  array<string, mixed>  $attributes
 */
function unsavedOrder(array $attributes): Pedido
{
    $order = new Pedido;
    $order->setRawAttributes($attributes);

    return $order;
}

it('declares exactly the seven supported roles', function (): void {
    expect(User::ROLES)->toHaveCount(7)
        ->and(User::ROLES)->toEqual([
            'administrador',
            'compras_logistica',
            'ventas_mayoristas',
            'ventas_minoristas',
            'marketing',
            'cliente_mayorista',
            'cliente_minorista',
        ]);
});

it('keeps navigation visibility aligned with role modules', function (): void {
    $administrator = unsavedUser(['rol' => 'administrador']);
    $marketing = unsavedUser(['rol' => 'marketing']);
    $retailCustomer = unsavedUser(['rol' => 'cliente_minorista']);

    expect(PermisosRoles::canView($administrator, 'any-module'))->toBeTrue()
        ->and(PermisosRoles::canView($marketing, 'campanias'))->toBeTrue()
        ->and(PermisosRoles::canView($marketing, 'compras'))->toBeFalse()
        ->and(PermisosRoles::canView($retailCustomer, 'pedidos'))->toBeTrue()
        ->and(PermisosRoles::canView($retailCustomer, 'usuarios'))->toBeFalse();
});

it('allows middleware only when the authenticated role is declared', function (): void {
    $middleware = new AsegurarRol;
    $request = Request::create('/gestion', 'GET');
    $request->setUserResolver(fn (): User => unsavedUser(['rol' => 'administrador']));
    $next = fn (Request $request): Response => new Response('ok');

    $response = $middleware->handle($request, $next, 'administrador');

    expect($response->getStatusCode())->toBe(200);
});

it('allows product writes only to administrators', function (string $role, bool $allowed): void {
    $user = unsavedUser(['rol' => $role]);
    $product = unsavedProduct();
    $policy = new ProductoPolicy;

    expect($policy->update($user, $product))->toBe($allowed)
        ->and($policy->delete($user, $product))->toBe($allowed);
})->with([
    'administrator' => ['administrador', true],
    'marketing' => ['marketing', false],
    'purchasing' => ['compras_logistica', false],
    'wholesale sales' => ['ventas_mayoristas', false],
    'retail sales' => ['ventas_minoristas', false],
    'wholesale customer' => ['cliente_mayorista', false],
    'retail customer' => ['cliente_minorista', false],
]);

it('limits order visibility to owners and their authorized sales role', function (): void {
    $owner = unsavedUser(['id' => 1, 'rol' => 'cliente_minorista']);
    $retailSales = unsavedUser(['id' => 2, 'rol' => 'ventas_minoristas']);
    $wholesaleCustomer = unsavedUser(['id' => 3, 'rol' => 'cliente_mayorista']);
    $wholesaleSales = unsavedUser(['id' => 4, 'rol' => 'ventas_mayoristas']);
    $order = unsavedOrder(['usuario_id' => 1]);
    $order->setRelation('user', $owner);
    $policy = new PedidoPolicy;

    expect($policy->view($owner, $order))->toBeTrue()
        ->and($policy->view($retailSales, $order))->toBeTrue()
        ->and($policy->view($wholesaleSales, $order))->toBeFalse()
        ->and($policy->view($wholesaleCustomer, $order))->toBeFalse();
});

it('limits order status updates to administrators and matching sales roles', function (): void {
    $retailCustomer = unsavedUser(['id' => 1, 'rol' => 'cliente_minorista']);
    $administrator = unsavedUser(['id' => 2, 'rol' => 'administrador']);
    $retailSales = unsavedUser(['id' => 3, 'rol' => 'ventas_minoristas']);
    $marketing = unsavedUser(['id' => 4, 'rol' => 'marketing']);
    $order = unsavedOrder(['usuario_id' => 1]);
    $order->setRelation('user', $retailCustomer);
    $policy = new PedidoPolicy;

    expect($policy->updateStatus($administrator, $order))->toBeTrue()
        ->and($policy->updateStatus($retailSales, $order))->toBeTrue()
        ->and($policy->updateStatus($marketing, $order))->toBeFalse()
        ->and($policy->updateStatus($retailCustomer, $order))->toBeFalse();
});
