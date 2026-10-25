<?php

use App\Models\User;

it('renders the role dashboard from the shared account entry point', function (string $role, string $view): void {
    $user = User::factory()->create(['rol' => $role]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewIs($view)
        ->assertViewHas('role', $role);
})->with([
    ['marketing', 'paneles.marketing.dashboard'],
    ['compras_logistica', 'paneles.compras-logistica.dashboard'],
    ['ventas_mayoristas', 'paneles.ventas-mayoristas.dashboard'],
    ['ventas_minoristas', 'paneles.ventas-minoristas.dashboard'],
    ['cliente_mayorista', 'paneles.cliente-mayorista.dashboard'],
    ['cliente_minorista', 'paneles.cliente-minorista.dashboard'],
]);

it('renders the administrator dashboard through both existing admin entry points', function (): void {
    $user = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($user)
        ->get(route('management.dashboard'))
        ->assertOk()
        ->assertViewIs('paneles.administrador.dashboard')
        ->assertViewHas('products', 0);

    $this->actingAs($user)
        ->get(route('legacy.admin.dashboard'))
        ->assertOk()
        ->assertViewIs('paneles.administrador.dashboard');
});

it('keeps the administrator account configuration on the account route', function (): void {
    $user = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewIs('paneles.administrador.perfil.editar');
});

it('forbids non administrators from the administrator dashboard route', function (): void {
    $user = User::factory()->create(['rol' => 'marketing']);

    $this->actingAs($user)
        ->get(route('management.dashboard'))
        ->assertForbidden();
});
