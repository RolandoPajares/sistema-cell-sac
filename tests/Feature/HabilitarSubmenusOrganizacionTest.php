<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\SpanishAccessFixture;

it('presenta una vista previa RBAC sin modificar datos empresariales ni privilegios', function (): void {
    SpanishAccessFixture::user('administrador');
    $codigosPantalla = ['admin.company', 'admin.sites', 'admin.warehouses'];
    $identificadoresPantalla = DB::table('pantallas')->whereIn('codigo', $codigosPantalla)->pluck('id');
    $identificadoresPermiso = DB::table('permisos')->whereIn('codigo', [
        'pantalla.admin.company.consultar',
        'pantalla.admin.sites.consultar',
        'pantalla.admin.warehouses.consultar',
    ])->pluck('id');
    DB::table('elementos_menu')->whereIn('pantalla_id', $identificadoresPantalla)
        ->update(['permiso_id' => null, 'habilitado' => false]);
    DB::table('roles_permisos')->whereIn('permiso_id', $identificadoresPermiso)->delete();
    DB::table('permisos')->whereIn('id', $identificadoresPermiso)->delete();
    DB::table('pantallas')->whereIn('codigo', $codigosPantalla)->update(['ruta' => null, 'habilitada' => false]);

    $conteosAntes = [
        'empresas' => DB::table('empresas')->count(),
        'sedes' => DB::table('sedes')->count(),
        'almacenes' => DB::table('almacenes')->count(),
        'usuarios' => DB::table('usuarios')->count(),
        'auditoria' => DB::table('registros_auditoria')->count(),
    ];
    $asignacionesAntes = DB::table('roles_permisos')->orderBy('rol_id')->orderBy('permiso_id')->get()->toArray();

    $codigoSalida = Artisan::call('technology-cell:habilitar-submenus-organizacion', ['--no-interaction' => true]);

    expect($codigoSalida)->toBe(0)
        ->and(Artisan::output())->toContain('Vista previa de solo lectura')
        ->and(DB::table('empresas')->count())->toBe($conteosAntes['empresas'])
        ->and(DB::table('sedes')->count())->toBe($conteosAntes['sedes'])
        ->and(DB::table('almacenes')->count())->toBe($conteosAntes['almacenes'])
        ->and(DB::table('usuarios')->count())->toBe($conteosAntes['usuarios'])
        ->and(DB::table('registros_auditoria')->count())->toBe($conteosAntes['auditoria'])
        ->and(DB::table('roles_permisos')->orderBy('rol_id')->orderBy('permiso_id')->get()->toArray())->toEqual($asignacionesAntes)
        ->and(DB::table('pantallas')->whereIn('codigo', $codigosPantalla)->where('habilitada', true)->count())->toBe(0);
});

it('habilita únicamente las tres pantallas autorizadas, verifica el respaldo y es idempotente', function (): void {
    SpanishAccessFixture::user('administrador');
    $codigosPantalla = ['admin.company', 'admin.sites', 'admin.warehouses'];
    $identificadoresPantalla = DB::table('pantallas')->whereIn('codigo', $codigosPantalla)->pluck('id');
    $identificadoresPermiso = DB::table('permisos')->whereIn('codigo', [
        'pantalla.admin.company.consultar',
        'pantalla.admin.sites.consultar',
        'pantalla.admin.warehouses.consultar',
    ])->pluck('id');
    DB::table('elementos_menu')->whereIn('pantalla_id', $identificadoresPantalla)
        ->update(['permiso_id' => null, 'habilitado' => false]);
    DB::table('roles_permisos')->whereIn('permiso_id', $identificadoresPermiso)->delete();
    DB::table('permisos')->whereIn('id', $identificadoresPermiso)->delete();
    DB::table('pantallas')->whereIn('codigo', $codigosPantalla)->update(['ruta' => null, 'habilitada' => false]);

    $rolAdministradorId = (int) DB::table('roles')->where('codigo', 'administrador')->value('id');
    $asignacionesOtrosRoles = DB::table('roles_permisos')->where('rol_id', '<>', $rolAdministradorId)
        ->orderBy('rol_id')->orderBy('permiso_id')->get()->toArray();
    Storage::fake('local');

    $codigoSalida = Artisan::call('technology-cell:habilitar-submenus-organizacion', ['--aplicar' => true]);
    $salida = Artisan::output();
    $archivosRespaldo = Storage::disk('local')->allFiles('respaldos');

    expect($codigoSalida)->toBe(0)
        ->and($salida)->toContain('Submenús de empresa, sedes y almacenes habilitados')
        ->and($archivosRespaldo)->toHaveCount(1)
        ->and($salida)->toContain(hash('sha256', Storage::disk('local')->get($archivosRespaldo[0])))
        ->and(DB::table('pantallas')->whereIn('codigo', $codigosPantalla)->where('habilitada', true)->count())->toBe(3)
        ->and(DB::table('permisos')->whereIn('codigo', [
            'pantalla.admin.company.consultar',
            'pantalla.admin.sites.consultar',
            'pantalla.admin.warehouses.consultar',
        ])->where('activo', true)->count())->toBe(3)
        ->and(DB::table('roles_permisos')->where('rol_id', $rolAdministradorId)
            ->whereIn('permiso_id', DB::table('permisos')->whereIn('codigo', [
                'pantalla.admin.company.consultar',
                'pantalla.admin.sites.consultar',
                'pantalla.admin.warehouses.consultar',
            ])->select('id'))->where('alcance', 'empresa')->count())->toBe(3)
        ->and(DB::table('registros_auditoria')->where('accion', 'catalogo_rbac_submenus_organizacion_habilitados')->count())->toBe(1)
        ->and(DB::table('roles_permisos')->where('rol_id', '<>', $rolAdministradorId)
            ->orderBy('rol_id')->orderBy('permiso_id')->get()->toArray())->toEqual($asignacionesOtrosRoles);

    $salidaSegundaEjecucion = Artisan::call('technology-cell:habilitar-submenus-organizacion', ['--aplicar' => true]);

    expect($salidaSegundaEjecucion)->toBe(0)
        ->and(Artisan::output())->toContain('ya están habilitadas')
        ->and(Storage::disk('local')->allFiles('respaldos'))->toHaveCount(1)
        ->and(DB::table('registros_auditoria')->where('accion', 'catalogo_rbac_submenus_organizacion_habilitados')->count())->toBe(1);
});

it('rechaza activar un permiso organizacional ya asignado a otro rol', function (): void {
    SpanishAccessFixture::user('administrador');
    $pantalla = DB::table('pantallas')->where('codigo', 'admin.sites')->first();
    $codigoPermiso = 'pantalla.admin.sites.consultar';
    $permiso = DB::table('permisos')->where('codigo', $codigoPermiso)->first();

    if ($permiso === null) {
        $permisoId = DB::table('permisos')->insertGetId([
            'modulo_id' => $pantalla->modulo_id,
            'pantalla_id' => $pantalla->id,
            'codigo' => $codigoPermiso,
            'accion' => 'consultar',
            'nombre' => 'Consultar - Sedes',
            'es_sistema' => true,
            'activo' => false,
            'creado_en' => now(),
            'actualizado_en' => now(),
        ]);
    } else {
        $permisoId = (int) $permiso->id;
        DB::table('permisos')->where('id', $permisoId)->update(['activo' => false]);
    }

    $rolClienteId = (int) DB::table('roles')->where('codigo', 'cliente_minorista')->value('id');
    DB::table('roles_permisos')->insert([
        'rol_id' => $rolClienteId,
        'permiso_id' => $permisoId,
        'alcance' => 'propio',
        'creado_en' => now(),
    ]);
    DB::table('pantallas')->where('id', $pantalla->id)->update(['habilitada' => false]);
    DB::table('elementos_menu')->where('pantalla_id', $pantalla->id)->update(['habilitado' => false]);
    Storage::fake('local');

    $codigoSalida = Artisan::call('technology-cell:habilitar-submenus-organizacion', ['--aplicar' => true]);

    expect($codigoSalida)->toBe(1)
        ->and(Artisan::output())->toContain('ya está asignado a otro rol')
        ->and(DB::table('permisos')->where('id', $permisoId)->value('activo'))->toBe(0)
        ->and(DB::table('roles_permisos')->where('rol_id', $rolClienteId)->where('permiso_id', $permisoId)->exists())->toBeTrue()
        ->and(DB::table('registros_auditoria')->where('accion', 'catalogo_rbac_submenus_organizacion_habilitados')->exists())->toBeFalse()
        ->and(Storage::disk('local')->allFiles('respaldos'))->toBeEmpty();
});

it('rechaza alterar un vínculo de menú ubicado en un grupo distinto', function (): void {
    SpanishAccessFixture::user('administrador');
    $menuId = (int) DB::table('menus')->where('codigo', 'administrador.sidebar')->value('id');
    $pantallaId = (int) DB::table('pantallas')->where('codigo', 'admin.sites')->value('id');
    $grupoSeguridadId = (int) DB::table('elementos_menu')->where('menu_id', $menuId)
        ->where('codigo', 'grupo.seguridad')->value('id');
    DB::table('elementos_menu')->where('menu_id', $menuId)->where('pantalla_id', $pantallaId)
        ->update(['padre_id' => $grupoSeguridadId]);
    Storage::fake('local');

    $codigoSalida = Artisan::call('technology-cell:habilitar-submenus-organizacion', ['--aplicar' => true]);

    expect($codigoSalida)->toBe(1)
        ->and(Artisan::output())->toContain('está en otro grupo')
        ->and(DB::table('elementos_menu')->where('menu_id', $menuId)->where('pantalla_id', $pantallaId)
            ->value('padre_id'))->toBe($grupoSeguridadId)
        ->and(DB::table('registros_auditoria')->where('accion', 'catalogo_rbac_submenus_organizacion_habilitados')->exists())->toBeFalse()
        ->and(Storage::disk('local')->allFiles('respaldos'))->toBeEmpty();
});

it('prepara la habilitación independiente de Personal por sede sin tocar las otras pantallas', function (): void {
    SpanishAccessFixture::user('administrador');
    $pantalla = DB::table('pantallas')->where('codigo', 'admin.staff')->first();
    $permiso = DB::table('permisos')->where('codigo', 'pantalla.admin.staff.consultar')->first();
    $rolAdministradorId = (int) DB::table('roles')->where('codigo', 'administrador')->value('id');
    DB::table('pantallas')->where('id', $pantalla->id)->update(['ruta' => null, 'habilitada' => false]);
    DB::table('permisos')->where('id', $permiso->id)->update(['activo' => false]);
    DB::table('roles_permisos')->where('rol_id', $rolAdministradorId)->where('permiso_id', $permiso->id)->delete();
    DB::table('elementos_menu')->where('pantalla_id', $pantalla->id)->update(['permiso_id' => null, 'habilitado' => false]);
    $pantallasBase = DB::table('pantallas')->whereIn('codigo', ['admin.company', 'admin.sites', 'admin.warehouses'])
        ->orderBy('codigo')->get(['codigo', 'ruta', 'habilitada'])->toArray();
    $permisosBase = DB::table('permisos')->whereIn('codigo', [
        'pantalla.admin.company.consultar', 'pantalla.admin.sites.consultar', 'pantalla.admin.warehouses.consultar',
    ])->orderBy('codigo')->get(['codigo', 'activo'])->toArray();
    Storage::fake('local');

    $this->artisan('technology-cell:habilitar-submenus-organizacion', ['--incluir-personal' => true])
        ->expectsOutput('Vista previa de solo lectura. No se escribió en la base ni se creó un respaldo.')
        ->assertExitCode(0);

    expect(DB::table('pantallas')->where('id', $pantalla->id)->value('habilitada'))->toBe(0)
        ->and(DB::table('registros_auditoria')->where('accion', 'catalogo_rbac_submenus_organizacion_habilitados')->count())->toBe(0)
        ->and(Storage::disk('local')->allFiles('respaldos'))->toBeEmpty();

    $this->artisan('technology-cell:habilitar-submenus-organizacion', [
        '--incluir-personal' => true,
        '--aplicar' => true,
    ])->assertExitCode(0);

    expect(DB::table('pantallas')->where('codigo', 'admin.staff')->where('ruta', 'management.company_settings.staff')->where('habilitada', true)->exists())->toBeTrue()
        ->and(DB::table('permisos')->where('id', $permiso->id)->where('activo', true)->exists())->toBeTrue()
        ->and(DB::table('roles_permisos')->where('rol_id', $rolAdministradorId)->where('permiso_id', $permiso->id)->where('alcance', 'empresa')->exists())->toBeTrue()
        ->and(DB::table('elementos_menu')->where('pantalla_id', $pantalla->id)->where('permiso_id', $permiso->id)->where('habilitado', true)->exists())->toBeTrue()
        ->and(DB::table('pantallas')->whereIn('codigo', ['admin.company', 'admin.sites', 'admin.warehouses'])->orderBy('codigo')->get(['codigo', 'ruta', 'habilitada'])->toArray())->toEqual($pantallasBase)
        ->and(DB::table('permisos')->whereIn('codigo', [
            'pantalla.admin.company.consultar', 'pantalla.admin.sites.consultar', 'pantalla.admin.warehouses.consultar',
        ])->orderBy('codigo')->get(['codigo', 'activo'])->toArray())->toEqual($permisosBase)
        ->and(DB::table('registros_auditoria')->where('accion', 'catalogo_rbac_submenus_organizacion_habilitados')->count())->toBe(1)
        ->and(Storage::disk('local')->allFiles('respaldos'))->toHaveCount(1);
});
