<?php

use App\Http\Controllers\Gestion\Analitica\DashboardGlobal\DashboardGlobalPedidosController;
use App\Models\User;
use App\Servicios\Compartidos\Auditoria\RegistradorAuditoria;
use App\Servicios\Compartidos\Exportacion\ExportadorTabular;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

/**
 * Ejecuta la operación preparePedidosDashboardAuthorization definida por la clase DashboardGlobalPedidosTest.
 *
 * @return void Resultado de la operación.
 */
function preparePedidosDashboardAuthorization(): void
{
    Schema::create('empresas', function (Blueprint $table): void {
        $table->id();
        $table->boolean('activo');
    });
    if (! Schema::hasTable('sedes')) {
        Schema::create('sedes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nombre');
            $table->boolean('activa');
        });
    }
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
        ['id' => 2, 'codigo' => 'gestion.analitica.reportes.exportar', 'modulo_id' => 1, 'activo' => true],
        ['id' => 3, 'codigo' => 'gestion.seguridad.consultar', 'modulo_id' => 1, 'activo' => true],
        ['id' => 4, 'codigo' => 'gestion.organizacion.consultar', 'modulo_id' => 1, 'activo' => true],
    ]);
    DB::table('roles_permisos')->insert([
        ['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'empresa'],
        ['rol_id' => 1, 'permiso_id' => 2, 'alcance' => 'empresa'],
        ['rol_id' => 1, 'permiso_id' => 3, 'alcance' => 'empresa'],
        ['rol_id' => 1, 'permiso_id' => 4, 'alcance' => 'empresa'],
    ]);
    DB::table('usuarios_roles')->insert(['usuario_id' => 1, 'rol_id' => 1, 'activo' => true]);
}

test('filtra pedidos reales por estado y sede en una base temporal sqlite', function () {
    Schema::create('usuarios', function (Blueprint $table): void {
        $table->id();
        $table->string('nombre');
    });
    Schema::create('sedes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->boolean('activa');
    });
    Schema::create('pedidos', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedBigInteger('usuario_id');
        $table->unsignedBigInteger('sede_id');
        $table->string('canal');
        $table->string('estado');
        $table->decimal('total', 10, 2);
        $table->timestamp('creado_en');
    });
    DB::table('usuarios')->insert(['id' => 1, 'nombre' => 'Prueba temporal']);
    DB::table('sedes')->insert([
        ['id' => 1, 'empresa_id' => 1, 'nombre' => 'Sede 1', 'activa' => true],
        ['id' => 2, 'empresa_id' => 1, 'nombre' => 'Sede 2', 'activa' => true],
    ]);
    DB::table('pedidos')->insert([
        ['usuario_id' => 1, 'sede_id' => 1, 'canal' => 'ecommerce', 'estado' => 'Pendiente', 'total' => 40, 'creado_en' => now()],
        ['usuario_id' => 1, 'sede_id' => 2, 'canal' => 'ecommerce', 'estado' => 'Pendiente', 'total' => 50, 'creado_en' => now()],
        ['usuario_id' => 1, 'sede_id' => 1, 'canal' => 'ecommerce', 'estado' => 'Pagado', 'total' => 60, 'creado_en' => now()],
    ]);
    preparePedidosDashboardAuthorization();
    $request = Request::create('/gestion/dashboard-global/pedidos', 'GET', ['periodo' => 7, 'sede' => 1, 'estado' => 'Pendiente']);
    $request->setUserResolver(static function (): User {
        $user = new User;
        $user->setRawAttributes(['id' => 1, 'rol' => 'administrador']);

        return $user;
    });

    $view = (new DashboardGlobalPedidosController)->index($request, app(ServicioAutorizacion::class));

    expect($view->getData()['orders'])->toHaveCount(1)
        ->and($view->getData()['orders']->first()->total)->toBe('40.00')
        ->and($view->getData()['byStatus']['Pendiente'])->toBe(1);
});

test('restringe el dashboard al alcance de sede asignado al administrador', function () {
    Schema::create('usuarios', function (Blueprint $table): void {
        $table->id();
        $table->string('nombre');
    });
    Schema::create('sedes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->boolean('activa');
    });
    Schema::create('pedidos', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedBigInteger('usuario_id');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->string('estado');
        $table->decimal('total', 10, 2);
        $table->timestamp('creado_en');
    });
    preparePedidosDashboardAuthorization();
    DB::table('roles')->update(['alcance_permitido' => 'sede']);
    DB::table('usuarios_roles')->delete();
    DB::table('roles_permisos')->update(['alcance' => 'sede']);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => 1, 'sede_id' => 1, 'rol_id' => 1, 'activo' => true]);
    DB::table('sedes')->insert([
        ['id' => 1, 'empresa_id' => 1, 'nombre' => 'Sede asignada', 'activa' => true],
        ['id' => 2, 'empresa_id' => 1, 'nombre' => 'Sede ajena', 'activa' => true],
    ]);
    DB::table('usuarios')->insert(['id' => 1, 'nombre' => 'Administradora de sede']);
    DB::table('pedidos')->insert([
        ['usuario_id' => 1, 'empresa_id' => 1, 'sede_id' => 1, 'estado' => 'Pendiente', 'total' => 40, 'creado_en' => now()],
        ['usuario_id' => 1, 'empresa_id' => 1, 'sede_id' => 2, 'estado' => 'Pendiente', 'total' => 50, 'creado_en' => now()],
    ]);
    $request = Request::create('/gestion/dashboard-global/pedidos', 'GET');
    $request->setUserResolver(static function (): User {
        $user = new User;
        $user->setRawAttributes(['id' => 1, 'rol' => 'administrador']);

        return $user;
    });

    $view = (new DashboardGlobalPedidosController)->index($request, new ServicioAutorizacion)->getData();

    expect($view['orders'])->toHaveCount(1)
        ->and($view['orders']->first()->sede_id)->toBe(1)
        ->and($view['sites']->pluck('id')->all())->toBe([1]);
});

test('exporta pedidos y registra la operación en auditoría temporal', function () {
    Schema::create('pedidos', function (Blueprint $table): void {
        $table->increments('id');
        $table->string('estado');
        $table->decimal('total', 10, 2);
        $table->timestamp('creado_en');
    });
    Schema::create('registros_auditoria', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->string('accion');
        $table->string('entidad');
        $table->integer('entidad_id')->nullable();
        $table->json('valores_anteriores')->nullable();
        $table->json('valores_nuevos')->nullable();
        $table->string('direccion_ip')->nullable();
        $table->timestamp('creado_en');
    });
    DB::table('pedidos')->insert(['estado' => 'Pagado', 'total' => 40, 'creado_en' => now()]);
    preparePedidosDashboardAuthorization();
    $request = Request::create('/gestion/dashboard-global/pedidos/exportar/pdf', 'GET', ['periodo' => 7]);
    $request->setUserResolver(static function (): User {
        $user = new User;
        $user->setRawAttributes(['id' => 1, 'rol' => 'administrador']);

        return $user;
    });

    $response = (new DashboardGlobalPedidosController)->export($request, 'pdf', new ExportadorTabular, app(ServicioAutorizacion::class), new RegistradorAuditoria);

    expect($response->getContent())->toStartWith('%PDF-1.4')
        ->and(DB::table('registros_auditoria')->where('accion', 'exportar')->count())->toBe(1);
});
