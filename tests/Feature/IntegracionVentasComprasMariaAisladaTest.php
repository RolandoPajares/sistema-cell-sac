<?php

use App\Models\Auditoria\RegistroAuditoria;
use App\Models\Comercio\Venta;
use App\Models\User;
use App\Servicios\Gestion\Comercial\Compras\ServicioCompras;
use App\Servicios\Gestion\Comercial\Ventas\ServicioVentas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\IsolatedMariaDatabase;

it('tests the migration and commercial transactions exclusively on an isolated MariaDB', function (): void {
    $pipe = getenv('TECHNOLOGY_CELL_QA_PIPE');
    $root = getenv('TECHNOLOGY_CELL_QA_ROOT');
    $database = getenv('TECHNOLOGY_CELL_QA_DATABASE');
    if (! is_string($pipe) || ! is_string($root) || ! is_string($database)) {
        $this->markTestSkipped('La prueba requiere una instancia MariaDB descartable expresamente identificada.');
    }
    $temp = realpath(sys_get_temp_dir());
    $resolvedRoot = realpath($root);
    expect($temp)->not->toBeFalse()->and($resolvedRoot)->not->toBeFalse();
    if (! preg_match('/\Atc_ventas_[a-f0-9]{32}\z/', $pipe) || ! preg_match('/\Atc_ventas_laravel_[a-z0-9]+\z/', $database)
        || ! str_starts_with(strtolower($resolvedRoot), strtolower($temp.DIRECTORY_SEPARATOR.'technology_cell_ventas_pipe_'))) {
        throw new RuntimeException('La instancia indicada no es una instancia temporal autorizada.');
    }
    $pdo = new PDO('mysql:host=.;port=1;unix_socket=\\\\.\\pipe\\'.$pipe.';dbname='.$database, 'root', '', [PDO::ATTR_TIMEOUT => 1]);
    $identity = $pdo->query('SELECT @@version, @@datadir, @@skip_networking, @@socket')->fetch(PDO::FETCH_NUM);
    if ($identity[0] !== '10.4.32-MariaDB' || realpath($identity[1]) !== realpath($resolvedRoot.DIRECTORY_SEPARATOR.'datos')
        || (int) $identity[2] !== 1 || $identity[3] !== $pipe) {
        throw new RuntimeException('La identidad de MariaDB no coincide con la instancia aislada.');
    }
    [$pdo, $database] = IsolatedMariaDatabase::createFresh($pdo, $resolvedRoot, $pipe);
    config(['database.default' => 'mysql', 'database.connections.mysql.database' => $database, 'database.connections.mysql.url' => null]);
    DB::purge('mysql');
    DB::connection('mysql')->setPdo($pdo)->setReadPdo($pdo);

    expect(Schema::hasColumn('ventas', 'canal'))->toBeFalse()->and(Schema::hasColumn('ventas', 'observaciones'))->toBeFalse();
    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'previa', 'razon_social' => 'Empresa sintética previa']);
    DB::table('sedes')->insert(['id' => 1, 'empresa_id' => 1, 'codigo' => 'previa', 'nombre' => 'Sede sintética']);
    DB::table('almacenes')->insert(['id' => 1, 'sede_id' => 1, 'codigo' => 'previo', 'nombre' => 'Almacén sintético']);
    DB::table('ventas')->insert(['id' => 1, 'empresa_id' => 1, 'sede_id' => 1, 'almacen_id' => 1, 'codigo' => 'ANTERIOR', 'total' => '12.34']);
    $original = (array) DB::table('ventas')->where('id', 1)->first();
    $migration = require database_path('migrations/2026_11_03_192348_agregar_canal_y_observaciones_a_ventas.php');
    $migration->up();
    $actual = (array) DB::table('ventas')->where('id', 1)->first();
    expect(array_intersect_key($actual, $original))->toBe($original)
        ->and($actual['canal'])->toBeNull()->and($actual['observaciones'])->toBeNull();

    DB::transaction(function (): void {
        $companyId = DB::table('empresas')->insertGetId(['codigo' => 'sintetica', 'razon_social' => 'Empresa sintética']);
        $siteId = DB::table('sedes')->insertGetId(['empresa_id' => $companyId, 'codigo' => 'sede', 'nombre' => 'Sede sintética']);
        $warehouseId = DB::table('almacenes')->insertGetId(['sede_id' => $siteId, 'codigo' => 'almacen', 'nombre' => 'Almacén sintético']);
        $user = User::create(['nombre' => 'Usuario sintético', 'correo' => 'maria@example.test', 'password' => 'ClaveSegura123']);
        $moduleId = DB::table('modulos')->insertGetId(['codigo' => 'operaciones_sinteticas', 'nombre' => 'Operaciones']);
        $roleId = DB::table('roles')->insertGetId(['codigo' => 'operador_sintetico', 'nombre' => 'Operador', 'alcance_permitido' => 'sede']);
        foreach (['ventas.consultar', 'ventas.crear', 'ventas.anular', 'compras.consultar', 'compras.crear', 'compras.aprobar', 'compras.recibir'] as $code) {
            $permissionId = DB::table('permisos')->insertGetId(['modulo_id' => $moduleId, 'codigo' => 'gestion.'.$code, 'accion' => substr($code, strrpos($code, '.') + 1), 'nombre' => $code]);
            DB::table('roles_permisos')->insert(['rol_id' => $roleId, 'permiso_id' => $permissionId, 'alcance' => 'sede']);
        }
        DB::table('usuarios_sedes_roles')->insert(['usuario_id' => $user->id, 'sede_id' => $siteId, 'rol_id' => $roleId]);
        $categoryId = DB::table('categorias')->insertGetId(['nombre' => 'Categoría sintética', 'empresa_id' => $companyId]);
        $productId = DB::table('productos')->insertGetId(['empresa_id' => $companyId, 'marca' => 'Sintética', 'nombre' => 'Producto sintético', 'categoria_id' => $categoryId, 'categoria' => 'Sintética', 'precio' => '12.35', 'existencias' => 5]);
        DB::table('existencias_almacen')->insert(['empresa_id' => $companyId, 'sede_id' => $siteId, 'almacen_id' => $warehouseId, 'producto_id' => $productId, 'cantidad_fisica' => 5, 'cantidad_reservada' => 0]);
        $customerId = DB::table('clientes')->insertGetId(['empresa_id' => $companyId, 'documento' => 'QA-CLIENTE', 'nombre_contacto' => 'Cliente sintético', 'correo' => 'cliente@example.test']);
        $supplierId = DB::table('proveedores')->insertGetId(['empresa_id' => $companyId, 'nombre' => 'Proveedor sintético', 'ruc' => 'QA-PROVEEDOR', 'correo' => 'proveedor@example.test']);
        $saleData = ['cliente_id' => $customerId, 'tipo_venta' => 'fisica', 'fecha_venta' => now()->toDateString(), 'observaciones' => 'Prueba sintética', 'items' => [['producto_id' => $productId, 'cantidad' => 2]]];
        $saleService = app(ServicioVentas::class);
        expect(fn () => $saleService->anular(Venta::findOrFail(1), $user, 'Prueba de ámbito', null))->toThrow(HttpException::class);
        $sale = $saleService->registrarFisica($saleData, $user, str_repeat('a', 64), null);
        expect($sale->estado)->toBe('confirmada')
            ->and((float) $sale->subtotal)->toBe(24.70)
            ->and((float) $sale->impuestos)->toBe(4.45)
            ->and((float) $sale->total)->toBe(29.15)
            ->and((int) DB::table('existencias_almacen')->where('producto_id', $productId)->value('cantidad_fisica'))->toBe(3);
        expect($saleService->registrarFisica($saleData, $user, str_repeat('a', 64), null)->id)->toBe($sale->id);
        $saleService->anular($sale, $user, 'Prueba de anulación', null);
        $saleService->anular($sale, $user, 'Prueba de anulación', null);
        expect((int) DB::table('existencias_almacen')->where('producto_id', $productId)->value('cantidad_fisica'))->toBe(5)
            ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'anulacion_venta')->where('documento_id', $sale->id)->count())->toBe(1)
            ->and(DB::table('pagos')->count())->toBe(0);

        $purchaseData = ['proveedor_id' => $supplierId, 'fecha_compra' => now()->toDateString(), 'items' => [['producto_id' => $productId, 'cantidad' => 2, 'costo_unitario' => '10.10']]];
        $purchaseService = app(ServicioCompras::class);
        $purchase = $purchaseService->registrar($purchaseData, $user, str_repeat('b', 64), null);
        expect($purchase->estado)->toBe('Pendiente')->and((float) $purchase->total)->toBe(20.20)
            ->and($purchaseService->registrar($purchaseData, $user, str_repeat('b', 64), null)->id)->toBe($purchase->id);
        $purchaseService->aprobar($purchase, $user, null);
        $purchaseService->recibir($purchase, $user, null);
        $purchaseService->recibir($purchase, $user, null);
        expect((int) DB::table('existencias_almacen')->where('producto_id', $productId)->value('cantidad_fisica'))->toBe(7)
            ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->where('documento_id', $purchase->id)->count())->toBe(1);
        RegistroAuditoria::creating(fn () => throw new RuntimeException('Fallo sintético de auditoría.'));
        expect(fn () => $saleService->registrarFisica($saleData, $user, str_repeat('c', 64), null))->toThrow(RuntimeException::class);
        RegistroAuditoria::flushEventListeners();
        expect((int) DB::table('existencias_almacen')->where('producto_id', $productId)->value('cantidad_fisica'))->toBe(7);
        DB::table('usuarios_sedes_roles')->where('usuario_id', $user->id)->update(['activo' => false]);
        expect(fn () => $saleService->registrarFisica($saleData, $user, str_repeat('d', 64), null))->toThrow(HttpException::class);
    });
});
