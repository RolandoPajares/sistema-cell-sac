<?php

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Cotizacion;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
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
        'management.quotations' => 'gestion.cotizaciones.administrador',
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

it('filters the administrator quotation dashboard and calculates real status totals', function () {
    $admin = User::factory()->administrator()->create();
    $customer = Cliente::create([
        'tipo' => 'mayorista',
        'documento' => 'RUC-900001',
        'nombre_contacto' => 'Cliente de prueba',
        'correo' => 'cliente.cotizaciones@example.test',
        'activo' => true,
        'creado_en' => now(),
    ]);

    Cotizacion::create([
        'cliente_id' => $customer->id,
        'creado_por' => $admin->id,
        'total' => 320,
        'estado' => 'Borrador',
        'activo' => true,
        'creado_en' => now(),
    ]);
    $approved = Cotizacion::create([
        'cliente_id' => $customer->id,
        'creado_por' => $admin->id,
        'total' => 680,
        'estado' => 'Aprobada',
        'activo' => true,
        'creado_en' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('management.quotations', ['estado' => 'Aprobada']))
        ->assertOk()
        ->assertViewIs('gestion.cotizaciones.administrador')
        ->assertViewHas('totalQuotations', 2)
        ->assertViewHas('pendingQuotations', 1)
        ->assertViewHas('approvedQuotations', 1)
        ->assertViewHas('quotedTotal', 1000.0)
        ->assertViewHas('quotations', fn ($quotations): bool => $quotations->total() === 1
            && $quotations->getCollection()->sole()->is($approved))
        ->assertSee('Gestión de cotizaciones')
        ->assertSee('Nueva cotización')
        ->assertSee('aria-label="Buscar cotización, cliente o producto"', false)
        ->assertSee('aria-label="Cliente mayorista"', false)
        ->assertSee('aria-label="Estado de cotización"', false);
});

it('filters purchases and calculates their dashboard totals', function () {
    $administrator = User::factory()->administrator()->create();
    $firstSupplier = Proveedor::create([
        'nombre' => 'Proveedor principal',
        'ruc' => 'RUC-900101',
        'correo' => 'principal@example.test',
        'activo' => true,
        'creado_en' => now(),
    ]);
    $secondSupplier = Proveedor::create([
        'nombre' => 'Proveedor secundario',
        'ruc' => 'RUC-900102',
        'correo' => 'secundario@example.test',
        'activo' => true,
        'creado_en' => now(),
    ]);
    $category = Categoria::factory()->create();
    $product = Producto::factory()->create(['categoria_id' => $category->id]);
    $received = Compra::create([
        'proveedor_id' => $firstSupplier->id,
        'creado_por' => $administrator->id,
        'total' => 320,
        'estado' => 'Recibida',
        'fecha_compra' => now()->toDateString(),
        'activo' => true,
        'creado_en' => now(),
    ]);
    $received->details()->create([
        'producto_id' => $product->id,
        'cantidad' => 2,
        'costo_unitario' => 160,
    ]);
    $pending = Compra::create([
        'proveedor_id' => $secondSupplier->id,
        'creado_por' => $administrator->id,
        'total' => 180,
        'estado' => 'Pendiente',
        'fecha_compra' => now()->toDateString(),
        'activo' => true,
        'creado_en' => now(),
    ]);
    $pending->details()->create([
        'producto_id' => $product->id,
        'cantidad' => 1,
        'costo_unitario' => 180,
    ]);

    $this->actingAs($administrator)
        ->get(route('management.purchases', [
            'proveedor' => $firstSupplier->id,
            'estado' => 'Recibida',
        ]))
        ->assertOk()
        ->assertViewIs('gestion.compras.listar')
        ->assertViewHas('totalPurchases', 2)
        ->assertViewHas('pendingPurchases', 1)
        ->assertViewHas('receivedPurchases', 1)
        ->assertViewHas('monthlyPurchaseTotal', 500.0)
        ->assertViewHas('monthlyPurchaseCount', 2)
        ->assertViewHas('purchases', fn ($purchases): bool => $purchases->total() === 1
            && $purchases->getCollection()->sole()->is($received))
        ->assertSee('Gestión de compras')
        ->assertSee('Nueva compra')
        ->assertSee('aria-label="Buscar orden, proveedor o producto"', false)
        ->assertSee($product->nombre);
});
