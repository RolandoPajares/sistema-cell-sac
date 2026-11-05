<?php

use App\Models\Comercio\Pedido;
use App\Models\User;
use Database\Seeders\RbacInicialSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

beforeEach(function (): void {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null]);
    DB::purge('sqlite');
    RefreshDatabaseState::$migrated = true;
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
    Schema::create('empresas', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo')->unique();
        $table->string('razon_social');
        $table->boolean('activo')->default(true);
    });
    Schema::create('sedes', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('empresa_id');
        $table->string('codigo');
        $table->string('nombre');
        $table->string('zona_horaria')->default('America/Lima');
        $table->boolean('activa')->default(true);
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('almacenes', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('sede_id');
        $table->string('codigo');
        $table->string('nombre');
        $table->boolean('activo')->default(true);
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
    Schema::create('modulos', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo')->unique();
        $table->string('nombre');
        $table->string('descripcion')->nullable();
        $table->string('icono')->nullable();
        $table->unsignedInteger('orden')->default(0);
        $table->boolean('habilitado')->default(true);
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('pantallas', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('modulo_id');
        $table->string('codigo')->unique();
        $table->string('nombre');
        $table->string('ruta')->nullable();
        $table->string('icono')->nullable();
        $table->boolean('habilitada')->default(true);
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('permisos', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('modulo_id');
        $table->foreignId('pantalla_id')->nullable();
        $table->string('codigo')->unique();
        $table->string('accion');
        $table->string('nombre');
        $table->boolean('es_sistema')->default(true);
        $table->boolean('activo')->default(true);
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('roles_permisos', function (Blueprint $table): void {
        $table->foreignId('rol_id');
        $table->foreignId('permiso_id');
        $table->string('alcance');
        $table->timestamp('creado_en')->nullable();
    });
    Schema::create('usuarios_roles', function (Blueprint $table): void {
        $table->foreignId('usuario_id');
        $table->foreignId('rol_id');
        $table->foreignId('asignado_por_usuario_id')->nullable();
        $table->timestamp('asignado_en')->nullable();
        $table->timestamp('vigente_hasta')->nullable();
        $table->boolean('activo')->default(true);
    });
    Schema::create('usuarios_sedes_roles', function (Blueprint $table): void {
        $table->foreignId('usuario_id');
        $table->foreignId('sede_id');
        $table->foreignId('rol_id');
        $table->foreignId('asignado_por_usuario_id')->nullable();
        $table->timestamp('asignado_en')->nullable();
        $table->timestamp('vigente_hasta')->nullable();
        $table->boolean('activo')->default(true);
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
        $table->foreignId('rol_id');
        $table->foreignId('menu_id');
        $table->timestamp('creado_en')->nullable();
    });
    Schema::create('elementos_menu', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('menu_id');
        $table->foreignId('padre_id')->nullable();
        $table->foreignId('pantalla_id')->nullable();
        $table->foreignId('permiso_id')->nullable();
        $table->string('codigo');
        $table->string('etiqueta');
        $table->string('icono')->nullable();
        $table->string('tipo');
        $table->unsignedInteger('orden')->default(0);
        $table->boolean('habilitado')->default(true);
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('versiones_inicializacion_control_acceso', function (Blueprint $table): void {
        $table->string('codigo')->primary();
        $table->timestamp('aplicado_en');
    });
    Schema::create('pedidos', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('usuario_id');
        $table->foreignId('empresa_id')->nullable();
        $table->foreignId('sede_id')->nullable();
        $table->foreignId('almacen_id')->nullable();
        $table->foreignId('cliente_id')->nullable();
        $table->decimal('subtotal', 10, 2)->default(0);
        $table->decimal('descuento', 10, 2)->default(0);
        $table->decimal('total', 10, 2);
        $table->string('estado')->default('Pendiente');
        $table->string('canal')->nullable();
        $table->timestamp('creado_en')->nullable();
    });
    Schema::create('detalles_pedidos', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('pedido_id');
        $table->foreignId('producto_id');
        $table->unsignedInteger('cantidad');
        $table->decimal('precio_unitario', 10, 2);
    });
    Schema::create('productos', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('empresa_id')->nullable();
        $table->string('nombre');
        $table->string('marca')->default('Marca de prueba');
        $table->string('url_imagen')->nullable();
        $table->boolean('activo')->default(true);
    });
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'TECHCELL', 'razon_social' => 'Empresa sintética']);
    DB::table('sedes')->insert(['id' => 1, 'empresa_id' => 1, 'codigo' => 'PRINCIPAL', 'nombre' => 'Sede sintética']);
    DB::table('almacenes')->insert(['id' => 1, 'sede_id' => 1, 'codigo' => 'PRINCIPAL', 'nombre' => 'Almacén sintético']);
    app(RbacInicialSeeder::class)->run();
});

afterEach(function (): void {
    RefreshDatabaseState::$migrated = false;
});

function crearUsuarioParaPedidos(string $codigoRol, ?string $nombre = null): User
{
    $usuario = User::create([
        'nombre' => $nombre ?? 'Usuario sintético',
        'correo' => 'prueba-'.Str::uuid().'@example.test',
        'password' => Hash::make('ClaveSintetica123'),
        'rol' => $codigoRol,
    ]);
    $rol = DB::table('roles')->where('codigo', $codigoRol)->first(['id', 'alcance_permitido']);
    if ($rol->alcance_permitido === 'sede') {
        DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $usuario->id, 'sede_id' => 1, 'rol_id' => $rol->id, 'activo' => true]);
    } else {
        DB::table('usuarios_roles')->insert(['usuario_id' => $usuario->id, 'rol_id' => $rol->id, 'activo' => true]);
    }

    return $usuario;
}

function crearPedidoParaFiltroPedidosOnline(User $customer, string $status, string $channel, DateTimeInterface $createdAt): Pedido
{
    $order = Pedido::query()->create([
        'usuario_id' => $customer->getKey(),
        'empresa_id' => 1,
        'sede_id' => 1,
        'subtotal' => 100,
        'descuento' => 0,
        'total' => 100,
        'estado' => $status,
        'creado_en' => $createdAt,
    ]);

    $order->forceFill(['canal' => $channel])->save();

    return $order;
}

it('filters stored ecommerce orders without confusing buyer type with channel', function () {
    $administrator = crearUsuarioParaPedidos('administrador');
    $retailCustomer = crearUsuarioParaPedidos('cliente_minorista', 'Cliente Minorista');
    $wholesaleCustomer = crearUsuarioParaPedidos('cliente_mayorista', 'Cliente Mayorista');
    $retailOrder = crearPedidoParaFiltroPedidosOnline($retailCustomer, 'Pendiente', 'ecommerce', now()->subHours(2));
    $wholesaleOrder = crearPedidoParaFiltroPedidosOnline($wholesaleCustomer, 'Pendiente', 'ecommerce', now()->subHour());
    $expectedIds = [$retailOrder->getKey(), $wholesaleOrder->getKey()];
    sort($expectedIds);
    expect(Pedido::query()->where('canal', 'ecommerce')->count())->toBe(2);

    $response = $this->actingAs($administrator)
        ->get(route('management.orders', ['canal' => 'ecommerce']))
        ->assertOk();
    $filteredIds = $response->viewData('orders')->getCollection()->modelKeys();
    sort($filteredIds);

    expect($filteredIds)->toEqual($expectedIds);
});

it('combines channel status and period filters without broadening the result', function () {
    $administrator = crearUsuarioParaPedidos('administrador');
    $customer = crearUsuarioParaPedidos('cliente_minorista');
    $matchingOrder = crearPedidoParaFiltroPedidosOnline($customer, 'Pendiente', 'ecommerce', now()->subDays(2));
    crearPedidoParaFiltroPedidosOnline($customer, 'Cancelado', 'ecommerce', now()->subDay());
    crearPedidoParaFiltroPedidosOnline($customer, 'En proceso', 'ecommerce', now()->subDay());
    crearPedidoParaFiltroPedidosOnline($customer, 'Pendiente', 'ecommerce', now()->subDays(8));

    $response = $this->actingAs($administrator)
        ->get(route('management.orders', [
            'canal' => 'ecommerce',
            'estado' => 'pendiente',
            'periodo' => '7',
        ]))
        ->assertOk();
    expect($response->viewData('filters')['canal'])->toBe('ecommerce');
    $orders = $response->viewData('orders');

    expect($orders->total())->toBe(1)
        ->and($orders->getCollection()->first()?->is($matchingOrder))->toBeTrue();
});

it('returns no rows for an unknown channel instead of dropping that filter', function () {
    $administrator = crearUsuarioParaPedidos('administrador');
    $customer = crearUsuarioParaPedidos('cliente_minorista');
    crearPedidoParaFiltroPedidosOnline($customer, 'Pendiente', 'ecommerce', now());

    $this->actingAs($administrator)
        ->get(route('management.orders', ['canal' => 'tienda_online']))
        ->assertOk()
        ->assertViewHas('orders', fn ($orders): bool => $orders->total() === 0);

    $this->actingAs($administrator)
        ->get(route('management.orders', ['canal' => '0']))
        ->assertOk()
        ->assertViewHas('orders', fn ($orders): bool => $orders->total() === 0);
});

it('rejects malformed channel filters instead of ignoring them', function () {
    $administrator = crearUsuarioParaPedidos('administrador');

    $this->actingAs($administrator)
        ->getJson(route('management.orders', ['canal' => ['ecommerce']]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('canal');
});

it('shows payment as unverified for pending delivered and cancelled orders without financial records', function () {
    $administrator = crearUsuarioParaPedidos('administrador');
    $customer = crearUsuarioParaPedidos('cliente_minorista');
    crearPedidoParaFiltroPedidosOnline($customer, 'Pendiente', 'ecommerce', now());
    crearPedidoParaFiltroPedidosOnline($customer, 'Entregado', 'ecommerce', now()->subMinute());
    crearPedidoParaFiltroPedidosOnline($customer, 'Cancelado', 'ecommerce', now()->subMinutes(2));

    $this->actingAs($administrator)
        ->get(route('management.orders'))
        ->assertOk()
        ->assertSee('Estado de pago')
        ->assertSee('No verificado')
        ->assertSee('Sin fuente financiera integrada')
        ->assertDontSee('Reembolsado')
        ->assertDontSee('Pagado');
});

it('restricts an authorized site role to its assigned site and denies insufficient scope', function () {
    $siteEmployee = crearUsuarioParaPedidos('ventas_minoristas');
    $unrelatedUser = crearUsuarioParaPedidos('marketing');
    $customer = crearUsuarioParaPedidos('cliente_minorista');
    $this->seed(RbacInicialSeeder::class);

    $companyId = (int) DB::table('empresas')->where('codigo', 'TECHCELL')->value('id');
    $authorizedSiteId = (int) DB::table('sedes')->where('empresa_id', $companyId)->where('codigo', 'PRINCIPAL')->value('id');
    $unauthorizedSiteId = DB::table('sedes')->insertGetId([
        'empresa_id' => $companyId,
        'codigo' => 'SECUNDARIA-TEST',
        'nombre' => 'Sede no asignada de prueba',
        'zona_horaria' => 'America/Lima',
        'activa' => true,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    $authorizedOrder = crearPedidoParaFiltroPedidosOnline($customer, 'Pendiente', 'ecommerce', now());
    $authorizedOrder->forceFill(['empresa_id' => $companyId, 'sede_id' => $authorizedSiteId])->save();
    $unauthorizedOrder = crearPedidoParaFiltroPedidosOnline($customer, 'Pendiente', 'ecommerce', now()->subMinute());
    $unauthorizedOrder->forceFill(['empresa_id' => $companyId, 'sede_id' => $unauthorizedSiteId])->save();

    $this->withSession(['sede_actual_id' => $authorizedSiteId])
        ->actingAs($siteEmployee)
        ->get(route('management.orders'))
        ->assertOk()
        ->assertSee('Pedido #'.$authorizedOrder->getKey())
        ->assertDontSee('Pedido #'.$unauthorizedOrder->getKey());

    $this->withSession(['sede_actual_id' => $unauthorizedSiteId])
        ->actingAs($siteEmployee)
        ->get(route('management.orders'))
        ->assertForbidden();

    $this->actingAs($unrelatedUser)
        ->get(route('management.orders'))
        ->assertForbidden();
});

it('excludes a buyer after RBAC revocation even if the legacy role remains', function (): void {
    $seller = crearUsuarioParaPedidos('ventas_minoristas');
    $buyer = crearUsuarioParaPedidos('cliente_minorista');
    $order = crearPedidoParaFiltroPedidosOnline($buyer, 'Pendiente', 'ecommerce', now());

    $this->withSession(['sede_actual_id' => 1])->actingAs($seller)
        ->get(route('management.orders'))
        ->assertOk()
        ->assertViewHas('orders', fn ($orders): bool => $orders->getCollection()->modelKeys() === [$order->id]);

    DB::table('usuarios_roles')->where('usuario_id', $buyer->id)->update(['activo' => false]);

    expect($buyer->fresh()->rol)->toBe('cliente_minorista');
    $this->withSession(['sede_actual_id' => 1])->actingAs($seller)
        ->get(route('management.orders'))
        ->assertOk()
        ->assertViewHas('orders', fn ($orders): bool => $orders->total() === 0);
});
