<?php

use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioDelegacion;
use App\Servicios\ServicioMenuDinamico;
use Database\Seeders\RbacInicialSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\SpanishAccessFixture;

it('seeds the seven RBAC roles without inventing organization data', function (): void {
    $this->seed(RbacInicialSeeder::class);
    $this->seed(RbacInicialSeeder::class);

    $roleCodes = DB::table('roles')->orderBy('codigo')->pluck('codigo')->all();
    $expectedRoleCodes = User::ROLES;
    sort($roleCodes);
    sort($expectedRoleCodes);

    expect($roleCodes)->toBe($expectedRoleCodes)
        ->and(DB::table('roles')->count())->toBe(7)
        ->and(DB::table('empresas')->count())->toBe(0)
        ->and(DB::table('sedes')->count())->toBe(0)
        ->and(DB::table('almacenes')->count())->toBe(0)
        ->and(DB::table('permisos')->count())->toBe(174)
        ->and(DB::table('pantallas')->whereIn('codigo', ['admin.payments', 'admin.cash_registers', 'admin.refunds'])
            ->where('habilitada', true)->count())->toBe(3)
        ->and(DB::table('pantallas')->where('codigo', 'admin.cash_registers')->value('ruta'))
        ->toBe('management.payments.preview.cash_registers')
        ->and(DB::table('permisos')->where('codigo', 'pantalla.admin.payments.consultar')->exists())->toBeTrue()
        ->and(DB::table('permisos')->where('codigo', 'pantalla.admin.warehouses.consultar')->exists())->toBeTrue()
        ->and(DB::table('permisos')->where('codigo', 'pantalla.admin.sites.consultar')->exists())->toBeTrue()
        ->and(DB::table('pantallas')->where('codigo', 'admin.company')->value('ruta'))->toBe('management.company_settings.data')
        ->and(DB::table('permisos')->where('codigo', 'pantalla.admin.company.consultar')->exists())->toBeTrue()
        ->and(DB::table('pantallas')->where('codigo', 'admin.staff')->where('habilitada', true)->value('ruta'))
        ->toBe('management.company_settings.staff')
        ->and(DB::table('permisos')->where('codigo', 'pantalla.admin.staff.consultar')->exists())->toBeTrue()
        ->and(DB::table('permisos')->where('codigo', 'gestion.seguridad.actualizar')->exists())->toBeTrue();
});

it('rechaza el código reservado superadministrador y conserva los siete roles existentes', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');

    $this->actingAs($administrador)
        ->post(route('management.roles_permissions.store'), [
            'codigo' => 'superadministrador',
            'nombre' => 'Superadministrador',
            'alcance_permitido' => 'empresa',
        ])
        ->assertSessionHasErrors([
            'codigo' => 'El código superadministrador está reservado y no se puede crear.',
        ]);

    expect(DB::table('roles')->count())->toBe(7)
        ->and(DB::table('roles')->where('codigo', 'superadministrador')->exists())->toBeFalse();
});

it('does not restore revoked privileges or overwrite customized menus on a repeat seed', function (): void {
    User::factory()->create(['rol' => 'marketing']);
    $this->seed(RbacInicialSeeder::class);

    $marketingRoleId = DB::table('roles')->where('codigo', 'marketing')->value('id');
    $campaignPermissionId = DB::table('permisos')->where('codigo', 'gestion.campanias.consultar')->value('id');
    $marketingMenuId = DB::table('menus')->where('codigo', 'marketing.sidebar')->value('id');
    $campaignMenuItem = DB::table('elementos_menu')
        ->where('menu_id', $marketingMenuId)
        ->where('codigo', 'item.admin.campaigns')
        ->first();

    DB::table('roles_permisos')->where('rol_id', $marketingRoleId)->where('permiso_id', $campaignPermissionId)->delete();
    DB::table('elementos_menu')->where('id', $campaignMenuItem->id)->update([
        'etiqueta' => 'Campañas revisadas',
        'habilitado' => false,
    ]);

    $this->seed(RbacInicialSeeder::class);

    expect(DB::table('roles_permisos')->where('rol_id', $marketingRoleId)->where('permiso_id', $campaignPermissionId)->exists())->toBeFalse()
        ->and(DB::table('elementos_menu')->where('id', $campaignMenuItem->id)->value('etiqueta'))->toBe('Campañas revisadas')
        ->and(DB::table('elementos_menu')->where('id', $campaignMenuItem->id)->value('habilitado'))->toBe(0);
});

it('previsualiza y habilita solo los submenús financieros existentes para el administrador', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $codigosPantalla = ['admin.payments', 'admin.cash_registers', 'admin.refunds'];
    $codigosPermiso = array_map(static fn (string $codigo): string => 'pantalla.'.$codigo.'.consultar', $codigosPantalla);
    $idsPantalla = DB::table('pantallas')->whereIn('codigo', $codigosPantalla)->pluck('id');
    $idsPermiso = DB::table('permisos')->whereIn('codigo', $codigosPermiso)->pluck('id');
    DB::table('elementos_menu')->whereIn('pantalla_id', $idsPantalla)->update(['permiso_id' => null]);
    DB::table('roles_permisos')->whereIn('permiso_id', $idsPermiso)->delete();
    DB::table('permisos')->whereIn('id', $idsPermiso)->delete();
    DB::table('pantallas')->whereIn('id', $idsPantalla)->update(['habilitada' => false]);

    $this->artisan('technology-cell:habilitar-submenus-financieros')
        ->expectsOutput('La vista previa es de solo lectura. Para aplicar, ejecute nuevamente con --aplicar.')
        ->assertExitCode(0);

    expect(DB::table('pantallas')->whereIn('codigo', $codigosPantalla)->where('habilitada', true)->count())->toBe(0)
        ->and(DB::table('permisos')->whereIn('codigo', $codigosPermiso)->count())->toBe(0)
        ->and(DB::table('registros_auditoria')->where('accion', 'catalogo_rbac_submenus_financieros_habilitados')->count())->toBe(0);

    $this->artisan('technology-cell:habilitar-submenus-financieros', ['--aplicar' => true])
        ->expectsConfirmation('¿Autoriza habilitar estos submenús financieros en esta base de datos?', 'yes')
        ->assertExitCode(0);

    $rolAdministradorId = DB::table('roles')->where('codigo', 'administrador')->value('id');
    $this->assertDatabaseHas('usuarios', ['id' => $administrador->id]);
    expect(DB::table('roles')->count())->toBe(7)
        ->and(DB::table('pantallas')->whereIn('codigo', $codigosPantalla)->where('habilitada', true)->count())->toBe(3)
        ->and(DB::table('permisos')->whereIn('codigo', $codigosPermiso)->where('activo', true)->count())->toBe(3)
        ->and(DB::table('roles_permisos')->where('rol_id', $rolAdministradorId)->whereIn('permiso_id', function ($consulta) use ($codigosPermiso): void {
            $consulta->select('id')->from('permisos')->whereIn('codigo', $codigosPermiso);
        })->where('alcance', 'empresa')->count())->toBe(3)
        ->and(DB::table('elementos_menu')->whereIn('pantalla_id', $idsPantalla)->whereNotNull('permiso_id')->where('habilitado', true)->count())->toBe(3)
        ->and(DB::table('registros_auditoria')->where('accion', 'catalogo_rbac_submenus_financieros_habilitados')->count())->toBe(1);

    $this->artisan('technology-cell:habilitar-submenus-financieros', ['--aplicar' => true])
        ->assertExitCode(0);
    expect(DB::table('registros_auditoria')->where('accion', 'catalogo_rbac_submenus_financieros_habilitados')->count())->toBe(1);
});

it('autoriza una ruta compartida por pantallas vigentes y bloquea si todas sus pantallas están deshabilitadas', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $marketing = SpanishAccessFixture::user('marketing');

    $this->actingAs($administrador)
        ->get(route('management.payments'))
        ->assertOk()
        ->assertSee('No hay pagos y cobros para mostrar');

    $this->actingAs($marketing)->get(route('management.payments'))->assertForbidden();

    $rolAdministradorId = DB::table('roles')->where('codigo', 'administrador')->value('id');
    $permisosDePantalla = DB::table('permisos')->whereIn('codigo', [
        'pantalla.admin.payments.consultar',
        'pantalla.admin.cash_registers.consultar',
    ])->pluck('id');
    DB::table('roles_permisos')->where('rol_id', $rolAdministradorId)->whereIn('permiso_id', $permisosDePantalla)->delete();

    $this->actingAs($administrador)->get(route('management.payments'))->assertForbidden();

    DB::table('roles_permisos')->insert($permisosDePantalla->map(static fn (int $permisoId): array => [
        'rol_id' => $rolAdministradorId,
        'permiso_id' => $permisoId,
        'alcance' => 'empresa',
        'creado_en' => now(),
    ])->all());

    DB::table('pantallas')->whereIn('codigo', ['admin.payments', 'admin.cash_registers'])
        ->update(['habilitada' => false]);

    $this->actingAs($administrador)->get(route('management.payments'))->assertForbidden();
});

it('requires screen permissions for the shipment and IMEI panels', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    Schema::create('envios_pedidos', function (Blueprint $tabla): void {
        $tabla->bigIncrements('id');
        $tabla->unsignedBigInteger('empresa_id');
        $tabla->unsignedBigInteger('sede_id');
        $tabla->unsignedInteger('pedido_id');
        $tabla->unsignedBigInteger('almacen_id');
        $tabla->unsignedBigInteger('guia_remision_id')->nullable();
        $tabla->string('codigo', 60);
        $tabla->string('modalidad', 30);
        $tabla->string('transportista', 120)->nullable();
        $tabla->string('servicio', 120)->nullable();
        $tabla->string('numero_seguimiento', 160)->nullable();
        $tabla->string('estado', 30)->default('pendiente');
        $tabla->string('moneda', 3)->default('PEN');
        $tabla->decimal('costo_envio', 15, 2)->default(0);
        $tabla->json('destino_historico');
        $tabla->dateTime('estimado_entrega_en')->nullable();
        $tabla->dateTime('despachado_en')->nullable();
        $tabla->dateTime('entregado_en')->nullable();
        $tabla->unsignedBigInteger('creado_por_usuario_id')->nullable();
        $tabla->dateTime('creado_en');
    });
    Schema::create('eventos_envios_pedidos', function (Blueprint $tabla): void {
        $tabla->bigIncrements('id');
        $tabla->unsignedBigInteger('envio_pedido_id');
        $tabla->string('estado', 30);
        $tabla->string('descripcion', 500)->nullable();
        $tabla->string('fuente', 30)->nullable();
        $tabla->string('identificador_externo', 160)->nullable();
        $tabla->dateTime('ocurrido_en');
        $tabla->dateTime('registrado_en')->nullable();
        $tabla->json('datos_seguros')->nullable();
    });
    $pantallas = [
        'management.inventory.preview.shipments' => [
            'codigo' => 'admin.shipments',
            'ruta' => 'management.inventory.preview.shipments',
            'permiso' => 'pantalla.admin.shipments.consultar',
        ],
        'management.inventory.preview.serials' => [
            'codigo' => 'admin.imei',
            'ruta' => 'management.inventory.preview.serials',
            'permiso' => 'pantalla.admin.imei.consultar',
        ],
    ];

    foreach (array_keys($pantallas) as $nombreRuta) {
        $this->actingAs($administrador)->get(route($nombreRuta))->assertForbidden();
    }

    $rolAdministradorId = DB::table('roles')->where('codigo', 'administrador')->value('id');

    foreach ($pantallas as $pantalla) {
        $registroPantalla = DB::table('pantallas')->where('codigo', $pantalla['codigo'])->firstOrFail();
        DB::table('pantallas')->where('id', $registroPantalla->id)->update([
            'ruta' => $pantalla['ruta'],
            'habilitada' => true,
        ]);
        $permisoId = DB::table('permisos')->insertGetId([
            'modulo_id' => $registroPantalla->modulo_id,
            'pantalla_id' => $registroPantalla->id,
            'codigo' => $pantalla['permiso'],
            'accion' => 'consultar',
            'nombre' => $pantalla['codigo'].' - consultar',
            'es_sistema' => true,
            'activo' => true,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);
        DB::table('roles_permisos')->insert([
            'rol_id' => $rolAdministradorId,
            'permiso_id' => $permisoId,
            'alcance' => 'empresa',
            'creado_en' => now(),
        ]);
    }

    foreach (array_keys($pantallas) as $nombreRuta) {
        $this->actingAs($administrador)->get(route($nombreRuta))->assertOk();
    }
});

it('restricts assigned employee permissions to their company site and customer permissions to owned records', function (): void {
    $marketingUser = SpanishAccessFixture::user('marketing');
    $customer = SpanishAccessFixture::user('cliente_minorista');
    $otherCustomer = SpanishAccessFixture::user('cliente_minorista');

    $companyId = DB::table('empresas')->where('codigo', 'PRUEBA')->value('id');
    $principalSiteId = DB::table('sedes')->where('empresa_id', $companyId)->where('codigo', 'PRINCIPAL')->value('id');
    $otherCompanyId = DB::table('empresas')->insertGetId([
        'codigo' => 'OTRA',
        'razon_social' => 'Otra empresa de prueba',
        'moneda_base' => 'PEN',
        'activo' => true,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    $otherSiteId = DB::table('sedes')->insertGetId([
        'empresa_id' => $companyId,
        'codigo' => 'SECUNDARIA',
        'nombre' => 'Sede de prueba sin asignación',
        'zona_horaria' => 'America/Lima',
        'activa' => true,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    $otherCompanySiteId = DB::table('sedes')->insertGetId([
        'empresa_id' => $otherCompanyId,
        'codigo' => 'PRINCIPAL',
        'nombre' => 'Sede de otra empresa',
        'zona_horaria' => 'America/Lima',
        'activa' => true,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);

    $authorization = app(ServicioAutorizacion::class);
    $delegation = app(ServicioDelegacion::class);
    $campaignPermissionId = DB::table('permisos')->where('codigo', 'gestion.campanias.consultar')->value('id');

    expect($authorization->allowsRecord($marketingUser, 'gestion.campanias.consultar', (int) $companyId, (int) $principalSiteId))->toBeTrue()
        ->and($authorization->allowsRecord($marketingUser, 'gestion.campanias.consultar', (int) $companyId, (int) $otherSiteId))->toBeFalse()
        ->and($authorization->allowsRecord($marketingUser, 'gestion.campanias.consultar', (int) $otherCompanyId, (int) $otherCompanySiteId))->toBeFalse()
        ->and($authorization->allowsRecord($customer, 'gestion.pedidos.consultar', (int) $companyId, (int) $principalSiteId, (int) $customer->id))->toBeTrue()
        ->and($authorization->allowsRecord($otherCustomer, 'gestion.pedidos.consultar', (int) $companyId, (int) $principalSiteId, (int) $customer->id))->toBeFalse()
        ->and($delegation->puedeDelegarPermisos($marketingUser, [(int) $campaignPermissionId], 'sede'))->toBeTrue()
        ->and($delegation->puedeDelegarPermisos($marketingUser, [(int) $campaignPermissionId], 'empresa'))->toBeFalse();

    $this->withSession(['sede_actual_id' => $otherSiteId])
        ->actingAs($marketingUser)
        ->get(route('management.campaigns'))
        ->assertForbidden();
});

it('builds role-specific menus from the database and enforces route permissions', function (): void {
    $usersByRole = collect(User::ROLES)->mapWithKeys(fn (string $role): array => [$role => SpanishAccessFixture::user($role)]);
    $administrator = $usersByRole['administrador'];
    $marketingUser = $usersByRole['marketing'];

    $this->seed(RbacInicialSeeder::class);

    $menuService = app(ServicioMenuDinamico::class);
    $administratorMenu = $menuService->paraUsuario($administrator);
    $marketingMenu = $menuService->paraUsuario($marketingUser);
    $menusByRole = $usersByRole->map(fn (User $user): array => $menuService->paraUsuario($user));
    $administratorRoutes = collect($administratorMenu['grupos'])->flatMap(fn (array $group): array => $group['items'])->pluck('route')->all();
    $marketingRoutes = collect($marketingMenu['grupos'])->flatMap(fn (array $group): array => $group['items'])->pluck('route')->all();

    expect($menusByRole->every(fn (array $menu): bool => $menu['dinamico'] && $menu['grupos'] !== []))->toBeTrue()
        ->and(DB::table('roles_menus')->count())->toBe(7)
        ->and($administratorMenu['dinamico'])->toBeTrue()
        ->and($administratorRoutes)->toContain('management.roles_permissions')
        ->and($marketingMenu['dinamico'])->toBeTrue()
        ->and($marketingRoutes)->toContain('management.campaigns')
        ->and($marketingRoutes)->not->toContain('management.roles_permissions');

    $this->actingAs($marketingUser)->get(route('management.campaigns'))->assertOk();
    $this->actingAs($marketingUser)->get(route('management.users'))->assertForbidden();

    $campaignPermissionId = DB::table('permisos')->where('codigo', 'gestion.campanias.consultar')->value('id');
    $marketingRoleId = DB::table('roles')->where('codigo', 'marketing')->value('id');
    DB::table('roles_permisos')->where('rol_id', $marketingRoleId)->where('permiso_id', $campaignPermissionId)->delete();

    $menuAfterRevocation = app(ServicioMenuDinamico::class)->paraUsuario($marketingUser);
    $routesAfterRevocation = collect($menuAfterRevocation['grupos'])->flatMap(fn (array $group): array => $group['items'])->pluck('route')->all();

    $this->actingAs($marketingUser)->get(route('management.campaigns'))->assertForbidden();
    expect($routesAfterRevocation)->not->toContain('management.campaigns');

    $securityModuleId = DB::table('modulos')->where('codigo', 'seguridad')->value('id');
    $this->actingAs($administrator)
        ->patch(route('management.modules.update', $securityModuleId), ['habilitado' => false])
        ->assertUnprocessable();
    expect(DB::table('modulos')->where('codigo', 'seguridad')->value('habilitado'))->toBe(1);
});

it('updates menu role assignments using the Spanish pivot timestamp from the SQL schema', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $this->seed(RbacInicialSeeder::class);
    $menuId = (int) DB::table('menus')->where('codigo', 'administrador.sidebar')->value('id');
    $rolId = (int) DB::table('roles')->where('codigo', 'administrador')->value('id');

    $this->actingAs($administrador)
        ->put(route('management.menus.roles', $menuId), ['roles' => [$rolId]])
        ->assertRedirect();

    expect(DB::table('roles_menus')->where('menu_id', $menuId)->where('rol_id', $rolId)->value('creado_en'))->not->toBeNull();
});

it('keeps global session management inaccessible while its RBAC screen is disabled', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $this->seed(RbacInicialSeeder::class);

    $this->actingAs($administrador)
        ->get(route('management.security_sessions'))
        ->assertForbidden();

    expect(DB::table('pantallas')->where('codigo', 'admin.sessions')->value('habilitada'))->toBe(0);
});

it('abre las gestiones de seguridad reales y elimina los resultados simulados de sus alias', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $this->seed(RbacInicialSeeder::class);

    foreach ([
        'management.users',
        'management.roles_permissions',
        'management.user_assignments',
        'management.menus',
        'management.modules',
    ] as $rutaOperativa) {
        $this->actingAs($administrador)->get(route($rutaOperativa))->assertOk();
    }

    $redireccionesOperativas = [
        'management.roles_permissions.preview.users' => ['management.users', []],
        'management.roles_permissions.preview.roles' => ['management.roles_permissions', []],
        'management.roles_permissions.preview.matrix' => ['management.roles_permissions', []],
        'management.roles_permissions.preview.assignments' => ['management.user_assignments', []],
        'management.roles_permissions.preview.site_assignments' => ['management.user_assignments', []],
        'management.roles_permissions.preview.menus' => ['management.menus', []],
        'management.roles_permissions.preview.modules' => ['management.modules', []],
        'management.roles_permissions.preview.blocks' => ['management.users', ['estado' => 'inactivo']],
    ];

    foreach ($redireccionesOperativas as $rutaAnterior => [$rutaOperativa, $parametros]) {
        $this->actingAs($administrador)
            ->get(route($rutaAnterior))
            ->assertRedirect(route($rutaOperativa, $parametros));
    }

    $this->actingAs($administrador)
        ->get(route('management.roles_permissions.preview.policies'))
        ->assertOk()
        ->assertSee('El esquema actual no contiene una entidad')
        ->assertDontSee('Compras Demo');

    $this->actingAs($administrador)
        ->get(route('management.roles_permissions.preview.sessions'))
        ->assertOk()
        ->assertSee('El catálogo RBAC mantiene deshabilitado el submenú global')
        ->assertDontSee('Administrador Demo');

    $usuarioMarketing = SpanishAccessFixture::user('marketing');
    $this->actingAs($usuarioMarketing)
        ->get(route('management.roles_permissions.preview.users'))
        ->assertForbidden();
});

it('does not expose view-only placeholders as working administrative modules', function (): void {
    $administrator = User::factory()->administrator()->create();

    $this->actingAs($administrator)->get(route('management.payments'))->assertForbidden();
});

it('preserves the last active global administrator assignment', function (): void {
    $administrator = SpanishAccessFixture::user('administrador');
    $administratorRoleId = DB::table('roles')->where('codigo', 'administrador')->value('id');

    $this->actingAs($administrator)
        ->delete(route('management.user_assignments.revoke', [$administrator->id, $administratorRoleId]))
        ->assertUnprocessable();

    $this->assertDatabaseHas('usuarios_roles', [
        'usuario_id' => $administrator->id,
        'rol_id' => $administratorRoleId,
        'activo' => true,
    ]);
});

it('does not deactivate a role that is still assigned at a site', function (): void {
    $administrator = SpanishAccessFixture::user('administrador');
    $roleId = (int) DB::table('roles')->where('codigo', 'marketing')->value('id');
    $siteId = (int) DB::table('sedes')->where('activa', true)->value('id');

    DB::table('usuarios_sedes_roles')->insert([
        'usuario_id' => $administrator->id,
        'sede_id' => $siteId,
        'rol_id' => $roleId,
        'asignado_por_usuario_id' => $administrator->id,
        'asignado_en' => now(),
        'activo' => true,
    ]);

    $this->actingAs($administrator)
        ->patch(route('management.roles_permissions.status', $roleId), ['activo' => 0])
        ->assertStatus(409);

    expect(DB::table('roles')->where('id', $roleId)->value('activo'))->toBe(1);
});

it('does not change role scope while active assignments exist', function (): void {
    $administrator = SpanishAccessFixture::user('administrador');
    $roleId = (int) DB::table('roles')->where('codigo', 'marketing')->value('id');

    DB::table('usuarios_roles')->insert([
        'usuario_id' => $administrator->id,
        'rol_id' => $roleId,
        'asignado_por_usuario_id' => $administrator->id,
        'asignado_en' => now(),
        'activo' => true,
    ]);

    $this->actingAs($administrator)
        ->put(route('management.roles_permissions.update', $roleId), [
            'nombre' => 'Marketing',
            'descripcion' => 'Rol de marketing',
            'alcance_permitido' => 'sede',
        ])
        ->assertStatus(409);
});

it('keeps indispensable RBAC screens enabled', function (): void {
    $administrator = SpanishAccessFixture::user('administrador');
    $screenId = (int) DB::table('pantallas')->where('codigo', 'admin.roles')->value('id');

    $this->actingAs($administrator)
        ->patch(route('management.modules.screens.update', $screenId), ['habilitada' => 0])
        ->assertStatus(422);

    expect(DB::table('pantallas')->where('id', $screenId)->value('habilitada'))->toBe(1);
});
