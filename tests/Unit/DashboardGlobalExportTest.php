<?php

use App\Http\Controllers\Paneles\Administrador\DashboardGlobalVentasController;
use App\Models\User;
use App\Servicios\ExportadorTabular;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

function prepareGlobalDashboardAuthorization(int $userId = 1): void
{
    Schema::create('empresas', function (Blueprint $table): void {
        $table->id();
        $table->boolean('activo');
    });
    Schema::create('sedes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->boolean('activa');
    });
    Schema::create('roles', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo');
        $table->string('alcance_permitido');
        $table->boolean('activo');
    });
    Schema::create('modulos', function (Blueprint $table): void {
        $table->id();
        $table->boolean('habilitado');
    });
    Schema::create('pantallas', function (Blueprint $table): void {
        $table->id();
        $table->boolean('habilitada');
    });
    Schema::create('permisos', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo');
        $table->unsignedBigInteger('modulo_id');
        $table->unsignedBigInteger('pantalla_id')->nullable();
        $table->boolean('activo');
    });
    Schema::create('roles_permisos', function (Blueprint $table): void {
        $table->unsignedBigInteger('rol_id');
        $table->unsignedBigInteger('permiso_id');
        $table->string('alcance');
    });
    Schema::create('usuarios_roles', function (Blueprint $table): void {
        $table->unsignedBigInteger('usuario_id');
        $table->unsignedBigInteger('rol_id');
        $table->boolean('activo');
        $table->timestamp('vigente_hasta')->nullable();
    });
    Schema::create('usuarios_sedes_roles', function (Blueprint $table): void {
        $table->unsignedBigInteger('usuario_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('rol_id');
        $table->boolean('activo');
        $table->timestamp('vigente_hasta')->nullable();
    });
    Schema::create('menus', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo');
    });
    Schema::create('roles_menus', function (Blueprint $table): void {
        $table->id();
    });
    Schema::create('elementos_menu', function (Blueprint $table): void {
        $table->id();
    });
    DB::table('empresas')->insert(['id' => 1, 'activo' => true]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'administrador', 'alcance_permitido' => 'empresa', 'activo' => true]);
    DB::table('modulos')->insert(['id' => 1, 'habilitado' => true]);
    DB::table('permisos')->insert([
        ['id' => 1, 'codigo' => 'gestion.panel.consultar', 'modulo_id' => 1, 'activo' => true],
        ['id' => 2, 'codigo' => 'gestion.reportes.exportar', 'modulo_id' => 1, 'activo' => true],
    ]);
    DB::table('roles_permisos')->insert([
        ['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'empresa'],
        ['rol_id' => 1, 'permiso_id' => 2, 'alcance' => 'empresa'],
    ]);
    DB::table('usuarios_roles')->insert(['usuario_id' => $userId, 'rol_id' => 1, 'activo' => true]);
}

test('exporta csv con encabezados y evita fórmulas de hoja de cálculo', function () {
    $exporter = new ExportadorTabular;

    $response = $exporter->download('csv', 'ventas', ['Pedido', 'Cliente'], [
        [1, '=SUM(1+1)'],
        [2, "\u{200B}+SUM(1+1)"],
        [3, "\t@SUM(1)"],
        [4, '-12.50'],
        [5, '-1+1'],
    ]);
    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    expect($content)->toContain('Pedido,Cliente')
        ->toContain("'=SUM(1+1)")
        ->toContain("'\u{200B}+SUM(1+1)")
        ->toContain("'\t@SUM(1)")
        ->toContain("'-1+1")
        ->toContain('-12.50');
});

test('exporta un libro excel xlsx válido', function () {
    $exporter = new ExportadorTabular;

    $response = $exporter->download('excel', 'ventas', ['Pedido'], [[1, "\u{200B}=1+1", "\t+SUM(A1)", '-12.50']]);
    $archiveFile = tempnam(sys_get_temp_dir(), 'tc-test-');
    file_put_contents($archiveFile, $response->getContent());
    $archive = new ZipArchive;
    $opened = $archive->open($archiveFile);
    $sheet = $opened === true ? $archive->getFromName('xl/worksheets/sheet1.xml') : false;
    if ($opened === true) {
        $archive->close();
    }
    unlink($archiveFile);

    expect($opened)->toBeTrue()
        ->and($sheet)->toContain('<sheetData>')
        ->toContain("&apos;\u{200B}=1+1")
        ->toContain("&apos;\t+SUM(A1)")
        ->toContain('-12.50')
        ->not->toContain('<f>');
});

test('exporta un documento pdf con estructura básica', function () {
    $exporter = new ExportadorTabular;

    $response = $exporter->download('pdf', 'ventas', ['Pedido'], [[1]]);

    expect($response->getContent())->toStartWith('%PDF-1.4')->toContain('%%EOF');
});

test('separa pedidos registrados de no cancelados y deja pendientes los cobros', function () {
    Schema::create('pedidos', function (Blueprint $table): void {
        $table->increments('id');
        $table->decimal('total', 10, 2);
        $table->string('estado');
        $table->timestamp('creado_en');
    });
    DB::table('pedidos')->insert([
        ['total' => 100, 'estado' => 'Pendiente', 'creado_en' => now()],
        ['total' => 200, 'estado' => 'Cancelado', 'creado_en' => now()],
        ['total' => 250, 'estado' => 'Cancelada', 'creado_en' => now()],
        ['total' => 300, 'estado' => 'Entregado', 'creado_en' => now()],
    ]);
    prepareGlobalDashboardAuthorization();
    $request = Request::create('/gestion/dashboard-global/ventas', 'GET', ['periodo' => 30]);
    $request->setUserResolver(static function (): User {
        $user = new User;
        $user->setRawAttributes(['id' => 1, 'rol' => 'administrador']);

        return $user;
    });

    $view = (new DashboardGlobalVentasController)->index($request, app(ServicioAutorizacion::class));

    expect($view->getData()['orderCount'])->toBe(4)
        ->and($view->getData()['nonCancelledCount'])->toBe(2)
        ->and($view->getData()['income'])->toBeNull()
        ->and($view->getData()['averageTicket'])->toBeNull()
        ->and($view->getData()['completedCount'])->toBeNull()
        ->and($view->getData()['dailyTotals'])->toBeEmpty();
});

test('usa siete días comerciales de Lima y excluye registros futuros', function () {
    $this->travelTo(Carbon::parse('2026-11-02 15:00:00', 'UTC'));
    try {
        Schema::create('pedidos', function (Blueprint $table): void {
            $table->increments('id');
            $table->decimal('total', 10, 2);
            $table->string('estado');
            $table->timestamp('creado_en');
        });
        DB::table('pedidos')->insert([
            ['total' => 1, 'estado' => 'Pendiente', 'creado_en' => '2026-10-27 04:59:59'],
            ['total' => 2, 'estado' => 'Pendiente', 'creado_en' => '2026-10-27 05:00:00'],
            ['total' => 3, 'estado' => 'Cancelado', 'creado_en' => '2026-11-02 15:00:00'],
            ['total' => 4, 'estado' => 'Entregado', 'creado_en' => '2026-11-02 15:00:01'],
        ]);
        prepareGlobalDashboardAuthorization();
        $request = Request::create('/gestion/dashboard-global/ventas', 'GET', ['periodo' => 7]);
        $user = new User;
        $user->setRawAttributes(['id' => 1, 'rol' => 'administrador']);
        $request->setUserResolver(static fn (): User => $user);

        $data = (new DashboardGlobalVentasController)->index($request, app(ServicioAutorizacion::class))->getData();

        expect($data['orderCount'])->toBe(2)
            ->and($data['nonCancelledCount'])->toBe(1);
    } finally {
        $this->travelBack();
    }
});

test('exporta los mismos pedidos filtrados sin presentar importes como cobros', function () {
    Schema::create('pedidos', function (Blueprint $table): void {
        $table->increments('id');
        $table->decimal('total', 10, 2);
        $table->string('estado');
        $table->string('canal')->nullable();
        $table->timestamp('creado_en');
    });
    Schema::create('registros_auditoria', function (Blueprint $table): void {
        $table->increments('id');
        $table->integer('usuario_id')->nullable();
        $table->string('accion');
        $table->string('entidad');
        $table->integer('entidad_id')->nullable();
        $table->text('valores_anteriores')->nullable();
        $table->text('valores_nuevos')->nullable();
        $table->string('direccion_ip')->nullable();
        $table->timestamp('creado_en');
    });
    DB::table('pedidos')->insert([
        ['total' => 100, 'estado' => 'Pendiente', 'canal' => 'ecommerce', 'creado_en' => now()],
        ['total' => 200, 'estado' => 'Entregado', 'canal' => 'mostrador', 'creado_en' => now()],
        ['total' => 300, 'estado' => "\n=1+1", 'canal' => 'ecommerce', 'creado_en' => now()],
        ['total' => 400, 'estado' => "\u{200B}@SUM(1)", 'canal' => 'ecommerce', 'creado_en' => now()],
    ]);
    prepareGlobalDashboardAuthorization();
    $user = new User;
    $user->setRawAttributes(['id' => 1, 'rol' => 'administrador']);
    $request = Request::create('/gestion/dashboard-global/ventas/exportar/csv', 'GET', ['periodo' => 7, 'canal' => 'ecommerce']);
    $request->setUserResolver(static fn (): User => $user);
    $controller = new DashboardGlobalVentasController;
    $authorization = app(ServicioAutorizacion::class);
    $view = $controller->index($request, $authorization);
    $response = $controller->export($request, 'csv', new ExportadorTabular, $authorization, new RegistradorAuditoria);
    ob_start();
    $response->sendContent();
    $csv = ob_get_clean();
    $excel = $controller->export($request, 'excel', new ExportadorTabular, $authorization, new RegistradorAuditoria);
    $archiveFile = tempnam(sys_get_temp_dir(), 'tc-dashboard-');
    file_put_contents($archiveFile, $excel->getContent());
    $archive = new ZipArchive;
    $archive->open($archiveFile);
    $sheet = $archive->getFromName('xl/worksheets/sheet1.xml');
    $archive->close();
    unlink($archiveFile);
    $pdf = $controller->export($request, 'pdf', new ExportadorTabular, $authorization, new RegistradorAuditoria);

    expect($view->getData()['orderCount'])->toBe(3)
        ->and(count(array_filter(explode("\n", trim($csv)))))->toBe(4)
        ->and($csv)->toContain('Importe del pedido PEN (no cobro)')->toContain('Pendiente')->not->toContain('Entregado')
        ->and($csv)->toContain("'=1+1")->toContain("'@SUM(1)")
        ->and($sheet)->toContain('r="16"')->toContain('r="18"')->toContain('r="19"')
        ->and($sheet)->toContain('Pendiente')->not->toContain('Entregado')->not->toContain('<f>')
        ->and(substr_count($pdf->getContent(), '/Type /Page /Parent'))->toBe(1)
        ->and($pdf->getContent())->toContain('Pendiente')->not->toContain('Entregado')
        ->and(DB::table('registros_auditoria')->count())->toBe(3);

    $invalidChannelRequest = Request::create('/gestion/dashboard-global/ventas', 'GET', ['periodo' => 7, 'canal' => "ecommerce' OR 1=1 --"]);
    $invalidChannelRequest->setUserResolver(static fn (): User => $user);

    expect(fn () => $controller->index($invalidChannelRequest, $authorization))
        ->toThrow(HttpException::class);
});

test('limita pedidos a sedes de empresas activas y aplica permisos de exportación', function () {
    Schema::create('empresas', function (Blueprint $table): void {
        $table->id();
        $table->boolean('activo');
    });
    Schema::create('sedes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->boolean('activa');
    });
    Schema::create('pedidos', function (Blueprint $table): void {
        $table->increments('id');
        $table->decimal('total', 10, 2);
        $table->string('estado');
        $table->timestamp('creado_en');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
    });
    Schema::create('registros_auditoria', function (Blueprint $table): void {
        $table->increments('id');
        $table->integer('usuario_id')->nullable();
        $table->string('accion');
        $table->string('entidad');
        $table->integer('entidad_id')->nullable();
        $table->text('valores_anteriores')->nullable();
        $table->text('valores_nuevos')->nullable();
        $table->string('direccion_ip')->nullable();
        $table->timestamp('creado_en');
    });
    Schema::create('roles', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo');
        $table->string('alcance_permitido');
        $table->boolean('activo');
    });
    Schema::create('modulos', function (Blueprint $table): void {
        $table->id();
        $table->boolean('habilitado');
    });
    Schema::create('pantallas', function (Blueprint $table): void {
        $table->id();
        $table->boolean('habilitada');
    });
    Schema::create('permisos', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo');
        $table->unsignedBigInteger('modulo_id');
        $table->unsignedBigInteger('pantalla_id')->nullable();
        $table->boolean('activo');
    });
    Schema::create('roles_permisos', function (Blueprint $table): void {
        $table->unsignedBigInteger('rol_id');
        $table->unsignedBigInteger('permiso_id');
        $table->string('alcance');
    });
    Schema::create('usuarios_roles', function (Blueprint $table): void {
        $table->unsignedBigInteger('usuario_id');
        $table->unsignedBigInteger('rol_id');
        $table->boolean('activo');
        $table->timestamp('vigente_hasta')->nullable();
    });
    Schema::create('usuarios_sedes_roles', function (Blueprint $table): void {
        $table->unsignedBigInteger('usuario_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('rol_id');
        $table->boolean('activo');
        $table->timestamp('vigente_hasta')->nullable();
    });
    Schema::create('menus', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo');
    });
    Schema::create('roles_menus', function (Blueprint $table): void {
        $table->id();
    });
    Schema::create('elementos_menu', function (Blueprint $table): void {
        $table->id();
    });
    DB::table('empresas')->insert([
        ['id' => 1, 'activo' => true],
        ['id' => 2, 'activo' => false],
    ]);
    DB::table('sedes')->insert([
        ['id' => 1, 'empresa_id' => 1, 'nombre' => 'Principal', 'activa' => true],
        ['id' => 2, 'empresa_id' => 2, 'nombre' => 'Otra sede', 'activa' => true],
    ]);
    DB::table('pedidos')->insert([
        ['total' => 10, 'estado' => 'Pendiente', 'creado_en' => now(), 'empresa_id' => 1, 'sede_id' => 1],
        ['total' => 20, 'estado' => 'Pendiente', 'creado_en' => now(), 'empresa_id' => 2, 'sede_id' => 2],
    ]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'administrador', 'alcance_permitido' => 'sede', 'activo' => true]);
    DB::table('modulos')->insert(['id' => 1, 'habilitado' => true]);
    DB::table('permisos')->insert([
        ['id' => 1, 'codigo' => 'gestion.panel.consultar', 'modulo_id' => 1, 'activo' => true],
        ['id' => 2, 'codigo' => 'gestion.reportes.exportar', 'modulo_id' => 1, 'activo' => true],
    ]);
    DB::table('roles_permisos')->insert([
        ['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'sede'],
        ['rol_id' => 1, 'permiso_id' => 2, 'alcance' => 'sede'],
    ]);
    DB::table('menus')->insert(['codigo' => 'administrador.sidebar']);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => 1, 'sede_id' => 1, 'rol_id' => 1, 'activo' => true]);

    $controller = new DashboardGlobalVentasController;
    $siteAdmin = new User;
    $siteAdmin->setRawAttributes(['id' => 1, 'rol' => 'administrador']);
    $siteRequest = Request::create('/gestion/dashboard-global/ventas', 'GET');
    $siteRequest->setUserResolver(static fn (): User => $siteAdmin);
    $siteView = $controller->index($siteRequest, new ServicioAutorizacion)->getData();

    expect($siteView['orderCount'])->toBe(1)
        ->and($siteView['sites']->pluck('id')->all())->toBe([1]);

    $siteExport = $controller->export($siteRequest, 'csv', new ExportadorTabular, new ServicioAutorizacion, new RegistradorAuditoria);
    ob_start();
    $siteExport->sendContent();
    $siteCsv = ob_get_clean();

    expect($siteCsv)->toContain('10.00')->not->toContain('20.00');

    $foreignSite = Request::create('/gestion/dashboard-global/ventas', 'GET', ['sede' => 2]);
    $foreignSite->setUserResolver(static fn (): User => $siteAdmin);
    expect(fn () => $controller->index($foreignSite, new ServicioAutorizacion))->toThrow(HttpException::class);

    DB::table('roles_permisos')->where('permiso_id', 2)->delete();
    $exportForbiddenRequest = Request::create('/gestion/dashboard-global/ventas/exportar/csv', 'GET');
    $exportForbiddenRequest->setUserResolver(static fn (): User => $siteAdmin);

    expect(fn () => $controller->export($exportForbiddenRequest, 'csv', new ExportadorTabular, new ServicioAutorizacion, new RegistradorAuditoria))
        ->toThrow(HttpException::class);
});

test('rechaza acceso al dashboard y a la exportación sin rol administrador', function () {
    $user = new User;
    $user->setRawAttributes(['id' => 3, 'rol' => 'ventas_minoristas']);
    $request = Request::create('/gestion/dashboard-global/ventas', 'GET');
    $request->setUserResolver(static fn (): User => $user);
    $controller = new DashboardGlobalVentasController;
    $authorization = new ServicioAutorizacion;

    expect(fn () => $controller->index($request, $authorization))
        ->toThrow(HttpException::class);
    expect(fn () => $controller->export($request, 'csv', new ExportadorTabular, $authorization, new RegistradorAuditoria))
        ->toThrow(HttpException::class);
});

test('rechaza exportaciones mayores al límite sin cargar todos los pedidos', function () {
    Schema::create('pedidos', function (Blueprint $table): void {
        $table->increments('id');
        $table->decimal('total', 10, 2);
        $table->string('estado');
        $table->timestamp('creado_en');
    });
    $record = ['total' => 1, 'estado' => 'Pendiente', 'creado_en' => now()->toDateTimeString()];
    for ($batch = 0; $batch < 10; $batch++) {
        DB::table('pedidos')->insert(array_fill(0, 500, $record));
    }
    DB::table('pedidos')->insert($record);
    prepareGlobalDashboardAuthorization();
    $user = new User;
    $user->setRawAttributes(['id' => 1, 'rol' => 'administrador']);
    $request = Request::create('/gestion/dashboard-global/ventas/exportar/csv', 'GET', ['periodo' => 7]);
    $request->setUserResolver(static fn (): User => $user);

    expect(fn () => (new DashboardGlobalVentasController)->export($request, 'csv', new ExportadorTabular, new ServicioAutorizacion, new RegistradorAuditoria))
        ->toThrow(HttpException::class);
});
