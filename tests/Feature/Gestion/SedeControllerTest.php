<?php

use App\Models\Organizacion\Sede;
use Illuminate\Support\Facades\DB;
use Tests\SpanishAccessFixture;

it('muestra el estado vacío de sedes con indicadores reales y permite iniciar un registro', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $organizacion = SpanishAccessFixture::organization();

    DB::table('almacenes')->where('id', $organizacion['almacen_id'])->delete();
    DB::table('sedes')->where('id', $organizacion['sede_id'])->delete();

    $this->actingAs($administrador)
        ->get(route('management.company_settings.sites'))
        ->assertOk()
        ->assertViewIs('gestion.organizacion.sedes.listar')
        ->assertViewHas('sedes', fn ($sedes): bool => $sedes->total() === 0)
        ->assertViewHas('resumen', fn (array $resumen): bool => $resumen['total'] === 0
            && $resumen['activas'] === 0
            && $resumen['inactivas'] === 0)
        ->assertViewHas('totalAlmacenes', 0)
        ->assertSee('No hay sedes registradas que coincidan con los filtros.')
        ->assertSee('Nueva sede');
});

it('registra, filtra, edita y desactiva sedes reales con eventos de auditoría', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $organizacion = SpanishAccessFixture::organization();

    $this->actingAs($administrador)
        ->post(route('management.company_settings.sites.store'), [
            'codigo' => 'SEDE-TEST',
            'nombre' => 'Sede de prueba',
            'direccion' => 'Av. de prueba 123',
            'telefono' => '011234567',
            'zona_horaria' => 'America/Lima',
        ])
        ->assertRedirect();

    $sede = Sede::query()->where('empresa_id', $organizacion['empresa_id'])->where('codigo', 'SEDE-TEST')->firstOrFail();

    $this->get(route('management.company_settings.sites', ['buscar' => 'SEDE-TEST', 'estado' => 'activa']))
        ->assertOk()
        ->assertViewHas('sedes', fn ($sedes): bool => $sedes->total() === 1
            && $sedes->getCollection()->sole()->is($sede)
            && (int) $sedes->getCollection()->sole()->almacenes_count === 0)
        ->assertSee('Sede de prueba');

    $this->put(route('management.company_settings.sites.update', $sede), [
        'codigo' => 'SEDE-EDITADA',
        'nombre' => 'Sede actualizada',
        'direccion' => 'Calle actualizada 456',
        'telefono' => '019876543',
        'zona_horaria' => 'America/Bogota',
    ])->assertRedirect();

    $this->assertDatabaseHas('sedes', [
        'id' => $sede->id,
        'empresa_id' => $organizacion['empresa_id'],
        'codigo' => 'SEDE-EDITADA',
        'nombre' => 'Sede actualizada',
        'direccion' => 'Calle actualizada 456',
        'zona_horaria' => 'America/Bogota',
    ]);

    $this->patch(route('management.company_settings.sites.status', $sede), [
        'activa' => false,
        'motivo' => 'La sede se cierra temporalmente.',
    ])->assertRedirect();

    $this->assertDatabaseHas('sedes', ['id' => $sede->id, 'activa' => false]);
    expect(DB::table('registros_auditoria')->where('entidad', 'sedes')->where('entidad_id', $sede->id)->count())->toBe(3);
});

it('valida unicidad y cambios de estado y deniega a usuarios sin rol administrador', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $organizacion = SpanishAccessFixture::organization();

    $this->actingAs($administrador)
        ->post(route('management.company_settings.sites.store'), [
            'codigo' => 'PRINCIPAL',
            'nombre' => 'Código duplicado',
            'zona_horaria' => 'America/Lima',
        ])
        ->assertSessionHasErrors('codigo');

    $this->patch(route('management.company_settings.sites.status', $organizacion['sede_id']), [
        'activa' => false,
        'motivo' => 'corto',
    ])->assertSessionHasErrors('motivo');

    $marketing = SpanishAccessFixture::user('marketing');

    $this->actingAs($marketing)
        ->get(route('management.company_settings.sites'))
        ->assertForbidden();
});
