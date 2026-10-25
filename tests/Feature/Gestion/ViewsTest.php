<?php

use App\Models\User;

it('renders each management module with its canonical list view', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $viewsByRoute = [
        'management.audit' => 'gestion.auditoria.listar',
        'management.campaigns' => 'gestion.campanias.listar',
        'management.categories' => 'gestion.categorias.listar',
        'management.customers' => 'gestion.clientes.listar',
        'management.purchases' => 'gestion.compras.listar',
        'quotations.index' => 'gestion.cotizaciones.listar',
        'management.coupons' => 'gestion.cupones.listar',
        'management.inventory' => 'gestion.inventario.listar',
        'management.products' => 'gestion.productos.listar',
        'management.suppliers' => 'gestion.proveedores.listar',
        'management.reports' => 'gestion.reportes.listar',
        'management.segments' => 'gestion.segmentos.listar',
        'management.users' => 'gestion.usuarios.listar',
    ];

    foreach ($viewsByRoute as $routeName => $viewName) {
        $this->actingAs($admin)
            ->get(route($routeName))
            ->assertOk()
            ->assertViewIs($viewName);
    }
});
