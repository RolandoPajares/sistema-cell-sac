<?php

use Illuminate\Support\Facades\DB;
use Tests\SpanishAccessFixture;

it('muestra la matriz real con los siete roles, nueve secciones y acciones registradas', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');

    $respuesta = $this->withoutVite()->actingAs($administrador)->get(route('management.permissions_matrix'));

    $respuesta->assertOk()
        ->assertSee('Matriz de privilegios')
        ->assertSee('Vista previa del menú')
        ->assertSee('Resumen')
        ->assertSee('Pantallas del módulo')
        ->assertSee('Descartar cambios')
        ->assertSee('Guardar cambios')
        ->assertViewHas('matrixGroups', fn ($groups): bool => $groups->count() === 9
            && $groups->pluck('nombre')->contains('Operaciones comerciales')
            && $groups->pluck('nombre')->contains('Configuración del sistema'));

    foreach (['Administrador', 'Compras y logística', 'Ventas mayoristas', 'Ventas minoristas', 'Marketing', 'Cliente mayorista', 'Cliente minorista'] as $nombreRole) {
        $respuesta->assertSee($nombreRole);
    }

    expect($respuesta->getContent())
        ->toContain('data-preview-items=')
        ->toContain('data-module-selector')
        ->toContain('data-ajax-submit')
        ->toContain('data-open-permission-modal')
        ->toContain('data-permission-screen-record')
        ->toContain('data-permission-action="crear"')
        ->toContain('data-permission-action="aprobar"')
        ->not->toContain('Aplicar plantilla recomendada');
});

it('muestra módulos y pantallas sin permisos sin fabricar acciones', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $suffix = strtolower(bin2hex(random_bytes(5)));
    $moduleId = DB::table('modulos')->insertGetId([
        'codigo' => 'modulo_vacio_'.$suffix,
        'nombre' => 'Módulo sin catálogo '.$suffix,
        'orden' => 9999,
        'habilitado' => true,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    $screenId = DB::table('pantallas')->insertGetId([
        'modulo_id' => $moduleId,
        'codigo' => 'pantalla_vacia_'.$suffix,
        'nombre' => 'Pantalla sin acciones '.$suffix,
        'habilitada' => true,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    $menuId = (int) DB::table('menus')->where('codigo', 'administrador.sidebar')->value('id');
    $groupId = (int) DB::table('elementos_menu')
        ->where('menu_id', $menuId)
        ->where('codigo', 'grupo.inventario')
        ->value('id');
    DB::table('elementos_menu')->insert([
        'menu_id' => $menuId,
        'codigo' => 'pantalla_vacia_menu_'.$suffix,
        'padre_id' => $groupId,
        'pantalla_id' => $screenId,
        'permiso_id' => null,
        'etiqueta' => 'Pantalla sin acciones '.$suffix,
        'tipo' => 'pantalla',
        'orden' => 9999,
        'habilitado' => true,
        'actualizado_en' => now(),
    ]);

    $this->withoutVite()->actingAs($administrador)
        ->get(route('management.permissions_matrix'))
        ->assertOk()
        ->assertSee('Módulo sin catálogo '.$suffix)
        ->assertSee('Pantalla sin acciones '.$suffix)
        ->assertSee('0 acciones registradas');
});

it('actualiza desde el modal solo los permisos de la pantalla seleccionada', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $marketingRoleId = (int) DB::table('roles')->where('codigo', 'marketing')->value('id');
    $screen = DB::table('pantallas')
        ->join('modulos', 'modulos.id', '=', 'pantallas.modulo_id')
        ->where('modulos.codigo', '!=', 'seguridad')
        ->where('pantallas.habilitada', true)
        ->where('modulos.habilitado', true)
        ->whereExists(fn ($query) => $query->selectRaw('1')->from('permisos')->whereColumn('permisos.pantalla_id', 'pantallas.id')->where('permisos.activo', true))
        ->orderBy('pantallas.id')
        ->first(['pantallas.id', 'pantallas.modulo_id']);
    $permissionId = (int) DB::table('permisos')->where('pantalla_id', $screen->id)->where('activo', true)->value('id');
    $unrelatedPermissionId = (int) DB::table('roles_permisos')->where('rol_id', $marketingRoleId)->where('permiso_id', '!=', $permissionId)->value('permiso_id');

    $this->actingAs($administrador)
        ->put(route('management.roles_permissions.screen_permissions', ['role' => $marketingRoleId]), [
            'modulo_id' => $screen->modulo_id,
            'pantalla_id' => $screen->id,
            'alcance' => 'sede',
            'permisos' => [$permissionId],
        ])
        ->assertOk()
        ->assertJsonStructure(['message', 'permission_ids']);

    expect(DB::table('roles_permisos')->where('rol_id', $marketingRoleId)->where('permiso_id', $permissionId)->where('alcance', 'sede')->exists())->toBeTrue()
        ->and(DB::table('roles_permisos')->where('rol_id', $marketingRoleId)->where('permiso_id', $unrelatedPermissionId)->exists())->toBeTrue();

    $this->actingAs($administrador)
        ->put(route('management.roles_permissions.screen_permissions', ['role' => $marketingRoleId]), [
            'modulo_id' => $screen->modulo_id,
            'pantalla_id' => $screen->id,
            'alcance' => 'sede',
            'permisos' => [],
        ])
        ->assertOk();

    expect(DB::table('roles_permisos')->where('rol_id', $marketingRoleId)->where('permiso_id', $permissionId)->where('alcance', 'sede')->exists())->toBeFalse()
        ->and(DB::table('roles_permisos')->where('rol_id', $marketingRoleId)->where('permiso_id', $unrelatedPermissionId)->exists())->toBeTrue();
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
        ->putJson(route('management.roles_permissions.permissions', $marketingRoleId), [
            'alcance' => 'sede',
            'permisos' => $permissionIds,
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Los privilegios del rol fueron actualizados.');

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
        ->putJson(route('management.roles_permissions.permissions', $retailRoleId), [
            'alcance' => 'propio',
            'permisos' => [$createOrderPermissionId],
        ])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Los roles de alcance propio no pueden recibir permisos administrativos.');

    $this->assertDatabaseMissing('roles_permisos', [
        'rol_id' => $retailRoleId,
        'permiso_id' => $createOrderPermissionId,
    ]);
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
