<?php

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Organizacion\Almacen;
use App\Models\Organizacion\Sede;
use Illuminate\Support\Facades\DB;
use Tests\SpanishAccessFixture;

it('muestra el estado vacío y métricas en cero sin inventar almacenes', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $organizacion = SpanishAccessFixture::organization();

    DB::table('almacenes')->where('id', $organizacion['almacen_id'])->delete();

    $this->actingAs($administrador)
        ->get(route('management.company_settings.warehouses'))
        ->assertOk()
        ->assertViewIs('gestion.inventario-logistica.almacenes.listar')
        ->assertViewHas('almacenes', fn ($almacenes): bool => $almacenes->total() === 0)
        ->assertViewHas('resumen', fn (array $resumen): bool => $resumen['total'] === 0
            && $resumen['activos'] === 0
            && $resumen['unidades_fisicas'] === 0.0
            && $resumen['productos_con_existencias'] === 0)
        ->assertSee('No hay almacenes registrados que coincidan con los filtros.')
        ->assertSee('Nuevo almacén');
});

it('registra, filtra, actualiza y desactiva un almacén real con auditoría', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $organizacion = SpanishAccessFixture::organization();
    $categoria = Categoria::factory()->create();
    $producto = Producto::factory()->create(['categoria_id' => $categoria->id]);

    $this->actingAs($administrador)
        ->post(route('management.company_settings.warehouses.store'), [
            'sede_id' => $organizacion['sede_id'],
            'codigo' => 'ALM-PRUEBA',
            'nombre' => 'Almacén de prueba',
            'tipo' => 'general',
            'permite_venta' => true,
        ])
        ->assertRedirect();

    $almacen = Almacen::query()->where('sede_id', $organizacion['sede_id'])->where('codigo', 'ALM-PRUEBA')->firstOrFail();

    DB::table('existencias_almacen')->insert([
        'almacen_id' => $almacen->id,
        'producto_id' => $producto->id,
        'sede_id' => $organizacion['sede_id'],
        'empresa_id' => $organizacion['empresa_id'],
        'cantidad_fisica' => 4.5,
        'cantidad_reservada' => 1,
    ]);

    $this->get(route('management.company_settings.warehouses', ['buscar' => 'ALM-PRUEBA', 'estado' => 'activo']))
        ->assertOk()
        ->assertViewHas('almacenes', fn ($almacenes): bool => $almacenes->total() === 1
            && (float) $almacenes->getCollection()->sole()->unidades_fisicas === 4.5
            && (int) $almacenes->getCollection()->sole()->productos_con_existencias === 1)
        ->assertViewHas('resumen', fn (array $resumen): bool => $resumen['unidades_fisicas'] === 4.5
            && $resumen['productos_con_existencias'] === 1)
        ->assertSee('Almacén de prueba');

    $this->put(route('management.company_settings.warehouses.update', $almacen), [
        'codigo' => 'ALM-EDITADO',
        'nombre' => 'Almacén actualizado',
        'tipo' => 'despacho',
        'permite_venta' => false,
    ])->assertRedirect();

    $this->assertDatabaseHas('almacenes', [
        'id' => $almacen->id,
        'codigo' => 'ALM-EDITADO',
        'nombre' => 'Almacén actualizado',
        'tipo' => 'despacho',
        'permite_venta' => false,
    ]);

    $this->patch(route('management.company_settings.warehouses.status', $almacen), [
        'activo' => false,
        'motivo' => 'Se reorganiza la operación de almacenamiento.',
    ])->assertRedirect();

    $this->assertDatabaseHas('almacenes', ['id' => $almacen->id, 'activo' => false]);
    expect(DB::table('registros_auditoria')->where('entidad', 'almacenes')->where('entidad_id', $almacen->id)->count())
        ->toBe(3);
});

it('rechaza crear un almacén fuera de la sede seleccionada y deniega a quien no tenga rol administrador', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $organizacion = SpanishAccessFixture::organization();
    $sedeSeleccionada = Sede::query()->create([
        'empresa_id' => $organizacion['empresa_id'],
        'codigo' => 'SEDE-AUTORIZADA',
        'nombre' => 'Sede seleccionada',
        'activa' => true,
    ]);

    $this->actingAs($administrador)
        ->withSession(['sede_actual_id' => $sedeSeleccionada->id])
        ->post(route('management.company_settings.warehouses.store'), [
            'sede_id' => $organizacion['sede_id'],
            'codigo' => 'ALM-FUERA-ALCANCE',
            'nombre' => 'Almacén fuera del alcance',
            'tipo' => 'general',
            'permite_venta' => true,
        ])
        ->assertNotFound();

    $usuarioMarketing = SpanishAccessFixture::user('marketing');

    $this->actingAs($usuarioMarketing)
        ->get(route('management.company_settings.warehouses'))
        ->assertForbidden();

    expect(Almacen::query()->where('codigo', 'ALM-FUERA-ALCANCE')->exists())->toBeFalse();
});

it('valida códigos únicos y motivos de cambio de estado', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $organizacion = SpanishAccessFixture::organization();

    $this->actingAs($administrador)
        ->post(route('management.company_settings.warehouses.store'), [
            'sede_id' => $organizacion['sede_id'],
            'codigo' => 'PRINCIPAL',
            'nombre' => 'Código ya usado',
            'tipo' => 'general',
            'permite_venta' => true,
        ])
        ->assertSessionHasErrors('codigo');

    $this->patch(route('management.company_settings.warehouses.status', $organizacion['almacen_id']), [
        'activo' => false,
        'motivo' => 'corto',
    ])->assertSessionHasErrors('motivo');
});
