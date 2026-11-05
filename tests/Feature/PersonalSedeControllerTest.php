<?php

use Illuminate\Support\Facades\DB;
use Tests\SpanishAccessFixture;

it('muestra un estado vacío real cuando no hay personal asignado a sedes', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');

    $this->actingAs($administrador)
        ->get(route('management.company_settings.staff'))
        ->assertOk()
        ->assertViewIs('paneles.administrador.organizacion.personal.listar')
        ->assertViewHas('asignaciones', fn ($asignaciones): bool => $asignaciones->total() === 0)
        ->assertSee('No hay asignaciones de personal que coincidan con los filtros')
        ->assertSee('Usuarios asignados')
        ->assertDontSee('Carlos Mendoza')
        ->assertDontSee('Vacantes abiertas');

    expect(DB::table('usuarios_sedes_roles')->count())->toBe(0);
});

it('consulta y filtra asignaciones vigentes reales y deniega el acceso a otros roles', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $operador = SpanishAccessFixture::user('marketing');
    $sedeId = (int) DB::table('usuarios_sedes_roles')->where('usuario_id', $operador->getKey())->value('sede_id');
    $nombreSede = (string) DB::table('sedes')->where('id', $sedeId)->value('nombre');

    $this->actingAs($administrador)
        ->get(route('management.company_settings.staff', [
            'buscar' => $operador->nombre,
            'sede_id' => $sedeId,
            'estado' => 'vigente',
        ]))
        ->assertOk()
        ->assertViewHas('asignaciones', fn ($asignaciones): bool => $asignaciones->total() === 1
            && $asignaciones->getCollection()->sole()->usuario_correo === $operador->correo
            && $asignaciones->getCollection()->sole()->vigente)
        ->assertSee($operador->nombre)
        ->assertSee($operador->correo)
        ->assertSee('Marketing')
        ->assertSee($nombreSede)
        ->assertSee('Vigente')
        ->assertDontSee('Lucía Ramírez');

    $this->actingAs($operador)
        ->get(route('management.company_settings.staff'))
        ->assertForbidden();
});

it('requiere el permiso RBAC específico de pantalla y deniega responsabilidades sin contrato', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $rolAdministradorId = (int) DB::table('roles')->where('codigo', 'administrador')->value('id');
    $permisoPersonalId = (int) DB::table('permisos')->where('codigo', 'pantalla.admin.staff.consultar')->value('id');
    DB::table('roles_permisos')->where('rol_id', $rolAdministradorId)->where('permiso_id', $permisoPersonalId)->delete();

    $this->actingAs($administrador)
        ->get(route('management.company_settings.staff'))
        ->assertForbidden();

    $this->actingAs($administrador)
        ->get(route('management.company_settings.responsibles'))
        ->assertForbidden();
});
