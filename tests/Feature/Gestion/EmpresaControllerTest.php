<?php

use Illuminate\Support\Facades\DB;
use Tests\SpanishAccessFixture;

it('consulta la ficha empresarial y sus relaciones reales', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $organizacion = SpanishAccessFixture::organization();

    $this->actingAs($administrador)
        ->get(route('management.company_settings.data'))
        ->assertOk()
        ->assertViewIs('gestion.administracion.organizacion.datos-empresa')
        ->assertViewHas('empresa', fn ($empresa): bool => (int) $empresa->id === $organizacion['empresa_id']
            && $empresa->razon_social === 'Empresa sintética de pruebas'
            && $empresa->sedes_count === 1
            && $empresa->sedes_activas_count === 1)
        ->assertViewHas('almacenes', 1)
        ->assertSee('Empresa sintética de pruebas')
        ->assertDontSee('20604567891');
});

it('actualiza los campos existentes de la empresa y registra auditoría', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $organizacion = SpanishAccessFixture::organization();

    $this->actingAs($administrador)
        ->put(route('management.company_settings.data.update'), [
            'razon_social' => 'CELL SAC Actualizada',
            'nombre_comercial' => 'CELL SAC',
            'ruc' => '20123456789',
            'moneda_base' => 'PEN',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('empresas', [
        'id' => $organizacion['empresa_id'],
        'razon_social' => 'CELL SAC Actualizada',
        'nombre_comercial' => 'CELL SAC',
        'ruc' => '20123456789',
        'moneda_base' => 'PEN',
    ]);

    expect(DB::table('registros_auditoria')
        ->where('entidad', 'empresas')
        ->where('entidad_id', $organizacion['empresa_id'])
        ->where('accion', 'empresa.actualizada')
        ->count())->toBe(1);
});

it('rechaza campos fuera del esquema y bloquea a usuarios sin autorización', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');

    $this->actingAs($administrador)
        ->put(route('management.company_settings.data.update'), [
            'razon_social' => 'CELL SAC',
            'nombre_comercial' => 'CELL SAC',
            'ruc' => '123',
            'moneda_base' => 'PEN',
            'codigo' => 'CAMBIO-NO-PERMITIDO',
        ])
        ->assertSessionHasErrors(['ruc', 'codigo']);

    $marketing = SpanishAccessFixture::user('marketing');

    $this->actingAs($marketing)
        ->get(route('management.company_settings.data'))
        ->assertForbidden();
});
