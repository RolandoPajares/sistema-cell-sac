<?php

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Comunicacion\MensajeContacto;
use App\Models\User;

it('exposes the public modules migrated from the legacy application', function () {
    foreach ([
        'home',
        'pages.about',
        'pages.contact',
        'catalog.index',
        'recommendations',
        'comparison',
        'smart.optimizer',
        'assistant',
    ] as $route) {
        $this->get(route($route))->assertOk();
    }
});

it('stores contact requests through the Laravel service flow', function () {
    $this->post(route('pages.contact.store'), [
        'nombre' => 'Cliente de prueba',
        'contacto' => 'cliente@example.test',
        'mensaje' => '¿Tienen disponibilidad de un equipo?',
    ])->assertRedirect();

    expect(MensajeContacto::query()->count())->toBe(1);
    $this->assertDatabaseHas('mensajes_contacto', [
        'contacto' => 'cliente@example.test',
        'estado' => 'Nuevo',
    ]);
});

it('calculates an optimizer proposal from active catalog stock', function () {
    $category = Categoria::factory()->create(['nombre' => 'Celulares']);
    Producto::factory()->create([
        'categoria_id' => $category->id,
        'categoria' => 'Celulares',
        'precio' => 500,
        'existencias' => 2,
        'nombre' => 'Equipo optimizador',
    ]);

    $this->get(route('smart.optimizer', ['presupuesto' => 500]))
        ->assertOk()
        ->assertSee('Equipo optimizador');
});

it('protects administrative catalog modules by role', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $marketing = User::factory()->create(['rol' => 'marketing']);

    $this->actingAs($admin)->get(route('management.categories'))->assertOk();
    $this->actingAs($marketing)->get(route('management.categories'))->assertForbidden();
});

it('exports authorized report data as an Excel-compatible CSV', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);

    $response = $this->actingAs($admin)->get(route('management.reports.export'));

    $response
        ->assertOk()
        ->assertDownload('reporte-pedidos.csv')
        ->assertStreamed();

    expect($response->streamedContent())->toContain('id,usuario_id,subtotal,descuento,total,estado,creado_en');
});

it('forbids customer roles from exporting internal reports', function () {
    $customer = User::factory()->create(['rol' => 'cliente_minorista']);

    $this->actingAs($customer)
        ->get(route('management.reports.export'))
        ->assertForbidden();
});
