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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

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

it('denies middleware when only an unassigned legacy role is declared', function (): void {
    $middleware = app(AsegurarRol::class);
    $request = Request::create('/gestion', 'GET');
    $request->setUserResolver(fn (): User => unsavedUser(['rol' => 'administrador']));
    $next = fn (Request $request): Response => new Response('ok');

    expect(fn () => $middleware->handle($request, $next, 'administrador'))
        ->toThrow(HttpException::class);
});

it('denies product writes when roles have no RBAC assignments', function (string $role, bool $allowed): void {
    $user = unsavedUser(['rol' => $role]);
    $product = unsavedProduct();
    $policy = new ProductoPolicy;

    expect($policy->update($user, $product))->toBe($allowed)
        ->and($policy->delete($user, $product))->toBe($allowed);
})->with([
    'administrator' => ['administrador', false],
    'marketing' => ['marketing', false],
    'purchasing' => ['compras_logistica', false],
    'wholesale sales' => ['ventas_mayoristas', false],
    'retail sales' => ['ventas_minoristas', false],
    'wholesale customer' => ['cliente_mayorista', false],
    'retail customer' => ['cliente_minorista', false],
]);

it('denies order visibility when legacy roles have no RBAC assignments', function (): void {
    $owner = unsavedUser(['id' => 1, 'rol' => 'cliente_minorista']);
    $retailSales = unsavedUser(['id' => 2, 'rol' => 'ventas_minoristas']);
    $wholesaleCustomer = unsavedUser(['id' => 3, 'rol' => 'cliente_mayorista']);
    $wholesaleSales = unsavedUser(['id' => 4, 'rol' => 'ventas_mayoristas']);
    $order = unsavedOrder(['usuario_id' => 1]);
    $order->setRelation('user', $owner);
    $policy = new PedidoPolicy;

    expect($policy->view($owner, $order))->toBeFalse()
        ->and($policy->view($retailSales, $order))->toBeFalse()
        ->and($policy->view($wholesaleSales, $order))->toBeFalse()
        ->and($policy->view($wholesaleCustomer, $order))->toBeFalse();
});

it('denies order status updates when legacy roles have no RBAC assignments', function (): void {
    $retailCustomer = unsavedUser(['id' => 1, 'rol' => 'cliente_minorista']);
    $administrator = unsavedUser(['id' => 2, 'rol' => 'administrador']);
    $retailSales = unsavedUser(['id' => 3, 'rol' => 'ventas_minoristas']);
    $marketing = unsavedUser(['id' => 4, 'rol' => 'marketing']);
    $order = unsavedOrder(['usuario_id' => 1]);
    $order->setRelation('user', $retailCustomer);
    $policy = new PedidoPolicy;

    expect($policy->updateStatus($administrator, $order))->toBeFalse()
        ->and($policy->updateStatus($retailSales, $order))->toBeFalse()
        ->and($policy->updateStatus($marketing, $order))->toBeFalse()
        ->and($policy->updateStatus($retailCustomer, $order))->toBeFalse();
});
