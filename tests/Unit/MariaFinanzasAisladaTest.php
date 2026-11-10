<?php

use App\Models\Finanzas\Caja;
use App\Models\User;
use App\Servicios\Gestion\Finanzas\ServicioCajas;
use App\Servicios\Gestion\Finanzas\ServicioOperacionesFinancieras;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

it('comprueba login caja arqueo y pagos manuales en MariaDB aislado', function (): void {
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

    $countsBefore = [DB::table('cajas')->count(), DB::table('pagos')->count(),
        DB::table('movimientos_caja')->count(), DB::table('pedidos')->count()];

    DB::beginTransaction();
    try {
        $administrator = User::query()->where('correo', 'admin-qa@example.test')->firstOrFail();
        $companyId = (int) DB::table('empresas')->where('codigo', 'EMPRESA_QA')->value('id');
        $siteId = (int) DB::table('sedes')->where('empresa_id', $companyId)->value('id');
        $warehouseId = (int) DB::table('almacenes')->where('sede_id', $siteId)->value('id');

        $organizationCounts = [DB::table('empresas')->count(), DB::table('sedes')->count(),
            DB::table('almacenes')->count(), DB::table('usuarios')->count()];
        $this->artisan('technology-cell:inicializar', ['--vista-previa' => true])->assertExitCode(0);
        expect([DB::table('empresas')->count(), DB::table('sedes')->count(),
            DB::table('almacenes')->count(), DB::table('usuarios')->count()])->toBe($organizationCounts);

        $this->post(route('login.store'), ['correo' => $administrator->correo, 'password' => 'ClaveQAsegura123'])
            ->assertRedirect(route('management.dashboard'));
        $this->assertAuthenticatedAs($administrator);
        $this->withSession(['sede_actual_id' => $siteId]);

        $boxes = app(ServicioCajas::class);
        $box = $boxes->crear(['sede_id' => $siteId, 'codigo' => 'CAJA-'.Str::upper(Str::random(8)),
            'nombre' => 'Caja sintética QA'], $administrator, null);
        $session = $boxes->abrirSesion($box, '100.00', (string) Str::uuid(), $administrator, null);
        $boxes->registrarMovimiento($session, 'entrada', '20.00', 'Ingreso contado QA',
            (string) Str::uuid(), $administrator, null);
        $boxes->registrarMovimiento($session, 'salida', '10.00', 'Retiro contado QA',
            (string) Str::uuid(), $administrator, null);
        expect($boxes->arqueo($session)['saldo_esperado'])->toBe('110.00');

        $orderId = DB::table('pedidos')->insertGetId(['usuario_id' => $administrator->id,
            'empresa_id' => $companyId, 'sede_id' => $siteId, 'almacen_id' => $warehouseId,
            'total' => '25.00', 'subtotal' => '25.00', 'estado' => 'Pendiente']);
        $cashMethodId = DB::table('medios_pago')->insertGetId(['codigo' => 'EFECTIVO-'.Str::upper(Str::random(8)),
            'nombre' => 'Efectivo QA', 'requiere_caja' => true, 'requiere_banco' => false, 'activo' => true]);
        $operations = app(ServicioOperacionesFinancieras::class);
        $data = ['token' => (string) Str::uuid(), 'origen' => 'pedido:'.$orderId,
            'medio_pago_id' => $cashMethodId, 'sesion_caja_id' => $session->id, 'importe' => '25.00'];
        $payment = $operations->registrarPago($data, $administrator, null);
        expect($operations->registrarPago($data, $administrator, null)->id)->toBe($payment->id)
            ->and($payment->estado)->toBe('pendiente');
        $confirmed = $operations->confirmarPagoEfectivo($payment, '25.00', 'Efectivo contado QA', $administrator, null);
        expect($confirmed->estado)->toBe('registrado')
            ->and($operations->confirmarPagoEfectivo($payment, '25.00', 'Efectivo contado QA', $administrator, null)->id)->toBe($payment->id)
            ->and(DB::table('movimientos_caja')->where('pago_id', $payment->id)->count())->toBe(1)
            ->and($boxes->arqueo($session)['saldo_esperado'])->toBe('135.00');

        $bankOrderId = DB::table('pedidos')->insertGetId(['usuario_id' => $administrator->id,
            'empresa_id' => $companyId, 'sede_id' => $siteId, 'almacen_id' => $warehouseId,
            'total' => '15.00', 'subtotal' => '15.00', 'estado' => 'Pendiente']);
        $bankMethodId = DB::table('medios_pago')->insertGetId(['codigo' => 'BANCO-'.Str::upper(Str::random(8)),
            'nombre' => 'Banco QA', 'requiere_caja' => false, 'requiere_banco' => true, 'activo' => true]);
        $accountId = DB::table('cuentas_bancarias')->insertGetId(['empresa_id' => $companyId,
            'codigo' => 'CTA-'.Str::upper(Str::random(8)), 'banco' => 'Banco sintético', 'tipo_cuenta' => 'corriente', 'moneda' => 'PEN']);
        $bankPayment = $operations->registrarPago(['token' => (string) Str::uuid(),
            'origen' => 'pedido:'.$bankOrderId, 'medio_pago_id' => $bankMethodId,
            'cuenta_bancaria_id' => $accountId, 'importe' => '15.00', 'referencia' => 'REFERENCIA-QA'], $administrator, null);
        expect($bankPayment->estado)->toBe('pendiente')
            ->and(DB::table('movimientos_bancarios')->where('pago_id', $bankPayment->id)->count())->toBe(0);
        expect(fn () => $operations->confirmarPagoEfectivo($bankPayment, '15.00', 'Verificación QA', $administrator, null))
            ->toThrow(HttpException::class);

        expect(fn () => $boxes->cerrarSesion($session, '134.00', (string) Str::uuid(), $administrator, null))
            ->toThrow(HttpException::class);
        $closed = $boxes->cerrarSesion($session, '135.00', (string) Str::uuid(), $administrator, null);
        expect($closed->estado)->toBe('cerrada')
            ->and($boxes->arqueo($closed)['diferencia'])->toBe('0.00')
            ->and(Caja::query()->findOrFail($box->id)->activa)->toBeTrue();
    } finally {
        DB::rollBack();
    }
    expect([DB::table('cajas')->count(), DB::table('pagos')->count(),
        DB::table('movimientos_caja')->count(), DB::table('pedidos')->count()])->toBe($countsBefore);
});
