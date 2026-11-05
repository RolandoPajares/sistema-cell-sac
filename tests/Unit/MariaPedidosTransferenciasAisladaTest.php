<?php

use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioInventario;
use App\Servicios\ServicioPedidos;
use App\Servicios\ServicioTransferenciasExistencias;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

it('comprueba checkout cancelacion y transferencia parcial en MariaDB aislado', function (): void {
    $pipe = getenv('TECHNOLOGY_CELL_QA_PIPE');
    $root = getenv('TECHNOLOGY_CELL_QA_ROOT');
    $database = getenv('TECHNOLOGY_CELL_QA_DATABASE');
    if (! is_string($pipe) || ! is_string($root) || ! is_string($database)) {
        $this->markTestSkipped('Requiere la instancia MariaDB descartable inicializada.');
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

    $migration = require database_path('migrations/2026_11_03_220504_add_cantidad_devuelta_to_detalles_transferencias_existencias.php');
    $migration->up();
    expect(Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_devuelta'))->toBeTrue();

    $countsBefore = [DB::table('productos')->count(), DB::table('pedidos')->count(),
        DB::table('transferencias_existencias')->count(), DB::table('movimientos_inventario')->count()];

    DB::beginTransaction();
    try {
        $administrator = User::query()->where('correo', 'admin-qa@example.test')->firstOrFail();
        $companyId = (int) DB::table('empresas')->where('codigo', 'EMPRESA_QA')->value('id');
        $sourceSiteId = (int) DB::table('sedes')->where('empresa_id', $companyId)->where('codigo', 'SEDE_QA')->sole()->id;
        $sourceWarehouseId = (int) DB::table('almacenes')->where('sede_id', $sourceSiteId)->where('codigo', 'ALMACEN_QA')->sole()->id;
        $categoryId = DB::table('categorias')->insertGetId([
            'nombre' => 'Categoria QA '.Str::random(8), 'empresa_id' => $companyId,
        ]);
        $product = Producto::query()->create([
            'empresa_id' => $companyId, 'categoria_id' => $categoryId,
            'categoria' => 'Categoria QA', 'marca' => 'Marca QA', 'nombre' => 'Producto QA '.Str::random(8),
            'precio' => '100.00', 'existencias' => 5, 'activo' => true,
        ]);
        DB::table('existencias_almacen')->insert([
            'empresa_id' => $companyId, 'sede_id' => $sourceSiteId,
            'almacen_id' => $sourceWarehouseId, 'producto_id' => $product->id,
            'cantidad_fisica' => 5, 'cantidad_reservada' => 0,
        ]);
        $customer = User::query()->create([
            'nombre' => 'Cliente QA', 'correo' => 'cliente-'.Str::lower(Str::random(12)).'@example.test',
            'password' => 'ClaveQAsegura123', 'rol' => 'cliente_minorista', 'acceso_controlado' => true,
        ]);
        $customerRoleId = (int) DB::table('roles')->where('codigo', 'cliente_minorista')->value('id');
        DB::table('usuarios_roles')->insert([
            'usuario_id' => $customer->id, 'rol_id' => $customerRoleId, 'activo' => true,
        ]);

        $serializedProduct = Producto::query()->create([
            'empresa_id' => $companyId, 'categoria_id' => $categoryId,
            'categoria' => 'Categoria QA', 'marca' => 'Marca QA', 'nombre' => 'Equipo serial QA '.Str::random(8),
            'precio' => '200.00', 'existencias' => 2, 'activo' => true,
        ]);
        $variantId = DB::table('variantes_productos')->insertGetId([
            'producto_id' => $serializedProduct->id, 'nombre_color' => 'Negro',
            'existencias' => 2, 'controla_serie' => true,
        ]);
        DB::table('existencias_almacen')->insert([
            'empresa_id' => $companyId, 'sede_id' => $sourceSiteId,
            'almacen_id' => $sourceWarehouseId, 'producto_id' => $serializedProduct->id,
            'variante_id' => $variantId, 'cantidad_fisica' => 2, 'cantidad_reservada' => 0,
        ]);
        expect(app(ServicioInventario::class)->disponibilidadParaOperacion($customer, [$serializedProduct->id])[$serializedProduct->id])->toBe(0);

        $checkoutToken = bin2hex(random_bytes(32));
        $this->actingAs($customer)->withSession(['cart' => [$product->id => 1], 'checkout_token' => $checkoutToken])
            ->post(route('checkout'), ['checkout_token' => $checkoutToken])->assertRedirect();
        $order = Pedido::query()->where('usuario_id', $customer->id)->sole();
        expect((int) DB::table('existencias_almacen')->where('producto_id', $product->id)->value('cantidad_fisica'))->toBe(4)
            ->and($order->estado)->toBe('Pendiente')
            ->and(app(ServicioPedidos::class)->checkout($customer, [$product->id => 1], null, null, $checkoutToken)->id)->toBe($order->id)
            ->and(Pedido::query()->where('usuario_id', $customer->id)->count())->toBe(1);

        $this->actingAs($administrator)->patch(route('orders.status', $order), ['estado' => 'Cancelado'])->assertRedirect();
        $this->actingAs($administrator)->patch(route('orders.status', $order), ['estado' => 'Cancelado'])->assertRedirect();
        expect((int) DB::table('existencias_almacen')->where('producto_id', $product->id)->value('cantidad_fisica'))->toBe(5)
            ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'pedido_cancelacion')
                ->where('documento_id', (string) $order->id)->count())->toBe(1);

        $destinationSiteId = DB::table('sedes')->insertGetId([
            'empresa_id' => $companyId, 'codigo' => 'DEST-'.Str::upper(Str::random(8)),
            'nombre' => 'Sede destino QA', 'activa' => true,
        ]);
        $destinationWarehouseId = DB::table('almacenes')->insertGetId([
            'sede_id' => $destinationSiteId, 'codigo' => 'DEST-QA',
            'nombre' => 'Almacen destino QA', 'activo' => true,
        ]);
        $logistics = User::query()->create([
            'nombre' => 'Logistica QA', 'correo' => 'logistica-'.Str::lower(Str::random(12)).'@example.test',
            'password' => 'ClaveQAsegura123', 'rol' => 'compras_logistica', 'acceso_controlado' => true,
        ]);
        $logisticsRoleId = (int) DB::table('roles')->where('codigo', 'compras_logistica')->value('id');
        DB::table('usuarios_sedes_roles')->insert([
            'usuario_id' => $logistics->id, 'sede_id' => $sourceSiteId,
            'rol_id' => $logisticsRoleId, 'activo' => true,
        ]);
        $authorization = app(ServicioAutorizacion::class);
        expect($authorization->allowsRecord($logistics, 'gestion.inventario.transferir', $companyId, $sourceSiteId))->toBeTrue()
            ->and($authorization->allowsRecord($logistics, 'gestion.inventario.transferir', $companyId, $destinationSiteId))->toBeFalse();
        DB::table('usuarios_sedes_roles')->where('usuario_id', $logistics->id)
            ->update(['vigente_hasta' => now()->subSecond()]);
        expect($authorization->allowsRecord($logistics, 'gestion.inventario.transferir', $companyId, $sourceSiteId))->toBeFalse();
        $transfers = app(ServicioTransferenciasExistencias::class);
        $requestData = ['almacen_origen_id' => $sourceWarehouseId, 'almacen_destino_id' => $destinationWarehouseId,
            'clave_idempotencia' => (string) Str::uuid(), 'items' => [['producto_id' => $product->id, 'cantidad' => 3]]];
        $transfer = $transfers->request($requestData, $administrator, null);
        $detailId = $transfer->detalles->sole()->id;
        expect($transfers->request($requestData, $administrator, null)->id)->toBe($transfer->id)
            ->and((int) DB::table('existencias_almacen')->where('almacen_id', $sourceWarehouseId)
                ->where('producto_id', $product->id)->value('cantidad_fisica'))->toBe(5);

        $transfer = $transfers->approve($transfer, $administrator, (string) Str::uuid(), null);
        expect($transfer->estado)->toBe('aprobada')
            ->and((int) DB::table('existencias_almacen')->where('almacen_id', $sourceWarehouseId)
                ->where('producto_id', $product->id)->value('cantidad_fisica'))->toBe(5);
        $dispatchData = ['clave_idempotencia' => (string) Str::uuid(),
            'items' => [['detalle_id' => $detailId, 'cantidad' => 3]]];
        $transfer = $transfers->dispatch($transfer, $dispatchData, $administrator, null);
        $transfers->dispatch($transfer, $dispatchData, $administrator, null);
        expect($transfer->estado)->toBe('en tránsito')
            ->and((int) DB::table('existencias_almacen')->where('almacen_id', $sourceWarehouseId)
                ->where('producto_id', $product->id)->value('cantidad_fisica'))->toBe(2)
            ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'transferencia_despacho')
                ->where('documento_id', (string) $transfer->id)->count())->toBe(1);

        $partialData = ['clave_idempotencia' => (string) Str::uuid(),
            'items' => [['detalle_id' => $detailId, 'cantidad' => 1]]];
        $transfer = $transfers->receive($transfer, $partialData, $administrator, null);
        $transfers->receive($transfer, $partialData, $administrator, null);
        expect($transfer->estado)->toBe('recibida parcialmente')
            ->and((int) DB::table('existencias_almacen')->where('almacen_id', $destinationWarehouseId)
                ->where('producto_id', $product->id)->value('cantidad_fisica'))->toBe(1);
        expect(fn () => $transfers->close($transfer, ['clave_idempotencia' => (string) Str::uuid()], $administrator, null))
            ->toThrow(HttpException::class);
        expect(fn () => $transfers->receive($transfer, ['clave_idempotencia' => (string) Str::uuid(),
            'items' => [['detalle_id' => $detailId, 'cantidad' => 3]]], $administrator, null))
            ->toThrow(HttpException::class);

        $transfer = $transfers->receive($transfer, ['clave_idempotencia' => (string) Str::uuid(),
            'items' => [['detalle_id' => $detailId, 'cantidad' => 2]]], $administrator, null);
        expect($transfer->estado)->toBe('recibida totalmente')
            ->and((int) DB::table('existencias_almacen')->where('almacen_id', $destinationWarehouseId)
                ->where('producto_id', $product->id)->value('cantidad_fisica'))->toBe(3)
            ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'transferencia_recepcion')
                ->where('documento_id', (string) $transfer->id)->count())->toBe(2);
        $closed = $transfers->close($transfer, ['clave_idempotencia' => (string) Str::uuid()], $administrator, null);
        expect($closed->estado)->toBe('cerrada');
    } finally {
        DB::rollBack();
    }
    expect([DB::table('productos')->count(), DB::table('pedidos')->count(),
        DB::table('transferencias_existencias')->count(), DB::table('movimientos_inventario')->count()])->toBe($countsBefore);
});
