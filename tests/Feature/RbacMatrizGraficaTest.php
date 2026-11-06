<?php

use Illuminate\Support\Facades\DB;
use Tests\SpanishAccessFixture;

it('muestra la matriz real con los siete roles y acciones registradas', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');

    $respuesta = $this->withoutVite()->actingAs($administrador)->get(route('management.permissions_matrix'));

    $respuesta->assertOk()
        ->assertSee('Matriz de privilegios')
        ->assertSee('Aplicar plantilla recomendada')
        ->assertSee('Vista previa del menú')
        ->assertSee('Descartar cambios')
        ->assertSee('Guardar cambios');

    foreach (['Administrador', 'Compras y logística', 'Ventas mayoristas', 'Ventas minoristas', 'Marketing', 'Cliente mayorista', 'Cliente minorista'] as $nombreRole) {
        $respuesta->assertSee($nombreRole);
    }

    expect($respuesta->getContent())
        ->toContain('data-template-permissions=')
        ->toContain('data-preview-items=')
        ->toContain('>Crear<')
        ->toContain('>Aprobar<');
});

it('propone plantillas ecommerce sin conceder permisos administrativos a clientes', function (): void {
    SpanishAccessFixture::user('administrador');
    $wholesaleRoleId = DB::table('roles')->where('codigo', 'cliente_mayorista')->value('id');
    $retailRoleId = DB::table('roles')->where('codigo', 'cliente_minorista')->value('id');
    $createOrderPermissionId = DB::table('permisos')->where('codigo', 'gestion.pedidos.crear')->value('id');
    $wholesalePermissions = DB::table('roles_permisos')->where('rol_id', $wholesaleRoleId)->pluck('permiso_id')->all();
    $retailPermissions = DB::table('roles_permisos')->where('rol_id', $retailRoleId)->pluck('permiso_id')->all();

    expect($wholesalePermissions)->not->toContain($createOrderPermissionId)
        ->and($retailPermissions)->not->toContain($createOrderPermissionId);
});

it('guarda la selección de la matriz en una transacción auditada', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $marketingRoleId = (int) DB::table('roles')->where('codigo', 'marketing')->value('id');
    $permissionIds = DB::table('permisos')->whereIn('codigo', [
        'gestion.campanias.consultar',
        'gestion.campanias.crear',
    ])->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();

    $this->actingAs($administrador)
        ->put(route('management.roles_permissions.permissions', $marketingRoleId), [
            'alcance' => 'sede',
            'permisos' => $permissionIds,
        ])
        ->assertRedirect();

    expect(DB::table('roles_permisos')->where('rol_id', $marketingRoleId)->pluck('permiso_id')->map(static fn (mixed $id): int => (int) $id)->all())
        ->toEqualCanonicalizing($permissionIds)
        ->and(DB::table('registros_auditoria')->where('accion', 'actualizar_permisos_rol')->where('entidad_id', $marketingRoleId)->exists())
        ->toBeTrue();
});

it('protege la matriz frente a usuarios no autorizados y al rol administrador', function (): void {
    $marketing = SpanishAccessFixture::user('marketing');
    $administrador = SpanishAccessFixture::user('administrador');
    $adminRoleId = (int) DB::table('roles')->where('codigo', 'administrador')->value('id');

    $this->actingAs($marketing)
        ->get(route('management.permissions_matrix'))
        ->assertForbidden();

    $this->actingAs($administrador)
        ->put(route('management.roles_permissions.permissions', $adminRoleId), [
            'alcance' => 'empresa',
            'permisos' => [],
        ])
        ->assertUnprocessable();
});

it('impide conceder el permiso administrativo de pedidos a los roles ecommerce', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $retailRoleId = (int) DB::table('roles')->where('codigo', 'cliente_minorista')->value('id');
    $createOrderPermissionId = (int) DB::table('permisos')->where('codigo', 'gestion.pedidos.crear')->value('id');

    $this->actingAs($administrador)
        ->put(route('management.roles_permissions.permissions', $retailRoleId), [
            'alcance' => 'propio',
            'permisos' => [$createOrderPermissionId],
        ])
        ->assertUnprocessable();
});

it('preserva seguridad y delegacion mientras permite administrar privilegios empresariales', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $marketingRoleId = (int) DB::table('roles')->where('codigo', 'marketing')->value('id');
    $securityPermissionId = (int) DB::table('permisos')->where('codigo', 'gestion.seguridad.consultar')->value('id');
    $campaignPermissionId = (int) DB::table('permisos')->where('codigo', 'gestion.campanias.consultar')->value('id');
    $otherSecurityPermissionId = (int) DB::table('permisos')->where('codigo', 'gestion.seguridad.actualizar')->value('id');

    DB::table('roles_permisos')->insert([
        'rol_id' => $marketingRoleId,
        'permiso_id' => $securityPermissionId,
        'alcance' => 'sede',
        'creado_en' => now(),
    ]);

    $this->actingAs($administrador)
        ->put(route('management.roles_permissions.permissions', $marketingRoleId), [
            'alcance' => 'sede',
            'permisos' => [$campaignPermissionId],
        ])
        ->assertRedirect();

    expect(DB::table('roles_permisos')->where('rol_id', $marketingRoleId)->where('permiso_id', $securityPermissionId)->exists())->toBeTrue()
        ->and(DB::table('roles_permisos')->where('rol_id', $marketingRoleId)->where('permiso_id', $campaignPermissionId)->exists())->toBeTrue();

    $this->actingAs($administrador)
        ->put(route('management.roles_permissions.permissions', $marketingRoleId), [
            'alcance' => 'sede',
            'permisos' => [$otherSecurityPermissionId],
        ])
        ->assertUnprocessable();
});
