<?php

use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Compartidos\Seguridad\ServicioMenuDinamico;
use App\Servicios\Gestion\Administracion\Seguridad\ServicioDelegacion;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    $pipe = getenv('TECHNOLOGY_CELL_QA_PIPE');
    $root = getenv('TECHNOLOGY_CELL_QA_ROOT');
    $database = getenv('TECHNOLOGY_CELL_QA_DATABASE');
    if (! is_string($pipe) || ! is_string($root) || ! is_string($database)) {
        $this->markTestSkipped('Requiere la instancia MariaDB descartable identificada.');
    }

    $temp = realpath(sys_get_temp_dir());
    $resolvedRoot = realpath($root);
    if ($temp === false || $resolvedRoot === false
        || ! preg_match('/\Atc_ventas_[a-f0-9]{32}\z/', $pipe)
        || ! preg_match('/\Atc_ventas_laravel_[a-z0-9]+\z/', $database)
        || ! str_starts_with(strtolower($resolvedRoot), strtolower($temp.DIRECTORY_SEPARATOR.'technology_cell_ventas_pipe_'))) {
        throw new RuntimeException('La conexion indicada no es una instancia temporal autorizada.');
    }

    $pdo = new PDO('mysql:host=.;port=1;unix_socket=\\\\.\\pipe\\'.$pipe.';dbname='.$database, 'root', '', [PDO::ATTR_TIMEOUT => 1]);
    $identity = $pdo->query('SELECT @@version, @@datadir, @@skip_networking, @@socket')->fetch(PDO::FETCH_NUM);
    if ($identity[0] !== '10.4.32-MariaDB' || realpath($identity[1]) !== realpath($resolvedRoot.DIRECTORY_SEPARATOR.'datos')
        || (int) $identity[2] !== 1 || $identity[3] !== $pipe) {
        throw new RuntimeException('La identidad MariaDB no corresponde al directorio descartable.');
    }

    config(['database.default' => 'mysql', 'database.connections.mysql.database' => $database, 'database.connections.mysql.url' => null]);
    DB::purge('mysql');
    DB::connection('mysql')->setPdo($pdo)->setReadPdo($pdo);
    DB::beginTransaction();
});

afterEach(function (): void {
    if (is_string(getenv('TECHNOLOGY_CELL_QA_DATABASE')) && DB::transactionLevel() > 0) {
        DB::rollBack();
    }
});

it('loads the inherited roles and protects administrative routes with the database menu', function (): void {
    $administrator = User::query()->where('correo', 'admin-qa@example.test')->firstOrFail();
    $siteId = (int) DB::table('sedes')->where('codigo', 'SEDE_QA')->value('id');
    $buyer = User::query()->create([
        'nombre' => 'Comprador QA', 'correo' => 'comprador-'.Str::lower(Str::random(12)).'@example.test',
        'password' => 'ClaveQAsegura123', 'rol' => 'compras_logistica', 'acceso_controlado' => true,
    ]);
    DB::table('usuarios_sedes_roles')->insert([
        'usuario_id' => $buyer->id, 'sede_id' => $siteId,
        'rol_id' => DB::table('roles')->where('codigo', 'compras_logistica')->value('id'),
        'activo' => true,
    ]);
    $menu = app(ServicioMenuDinamico::class)->paraUsuario($administrator);
    $routes = collect($menu['grupos'])->flatMap(fn (array $group): array => $group['items'])->pluck('route');

    expect(DB::table('roles')->where('es_sistema', true)->count())->toBe(7)
        ->and($menu['dinamico'])->toBeTrue()
        ->and($routes)->toContain('management.roles_permissions');

    $this->actingAs($buyer)->get(route('management.users'))->assertForbidden();
    $this->actingAs($administrator)->get(route('management.roles_permissions'))->assertOk();
    $this->get(route('catalog.index'))->assertOk();
});

it('refuses a second site to unassigned employees and preserves single-site stock operations', function (): void {
    $companyId = (int) DB::table('empresas')->where('codigo', 'EMPRESA_QA')->value('id');
    $primarySiteId = (int) DB::table('sedes')->where('codigo', 'SEDE_QA')->value('id');
    $primaryWarehouseId = (int) DB::table('almacenes')->where('sede_id', $primarySiteId)->value('id');
    $buyer = User::query()->create([
        'nombre' => 'Comprador QA', 'correo' => 'comprador-'.Str::lower(Str::random(12)).'@example.test',
        'password' => 'ClaveQAsegura123', 'rol' => 'compras_logistica', 'acceso_controlado' => true,
    ]);
    DB::table('usuarios_sedes_roles')->insert([
        'usuario_id' => $buyer->id, 'sede_id' => $primarySiteId,
        'rol_id' => DB::table('roles')->where('codigo', 'compras_logistica')->value('id'),
        'activo' => true,
    ]);
    $secondarySiteId = DB::table('sedes')->insertGetId([
        'empresa_id' => $companyId,
        'codigo' => 'AUDIT_SECONDARY',
        'nombre' => 'Sede temporal de prueba',
        'activa' => true,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);

    $authorization = app(ServicioAutorizacion::class);
    $delegation = app(ServicioDelegacion::class);
    $permissionId = (int) DB::table('permisos')->where('codigo', 'gestion.seguridad.asignar')->value('id');

    expect($authorization->allowsRecord($buyer, 'gestion.compras.consultar', $companyId, $secondarySiteId))->toBeFalse()
        ->and($delegation->puedeDelegarPermisos($buyer, [$permissionId], 'empresa'))->toBeFalse();

    $this->withSession(['sede_actual_id' => $secondarySiteId])
        ->actingAs($buyer)
        ->get(route('management.purchases'))
        ->assertForbidden();

    $categoryId = DB::table('categorias')->insertGetId(['empresa_id' => $companyId, 'nombre' => 'Categoria QA '.Str::random(8)]);
    $product = Producto::query()->create([
        'empresa_id' => $companyId, 'categoria_id' => $categoryId,
        'categoria' => 'Categoria QA', 'marca' => 'Marca QA', 'nombre' => 'Producto QA '.Str::random(8),
        'precio' => '20.00', 'existencias' => 2, 'activo' => true,
    ]);
    DB::table('existencias_almacen')->insert([
        'empresa_id' => $companyId, 'sede_id' => $primarySiteId,
        'almacen_id' => $primaryWarehouseId, 'producto_id' => $product->id,
        'cantidad_fisica' => 2, 'cantidad_reservada' => 0,
    ]);
    try {
        app(ServicioInventario::class)->adjust($product, 'salida', 1, 'Prueba temporal de sede', $buyer);
        $this->fail('El inventario no debe operar con dos sedes activas.');
    } catch (HttpException $exception) {
        expect($exception->getStatusCode())->toBeIn([403, 503]);
    }
    expect((int) DB::table('existencias_almacen')->where('producto_id', $product->id)->value('cantidad_fisica'))->toBe(2);
});

it('keeps ecommerce checkout and the three stock balances synchronized', function (): void {
    $companyId = (int) DB::table('empresas')->where('codigo', 'EMPRESA_QA')->value('id');
    $siteId = (int) DB::table('sedes')->where('codigo', 'SEDE_QA')->value('id');
    $warehouseId = (int) DB::table('almacenes')->where('sede_id', $siteId)->value('id');
    $categoryId = DB::table('categorias')->insertGetId([
        'empresa_id' => $companyId, 'nombre' => 'Categoria QA '.Str::random(8),
    ]);
    $product = Producto::query()->create([
        'empresa_id' => $companyId, 'categoria_id' => $categoryId,
        'categoria' => 'Categoria QA', 'marca' => 'Marca QA', 'nombre' => 'Producto QA '.Str::random(8),
        'precio' => '100.00', 'existencias' => 3, 'activo' => true,
    ]);
    $variantId = DB::table('variantes_productos')->insertGetId([
        'producto_id' => $product->id, 'nombre_color' => 'Negro', 'existencias' => 3,
        'controla_serie' => false,
    ]);
    DB::table('existencias_almacen')->insert([
        'empresa_id' => $companyId, 'sede_id' => $siteId, 'almacen_id' => $warehouseId,
        'producto_id' => $product->id, 'variante_id' => $variantId,
        'cantidad_fisica' => 3, 'cantidad_reservada' => 0,
    ]);
    $customer = User::query()->create([
        'nombre' => 'Cliente QA', 'correo' => 'cliente-'.Str::lower(Str::random(12)).'@example.test',
        'password' => 'ClaveQAsegura123', 'rol' => 'cliente_minorista', 'acceso_controlado' => true,
    ]);
    DB::table('usuarios_roles')->insert([
        'usuario_id' => $customer->id,
        'rol_id' => DB::table('roles')->where('codigo', 'cliente_minorista')->value('id'),
        'activo' => true,
    ]);
    $checkoutToken = bin2hex(random_bytes(32));
    $this->actingAs($customer)->withSession([
        'cart' => [$product->id => 1], 'checkout_token' => $checkoutToken,
    ])->post(route('checkout'), ['checkout_token' => $checkoutToken])->assertRedirect();

    $order = Pedido::query()->where('usuario_id', $customer->id)->sole();
    expect((int) $order->empresa_id)->toBe($companyId)
        ->and((int) $order->sede_id)->toBe($siteId)
        ->and((int) $order->almacen_id)->toBe($warehouseId)
        ->and((int) $product->fresh()->existencias)->toBe(2)
        ->and((int) DB::table('variantes_productos')->where('id', $variantId)->value('existencias'))->toBe(2)
        ->and((int) DB::table('existencias_almacen')->where('variante_id', $variantId)->value('cantidad_fisica'))->toBe(2)
        ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'pedido')
            ->where('documento_id', (string) $order->id)->count())->toBe(1)
        ->and(DB::table('registros_auditoria')->where('accion', 'order.created')
            ->where('entidad', 'pedidos')->where('entidad_id', $order->id)->exists())->toBeTrue();
});
