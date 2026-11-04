<?php

use App\Http\Controllers\Gestion\Compras\CompraController;
use App\Http\Controllers\Gestion\DevolucionesGarantias\DevolucionGarantiaController;
use App\Http\Controllers\Gestion\Ventas\VentaController;
use App\Http\Controllers\Paneles\ClienteMinorista\DashboardController as DashboardClienteMinorista;
use App\Http\Controllers\Paneles\ResolutorDashboard;
use App\Http\Middleware\RegistrarSesionUsuario;
use App\Http\Requests\ActualizarVentaRequest;
use App\Http\Requests\AjustarInventarioRequest;
use App\Http\Requests\FiltroVentaRequest;
use App\Http\Requests\GuardarVentaRequest;
use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Catalogo\Producto;
use App\Models\Comercio\DevolucionGarantia;
use App\Models\Comercio\Pedido;
use App\Models\Comercio\Venta;
use App\Models\Compras\Compra;
use App\Models\User;
use App\Policies\PedidoPolicy;
use App\Servicios\ConsultaVentas;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioCatalogo;
use App\Servicios\ServicioCompras;
use App\Servicios\ServicioInventario;
use App\Servicios\ServicioMenuDinamico;
use App\Servicios\ServicioPedidos;
use App\Servicios\ServicioVentas;
use Database\Seeders\RbacInicialSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
    DB::purge('sqlite');
    expect(DB::connection()->getDriverName())->toBe('sqlite')
        ->and(DB::connection()->getDatabaseName())->toBe(':memory:');

    Schema::create('usuarios', function (Blueprint $table): void {
        $table->id();
        $table->string('nombre');
        $table->string('correo')->unique();
        $table->string('resumen_contrasena');
        $table->string('rol')->nullable();
        $table->string('token_recordatorio')->nullable();
        $table->boolean('activo')->default(true);
        $table->boolean('acceso_controlado')->default(true);
        $table->timestamp('bloqueado_hasta')->nullable();
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('roles', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo')->unique();
        $table->string('nombre');
        $table->string('descripcion')->nullable();
        $table->string('alcance_permitido');
        $table->boolean('es_sistema')->default(false);
        $table->boolean('activo')->default(true);
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('permisos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('modulo_id');
        $table->unsignedBigInteger('pantalla_id')->nullable();
        $table->string('codigo')->unique();
        $table->string('accion')->default('consultar');
        $table->string('nombre')->default('Permiso');
        $table->boolean('activo')->default(true);
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('roles_permisos', function (Blueprint $table): void {
        $table->unsignedBigInteger('rol_id');
        $table->unsignedBigInteger('permiso_id');
        $table->string('alcance');
        $table->timestamp('creado_en')->nullable();
        $table->unique(['rol_id', 'permiso_id', 'alcance']);
    });
    Schema::create('usuarios_roles', function (Blueprint $table): void {
        $table->unsignedBigInteger('usuario_id');
        $table->unsignedBigInteger('rol_id');
        $table->unsignedBigInteger('asignado_por_usuario_id')->nullable();
        $table->timestamp('asignado_en')->nullable();
        $table->timestamp('vigente_hasta')->nullable();
        $table->boolean('activo')->default(true);
    });
    Schema::create('usuarios_sedes_roles', function (Blueprint $table): void {
        $table->unsignedBigInteger('usuario_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('rol_id');
        $table->unsignedBigInteger('asignado_por_usuario_id')->nullable();
        $table->timestamp('asignado_en')->nullable();
        $table->timestamp('vigente_hasta')->nullable();
        $table->boolean('activo')->default(true);
    });
    Schema::create('empresas', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo');
        $table->string('razon_social')->default('Empresa de prueba');
        $table->boolean('activo')->default(true);
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('sedes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('codigo')->default('sede');
        $table->string('nombre')->default('Sede de prueba');
        $table->boolean('activa')->default(true);
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('pedidos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('usuario_id');
        $table->unsignedBigInteger('empresa_id')->nullable();
        $table->unsignedBigInteger('sede_id')->nullable();
    });
    Schema::create('modulos', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo')->nullable()->unique();
        $table->string('nombre')->default('Módulo');
        $table->string('descripcion')->nullable();
        $table->string('icono')->nullable();
        $table->unsignedInteger('orden')->default(0);
        $table->boolean('habilitado')->default(true);
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('pantallas', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('modulo_id');
        $table->string('codigo');
        $table->string('nombre')->default('Pantalla');
        $table->string('ruta')->nullable();
        $table->string('icono')->nullable();
        $table->boolean('habilitada')->default(true);
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('menus', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo')->unique();
        $table->string('nombre');
        $table->string('contexto');
        $table->boolean('habilitado')->default(true);
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('roles_menus', function (Blueprint $table): void {
        $table->unsignedBigInteger('rol_id');
        $table->unsignedBigInteger('menu_id');
        $table->timestamp('creado_en')->nullable();
        $table->unique(['rol_id', 'menu_id']);
    });
    Schema::create('elementos_menu', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('menu_id');
        $table->unsignedBigInteger('padre_id')->nullable();
        $table->unsignedBigInteger('pantalla_id')->nullable();
        $table->unsignedBigInteger('permiso_id')->nullable();
        $table->string('codigo');
        $table->string('etiqueta');
        $table->string('icono')->nullable();
        $table->string('tipo');
        $table->unsignedInteger('orden');
        $table->boolean('habilitado')->default(true);
        $table->timestamp('actualizado_en')->nullable();
        $table->unique(['menu_id', 'codigo']);
    });
    Schema::create('versiones_inicializacion_control_acceso', function (Blueprint $table): void {
        $table->string('codigo')->primary();
        $table->timestamp('aplicado_en');
    });
    Schema::create('registros_auditoria', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->string('accion');
        $table->string('entidad');
        $table->bigInteger('entidad_id')->nullable();
        $table->text('valores_anteriores')->nullable();
        $table->text('valores_nuevos')->nullable();
        $table->string('direccion_ip')->nullable();
        $table->timestamp('creado_en')->nullable();
    });
});

it('authenticates using the Spanish password and remember-token columns', function (): void {
    $user = User::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.test', 'password' => 'ClaveSegura123']);

    expect(Hash::check('ClaveSegura123', $user->resumen_contrasena))->toBeTrue()
        ->and(Auth::validate(['correo' => 'prueba@example.test', 'password' => 'ClaveSegura123']))->toBeTrue()
        ->and(Auth::validate(['correo' => 'prueba@example.test', 'password' => 'incorrecta']))->toBeFalse()
        ->and($user->getRememberTokenName())->toBe('token_recordatorio')
        ->and($user->toArray())->not->toHaveKey('resumen_contrasena');

    $hash = Hash::make('NuevaClave123');
    $user->update(['password' => $hash]);
    expect($user->fresh()->resumen_contrasena)->toBe($hash)
        ->and(Auth::validate(['correo' => 'prueba@example.test', 'password' => 'NuevaClave123']))->toBeTrue();
});

it('signs in an active user and rejects a blocked user against the Spanish schema', function (): void {
    $user = User::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.test', 'password' => 'ClaveSegura123']);
    $this->post(route('login.store'), ['correo' => $user->correo, 'password' => 'ClaveSegura123'])
        ->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);

    Auth::logout();
    $user->update(['activo' => false]);
    $this->post(route('login.store'), ['correo' => $user->correo, 'password' => 'ClaveSegura123'])
        ->assertSessionHasErrors('correo');
    $this->assertGuest();
});

it('updates the authenticated profile and password using the imported user columns', function (): void {
    $user = User::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.test', 'password' => 'ClaveSegura123']);

    $this->actingAs($user)->put(route('account.profile'), [
        'nombre' => 'Nombre actualizado', 'correo' => 'nuevo@example.test',
    ])->assertRedirect();
    expect($user->fresh()->nombre)->toBe('Nombre actualizado')
        ->and($user->fresh()->correo)->toBe('nuevo@example.test');

    $this->put(route('account.password'), [
        'password_actual' => 'ClaveSegura123', 'password' => 'NuevaClave123', 'password_confirmation' => 'NuevaClave123',
    ])->assertRedirect();
    expect(Hash::check('NuevaClave123', $user->fresh()->resumen_contrasena))->toBeTrue();
});

it('records and revokes an application session using the Spanish schema', function (): void {
    Schema::create('sesiones_usuarios', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('usuario_id');
        $table->string('resumen_identificador')->unique();
        $table->string('direccion_ip')->nullable();
        $table->text('agente_usuario')->nullable();
        $table->timestamp('ultimo_acceso')->nullable();
        $table->timestamp('revocada_en')->nullable();
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    $user = User::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.test', 'password' => 'ClaveSegura123']);
    $session = new Store('prueba', new ArraySessionHandler(120));
    $session->start();
    $request = Request::create('/cuenta', 'GET');
    $request->setLaravelSession($session);
    $request->setUserResolver(fn (): User => $user);
    $middleware = app(RegistrarSesionUsuario::class);
    $next = fn (): Response => new Response('ok');

    expect($middleware->handle($request, $next)->getStatusCode())->toBe(200)
        ->and(DB::table('sesiones_usuarios')->where('usuario_id', $user->id)->count())->toBe(1);

    DB::table('sesiones_usuarios')->update(['revocada_en' => now()]);
    expect($middleware->handle($request, $next)->getStatusCode())->toBe(302);
});

it('loads catalog and order relations from the Spanish table names', function (): void {
    Schema::create('productos', function (Blueprint $table): void {
        $table->id();
        $table->string('nombre');
    });
    Schema::create('proveedores', function (Blueprint $table): void {
        $table->id();
        $table->string('nombre');
    });
    Schema::create('productos_proveedores', function (Blueprint $table): void {
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('proveedor_id');
    });
    Schema::create('variantes_productos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('producto_id');
        $table->string('nombre_color');
        $table->integer('existencias');
    });
    Schema::create('imagenes_variantes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('variante_id');
        $table->string('ruta_imagen');
        $table->integer('orden');
    });
    Schema::create('referencias_imagenes_productos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('producto_id');
        $table->string('ruta_imagen');
        $table->integer('orden');
    });
    Schema::create('caracteristicas_productos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('producto_id');
        $table->string('nombre');
        $table->string('valor');
    });
    Schema::create('detalles_pedidos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('pedido_id');
        $table->unsignedBigInteger('producto_id');
        $table->integer('cantidad');
        $table->decimal('precio_unitario', 10, 2);
    });
    DB::table('productos')->insert(['id' => 1, 'nombre' => 'Producto de prueba']);
    DB::table('proveedores')->insert(['id' => 1, 'nombre' => 'Proveedor de prueba']);
    DB::table('productos_proveedores')->insert(['producto_id' => 1, 'proveedor_id' => 1]);
    DB::table('variantes_productos')->insert(['id' => 1, 'producto_id' => 1, 'nombre_color' => 'Azul', 'existencias' => 2]);
    DB::table('imagenes_variantes')->insert(['variante_id' => 1, 'ruta_imagen' => 'variante.png', 'orden' => 0]);
    DB::table('referencias_imagenes_productos')->insert(['producto_id' => 1, 'ruta_imagen' => 'producto.png', 'orden' => 0]);
    DB::table('caracteristicas_productos')->insert(['producto_id' => 1, 'nombre' => 'Color', 'valor' => 'Azul']);
    DB::table('pedidos')->insert(['id' => 1, 'usuario_id' => 1]);
    DB::table('detalles_pedidos')->insert(['pedido_id' => 1, 'producto_id' => 1, 'cantidad' => 2, 'precio_unitario' => 10]);

    $product = Producto::with(['suppliers', 'variants.images', 'images', 'features'])->findOrFail(1);
    $order = Pedido::with('details.product')->findOrFail(1);

    expect($product->suppliers->sole()->nombre)->toBe('Proveedor de prueba')
        ->and($product->variants->sole()->images->sole()->ruta_imagen)->toBe('variante.png')
        ->and($product->images->sole()->ruta_imagen)->toBe('producto.png')
        ->and($product->features->sole()->valor)->toBe('Azul')
        ->and($order->details->sole()->product->nombre)->toBe('Producto de prueba');
});

it('denies a legacy administrator when RBAC is not initialized', function (): void {
    $user = User::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.test', 'password' => 'ClaveSegura123', 'rol' => 'administrador']);
    $authorization = app(ServicioAutorizacion::class);

    expect($authorization->hasRole($user, 'administrador'))->toBeFalse()
        ->and($authorization->allows($user, 'gestion.pedidos.consultar'))->toBeFalse()
        ->and($authorization->allowsGlobal($user, 'gestion.pedidos.consultar'))->toBeFalse();

    $this->actingAs($user)->get(route('orders.index'))->assertForbidden();
    expect(fn () => app(ServicioPedidos::class)->checkout($user, [1 => 1], null, null, str_repeat('a', 64)))
        ->toThrow(HttpException::class);
});

it('selects the dashboard from assigned roles instead of the legacy role field', function (): void {
    $user = User::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.test', 'password' => 'ClaveSegura123', 'rol' => 'administrador']);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('permisos')->insert(['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.pedidos.consultar']);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'cliente_minorista', 'nombre' => 'Cliente', 'alcance_permitido' => 'propio']);
    DB::table('usuarios_roles')->insert(['usuario_id' => $user->id, 'rol_id' => 1]);

    expect(app(ResolutorDashboard::class)->for($user))->toBeInstanceOf(DashboardClienteMinorista::class);
});

it('initializes RBAC idempotently without assigning any roles to existing users', function (): void {
    Schema::create('almacenes', fn (Blueprint $table) => $table->id());
    $user = User::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.test', 'password' => 'ClaveSegura123', 'rol' => 'administrador']);

    app(RbacInicialSeeder::class)->run();
    $permissionCount = DB::table('permisos')->count();
    app(RbacInicialSeeder::class)->run();

    expect(DB::table('roles')->count())->toBe(7)
        ->and($permissionCount)->toBeGreaterThan(0)
        ->and(DB::table('permisos')->count())->toBe($permissionCount)
        ->and(DB::table('usuarios_roles')->count())->toBe(0)
        ->and(DB::table('usuarios_sedes_roles')->count())->toBe(0)
        ->and(app(ServicioAutorizacion::class)->hasRole($user, 'administrador'))->toBeFalse();
});

it('previsualizes and initializes RBAC with existing roles and an assigned administrator', function (): void {
    Schema::create('almacenes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('sede_id');
        $table->string('codigo');
        $table->string('nombre');
        $table->boolean('activo')->default(true);
    });

    $alcancesPorRol = [
        'administrador' => 'empresa',
        'compras_logistica' => 'sede',
        'ventas_mayoristas' => 'sede',
        'ventas_minoristas' => 'sede',
        'marketing' => 'sede',
        'cliente_mayorista' => 'propio',
        'cliente_minorista' => 'propio',
    ];
    $identificadoresRoles = [];

    foreach (User::ROLES as $codigoRol) {
        $identificadoresRoles[$codigoRol] = DB::table('roles')->insertGetId([
            'codigo' => $codigoRol,
            'nombre' => $codigoRol,
            'alcance_permitido' => $alcancesPorRol[$codigoRol],
            'es_sistema' => true,
            'activo' => true,
        ]);
    }

    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'EMPRESA_QA', 'razon_social' => 'Empresa sintética', 'activo' => true]);
    DB::table('sedes')->insert(['id' => 1, 'empresa_id' => 1, 'codigo' => 'SEDE_QA', 'nombre' => 'Sede sintética', 'activa' => true]);
    DB::table('almacenes')->insert(['id' => 1, 'sede_id' => 1, 'codigo' => 'ALMACEN_QA', 'nombre' => 'Almacén sintético', 'activo' => true]);
    $administrador = User::create([
        'nombre' => 'Administrador de prueba',
        'correo' => 'admin@example.test',
        'password' => 'ClaveSegura123',
        'acceso_controlado' => true,
    ]);
    DB::table('usuarios_roles')->insert([
        'usuario_id' => $administrador->id,
        'rol_id' => $identificadoresRoles['administrador'],
        'asignado_en' => now(),
        'activo' => true,
    ]);

    $this->artisan('technology-cell:inicializar-rbac')
        ->expectsOutputToContain('La vista previa no realiza escrituras.')
        ->assertExitCode(0);

    expect(DB::table('roles')->count())->toBe(7)
        ->and(DB::table('permisos')->count())->toBe(0)
        ->and(DB::table('usuarios_roles')->count())->toBe(1)
        ->and(DB::table('registros_auditoria')->count())->toBe(0);

    $this->artisan('technology-cell:inicializar-rbac', ['--aplicar' => true])
        ->expectsConfirmation('¿Autoriza inicializar el catálogo RBAC en esta base de datos?', 'yes')
        ->assertExitCode(0);

    $autorizacion = app(ServicioAutorizacion::class);
    $ubicacion = $autorizacion->operationLocation($administrador);
    $menu = app(ServicioMenuDinamico::class)->paraUsuario($administrador);
    $rutasMenu = collect($menu['grupos'])->flatMap(fn (array $grupo): array => $grupo['items'])
        ->pluck('route')
        ->all();

    expect(DB::table('roles')->pluck('id', 'codigo')->sortKeys()->all())->toBe(collect($identificadoresRoles)->sortKeys()->all());
    expect(DB::table('usuarios_roles')->count())->toBe(1);
    expect(DB::table('usuarios_sedes_roles')->count())->toBe(0);
    expect(DB::table('roles_permisos')->where('rol_id', $identificadoresRoles['administrador'])->count())->toBeGreaterThan(0);
    expect(DB::table('roles_menus')->where('rol_id', $identificadoresRoles['administrador'])->count())->toBe(1);
    expect(DB::table('registros_auditoria')->where('accion', 'catalogo_rbac_inicializado')->count())->toBe(1);
    expect($autorizacion->hasRole($administrador, 'administrador'))->toBeTrue();
    expect($autorizacion->allows($administrador, 'gestion.categorias.consultar'))->toBeTrue();
    expect($autorizacion->allowsGlobal($administrador, 'gestion.categorias.consultar'))->toBeTrue();
    expect($ubicacion)->toBe(['empresa_id' => 1, 'sede_id' => 1, 'almacen_id' => 1]);
    expect($rutasMenu)->toContain('management.categories', 'management.products', 'management.inventory', 'management.sales');

    $this->post(route('login.store'), [
        'correo' => 'admin@example.test',
        'password' => 'ClaveSegura123',
    ])->assertRedirect(route('management.dashboard'));
});

it('refuses initialization over an unversioned RBAC catalog', function (): void {
    Schema::create('almacenes', fn (Blueprint $table) => $table->id());
    DB::table('roles')->insert(['codigo' => 'marketing', 'nombre' => 'Marketing', 'alcance_permitido' => 'sede']);

    expect(fn () => app(RbacInicialSeeder::class)->run())->toThrow(RuntimeException::class)
        ->and(DB::table('roles')->count())->toBe(1)
        ->and(DB::table('versiones_inicializacion_control_acceso')->count())->toBe(0);
});

it('initializes an empty installation only with explicit confirmation and never duplicates it', function (): void {
    Schema::create('almacenes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('sede_id');
        $table->string('codigo');
        $table->string('nombre');
        $table->boolean('activo')->default(true);
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });

    $this->artisan('technology-cell:inicializar')->assertExitCode(1);
    $this->artisan('technology-cell:inicializar', ['--vista-previa' => true, '--confirmar' => true])->assertExitCode(0);
    expect(DB::table('usuarios')->count())->toBe(0);

    $this->artisan('technology-cell:inicializar', ['--confirmar' => true])
        ->expectsQuestion('Código de empresa', 'empresa_prueba')
        ->expectsQuestion('Razón social', 'Empresa de prueba')
        ->expectsQuestion('Código de sede', 'sede_prueba')
        ->expectsQuestion('Nombre de sede', 'Sede de prueba')
        ->expectsQuestion('Código de almacén', 'almacen_prueba')
        ->expectsQuestion('Nombre de almacén', 'Almacén de prueba')
        ->expectsQuestion('Nombre del administrador inicial', 'Administrador de prueba')
        ->expectsQuestion('Correo del administrador inicial', 'admin@example.test')
        ->expectsQuestion('Contraseña del administrador inicial', 'ClaveSegura123')
        ->expectsQuestion('Repite la contraseña', 'ClaveSegura123')
        ->expectsConfirmation('¿Crear estos registros en la base configurada?', 'no')
        ->assertExitCode(1);
    expect(DB::table('usuarios')->count())->toBe(0)->and(DB::table('roles')->count())->toBe(0);

    $this->artisan('technology-cell:inicializar', ['--confirmar' => true])
        ->expectsQuestion('Código de empresa', 'empresa_prueba')
        ->expectsQuestion('Razón social', 'Empresa de prueba')
        ->expectsQuestion('Código de sede', 'sede_prueba')
        ->expectsQuestion('Nombre de sede', 'Sede de prueba')
        ->expectsQuestion('Código de almacén', 'almacen_prueba')
        ->expectsQuestion('Nombre de almacén', 'Almacén de prueba')
        ->expectsQuestion('Nombre del administrador inicial', 'Administrador de prueba')
        ->expectsQuestion('Correo del administrador inicial', 'admin@example.test')
        ->expectsQuestion('Contraseña del administrador inicial', 'a1')
        ->expectsQuestion('Repite la contraseña', 'a1')
        ->expectsOutputToContain('La contraseña debe tener al menos 12 caracteres.')
        ->assertExitCode(1);
    expect(DB::table('usuarios')->count())->toBe(0)->and(DB::table('roles')->count())->toBe(0);

    $this->artisan('technology-cell:inicializar', ['--confirmar' => true])
        ->expectsQuestion('Código de empresa', 'empresa_prueba')
        ->expectsQuestion('Razón social', 'Empresa de prueba')
        ->expectsQuestion('Código de sede', 'sede_prueba')
        ->expectsQuestion('Nombre de sede', 'Sede de prueba')
        ->expectsQuestion('Código de almacén', 'almacen_prueba')
        ->expectsQuestion('Nombre de almacén', 'Almacén de prueba')
        ->expectsQuestion('Nombre del administrador inicial', 'Administrador de prueba')
        ->expectsQuestion('Correo del administrador inicial', 'admin@example.test')
        ->expectsQuestion('Contraseña del administrador inicial', 'ClaveSegura123')
        ->expectsQuestion('Repite la contraseña', 'ClaveSegura123')
        ->expectsConfirmation('¿Crear estos registros en la base configurada?', 'yes')
        ->assertExitCode(0);

    $user = User::where('correo', 'admin@example.test')->firstOrFail();
    $hash = $user->resumen_contrasena;
    expect(DB::table('empresas')->count())->toBe(1)
        ->and(DB::table('sedes')->count())->toBe(1)
        ->and(DB::table('almacenes')->count())->toBe(1)
        ->and(DB::table('usuarios_roles')->where('usuario_id', $user->id)->count())->toBe(1)
        ->and(app(ServicioAutorizacion::class)->hasRole($user, 'administrador'))->toBeTrue();

    $this->artisan('technology-cell:inicializar', ['--confirmar' => true])->assertExitCode(0);
    expect(DB::table('usuarios')->count())->toBe(1)
        ->and($user->fresh()->resumen_contrasena)->toBe($hash)
        ->and(DB::table('registros_auditoria')->where('accion', 'instalacion.inicializada')->count())->toBe(1);
});

it('refuses first-time initialization when an account already exists', function (): void {
    Schema::create('almacenes', fn (Blueprint $table) => $table->id());
    User::create(['nombre' => 'Existente', 'correo' => 'existente@example.test', 'password' => 'ClaveSegura123']);

    $this->artisan('technology-cell:inicializar', ['--confirmar' => true])->assertExitCode(1);
    expect(DB::table('roles')->count())->toBe(0)
        ->and(DB::table('usuarios_roles')->count())->toBe(0)
        ->and(DB::table('empresas')->count())->toBe(0);
});

it('rolls back the entire initialization if its audit record cannot be created', function (): void {
    Schema::create('almacenes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('sede_id');
        $table->string('codigo');
        $table->string('nombre');
        $table->boolean('activo')->default(true);
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    RegistroAuditoria::creating(fn () => throw new RuntimeException('Fallo sintético de auditoría.'));

    $this->artisan('technology-cell:inicializar', ['--confirmar' => true])
        ->expectsQuestion('Código de empresa', 'empresa_prueba')
        ->expectsQuestion('Razón social', 'Empresa de prueba')
        ->expectsQuestion('Código de sede', 'sede_prueba')
        ->expectsQuestion('Nombre de sede', 'Sede de prueba')
        ->expectsQuestion('Código de almacén', 'almacen_prueba')
        ->expectsQuestion('Nombre de almacén', 'Almacén de prueba')
        ->expectsQuestion('Nombre del administrador inicial', 'Administrador de prueba')
        ->expectsQuestion('Correo del administrador inicial', 'admin@example.test')
        ->expectsQuestion('Contraseña del administrador inicial', 'ClaveSegura123')
        ->expectsQuestion('Repite la contraseña', 'ClaveSegura123')
        ->expectsConfirmation('¿Crear estos registros en la base configurada?', 'yes')
        ->expectsOutputToContain('Etapa: auditoría; tipo: RuntimeException; código: sin código.')
        ->assertExitCode(1);

    expect(DB::table('usuarios')->count())->toBe(0)
        ->and(DB::table('roles')->count())->toBe(0)
        ->and(DB::table('empresas')->count())->toBe(0)
        ->and(DB::table('versiones_inicializacion_control_acceso')->count())->toBe(0);
    RegistroAuditoria::flushEventListeners();
});

it('assigns and revokes a site role with audited snapshots', function (): void {
    Schema::create('almacenes', fn (Blueprint $table) => $table->id());
    app(RbacInicialSeeder::class)->run();
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'empresa_prueba']);
    DB::table('sedes')->insert(['id' => 1, 'empresa_id' => 1]);
    $admin = User::create(['nombre' => 'Administrador', 'correo' => 'admin@example.test', 'password' => 'ClaveSegura123']);
    $target = User::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.test', 'password' => 'ClaveSegura123']);
    $adminRole = DB::table('roles')->where('codigo', 'administrador')->value('id');
    $siteRole = DB::table('roles')->where('codigo', 'marketing')->value('id');
    DB::table('usuarios_roles')->insert(['usuario_id' => $admin->id, 'rol_id' => $adminRole]);

    $this->actingAs($admin)->post(route('management.user_assignments.store', $target), [
        'rol_id' => $siteRole, 'alcance' => 'sede', 'sede_id' => 1,
    ])->assertRedirect();

    expect(DB::table('usuarios_sedes_roles')->where('usuario_id', $target->id)->value('activo'))->toBe(1);
    $createdAudit = json_decode(DB::table('registros_auditoria')->where('accion', 'asignar_rol_usuario')->value('valores_nuevos'), true);
    expect($createdAudit['asignaciones'][0]['sede_id'])->toBe(1)
        ->and($createdAudit['asignaciones'][0]['activo'])->toBeTrue();

    $this->delete(route('management.user_assignments.revoke_site', [$target->id, $siteRole, 1]))->assertRedirect();
    expect(DB::table('usuarios_sedes_roles')->where('usuario_id', $target->id)->value('activo'))->toBe(0);
    $revokedAudit = json_decode(DB::table('registros_auditoria')->where('accion', 'revocar_rol_usuario')->value('valores_nuevos'), true);
    expect($revokedAudit['asignaciones'][0]['sede_id'])->toBe(1)
        ->and($revokedAudit['asignaciones'][0]['activo'])->toBeFalse();

    $this->delete(route('management.user_assignments.revoke_site', [$target->id, $siteRole, 1]))->assertStatus(422);
});

it('updates a non-system role permission set without modifying the administrator role', function (): void {
    Schema::create('almacenes', fn (Blueprint $table) => $table->id());
    app(RbacInicialSeeder::class)->run();
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'empresa_prueba']);
    $admin = User::create(['nombre' => 'Administrador', 'correo' => 'admin@example.test', 'password' => 'ClaveSegura123']);
    $adminRole = DB::table('roles')->where('codigo', 'administrador')->value('id');
    $targetRole = DB::table('roles')->insertGetId(['codigo' => 'rol_prueba', 'nombre' => 'Rol de prueba', 'alcance_permitido' => 'sede']);
    $permission = DB::table('permisos')->where('codigo', 'gestion.campanias.consultar')->value('id');
    DB::table('usuarios_roles')->insert(['usuario_id' => $admin->id, 'rol_id' => $adminRole]);

    $this->actingAs($admin)->put(route('management.roles_permissions.permissions', $targetRole), [
        'alcance' => 'sede', 'permisos' => [$permission],
    ])->assertRedirect();
    expect(DB::table('roles_permisos')->where('rol_id', $targetRole)->pluck('permiso_id')->all())->toBe([$permission])
        ->and(DB::table('registros_auditoria')->where('accion', 'actualizar_permisos_rol')->count())->toBe(1);

    $this->put(route('management.roles_permissions.permissions', $adminRole), [
        'alcance' => 'empresa', 'permisos' => [$permission],
    ])->assertStatus(422);
});

it('lists current role assignments and creates a site user without trusting the legacy role field', function (): void {
    Schema::create('almacenes', fn (Blueprint $table) => $table->id());
    Schema::create('clientes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->string('documento');
        $table->string('razon_social')->nullable();
        $table->string('tipo');
        $table->string('ciudad')->nullable();
        $table->boolean('activo')->default(true);
    });
    app(RbacInicialSeeder::class)->run();
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'empresa_prueba']);
    DB::table('sedes')->insert(['id' => 1, 'empresa_id' => 1]);
    $admin = User::create(['nombre' => 'Administrador', 'correo' => 'admin@example.test', 'password' => 'ClaveSegura123']);
    User::create(['nombre' => 'Sin asignación', 'correo' => 'sinrol@example.test', 'password' => 'ClaveSegura123', 'rol' => 'administrador']);
    $adminRole = DB::table('roles')->where('codigo', 'administrador')->value('id');
    $siteRole = DB::table('roles')->where('codigo', 'marketing')->value('id');
    DB::table('usuarios_roles')->insert(['usuario_id' => $admin->id, 'rol_id' => $adminRole]);
    $this->withoutVite();

    $this->actingAs($admin)->get(route('management.users'))->assertOk()->assertSee('Sin rol vigente');
    $this->post(route('management.users.store'), [
        'nombre' => 'Marketing', 'correo' => 'marketing@example.test', 'rol' => 'marketing',
        'password' => 'ClaveSegura123', 'password_confirmation' => 'ClaveSegura123',
    ])->assertRedirect();

    $created = User::where('correo', 'marketing@example.test')->firstOrFail();
    expect(DB::table('usuarios_sedes_roles')->where('usuario_id', $created->id)->value('rol_id'))->toBe($siteRole)
        ->and(DB::table('usuarios_roles')->where('usuario_id', $created->id)->count())->toBe(0)
        ->and(Hash::check('ClaveSegura123', $created->resumen_contrasena))->toBeTrue();

    Schema::create('sesiones_usuarios', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('usuario_id');
        $table->string('resumen_identificador')->unique();
        $table->string('direccion_ip')->nullable();
        $table->text('agente_usuario')->nullable();
        $table->timestamp('ultimo_acceso')->nullable();
        $table->timestamp('revocada_en')->nullable();
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    DB::table('sesiones_usuarios')->insert(['usuario_id' => $created->id, 'resumen_identificador' => str_repeat('a', 64)]);
    $this->patch(route('management.users.activate', $created), ['activo' => false])->assertRedirect();
    expect($created->fresh()->activo)->toBeFalse()
        ->and(DB::table('sesiones_usuarios')->where('usuario_id', $created->id)->value('revocada_en'))->not->toBeNull();
});

it('immediately rejects a revoked global role even with a legacy administrator field', function (): void {
    $user = User::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.test', 'password' => 'ClaveSegura123', 'rol' => 'administrador']);
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'empresa_prueba']);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'administrador', 'nombre' => 'Administrador', 'alcance_permitido' => 'empresa']);
    DB::table('permisos')->insert(['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.pedidos.consultar']);
    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'empresa']);
    DB::table('usuarios_roles')->insert(['usuario_id' => $user->id, 'rol_id' => 1]);
    $authorization = app(ServicioAutorizacion::class);

    expect($authorization->hasRole($user, 'administrador'))->toBeTrue()
        ->and($authorization->allowsGlobal($user, 'gestion.pedidos.consultar'))->toBeTrue();

    DB::table('usuarios_roles')->where('usuario_id', $user->id)->update(['activo' => false]);

    expect($authorization->hasRole($user, 'administrador'))->toBeFalse()
        ->and($authorization->allows($user, 'gestion.pedidos.consultar'))->toBeFalse()
        ->and($authorization->allowsGlobal($user, 'gestion.pedidos.consultar'))->toBeFalse();
});

it('limits global order queries to the sole configured active company and consistent sites', function (): void {
    $user = User::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.test', 'password' => 'ClaveSegura123', 'rol' => 'administrador']);
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'principal', 'activo' => true]);
    DB::table('sedes')->insert([['id' => 1, 'empresa_id' => 1], ['id' => 2, 'empresa_id' => 2]]);
    DB::table('pedidos')->insert([
        ['id' => 1, 'usuario_id' => $user->id, 'empresa_id' => 1, 'sede_id' => 1],
        ['id' => 2, 'usuario_id' => $user->id, 'empresa_id' => 2, 'sede_id' => 2],
        ['id' => 3, 'usuario_id' => $user->id, 'empresa_id' => 1, 'sede_id' => 2],
        ['id' => 4, 'usuario_id' => $user->id, 'empresa_id' => null, 'sede_id' => null],
    ]);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'administrador', 'nombre' => 'Administrador', 'alcance_permitido' => 'empresa']);
    DB::table('permisos')->insert(['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.pedidos.consultar']);
    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'empresa']);
    DB::table('usuarios_roles')->insert(['usuario_id' => $user->id, 'rol_id' => 1]);
    $authorization = app(ServicioAutorizacion::class);

    expect($authorization->scopeRecordsFor(Pedido::query(), $user, 'gestion.pedidos.consultar')->pluck('id')->all())->toBe([1])
        ->and($authorization->allowsRecord($user, 'gestion.pedidos.consultar', 1, 2))->toBeFalse()
        ->and($authorization->allowsRecord($user, 'gestion.pedidos.consultar', null, null))->toBeFalse();

    DB::table('empresas')->insert(['id' => 2, 'codigo' => 'otra', 'activo' => false]);
    expect($authorization->scopeRecordsFor(Pedido::query(), $user, 'gestion.pedidos.consultar')->pluck('id')->all())->toBe([1])
        ->and($authorization->allows($user, 'gestion.pedidos.consultar'))->toBeTrue();

    DB::table('modulos')->where('id', 1)->update(['habilitado' => false]);
    expect($authorization->allowsGlobal($user, 'gestion.pedidos.consultar'))->toBeFalse();
});

it('limits a site role to its active assignment and never treats it as global', function (): void {
    $user = User::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.test', 'password' => 'ClaveSegura123']);
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'empresa_prueba']);
    DB::table('sedes')->insert([['id' => 1, 'empresa_id' => 1], ['id' => 2, 'empresa_id' => 1]]);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'ventas_minoristas', 'nombre' => 'Ventas', 'alcance_permitido' => 'sede']);
    DB::table('permisos')->insert(['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.pedidos.consultar']);
    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'sede']);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $user->id, 'sede_id' => 1, 'rol_id' => 1]);
    $authorization = app(ServicioAutorizacion::class);

    expect($authorization->hasRole($user, 'ventas_minoristas'))->toBeTrue()
        ->and($authorization->allowsRecord($user, 'gestion.pedidos.consultar', 1, 1))->toBeTrue()
        ->and($authorization->allowsRecord($user, 'gestion.pedidos.consultar', 1, 2))->toBeFalse()
        ->and($authorization->allowsGlobal($user, 'gestion.pedidos.consultar'))->toBeFalse();

    DB::table('usuarios_sedes_roles')->where('usuario_id', $user->id)->update(['vigente_hasta' => now()->subDay()]);
    expect($authorization->hasRole($user, 'ventas_minoristas'))->toBeFalse()
        ->and($authorization->allows($user, 'gestion.pedidos.consultar'))->toBeFalse();
});

it('excludes buyers with revoked RBAC roles from sales order access despite their legacy role', function (): void {
    $vendedor = User::create(['nombre' => 'Vendedor', 'correo' => 'vendedor@example.test', 'password' => 'ClaveSegura123', 'rol' => 'ventas_minoristas']);
    $cliente = User::create(['nombre' => 'Cliente', 'correo' => 'cliente@example.test', 'password' => 'ClaveSegura123', 'rol' => 'cliente_minorista']);
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'principal']);
    DB::table('sedes')->insert(['id' => 1, 'empresa_id' => 1]);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert([
        ['id' => 1, 'codigo' => 'ventas_minoristas', 'nombre' => 'Ventas', 'alcance_permitido' => 'sede'],
        ['id' => 2, 'codigo' => 'cliente_minorista', 'nombre' => 'Cliente', 'alcance_permitido' => 'propio'],
    ]);
    DB::table('permisos')->insert(['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.pedidos.consultar']);
    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'sede']);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $vendedor->id, 'sede_id' => 1, 'rol_id' => 1]);
    DB::table('usuarios_roles')->insert(['usuario_id' => $cliente->id, 'rol_id' => 2]);
    DB::table('pedidos')->insert(['id' => 1, 'usuario_id' => $cliente->id, 'empresa_id' => 1, 'sede_id' => 1]);
    $pedido = Pedido::with('user')->findOrFail(1);
    $consulta = static fn (): array => Pedido::query()
        ->whereHas('user.rolesVigentes', fn ($roles) => $roles->where('codigo', 'cliente_minorista'))
        ->pluck('id')->all();

    expect($consulta())->toBe([1])
        ->and((new PedidoPolicy)->view($vendedor, $pedido))->toBeTrue();

    DB::table('usuarios_roles')->where('usuario_id', $cliente->id)->update(['activo' => false]);

    expect($consulta())->toBe([])
        ->and((new PedidoPolicy)->view($vendedor, $pedido))->toBeFalse();
});

it('reads only authorized site balances and never substitutes the legacy product stock', function (): void {
    Schema::create('categorias', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->boolean('activo')->default(true);
    });
    Schema::create('proveedores', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->boolean('activo')->default(true);
    });
    Schema::create('productos_proveedores', function (Blueprint $table): void {
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('proveedor_id');
    });
    Schema::create('movimientos_inventario', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('usuario_id');
        $table->string('tipo_movimiento');
        $table->decimal('cantidad', 18, 6);
        $table->timestamp('creado_en')->nullable();
    });
    Schema::create('productos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->string('marca');
        $table->string('categoria')->default('Prueba');
        $table->unsignedBigInteger('categoria_id')->nullable();
        $table->decimal('precio', 10, 2);
        $table->decimal('precio_oferta', 10, 2)->nullable();
        $table->unsignedInteger('existencias');
        $table->boolean('activo')->default(true);
        $table->timestamp('creado_en')->nullable();
    });
    Schema::create('existencias_almacen', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('almacen_id');
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->decimal('cantidad_fisica', 18, 6);
        $table->decimal('cantidad_reservada', 18, 6)->default(0);
    });
    Schema::create('variantes_productos', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedInteger('producto_id');
        $table->string('codigo_sku', 80)->nullable();
        $table->string('nombre_color', 100)->default('Unico');
        $table->string('estado', 20)->default('activo');
    });
    $usuario = User::create(['nombre' => 'Logística', 'correo' => 'logistica@example.test', 'password' => 'ClaveSegura123', 'rol' => 'compras_logistica']);
    DB::table('empresas')->insert([['id' => 1, 'codigo' => 'principal'], ['id' => 2, 'codigo' => 'ajena']]);
    DB::table('sedes')->insert([
        ['id' => 1, 'empresa_id' => 1], ['id' => 2, 'empresa_id' => 1], ['id' => 3, 'empresa_id' => 2],
    ]);
    Schema::create('almacenes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('sede_id');
        $table->string('codigo');
        $table->string('nombre');
        $table->boolean('activo')->default(true);
    });
    DB::table('almacenes')->insert([
        ['id' => 1, 'sede_id' => 1, 'codigo' => 'principal', 'nombre' => 'Principal'],
        ['id' => 2, 'sede_id' => 2, 'codigo' => 'secundario', 'nombre' => 'Secundario'],
        ['id' => 3, 'sede_id' => 3, 'codigo' => 'ajeno', 'nombre' => 'Ajeno'],
    ]);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'compras_logistica', 'nombre' => 'Logística', 'alcance_permitido' => 'sede']);
    DB::table('permisos')->insert([
        ['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.inventario.consultar'],
        ['id' => 2, 'modulo_id' => 1, 'codigo' => 'gestion.productos.consultar'],
    ]);
    DB::table('roles_permisos')->insert([
        ['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'sede'],
        ['rol_id' => 1, 'permiso_id' => 2, 'alcance' => 'sede'],
    ]);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $usuario->id, 'sede_id' => 1, 'rol_id' => 1]);
    DB::table('productos')->insert([
        ['id' => 1, 'empresa_id' => 1, 'nombre' => 'Compartido', 'marca' => 'Marca', 'precio' => 10, 'precio_oferta' => 8, 'existencias' => 50],
        ['id' => 2, 'empresa_id' => 1, 'nombre' => 'Sin unidades', 'marca' => 'Marca', 'precio' => 20, 'precio_oferta' => null, 'existencias' => 99],
        ['id' => 3, 'empresa_id' => 1, 'nombre' => 'Otra sede', 'marca' => 'Marca', 'precio' => 30, 'precio_oferta' => null, 'existencias' => 8],
        ['id' => 4, 'empresa_id' => 2, 'nombre' => 'Otra empresa', 'marca' => 'Marca', 'precio' => 40, 'precio_oferta' => null, 'existencias' => 9],
        ['id' => 5, 'empresa_id' => 1, 'nombre' => 'Sin saldo operativo', 'marca' => 'Marca', 'precio' => 50, 'precio_oferta' => 40, 'existencias' => 77],
    ]);
    DB::table('existencias_almacen')->insert([
        ['empresa_id' => 1, 'sede_id' => 1, 'almacen_id' => 1, 'producto_id' => 1, 'cantidad_fisica' => 3],
        ['empresa_id' => 1, 'sede_id' => 2, 'almacen_id' => 2, 'producto_id' => 1, 'cantidad_fisica' => 47],
        ['empresa_id' => 1, 'sede_id' => 1, 'almacen_id' => 1, 'producto_id' => 2, 'cantidad_fisica' => 0],
        ['empresa_id' => 1, 'sede_id' => 2, 'almacen_id' => 2, 'producto_id' => 3, 'cantidad_fisica' => 8],
        ['empresa_id' => 2, 'sede_id' => 3, 'almacen_id' => 3, 'producto_id' => 4, 'cantidad_fisica' => 9],
    ]);

    $inventario = app(ServicioInventario::class);
    $alcance = $inventario->alcanceConsulta($usuario);
    $productos = $inventario->productosParaConsulta($alcance)->orderBy('productos.id')->get();

    expect($alcance)->toBe(['empresa_id' => 1, 'sede_id' => 1])
        ->and($productos->pluck('id')->all())->toBe([1, 2])
        ->and((int) $productos[0]->existencias_alcance)->toBe(3)
        ->and((int) $productos[1]->existencias_alcance)->toBe(0)
        ->and($inventario->resumenConsulta($alcance))->toBe(['productos' => 1, 'sin_existencias' => 3, 'valor_catalogo' => 30.0])
        ->and($inventario->productosParaConsulta($alcance, ['estado' => 'sin_existencias'])->count())->toBe(1)
        ->and($inventario->productosParaConsulta($alcance, ['estado' => 'con_existencias'])->count())->toBe(1)
        ->and($inventario->productosParaConsulta($alcance, ['estado' => 'sin_existencias'], true)->count())->toBe(3)
        ->and($inventario->resumenProductos($alcance))->toBe([
            'productos_activos' => 4,
            'productos_con_existencias' => 1,
            'productos_sin_existencias' => 3,
            'valor_inventario' => 24.0,
            'productos_en_oferta' => 2,
        ]);

    $this->actingAs($usuario)->get(route('management.inventory'))
        ->assertOk()
        ->assertSee('Compartido')
        ->assertSee('3 unidades')
        ->assertSee('Otra sede')
        ->assertSee('Sin SKU registrado')
        ->assertDontSee('Otra empresa')
        ->assertDontSee('SKU-DEMO-001');

    $this->actingAs($usuario)->get(route('management.products'))
        ->assertOk()
        ->assertSee('Sin saldo operativo')
        ->assertViewHas('products', fn ($productos): bool => $productos->getCollection()->contains(
            fn (Producto $producto): bool => $producto->nombre === 'Sin saldo operativo'
                && (float) $producto->existencias_alcance === 0.0,
        ))
        ->assertSee('productos-stock productos-stock--success">3</span>', false)
        ->assertSee('productos-stock productos-stock--danger">0</span>', false)
        ->assertSee('Sin SKU registrado')
        ->assertDontSee('SKU-0005')
        ->assertDontSee('Otra empresa');

    DB::table('usuarios_sedes_roles')->where('usuario_id', $usuario->id)->update(['activo' => false]);
    expect(fn (): array => $inventario->alcanceConsulta($usuario))->toThrow(HttpException::class);
    $this->actingAs($usuario)->get(route('management.inventory'))->assertForbidden();
});

it('adjusts the warehouse balance to zero atomically without fabricating variants or SKUs', function (): void {
    Schema::create('almacenes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('sede_id');
        $table->boolean('activo')->default(true);
        $table->boolean('permite_venta')->default(true);
    });
    Schema::create('productos', function (Blueprint $table): void {
        $table->id();
        $table->string('nombre');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedInteger('existencias')->default(0);
        $table->decimal('precio', 10, 2)->default(10);
        $table->decimal('precio_oferta', 10, 2)->nullable();
        $table->boolean('activo')->default(true);
    });
    Schema::create('existencias_almacen', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('almacen_id');
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->decimal('cantidad_fisica', 18, 6);
        $table->decimal('cantidad_reservada', 18, 6);
    });
    Schema::create('variantes_productos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('producto_id');
        $table->string('codigo_sku')->nullable();
        $table->unsignedInteger('existencias')->default(0);
    });
    Schema::create('movimientos_inventario', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('usuario_id');
        $table->string('tipo_movimiento');
        $table->decimal('cantidad', 18, 6);
        $table->string('notas');
        $table->timestamp('creado_en');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('almacen_id');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->string('documento_tipo')->nullable();
        $table->string('documento_id')->nullable();
    });
    $user = User::create(['nombre' => 'Prueba', 'correo' => 'prueba@example.test', 'password' => 'ClaveSegura123']);
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'empresa_prueba']);
    DB::table('sedes')->insert(['id' => 1, 'empresa_id' => 1]);
    DB::table('almacenes')->insert(['id' => 1, 'sede_id' => 1]);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'compras_logistica', 'nombre' => 'Compras', 'alcance_permitido' => 'sede']);
    DB::table('permisos')->insert(['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.inventario.ajustar']);
    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'sede']);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $user->id, 'sede_id' => 1, 'rol_id' => 1]);
    DB::table('productos')->insert(['id' => 1, 'nombre' => 'Producto de prueba', 'empresa_id' => 1, 'existencias' => 99]);
    DB::table('existencias_almacen')->insert(['id' => 1, 'almacen_id' => 1, 'producto_id' => 1, 'empresa_id' => 1, 'sede_id' => 1, 'cantidad_fisica' => 5, 'cantidad_reservada' => 0]);

    $movement = app(ServicioInventario::class)->adjust(Producto::findOrFail(1), 'ajuste', 0, 'Conteo', $user);

    expect((float) $movement->cantidad)->toBe(-5.0)
        ->and((int) DB::table('existencias_almacen')->where('id', 1)->value('cantidad_fisica'))->toBe(0)
        ->and((int) DB::table('productos')->where('id', 1)->value('existencias'))->toBe(0)
        ->and(DB::table('registros_auditoria')->where('accion', 'inventory.adjusted')->count())->toBe(1)
        ->and(DB::table('variantes_productos')->count())->toBe(0);

    $ajusteSinDiferencia = app(ServicioInventario::class)->adjust(Producto::findOrFail(1), 'ajuste', 0, 'Conteo repetido', $user);
    expect($ajusteSinDiferencia)->toBeNull()
        ->and(DB::table('movimientos_inventario')->count())->toBe(1)
        ->and(DB::table('registros_auditoria')->count())->toBe(1);

    DB::table('existencias_almacen')->where('id', 1)->update(['cantidad_fisica' => 2, 'cantidad_reservada' => 1]);
    expect(fn () => app(ServicioInventario::class)->adjust(Producto::findOrFail(1), 'ajuste', 0, 'Conteo', $user))
        ->toThrow(HttpException::class);
    expect((int) DB::table('existencias_almacen')->where('id', 1)->value('cantidad_fisica'))->toBe(2)
        ->and(DB::table('movimientos_inventario')->count())->toBe(1)
        ->and(DB::table('registros_auditoria')->count())->toBe(1);

    DB::table('productos')->insert(['id' => 2, 'nombre' => 'Producto nuevo', 'empresa_id' => 1, 'existencias' => 3]);
    DB::transaction(fn () => app(ServicioInventario::class)->registerNewProduct(Producto::findOrFail(2), $user));
    expect((int) DB::table('existencias_almacen')->where('producto_id', 2)->value('cantidad_fisica'))->toBe(0)
        ->and((int) DB::table('productos')->where('id', 2)->value('existencias'))->toBe(0)
        ->and(DB::table('movimientos_inventario')->count())->toBe(1)
        ->and(DB::table('variantes_productos')->count())->toBe(0);
    app(ServicioInventario::class)->adjust(Producto::findOrFail(2), 'entrada', 3, 'Ingreso desde inventario', $user);
    expect((int) DB::table('existencias_almacen')->where('producto_id', 2)->value('cantidad_fisica'))->toBe(3)
        ->and(DB::table('movimientos_inventario')->count())->toBe(2);

    Schema::table('pedidos', function (Blueprint $table): void {
        $table->unsignedBigInteger('almacen_id')->nullable();
        $table->string('canal')->nullable();
        $table->decimal('subtotal', 10, 2)->default(0);
        $table->decimal('descuento', 10, 2)->default(0);
        $table->string('cupon_codigo')->nullable();
        $table->decimal('total', 10, 2);
        $table->string('estado');
        $table->timestamp('creado_en')->nullable();
    });
    Schema::create('detalles_pedidos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('pedido_id');
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->unsignedInteger('cantidad');
        $table->decimal('precio_unitario', 10, 2);
    });
    DB::table('permisos')->insert([
        ['id' => 2, 'modulo_id' => 1, 'codigo' => 'gestion.pedidos.crear'],
        ['id' => 3, 'modulo_id' => 1, 'codigo' => 'gestion.pedidos.actualizar'],
    ]);
    Schema::create('carritos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id')->nullable();
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->string('resumen_token')->unique();
        $table->unsignedBigInteger('pedido_id')->nullable();
        $table->string('estado');
        $table->timestamp('actualizado_en')->nullable();
    });
    DB::table('roles_permisos')->insert([
        ['rol_id' => 1, 'permiso_id' => 2, 'alcance' => 'sede'],
        ['rol_id' => 1, 'permiso_id' => 3, 'alcance' => 'sede'],
    ]);
    $order = app(ServicioPedidos::class)->checkout($user, [2 => 1], null, null, str_repeat('a', 64));
    expect($order->canal)->toBe('ecommerce')
        ->and($order->estado)->toBe('Pendiente')
        ->and((int) DB::table('existencias_almacen')->where('producto_id', 2)->value('cantidad_fisica'))->toBe(2);

    $replayedOrder = app(ServicioPedidos::class)->checkout($user, [2 => 1], null, null, str_repeat('a', 64));
    expect($replayedOrder->id)->toBe($order->id)
        ->and(DB::table('pedidos')->count())->toBe(1)
        ->and((int) DB::table('existencias_almacen')->where('producto_id', 2)->value('cantidad_fisica'))->toBe(2);

    app(ServicioPedidos::class)->updateStatus($order, $user, 'Cancelado', null);
    app(ServicioPedidos::class)->updateStatus($order, $user, 'Cancelado', null);
    expect((int) DB::table('existencias_almacen')->where('producto_id', 2)->value('cantidad_fisica'))->toBe(3)
        ->and(DB::table('movimientos_inventario')->count())->toBe(4);

    DB::table('existencias_almacen')->where('producto_id', 2)->update(['cantidad_fisica' => 1]);
    expect((int) DB::table('productos')->where('id', 2)->value('existencias'))->toBe(3)
        ->and(fn () => app(ServicioPedidos::class)->checkout($user, [2 => 2], null, null, str_repeat('f', 64)))
        ->toThrow(HttpException::class);
    $this->actingAs($user)->postJson(route('cart.add', 2), ['cantidad' => 2])->assertStatus(422);
    Schema::create('campanas_publicitarias', function (Blueprint $table): void {
        $table->id();
        $table->boolean('activo');
        $table->timestamp('inicia_en');
        $table->timestamp('finaliza_en');
    });
    $this->withSession(['cart' => [2 => 2]])->get(route('checkout.form'))
        ->assertOk()->assertSee('La disponibilidad cambió.')->assertSee('disabled', false);
    expect(DB::table('pedidos')->count())->toBe(1)
        ->and((int) DB::table('existencias_almacen')->where('producto_id', 2)->value('cantidad_fisica'))->toBe(1);
    DB::table('existencias_almacen')->where('producto_id', 2)->update(['cantidad_fisica' => 3]);

    Schema::create('cupones', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo')->unique();
        $table->string('tipo_descuento');
        $table->decimal('valor', 10, 2);
        $table->decimal('minimo_compra', 10, 2)->default(0);
        $table->integer('limite_usos')->nullable();
        $table->integer('usos')->default(0);
        $table->timestamp('inicia_en');
        $table->timestamp('finaliza_en');
        $table->boolean('activo')->default(true);
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    DB::table('cupones')->insert(['codigo' => 'PRUEBA', 'tipo_descuento' => 'porcentaje', 'valor' => 10, 'inicia_en' => now()->subDay(), 'finaliza_en' => now()->addDay()]);
    $token = str_repeat('b', 64);
    $this->actingAs($user)->withSession(['cart' => [2 => 1], 'checkout_token' => $token, 'coupon_code' => 'PRUEBA'])
        ->postJson(route('checkout'), ['checkout_token' => str_repeat('c', 64)])->assertStatus(422);
    expect(DB::table('pedidos')->count())->toBe(1);
    $this->post(route('checkout'), ['checkout_token' => $token])->assertRedirect();
    $this->postJson(route('checkout'), ['checkout_token' => $token])->assertStatus(422);
    app(ServicioPedidos::class)->checkout($user, [2 => 1], 'PRUEBA', null, $token);
    expect(DB::table('pedidos')->count())->toBe(2)
        ->and((int) DB::table('existencias_almacen')->where('producto_id', 2)->value('cantidad_fisica'))->toBe(2)
        ->and((int) DB::table('cupones')->where('codigo', 'PRUEBA')->value('usos'))->toBe(1);

    Schema::create('pagos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('pedido_id');
    });
    DB::table('pagos')->insert(['pedido_id' => 2]);
    expect(fn () => app(ServicioPedidos::class)->updateStatus(Pedido::findOrFail(2), $user, 'Cancelado', null))
        ->toThrow(HttpException::class);
    expect((int) DB::table('existencias_almacen')->where('producto_id', 2)->value('cantidad_fisica'))->toBe(2);
    DB::table('pagos')->where('pedido_id', 2)->delete();
    DB::table('movimientos_inventario')->where('documento_tipo', 'pedido')->where('documento_id', '2')->update(['documento_tipo' => 'historico']);
    expect(fn () => app(ServicioPedidos::class)->updateStatus(Pedido::findOrFail(2), $user, 'Cancelado', null))
        ->toThrow(HttpException::class);
    expect((int) DB::table('existencias_almacen')->where('producto_id', 2)->value('cantidad_fisica'))->toBe(2);
    DB::table('movimientos_inventario')->where('documento_tipo', 'historico')->where('documento_id', '2')->update(['documento_tipo' => 'pedido']);

    Schema::table('variantes_productos', fn (Blueprint $table) => $table->boolean('controla_serie')->default(false));
    DB::table('variantes_productos')->insert(['id' => 2, 'producto_id' => 2, 'controla_serie' => true]);
    DB::table('existencias_almacen')->where('producto_id', 2)->update(['variante_id' => 2]);
    expect(fn () => app(ServicioPedidos::class)->checkout($user, [2 => 1], null, null, str_repeat('e', 64)))
        ->toThrow(HttpException::class);
    $this->postJson(route('cart.add', 2), ['cantidad' => 1])->assertStatus(422);
    $this->withSession(['cart' => [2 => 1]])->get(route('checkout.form'))
        ->assertOk()->assertSee('La disponibilidad cambió.')->assertSee('disabled', false);
    expect(DB::table('pedidos')->count())->toBe(2)
        ->and((int) DB::table('existencias_almacen')->where('producto_id', 2)->value('cantidad_fisica'))->toBe(2);
    DB::table('existencias_almacen')->where('producto_id', 2)->update(['variante_id' => null]);
    DB::table('roles_permisos')->where('permiso_id', 2)->delete();
    $this->postJson(route('cart.add', 2), ['cantidad' => 1])->assertForbidden();

    Schema::drop('registros_auditoria');
    expect(fn () => app(ServicioInventario::class)->adjust(Producto::findOrFail(1), 'ajuste', 1, 'Conteo', $user))
        ->toThrow(QueryException::class);
    expect((int) DB::table('existencias_almacen')->where('id', 1)->value('cantidad_fisica'))->toBe(2)
        ->and(DB::table('movimientos_inventario')->count())->toBe(5);

    DB::table('sedes')->insert(['id' => 2, 'empresa_id' => 1]);
    DB::table('almacenes')->insert(['id' => 2, 'sede_id' => 2]);
    DB::table('variantes_productos')->insert(['id' => 1, 'producto_id' => 1, 'existencias' => 2]);
    DB::table('existencias_almacen')->insert(['almacen_id' => 2, 'producto_id' => 1, 'variante_id' => 1, 'empresa_id' => 1, 'sede_id' => 2, 'cantidad_fisica' => 2, 'cantidad_reservada' => 0]);
    expect(fn () => app(ServicioInventario::class)->adjust(Producto::findOrFail(1), 'entrada', 1, 'Conteo', $user))
        ->toThrow(HttpException::class);
    expect((int) DB::table('existencias_almacen')->where('id', 1)->value('cantidad_fisica'))->toBe(2);
});

it('reads and exports only authorised sales, independently of orders and without mixing currencies', function (): void {
    Schema::table('pedidos', fn (Blueprint $table) => $table->string('canal')->nullable());
    Schema::create('clientes', function (Blueprint $table): void {
        $table->id();
        $table->string('razon_social')->nullable();
        $table->string('nombre_contacto')->nullable();
        $table->string('correo')->nullable();
    });
    Schema::create('ventas', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('cliente_id')->nullable();
        $table->unsignedBigInteger('pedido_id')->nullable();
        $table->string('codigo');
        $table->string('estado');
        $table->string('moneda');
        $table->decimal('total', 15, 2);
        $table->timestamp('fecha_venta');
        $table->timestamp('creado_en')->nullable();
    });
    Schema::create('detalles_ventas', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('venta_id');
        $table->unsignedBigInteger('producto_id');
        $table->string('descripcion_historica');
        $table->decimal('cantidad', 18, 6);
        $table->decimal('total_linea', 15, 2);
    });
    $user = User::create(['nombre' => 'Ventas', 'correo' => 'venta@example.test', 'password' => 'ClaveSegura123']);
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'empresa']);
    DB::table('sedes')->insert([['id' => 1, 'empresa_id' => 1], ['id' => 2, 'empresa_id' => 1]]);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'ventas_minoristas', 'nombre' => 'Ventas', 'alcance_permitido' => 'sede']);
    DB::table('permisos')->insert(['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.ventas.consultar']);
    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'sede']);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $user->id, 'sede_id' => 1, 'rol_id' => 1]);
    DB::table('clientes')->insert(['id' => 1, 'razon_social' => '=Cliente ficticio']);
    DB::table('pedidos')->insert(['id' => 1, 'usuario_id' => $user->id, 'empresa_id' => 1, 'sede_id' => 1, 'canal' => 'ecommerce']);
    DB::table('ventas')->insert([
        ['id' => 1, 'empresa_id' => 1, 'sede_id' => 1, 'cliente_id' => 1, 'pedido_id' => 1, 'codigo' => '=VENTA', 'estado' => 'borrador', 'moneda' => 'PEN', 'total' => 12, 'fecha_venta' => now()],
        ['id' => 2, 'empresa_id' => 1, 'sede_id' => 1, 'cliente_id' => null, 'pedido_id' => null, 'codigo' => 'VENTA-2', 'estado' => 'borrador', 'moneda' => 'USD', 'total' => 30, 'fecha_venta' => now()],
        ['id' => 3, 'empresa_id' => 1, 'sede_id' => 2, 'cliente_id' => null, 'pedido_id' => null, 'codigo' => 'OTRA-SEDE', 'estado' => 'borrador', 'moneda' => 'PEN', 'total' => 999, 'fecha_venta' => now()],
    ]);
    DB::table('detalles_ventas')->insert(['venta_id' => 1, 'producto_id' => 1, 'descripcion_historica' => 'Producto ficticio', 'cantidad' => 2, 'total_linea' => 12]);
    $filters = ['buscar' => null, 'estado' => null, 'canal' => null, 'from' => now()->startOfDay(), 'to' => now()->endOfDay()];
    $service = app(ConsultaVentas::class);
    $summary = $service->resumen($user, $filters);
    expect($service->query($user, $filters)->pluck('ventas.id')->all())->toBe([1, 2])
        ->and($summary['ordersCount'])->toBe(2)
        ->and($summary['monedasMultiples'])->toBeTrue()
        ->and($summary['totalSales'])->toBeNull()
        ->and($service->query($user, array_replace($filters, ['canal' => 'ecommerce']))->pluck('ventas.id')->all())->toBe([1]);

    $request = FiltroVentaRequest::create('/gestion/ventas/exportar', 'GET');
    $request->setUserResolver(fn () => $user);
    $request->setValidator(Validator::make($request->all(), $request->rules(), $request->messages(), $request->attributes()));
    $this->actingAs($user);
    view()->share('errors', new ViewErrorBag);
    $view = app(VentaController::class)->index($request);
    expect($view->getData()['orders']->pluck('id')->all())->toBe([2, 1]);
    $html = $view->render();
    expect($html)->toContain('VENTA-2')->toContain('Monedas múltiples')->not->toContain('OTRA-SEDE');
    $response = app(VentaController::class)->export($request);
    ob_start();
    $response->sendContent();
    $csv = ob_get_clean();
    expect($csv)->toContain("'=VENTA")->toContain("'=Cliente ficticio")->not->toContain('OTRA-SEDE');

    $this->withoutMiddleware(RegistrarSesionUsuario::class);
    $this->actingAs($user)->get(route('management.sales'))->assertOk()->assertSee('VENTA-2')->assertDontSee('OTRA-SEDE');
    $this->get(route('management.sales', ['cliente_id' => 1]))->assertOk()->assertSee('Cliente ficticio')->assertDontSee('VENTA-2');
    $this->get(route('management.sales', ['sede_id' => 2]))->assertOk()->assertDontSee('OTRA-SEDE');
    $this->get(route('management.sales', ['canal' => 'marketplace']))->assertSessionHasErrors('canal');
    $this->get(route('management.sales', ['desde' => '03-11-2026']))->assertSessionHasErrors('desde');
    $this->get(route('management.sales', ['desde' => '2026-11-20', 'hasta' => '2026-11-03']))->assertSessionHasErrors('hasta');
    $this->get(route('management.sales.export'))->assertForbidden();
    DB::table('permisos')->insert(['id' => 2, 'modulo_id' => 1, 'codigo' => 'gestion.ventas.exportar']);
    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 2, 'alcance' => 'sede']);
    $httpExport = $this->get(route('management.sales.export'))->assertOk()->streamedContent();
    expect($httpExport)->toContain('VENTA-2')->not->toContain('OTRA-SEDE');
    $filteredExport = $this->get(route('management.sales.export', [
        'canal' => 'ecommerce', 'desde' => now()->toDateString(), 'hasta' => now()->toDateString(),
    ]))->assertOk()->streamedContent();
    expect($filteredExport)->toContain("'=VENTA")->not->toContain('VENTA-2');
    DB::table('usuarios_sedes_roles')->update(['activo' => false]);
    expect($service->query($user, $filters)->count())->toBe(0);
    $this->get(route('management.sales'))->assertForbidden();
});

it('registra existencias en el almacén y variante seleccionados dentro del alcance autorizado', function (): void {
    Schema::create('almacenes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('sede_id');
        $table->boolean('activo')->default(true);
    });
    Schema::create('productos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->unsignedInteger('existencias')->default(0);
        $table->decimal('precio', 10, 2)->default(10);
        $table->boolean('activo')->default(true);
    });
    Schema::create('existencias_almacen', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('almacen_id');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->decimal('cantidad_fisica', 18, 6)->default(0);
        $table->decimal('cantidad_reservada', 18, 6)->default(0);
    });
    Schema::create('variantes_productos', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedInteger('producto_id');
        $table->string('estado')->nullable()->default('activo');
        $table->unsignedInteger('existencias')->default(0);
    });
    Schema::create('movimientos_inventario', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->string('tipo_movimiento');
        $table->decimal('cantidad', 18, 6);
        $table->string('notas');
        $table->timestamp('creado_en');
        $table->unsignedBigInteger('empresa_id')->nullable();
        $table->unsignedBigInteger('sede_id')->nullable();
        $table->unsignedBigInteger('almacen_id')->nullable();
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->string('documento_tipo')->nullable();
        $table->string('documento_id')->nullable();
    });

    $usuario = User::create(['nombre' => 'Logística', 'correo' => 'logistica@example.test', 'password' => 'ClaveSegura123']);
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'empresa_prueba']);
    DB::table('sedes')->insert([
        ['id' => 1, 'empresa_id' => 1],
        ['id' => 2, 'empresa_id' => 1],
    ]);
    DB::table('almacenes')->insert([
        ['id' => 1, 'sede_id' => 1],
        ['id' => 2, 'sede_id' => 1],
        ['id' => 3, 'sede_id' => 2],
    ]);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'compras_logistica', 'nombre' => 'Logística', 'alcance_permitido' => 'sede']);
    DB::table('permisos')->insert(['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.inventario.ajustar']);
    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'sede']);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $usuario->id, 'sede_id' => 1, 'rol_id' => 1]);
    DB::table('productos')->insert([
        ['id' => 1, 'empresa_id' => 1, 'nombre' => 'Producto sin variante', 'existencias' => 5],
        ['id' => 2, 'empresa_id' => 1, 'nombre' => 'Producto con variante', 'existencias' => 0],
    ]);
    DB::table('existencias_almacen')->insert([
        'almacen_id' => 1, 'producto_id' => 1, 'empresa_id' => 1, 'sede_id' => 1, 'cantidad_fisica' => 5,
    ]);
    DB::table('variantes_productos')->insert(['id' => 1, 'producto_id' => 2, 'estado' => 'activo']);

    $inventario = app(ServicioInventario::class);
    $conteoSinSaldo = $inventario->registrarMovimientoEnAlmacen(
        Producto::findOrFail(1), null, 2, 'ajuste', 0, 'Conteo sin unidades', $usuario,
    );
    expect($conteoSinSaldo)->toBeNull()
        ->and(DB::table('existencias_almacen')->where('almacen_id', 2)->count())->toBe(0)
        ->and(DB::table('movimientos_inventario')->count())->toBe(0);

    $entrada = $inventario->registrarMovimientoEnAlmacen(
        Producto::findOrFail(1), null, 2, 'entrada', 4, 'Ingreso inicial', $usuario,
    );
    expect((int) $entrada->almacen_id)->toBe(2)
        ->and((int) $entrada->empresa_id)->toBe(1)
        ->and((int) $entrada->sede_id)->toBe(1)
        ->and((int) DB::table('existencias_almacen')->where('almacen_id', 1)->value('cantidad_fisica'))->toBe(5)
        ->and((int) DB::table('existencias_almacen')->where('almacen_id', 2)->value('cantidad_fisica'))->toBe(4)
        ->and((int) DB::table('productos')->where('id', 1)->value('existencias'))->toBe(9);

    $entradaVariante = $inventario->registrarMovimientoEnAlmacen(
        Producto::findOrFail(2), 1, 2, 'entrada', 3, 'Ingreso de variante', $usuario,
    );
    expect((int) $entradaVariante->variante_id)->toBe(1)
        ->and((int) DB::table('existencias_almacen')->where('producto_id', 2)->where('almacen_id', 2)->value('cantidad_fisica'))->toBe(3)
        ->and((int) DB::table('variantes_productos')->where('id', 1)->value('existencias'))->toBe(3)
        ->and(DB::table('registros_auditoria')->where('accion', 'inventory.adjusted')->count())->toBe(2);

    expect(fn () => $inventario->registrarMovimientoEnAlmacen(
        Producto::findOrFail(1), null, 3, 'entrada', 1, 'Sede fuera del alcance', $usuario,
    ))->toThrow(HttpException::class);
    expect((int) DB::table('existencias_almacen')->where('almacen_id', 3)->count())->toBe(0)
        ->and(DB::table('movimientos_inventario')->count())->toBe(2);
});

it('reads purchase line totals from the Spanish table within the authorised site', function (): void {
    Schema::create('proveedores', function (Blueprint $table): void {
        $table->id();
        $table->string('nombre');
        $table->boolean('activo')->default(true);
    });
    Schema::create('productos', function (Blueprint $table): void {
        $table->id();
        $table->string('nombre');
        $table->boolean('activo')->default(true);
    });
    Schema::create('compras', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('almacen_id')->nullable();
        $table->unsignedBigInteger('proveedor_id');
        $table->boolean('activo')->default(true);
        $table->string('estado');
        $table->decimal('total', 12, 2);
        $table->date('fecha_compra');
    });
    Schema::create('detalles_compras', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('compra_id');
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->integer('cantidad');
        $table->decimal('costo_unitario', 10, 2);
    });
    Schema::create('movimientos_inventario', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('producto_id');
        $table->string('tipo_movimiento', 30);
        $table->decimal('cantidad', 18, 6);
        $table->string('notas', 500);
        $table->dateTime('creado_en');
        $table->unsignedBigInteger('empresa_id')->nullable();
        $table->unsignedBigInteger('sede_id')->nullable();
        $table->unsignedBigInteger('almacen_id')->nullable();
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->string('documento_tipo', 60)->nullable();
        $table->string('documento_id', 80)->nullable();
    });
    $user = User::create(['nombre' => 'Compras', 'correo' => 'compra@example.test', 'password' => 'ClaveSegura123']);
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'empresa']);
    DB::table('sedes')->insert([['id' => 1, 'empresa_id' => 1], ['id' => 2, 'empresa_id' => 1]]);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'compras_logistica', 'nombre' => 'Compras', 'alcance_permitido' => 'sede']);
    DB::table('permisos')->insert(['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.compras.consultar']);
    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'sede']);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $user->id, 'sede_id' => 1, 'rol_id' => 1]);
    DB::table('proveedores')->insert(['id' => 1, 'nombre' => 'Proveedor ficticio']);
    DB::table('productos')->insert(['id' => 1, 'nombre' => 'Producto ficticio']);
    DB::table('compras')->insert([
        ['id' => 1, 'empresa_id' => 1, 'sede_id' => 1, 'almacen_id' => 1, 'proveedor_id' => 1, 'estado' => 'Recibida', 'total' => 20, 'fecha_compra' => now()->toDateString()],
        ['id' => 2, 'empresa_id' => 1, 'sede_id' => 2, 'almacen_id' => 2, 'proveedor_id' => 1, 'estado' => 'Recibida', 'total' => 50, 'fecha_compra' => now()->toDateString()],
    ]);
    DB::table('detalles_compras')->insert([
        ['compra_id' => 1, 'producto_id' => 1, 'cantidad' => 2, 'costo_unitario' => 10],
        ['compra_id' => 2, 'producto_id' => 1, 'cantidad' => 5, 'costo_unitario' => 10],
    ]);
    DB::table('movimientos_inventario')->insert([
        ['producto_id' => 1, 'tipo_movimiento' => 'entrada', 'cantidad' => 2, 'notas' => 'Recepción de prueba', 'creado_en' => now(), 'empresa_id' => 1, 'sede_id' => 1, 'almacen_id' => 1, 'documento_tipo' => 'compra', 'documento_id' => '1'],
        ['producto_id' => 1, 'tipo_movimiento' => 'entrada', 'cantidad' => 5, 'notas' => 'Recepción de prueba', 'creado_en' => now(), 'empresa_id' => 1, 'sede_id' => 2, 'almacen_id' => 2, 'documento_tipo' => 'compra', 'documento_id' => '2'],
    ]);
    $request = Request::create('/gestion/compras', 'GET');
    $request->setUserResolver(fn () => $user);
    $data = app(CompraController::class)->index($request)->getData();

    expect($data['purchases']->pluck('id')->all())->toBe([1])
        ->and((int) $data['mostPurchasedProducts']->first()->cantidad)->toBe(2);
});

it('registers, approves and receives a purchase once with distinct permissions and atomic inventory', function (): void {
    Schema::table('registros_auditoria', fn (Blueprint $table) => $table->string('identificador_universal_evento')->nullable()->unique());
    Schema::create('almacenes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('sede_id');
        $table->boolean('activo')->default(true);
    });
    Schema::create('proveedores', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->boolean('activo')->default(true);
    });
    Schema::create('productos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->unsignedInteger('existencias');
        $table->boolean('activo')->default(true);
    });
    Schema::create('compras', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('almacen_id');
        $table->unsignedBigInteger('proveedor_id');
        $table->unsignedBigInteger('creado_por_usuario_id')->nullable();
        $table->decimal('total', 12, 2);
        $table->string('estado');
        $table->date('fecha_compra');
        $table->boolean('activo')->default(true);
        $table->timestamp('creado_en')->nullable();
    });
    Schema::create('detalles_compras', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('compra_id');
        $table->unsignedBigInteger('producto_id');
        $table->integer('cantidad');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->decimal('costo_unitario', 10, 2);
    });
    Schema::create('variantes_productos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('producto_id');
        $table->string('codigo_sku', 80)->nullable();
        $table->string('codigo_barras', 80)->nullable();
        $table->string('nombre_color', 100)->default('Único');
        $table->string('codigo_color', 20)->nullable();
        $table->unsignedInteger('existencias')->default(0);
        $table->boolean('controla_serie')->default(false);
        $table->string('estado', 20)->default('activo');
        $table->timestamp('fecha_creacion')->useCurrent();
    });
    Schema::create('existencias_almacen', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('almacen_id');
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->decimal('cantidad_fisica', 18, 6);
        $table->decimal('cantidad_reservada', 18, 6);
    });
    Schema::create('movimientos_inventario', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('usuario_id');
        $table->string('tipo_movimiento');
        $table->decimal('cantidad', 18, 6);
        $table->string('notas');
        $table->timestamp('creado_en');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('almacen_id');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->string('documento_tipo')->nullable();
        $table->string('documento_id')->nullable();
    });
    $user = User::create(['nombre' => 'Compras', 'correo' => 'compras@example.test', 'password' => 'ClaveSegura123']);
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'empresa']);
    DB::table('sedes')->insert(['id' => 1, 'empresa_id' => 1]);
    DB::table('almacenes')->insert(['id' => 1, 'sede_id' => 1]);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'compras_logistica', 'nombre' => 'Compras', 'alcance_permitido' => 'sede']);
    foreach (['consultar', 'crear', 'aprobar', 'recibir'] as $index => $accion) {
        DB::table('permisos')->insert(['id' => $index + 1, 'modulo_id' => 1, 'codigo' => 'gestion.compras.'.$accion]);
        DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => $index + 1, 'alcance' => 'sede']);
    }
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $user->id, 'sede_id' => 1, 'rol_id' => 1]);
    DB::table('proveedores')->insert(['id' => 1, 'empresa_id' => 1, 'nombre' => 'Proveedor ficticio']);
    DB::table('productos')->insert(['id' => 1, 'empresa_id' => 1, 'nombre' => 'Producto ficticio', 'existencias' => 5]);
    DB::table('existencias_almacen')->insert(['empresa_id' => 1, 'sede_id' => 1, 'almacen_id' => 1, 'producto_id' => 1, 'cantidad_fisica' => 5, 'cantidad_reservada' => 0]);
    $this->withoutMiddleware(RegistrarSesionUsuario::class);
    $this->actingAs($user)->get(route('management.purchases'))->assertOk()->assertSee('Proveedor ficticio');
    $token = session('compra_token');
    $datos = ['proveedor_id' => 1, 'fecha_compra' => now()->toDateString(), 'items' => [['producto_id' => 1, 'cantidad' => 2, 'costo_unitario' => '12.35']], 'compra_token' => $token];
    $this->postJson(route('management.purchases.store'), array_replace($datos, ['compra_token' => str_repeat('f', 64)]))->assertUnprocessable();
    $this->post(route('management.purchases.store'), $datos)->assertRedirect();
    $compra = Compra::firstOrFail();
    expect($compra->estado)->toBe('Pendiente')->and((float) $compra->total)->toBe(24.70)
        ->and((int) DB::table('existencias_almacen')->value('cantidad_fisica'))->toBe(5)
        ->and(DB::table('movimientos_inventario')->count())->toBe(0);
    $repetida = app(ServicioCompras::class)->registrar($datos, $user, $token, null);
    expect($repetida->id)->toBe($compra->id)->and(DB::table('compras')->count())->toBe(1);
    $alterada = $datos;
    $alterada['items'][0]['costo_unitario'] = '12.36';
    expect(fn () => app(ServicioCompras::class)->registrar($alterada, $user, $token, null))->toThrow(HttpException::class);
    $this->post(route('management.purchases.receive', $compra))->assertStatus(409);
    $this->post(route('management.purchases.approve', $compra))->assertRedirect();
    $this->post(route('management.purchases.approve', $compra))->assertRedirect();
    expect($compra->fresh()->estado)->toBe('Aprobada')->and(DB::table('movimientos_inventario')->count())->toBe(0);
    $this->post(route('management.purchases.receive', $compra))->assertRedirect();
    $this->post(route('management.purchases.receive', $compra))->assertRedirect();
    expect($compra->fresh()->estado)->toBe('Recibida')->and((int) DB::table('existencias_almacen')->value('cantidad_fisica'))->toBe(7)
        ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->count())->toBe(1)
        ->and(DB::table('registros_auditoria')->where('accion', 'compra.recibida')->count())->toBe(1);
    DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->update(['documento_tipo' => 'historico']);
    $this->post(route('management.purchases.receive', $compra))->assertStatus(409);
    expect((int) DB::table('existencias_almacen')->value('cantidad_fisica'))->toBe(7);
    DB::table('movimientos_inventario')->where('documento_tipo', 'historico')->update(['documento_tipo' => 'compra']);
    $segunda = app(ServicioCompras::class)->registrar($datos, $user, str_repeat('a', 64), null);
    app(ServicioCompras::class)->aprobar($segunda, $user, null);
    RegistroAuditoria::creating(fn () => throw new RuntimeException('Fallo sintético de auditoría.'));
    expect(fn () => app(ServicioCompras::class)->recibir($segunda, $user, null))->toThrow(RuntimeException::class);
    RegistroAuditoria::flushEventListeners();
    expect($segunda->fresh()->estado)->toBe('Aprobada')->and((int) DB::table('existencias_almacen')->value('cantidad_fisica'))->toBe(7)
        ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->count())->toBe(1);
    DB::table('usuarios_sedes_roles')->update(['activo' => false]);
    expect(fn () => app(ServicioCompras::class)->recibir($segunda, $user, null))->toThrow(HttpException::class);
});

it('confirms a physical sale once, deducts stock, and does not create an order or payment', function (): void {
    Schema::create('almacenes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('sede_id');
        $table->boolean('activo')->default(true);
    });
    Schema::create('clientes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('razon_social')->nullable();
        $table->string('nombre_contacto')->nullable();
        $table->string('correo')->nullable();
        $table->boolean('activo')->default(true);
    });
    Schema::create('productos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->unsignedInteger('existencias');
        $table->decimal('precio', 10, 2);
        $table->decimal('precio_oferta', 10, 2)->nullable();
        $table->boolean('activo')->default(true);
    });
    Schema::create('existencias_almacen', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('almacen_id');
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->decimal('cantidad_fisica', 18, 6);
        $table->decimal('cantidad_reservada', 18, 6);
    });
    Schema::create('movimientos_inventario', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('usuario_id');
        $table->string('tipo_movimiento');
        $table->decimal('cantidad', 18, 6);
        $table->string('notas');
        $table->timestamp('creado_en');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('almacen_id');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->string('documento_tipo')->nullable();
        $table->string('documento_id')->nullable();
    });
    Schema::create('ventas', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('almacen_id');
        $table->unsignedBigInteger('cliente_id')->nullable();
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->unsignedBigInteger('pedido_id')->nullable();
        $table->string('codigo');
        $table->string('estado')->default('borrador');
        $table->string('moneda')->default('PEN');
        $table->decimal('subtotal', 15, 2)->default(0);
        $table->decimal('total', 15, 2)->default(0);
        $table->timestamp('fecha_venta');
        $table->string('canal')->nullable();
        $table->string('observaciones', 500)->nullable();
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
        $table->unique(['empresa_id', 'codigo']);
    });
    Schema::create('detalles_ventas', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('venta_id');
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->string('descripcion_historica');
        $table->string('sku_historico')->nullable();
        $table->decimal('cantidad', 18, 6);
        $table->decimal('precio_unitario', 15, 2);
        $table->decimal('total_linea', 15, 2);
    });
    foreach (['pagos', 'documentos_electronicos', 'devoluciones_garantias'] as $tabla) {
        Schema::create($tabla, function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('venta_id')->nullable();
        });
    }
    Schema::table('pedidos', fn (Blueprint $table) => $table->string('canal')->nullable());
    $user = User::create(['nombre' => 'Ventas', 'correo' => 'ventas@example.test', 'password' => 'ClaveSegura123']);
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'empresa']);
    DB::table('sedes')->insert(['id' => 1, 'empresa_id' => 1]);
    DB::table('almacenes')->insert(['id' => 1, 'sede_id' => 1]);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'ventas_minoristas', 'nombre' => 'Ventas', 'alcance_permitido' => 'sede']);
    DB::table('permisos')->insert([
        ['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.ventas.consultar'],
        ['id' => 2, 'modulo_id' => 1, 'codigo' => 'gestion.ventas.crear'],
    ]);
    DB::table('roles_permisos')->insert([['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'sede'], ['rol_id' => 1, 'permiso_id' => 2, 'alcance' => 'sede']]);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $user->id, 'sede_id' => 1, 'rol_id' => 1]);
    DB::table('clientes')->insert(['id' => 1, 'empresa_id' => 1, 'razon_social' => 'Cliente ficticio']);
    DB::table('productos')->insert(['id' => 1, 'empresa_id' => 1, 'nombre' => 'Producto ficticio', 'existencias' => 5, 'precio' => 12.35]);
    DB::table('existencias_almacen')->insert(['empresa_id' => 1, 'sede_id' => 1, 'almacen_id' => 1, 'producto_id' => 1, 'cantidad_fisica' => 5, 'cantidad_reservada' => 1]);
    $this->withoutMiddleware(RegistrarSesionUsuario::class);
    $this->actingAs($user)->get(route('management.sales'))->assertOk()->assertSee('Producto ficticio');
    $token = session('venta_token');
    $datos = ['cliente_id' => 1, 'tipo_venta' => 'fisica', 'fecha_venta' => now()->toDateString(), 'observaciones' => 'Entrega directa', 'items' => [['producto_id' => 1, 'cantidad' => 2]], 'venta_token' => $token];
    $this->postJson(route('management.sales.store'), array_replace($datos, ['venta_token' => str_repeat('f', 64)]))->assertUnprocessable();
    expect(DB::table('ventas')->count())->toBe(0);
    $this->post(route('management.sales.store'), $datos)->assertRedirect(route('management.sales'));
    $venta = DB::table('ventas')->first();
    expect($venta->canal)->toBe('presencial')->and($venta->estado)->toBe('confirmada')
        ->and((float) $venta->total)->toBe(24.70)->and($venta->observaciones)->toBe('Entrega directa')
        ->and(DB::table('pedidos')->count())->toBe(0)->and(DB::table('pagos')->count())->toBe(0)
        ->and((int) DB::table('existencias_almacen')->value('cantidad_fisica'))->toBe(3)
        ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'venta')->count())->toBe(1)
        ->and(DB::table('detalles_ventas')->count())->toBe(1);
    $this->get(route('management.sales', ['canal' => 'presencial']))->assertOk()->assertSee($venta->codigo)->assertSee('presencial');
    $this->get(route('management.sales', ['canal' => 'ecommerce']))->assertOk()->assertDontSee($venta->codigo);
    $repetida = app(ServicioVentas::class)->registrarFisica($datos, $user, $token, null);
    expect($repetida->id)->toBe($venta->id)->and(DB::table('ventas')->count())->toBe(1)
        ->and(DB::table('movimientos_inventario')->count())->toBe(1);
    $datos['items'][0]['cantidad'] = 3;
    expect(fn () => app(ServicioVentas::class)->registrarFisica($datos, $user, $token, null))->toThrow(HttpException::class);
    expect(fn () => app(ServicioVentas::class)->registrarFisica($datos, $user, str_repeat('c', 64), null))->toThrow(HttpException::class);
    expect(DB::table('ventas')->count())->toBe(1)->and((int) DB::table('existencias_almacen')->value('cantidad_fisica'))->toBe(3);
    $datos['items'][0]['cantidad'] = 1;
    RegistroAuditoria::creating(fn () => throw new RuntimeException('Fallo sintético de auditoría.'));
    expect(fn () => app(ServicioVentas::class)->registrarFisica($datos, $user, str_repeat('d', 64), null))->toThrow(RuntimeException::class);
    RegistroAuditoria::flushEventListeners();
    expect(DB::table('ventas')->count())->toBe(1)->and(DB::table('detalles_ventas')->count())->toBe(1)
        ->and(DB::table('movimientos_inventario')->count())->toBe(1)->and((int) DB::table('existencias_almacen')->value('cantidad_fisica'))->toBe(3);
    Schema::create('dispositivos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('producto_id');
    });
    DB::table('dispositivos')->insert(['producto_id' => 1]);
    expect(fn () => app(ServicioVentas::class)->registrarFisica($datos, $user, str_repeat('b', 64), null))->toThrow(HttpException::class);
    expect(DB::table('ventas')->count())->toBe(1)->and((int) DB::table('existencias_almacen')->value('cantidad_fisica'))->toBe(3);
    DB::table('dispositivos')->delete();
    Schema::create('variantes_productos', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo_sku')->nullable();
        $table->boolean('controla_serie')->default(false);
        $table->unsignedInteger('existencias');
    });
    DB::table('variantes_productos')->insert(['id' => 1, 'codigo_sku' => 'SERIE-PRUEBA', 'controla_serie' => true, 'existencias' => 3]);
    DB::table('existencias_almacen')->update(['variante_id' => 1]);
    expect(fn () => app(ServicioVentas::class)->registrarFisica($datos, $user, str_repeat('a', 64), null))->toThrow(HttpException::class);
    expect(DB::table('ventas')->count())->toBe(1)->and((int) DB::table('existencias_almacen')->value('cantidad_fisica'))->toBe(3);
    DB::table('existencias_almacen')->update(['variante_id' => null]);
    $this->post(route('management.sales.cancel', $venta->id), ['motivo' => 'Error de registro'])->assertForbidden();
    DB::table('permisos')->insert(['id' => 3, 'modulo_id' => 1, 'codigo' => 'gestion.ventas.anular']);
    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 3, 'alcance' => 'sede']);
    $this->get(route('management.sales'))->assertOk()->assertSee('Anular venta')->assertSee('Producto ficticio');
    $this->postJson(route('management.sales.cancel', $venta->id), ['motivo' => 'no'])->assertUnprocessable();
    foreach (['pagos', 'documentos_electronicos', 'devoluciones_garantias'] as $tabla) {
        DB::table($tabla)->insert(['venta_id' => $venta->id]);
        $this->post(route('management.sales.cancel', $venta->id), ['motivo' => 'Error de registro'])->assertStatus(409);
        expect((int) DB::table('existencias_almacen')->value('cantidad_fisica'))->toBe(3);
        DB::table($tabla)->delete();
    }
    RegistroAuditoria::creating(fn () => throw new RuntimeException('Fallo sintético al anular.'));
    expect(fn () => app(ServicioVentas::class)->anular(Venta::findOrFail($venta->id), $user, 'Error de registro', null))->toThrow(RuntimeException::class);
    RegistroAuditoria::flushEventListeners();
    expect((int) DB::table('existencias_almacen')->value('cantidad_fisica'))->toBe(3)->and(DB::table('ventas')->value('estado'))->toBe('confirmada');
    $this->post(route('management.sales.cancel', $venta->id), ['motivo' => 'Error de registro'])->assertRedirect(route('management.sales'));
    $this->post(route('management.sales.cancel', $venta->id), ['motivo' => 'Error de registro'])->assertRedirect(route('management.sales'));
    expect(DB::table('ventas')->value('estado'))->toBe('anulada')
        ->and((int) DB::table('existencias_almacen')->value('cantidad_fisica'))->toBe(5)
        ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'anulacion_venta')->count())->toBe(1)
        ->and(DB::table('registros_auditoria')->where('accion', 'venta.anulada')->count())->toBe(1);
    DB::table('usuarios_sedes_roles')->update(['activo' => false]);
    expect(fn () => app(ServicioVentas::class)->registrarFisica($datos, $user, str_repeat('e', 64), null))->toThrow(HttpException::class);
});

it('does not report a sale as registered while the sales ledger is not integrated', function (): void {
    $controller = app(VentaController::class);

    foreach ([
        fn () => $controller->store(new GuardarVentaRequest),
        fn () => $controller->update(new ActualizarVentaRequest, new Pedido),
    ] as $action) {
        try {
            $action();
            $this->fail('La operación de ventas incompleta debe permanecer bloqueada.');
        } catch (HttpException $exception) {
            expect($exception->getStatusCode())->toBe(503);
        }
    }

    expect(DB::table('pedidos')->count())->toBe(0);
});

it('accepts zero only for an inventory adjustment', function (): void {
    $adjustment = ['tipo_movimiento' => 'ajuste', 'cantidad' => 0, 'notas' => 'Conteo'];
    $incoming = ['tipo_movimiento' => 'entrada', 'cantidad' => 0, 'notas' => 'Conteo'];

    $adjustmentRequest = AjustarInventarioRequest::create('/', 'POST', $adjustment);
    $incomingRequest = AjustarInventarioRequest::create('/', 'POST', $incoming);

    expect(Validator::make($adjustment, $adjustmentRequest->rules())->passes())->toBeTrue()
        ->and(Validator::make($incoming, $incomingRequest->rules())->fails())->toBeTrue();
});

it('does not create a public user when the customer role is missing', function (): void {
    $this->post(route('register.store'), [
        'nombre' => 'Prueba', 'correo' => 'prueba@example.test',
        'password' => 'ClaveSegura123', 'password_confirmation' => 'ClaveSegura123',
        'rol' => 'administrador',
    ])->assertStatus(503);

    expect(DB::table('usuarios')->count())->toBe(0);
});

it('assigns only the server-selected customer role at public registration', function (): void {
    DB::table('roles')->insert([
        ['id' => 1, 'codigo' => 'cliente_minorista', 'nombre' => 'Cliente', 'alcance_permitido' => 'propio'],
        ['id' => 2, 'codigo' => 'administrador', 'nombre' => 'Administrador', 'alcance_permitido' => 'empresa'],
    ]);

    $this->post(route('register.store'), [
        'nombre' => 'Prueba', 'correo' => 'prueba@example.test',
        'password' => 'ClaveSegura123', 'password_confirmation' => 'ClaveSegura123',
        'rol' => 'administrador',
    ])->assertRedirect(route('dashboard'));

    $user = User::where('correo', 'prueba@example.test')->firstOrFail();
    expect($user->rol)->toBe('cliente_minorista')
        ->and(DB::table('usuarios_roles')->where('usuario_id', $user->id)->pluck('rol_id')->all())->toBe([1]);
});

it('shows operational warehouse availability in the public catalog and API without exposing serialized stock', function (): void {
    Schema::create('categorias', function (Blueprint $table): void {
        $table->increments('id');
        $table->string('nombre');
        $table->boolean('activo')->default(true);
    });
    Schema::create('productos', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedInteger('categoria_id');
        $table->unsignedBigInteger('empresa_id');
        $table->string('marca');
        $table->string('nombre');
        $table->string('categoria');
        $table->decimal('precio', 10, 2);
        $table->decimal('precio_original', 10, 2)->nullable();
        $table->decimal('precio_oferta', 10, 2)->nullable();
        $table->string('descuento')->nullable();
        $table->string('url_imagen')->nullable();
        $table->unsignedInteger('existencias')->default(0);
        $table->string('almacenamiento')->nullable();
        $table->string('etiqueta')->nullable();
        $table->text('descripcion')->nullable();
        $table->boolean('activo')->default(true);
        $table->timestamp('creado_en')->nullable();
    });
    Schema::create('almacenes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('sede_id');
        $table->boolean('permite_venta')->default(true);
        $table->boolean('activo')->default(true);
    });
    Schema::create('existencias_almacen', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('producto_id');
        $table->unsignedBigInteger('variante_id')->nullable();
        $table->unsignedBigInteger('almacen_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('empresa_id');
        $table->decimal('cantidad_fisica', 18, 6);
        $table->decimal('cantidad_reservada', 18, 6);
    });
    Schema::create('variantes_productos', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedInteger('producto_id');
        $table->boolean('controla_serie')->default(false);
    });
    Schema::create('dispositivos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedInteger('producto_id');
    });
    Schema::create('referencias_imagenes_productos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedInteger('producto_id');
        $table->string('ruta_imagen');
        $table->unsignedInteger('orden')->default(0);
    });
    Schema::create('campanas_publicitarias', function (Blueprint $table): void {
        $table->id();
        $table->boolean('activo');
        $table->timestamp('inicia_en');
        $table->timestamp('finaliza_en');
    });

    DB::table('categorias')->insert(['id' => 1, 'nombre' => 'Celulares']);
    DB::table('empresas')->insert([
        ['id' => 1, 'codigo' => 'principal'], ['id' => 2, 'codigo' => 'ajena'],
    ]);
    DB::table('sedes')->insert([
        ['id' => 1, 'empresa_id' => 1], ['id' => 2, 'empresa_id' => 1], ['id' => 3, 'empresa_id' => 2],
    ]);
    DB::table('almacenes')->insert([
        ['id' => 1, 'sede_id' => 1, 'permite_venta' => true],
        ['id' => 2, 'sede_id' => 2, 'permite_venta' => true],
        ['id' => 3, 'sede_id' => 1, 'permite_venta' => false],
        ['id' => 4, 'sede_id' => 3, 'permite_venta' => true],
    ]);
    DB::table('productos')->insert([
        ['id' => 1, 'empresa_id' => 1, 'categoria_id' => 1, 'marca' => 'Marca', 'nombre' => 'Equipo con stock', 'categoria' => 'Celulares', 'precio' => 100, 'precio_original' => 100, 'precio_oferta' => 80, 'existencias' => 99, 'activo' => true],
        ['id' => 2, 'empresa_id' => 1, 'categoria_id' => 1, 'marca' => 'Marca', 'nombre' => 'Equipo sin stock', 'categoria' => 'Celulares', 'precio' => 100, 'precio_original' => null, 'precio_oferta' => null, 'existencias' => 9, 'activo' => true],
        ['id' => 3, 'empresa_id' => 1, 'categoria_id' => 1, 'marca' => 'Marca', 'nombre' => 'Equipo serial', 'categoria' => 'Celulares', 'precio' => 100, 'precio_original' => null, 'precio_oferta' => null, 'existencias' => 5, 'activo' => true],
    ]);
    DB::table('variantes_productos')->insert(['id' => 1, 'producto_id' => 3, 'controla_serie' => true]);
    DB::table('existencias_almacen')->insert([
        ['producto_id' => 1, 'variante_id' => null, 'almacen_id' => 1, 'sede_id' => 1, 'empresa_id' => 1, 'cantidad_fisica' => 5, 'cantidad_reservada' => 2],
        ['producto_id' => 1, 'variante_id' => null, 'almacen_id' => 2, 'sede_id' => 2, 'empresa_id' => 1, 'cantidad_fisica' => 4, 'cantidad_reservada' => 0],
        ['producto_id' => 1, 'variante_id' => null, 'almacen_id' => 3, 'sede_id' => 1, 'empresa_id' => 1, 'cantidad_fisica' => 100, 'cantidad_reservada' => 0],
        ['producto_id' => 1, 'variante_id' => null, 'almacen_id' => 4, 'sede_id' => 3, 'empresa_id' => 2, 'cantidad_fisica' => 80, 'cantidad_reservada' => 0],
        ['producto_id' => 3, 'variante_id' => 1, 'almacen_id' => 1, 'sede_id' => 1, 'empresa_id' => 1, 'cantidad_fisica' => 5, 'cantidad_reservada' => 0],
    ]);

    $this->get(route('catalog.index'))->assertOk()
        ->assertSee('4 unidades disponibles')->assertSee('Equipo sin stock')->assertSee('Agotado');
    $this->getJson(route('api.v1.catalog.index'))->assertOk()
        ->assertJsonPath('data.0.stock', 4)
        ->assertJsonPath('data.1.stock', 0)
        ->assertJsonPath('data.2.stock', 0);
    $this->getJson(route('api.v1.catalog.show', 1))->assertOk()->assertJsonPath('data.stock', 4);
    $this->get(route('home'))->assertOk()->assertSee('4 unidades disponibles');

    DB::table('productos')->insert(['id' => 4, 'empresa_id' => 1, 'categoria_id' => 1, 'marca' => 'Marca', 'nombre' => 'Equipo IMEI', 'categoria' => 'Celulares', 'precio' => 100, 'existencias' => 2, 'activo' => true]);
    DB::table('existencias_almacen')->insert(['producto_id' => 4, 'almacen_id' => 1, 'sede_id' => 1, 'empresa_id' => 1, 'cantidad_fisica' => 2, 'cantidad_reservada' => 0]);
    DB::table('dispositivos')->insert(['producto_id' => 4]);
    $this->getJson(route('api.v1.catalog.show', 4))->assertOk()->assertJsonPath('data.stock', 0);

    DB::table('almacenes')->where('id', 3)->update(['activo' => false]);
    $usuario = User::create(['nombre' => 'Comprador', 'correo' => 'comprador@example.test', 'password' => 'ClaveSegura123']);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'cliente_minorista', 'nombre' => 'Cliente', 'alcance_permitido' => 'sede']);
    DB::table('permisos')->insert(['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.pedidos.crear']);
    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'sede']);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $usuario->id, 'rol_id' => 1, 'sede_id' => 1]);
    $productos = Producto::query()->whereIn('id', [1, 2, 3])->get();
    app(ServicioCatalogo::class)->agregarDisponibilidad($productos, $usuario);
    expect((int) $productos->firstWhere('id', 1)->existencias_disponibles)->toBe(3)
        ->and((int) $productos->firstWhere('id', 2)->existencias_disponibles)->toBe(0)
        ->and((int) $productos->firstWhere('id', 3)->existencias_disponibles)->toBe(0);
});

it('does not disclose assistant order and sales totals through the legacy role', function (): void {
    Schema::table('pedidos', function (Blueprint $table): void {
        $table->string('estado');
    });
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'principal']);
    DB::table('sedes')->insert(['id' => 1, 'empresa_id' => 1]);
    $usuario = User::create(['nombre' => 'Cuenta', 'correo' => 'cuenta@example.test', 'password' => 'ClaveSegura123', 'rol' => 'administrador']);
    $otro = User::create(['nombre' => 'Otro', 'correo' => 'otro@example.test', 'password' => 'ClaveSegura123']);
    DB::table('pedidos')->insert([
        ['usuario_id' => $usuario->id, 'empresa_id' => 1, 'sede_id' => 1, 'estado' => 'Pendiente'],
        ['usuario_id' => $otro->id, 'empresa_id' => 1, 'sede_id' => 1, 'estado' => 'Pendiente'],
    ]);

    $this->actingAs($usuario)->postJson(route('assistant.ask'), ['consulta' => 'pedidos pendientes'])
        ->assertOk()->assertJsonPath('respuesta.mensaje', 'Este perfil no tiene habilitado el resumen interno de pedidos.');
    $this->postJson(route('assistant.ask'), ['consulta' => 'ventas del mes'])
        ->assertOk()->assertJsonPath('respuesta.mensaje', 'Este perfil no tiene habilitado el resumen interno de ventas.');

    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'cliente_minorista', 'nombre' => 'Cliente', 'alcance_permitido' => 'propio']);
    DB::table('permisos')->insert(['id' => 1, 'modulo_id' => 1, 'codigo' => 'gestion.pedidos.consultar']);
    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'propio']);
    DB::table('usuarios_roles')->insert(['usuario_id' => $usuario->id, 'rol_id' => 1]);
    $this->postJson(route('assistant.ask'), ['consulta' => 'pedidos pendientes'])
        ->assertOk()->assertJsonPath('respuesta.mensaje', 'Hay 1 pedido pendiente en tu alcance autorizado.');

    Schema::create('ventas', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->string('estado');
        $table->string('moneda');
        $table->decimal('total', 15, 2);
        $table->timestamp('fecha_venta');
    });
    DB::table('sedes')->insert(['id' => 2, 'empresa_id' => 1]);
    DB::table('roles')->insert(['id' => 2, 'codigo' => 'ventas_minoristas', 'nombre' => 'Vendedor', 'alcance_permitido' => 'sede']);
    DB::table('permisos')->insert(['id' => 2, 'modulo_id' => 1, 'codigo' => 'gestion.ventas.consultar']);
    DB::table('roles_permisos')->insert(['rol_id' => 2, 'permiso_id' => 2, 'alcance' => 'sede']);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $usuario->id, 'sede_id' => 1, 'rol_id' => 2]);
    DB::table('ventas')->insert([
        ['empresa_id' => 1, 'sede_id' => 1, 'estado' => 'confirmada', 'moneda' => 'PEN', 'total' => 20, 'fecha_venta' => now()],
        ['empresa_id' => 1, 'sede_id' => 1, 'estado' => 'confirmada', 'moneda' => 'USD', 'total' => 10, 'fecha_venta' => now()],
        ['empresa_id' => 1, 'sede_id' => 2, 'estado' => 'confirmada', 'moneda' => 'PEN', 'total' => 999, 'fecha_venta' => now()],
    ]);
    $this->postJson(route('assistant.ask'), ['consulta' => 'ventas del mes'])
        ->assertOk()->assertJsonPath('respuesta.mensaje', 'Ventas confirmadas del mes en tu alcance: PEN 20.00; USD 10.00.');
});

it('preserves return requests and records their real creator using the imported Spanish columns', function (): void {
    Schema::table('pedidos', function (Blueprint $table): void {
        $table->unsignedBigInteger('cliente_id')->nullable();
        $table->timestamp('creado_en')->nullable();
    });
    Schema::create('campanas_publicitarias', function (Blueprint $table): void {
        $table->id();
        $table->boolean('activo');
        $table->timestamp('inicia_en');
        $table->timestamp('finaliza_en');
    });
    Schema::create('clientes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('razon_social');
        $table->string('nombre_contacto')->nullable();
        $table->boolean('activo')->default(true);
    });
    Schema::create('productos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->boolean('activo')->default(true);
    });
    Schema::create('detalles_pedidos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('pedido_id');
        $table->unsignedBigInteger('producto_id');
    });
    Schema::create('devoluciones_garantias', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo')->unique();
        $table->unsignedBigInteger('pedido_id');
        $table->unsignedBigInteger('cliente_id');
        $table->unsignedBigInteger('producto_id')->nullable();
        $table->string('tipo');
        $table->text('motivo');
        $table->string('estado');
        $table->date('fecha_solicitud');
        $table->date('fecha_limite')->nullable();
        $table->text('resolucion')->nullable();
        $table->unsignedBigInteger('creado_por_usuario_id')->nullable();
        $table->boolean('activo')->default(true);
        $table->timestamp('creado_en')->useCurrent();
        $table->unsignedBigInteger('empresa_id')->nullable();
        $table->unsignedBigInteger('sede_id')->nullable();
    });
    $usuario = User::create(['nombre' => 'Gestor', 'correo' => 'gestor@example.test', 'password' => 'ClaveSegura123']);
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'principal']);
    DB::table('sedes')->insert(['id' => 1, 'empresa_id' => 1]);
    DB::table('modulos')->insert(['id' => 1]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'ventas_minoristas', 'nombre' => 'Ventas', 'alcance_permitido' => 'sede']);
    foreach (['crear', 'eliminar'] as $indice => $accion) {
        DB::table('permisos')->insert(['id' => $indice + 1, 'modulo_id' => 1, 'codigo' => 'gestion.devoluciones.'.$accion]);
        DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => $indice + 1, 'alcance' => 'sede']);
    }
    foreach (['gestion.devoluciones.consultar', 'gestion.pedidos.consultar'] as $indice => $codigo) {
        DB::table('permisos')->insert(['id' => $indice + 3, 'modulo_id' => 1, 'codigo' => $codigo]);
        DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => $indice + 3, 'alcance' => 'sede']);
    }
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $usuario->id, 'sede_id' => 1, 'rol_id' => 1]);
    DB::table('clientes')->insert(['id' => 1, 'empresa_id' => 1, 'razon_social' => 'Cliente de prueba']);
    DB::table('productos')->insert(['id' => 1, 'empresa_id' => 1, 'nombre' => 'Equipo de prueba']);
    DB::table('pedidos')->insert(['id' => 1, 'usuario_id' => $usuario->id, 'cliente_id' => 1, 'empresa_id' => 1, 'sede_id' => 1]);
    DB::table('detalles_pedidos')->insert(['pedido_id' => 1, 'producto_id' => 1]);
    DB::table('sedes')->insert(['id' => 2, 'empresa_id' => 1]);
    DB::table('clientes')->insert(['id' => 2, 'empresa_id' => 1, 'razon_social' => 'Cliente de otra sede']);
    DB::table('productos')->insert(['id' => 2, 'empresa_id' => 1, 'nombre' => 'Producto de otra sede']);
    DB::table('pedidos')->insert(['id' => 2, 'usuario_id' => $usuario->id, 'cliente_id' => 2, 'empresa_id' => 1, 'sede_id' => 2]);
    DB::table('detalles_pedidos')->insert(['pedido_id' => 2, 'producto_id' => 2]);
    $datos = [
        'codigo' => 'DEV-PRUEBA', 'pedido_id' => 1, 'cliente_id' => 1, 'producto_id' => 1,
        'tipo' => 'Devolución', 'motivo' => 'Equipo defectuoso', 'estado' => 'Recibida',
        'fecha_solicitud' => now()->toDateString(),
    ];

    $this->withoutMiddleware(RegistrarSesionUsuario::class);
    $this->actingAs($usuario)->post(route('management.returns.store'), $datos)->assertRedirect();
    $solicitud = DevolucionGarantia::query()->firstOrFail();
    expect((int) $solicitud->creado_por_usuario_id)->toBe($usuario->id)
        ->and($solicitud->creator->id)->toBe($usuario->id)
        ->and(DB::table('registros_auditoria')->where('accion', 'return.created')->count())->toBe(1);
    $consulta = Request::create('/gestion/devoluciones-garantias', 'GET');
    $consulta->setUserResolver(fn (): User => $usuario);
    $formulario = app(DevolucionGarantiaController::class)->index($consulta)->getData()['module']['form_data'];
    expect($formulario['orders']->pluck('id')->all())->toBe([1])
        ->and($formulario['customers']->pluck('id')->all())->toBe([1])
        ->and($formulario['products']->pluck('id')->all())->toBe([1]);
    RegistroAuditoria::creating(fn () => throw new RuntimeException('Fallo sintético de auditoría.'));
    $this->post(route('management.returns.store'), array_replace($datos, ['codigo' => 'DEV-ROLLBACK']))->assertStatus(500);
    RegistroAuditoria::flushEventListeners();
    expect(DevolucionGarantia::query()->count())->toBe(1);
    $this->delete(route('management.returns.destroy', $solicitud))->assertRedirect();
    expect(DevolucionGarantia::query()->count())->toBe(1)
        ->and($solicitud->fresh()->activo)->toBeFalse()
        ->and(DB::table('registros_auditoria')->where('accion', 'return.deactivated')->count())->toBe(1);
    $this->delete(route('management.returns.destroy', $solicitud))->assertNotFound();
});
