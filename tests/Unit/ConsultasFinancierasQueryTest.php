<?php

use App\Http\Controllers\Gestion\FinanzasController;
use App\Http\Requests\FiltroPagosRequest;
use App\Models\Finanzas\Pago;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioCajas;
use App\Servicios\ServicioConsultasFinancieras;
use App\Servicios\ServicioOperacionesFinancieras;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

function prepareFinanzasAuthorization(): ServicioAutorizacion
{
    Schema::create('empresas', function (Blueprint $table): void {
        $table->id();
        $table->boolean('activo');
    });
    Schema::create('sedes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
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
    });
    Schema::create('roles_menus', function (Blueprint $table): void {
        $table->id();
    });
    Schema::create('elementos_menu', function (Blueprint $table): void {
        $table->id();
    });
    DB::table('empresas')->insert(['id' => 1, 'activo' => true]);
    DB::table('sedes')->insert(['id' => 1, 'empresa_id' => 1, 'activa' => true]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'administrador', 'alcance_permitido' => 'empresa', 'activo' => true]);
    DB::table('modulos')->insert(['id' => 1, 'habilitado' => true]);
    DB::table('permisos')->insert([
        ['id' => 1, 'codigo' => 'gestion.pagos.consultar', 'modulo_id' => 1, 'activo' => true],
        ['id' => 2, 'codigo' => 'gestion.reembolsos.consultar', 'modulo_id' => 1, 'activo' => true],
    ]);
    DB::table('roles_permisos')->insert([
        ['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'empresa'],
        ['rol_id' => 1, 'permiso_id' => 2, 'alcance' => 'empresa'],
    ]);
    DB::table('usuarios_roles')->insert(['usuario_id' => 7, 'rol_id' => 1, 'activo' => true]);

    return new ServicioAutorizacion;
}

test('filtra pagos con las columnas y tipos definidos en el sql', function (): void {
    Schema::create('pagos', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->string('codigo');
        $table->string('tipo');
        $table->string('estado');
        $table->unsignedBigInteger('medio_pago_id');
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->unsignedInteger('pedido_id')->nullable();
        $table->unsignedBigInteger('venta_id')->nullable();
        $table->unsignedInteger('compra_id')->nullable();
        $table->char('moneda', 3);
        $table->decimal('importe', 15, 2);
        $table->string('referencia')->nullable();
        $table->dateTime('fecha_pago');
        $table->dateTime('creado_en');
    });
    DB::table('pagos')->insert([
        ['empresa_id' => 1, 'sede_id' => 1, 'codigo' => 'PAG-001', 'tipo' => 'cobro', 'estado' => 'registrado', 'medio_pago_id' => 1, 'moneda' => 'PEN', 'importe' => 40, 'fecha_pago' => '2026-11-01 10:00:00', 'creado_en' => '2026-11-01 10:00:00'],
        ['empresa_id' => 1, 'sede_id' => 1, 'codigo' => 'PAG-002', 'tipo' => 'pago', 'estado' => 'pendiente', 'medio_pago_id' => 1, 'moneda' => 'PEN', 'importe' => 25, 'fecha_pago' => '2026-10-01 10:00:00', 'creado_en' => '2026-10-01 10:00:00'],
    ]);
    $actor = new User;
    $actor->setRawAttributes(['id' => 7]);
    $authorization = prepareFinanzasAuthorization();
    $service = new ServicioConsultasFinancieras($authorization);

    expect($service->pagos($actor, [
        'buscar' => 'PAG-001',
        'tipo' => 'cobro',
        'estado' => 'registrado',
        'desde' => '2026-11-01',
        'hasta' => '2026-11-02',
    ])->count())->toBe(1);
});

test('limita los reembolsos a pagos visibles y aplica filtros de fecha y estado', function (): void {
    Schema::create('pagos', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->string('codigo');
        $table->string('tipo');
        $table->string('estado');
        $table->unsignedBigInteger('medio_pago_id');
        $table->char('moneda', 3);
        $table->decimal('importe', 15, 2);
        $table->dateTime('fecha_pago');
        $table->dateTime('creado_en');
    });
    Schema::create('devoluciones_garantias', function (Blueprint $table): void {
        $table->increments('id');
        $table->string('codigo');
        $table->unsignedInteger('cliente_id');
    });
    Schema::create('reembolsos', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedInteger('devolucion_id');
        $table->unsignedBigInteger('pago_id');
        $table->decimal('importe', 15, 2);
        $table->string('estado');
        $table->unsignedBigInteger('autorizado_por_usuario_id')->nullable();
        $table->dateTime('creado_en');
    });
    DB::table('pagos')->insert([
        ['id' => 1, 'empresa_id' => 1, 'sede_id' => 1, 'codigo' => 'COB-001', 'tipo' => 'reembolso', 'estado' => 'registrado', 'medio_pago_id' => 1, 'moneda' => 'PEN', 'importe' => 50, 'fecha_pago' => '2026-11-01 10:00:00', 'creado_en' => '2026-11-01 10:00:00'],
        ['id' => 2, 'empresa_id' => 2, 'sede_id' => 2, 'codigo' => 'COB-002', 'tipo' => 'reembolso', 'estado' => 'registrado', 'medio_pago_id' => 1, 'moneda' => 'PEN', 'importe' => 70, 'fecha_pago' => '2026-11-01 10:00:00', 'creado_en' => '2026-11-01 10:00:00'],
    ]);
    DB::table('devoluciones_garantias')->insert([
        ['id' => 1, 'codigo' => 'DEV-001', 'cliente_id' => 1],
        ['id' => 2, 'codigo' => 'DEV-002', 'cliente_id' => 2],
    ]);
    DB::table('reembolsos')->insert([
        ['devolucion_id' => 1, 'pago_id' => 1, 'importe' => 50, 'estado' => 'pendiente', 'creado_en' => '2026-11-01 10:00:00'],
        ['devolucion_id' => 2, 'pago_id' => 2, 'importe' => 70, 'estado' => 'pendiente', 'creado_en' => '2026-11-01 10:00:00'],
    ]);
    $actor = new User;
    $actor->setRawAttributes(['id' => 7]);
    $authorization = prepareFinanzasAuthorization();
    $service = new ServicioConsultasFinancieras($authorization);

    expect($service->reembolsos($actor, ['estado' => 'pendiente', 'desde' => '2026-11-01', 'hasta' => '2026-11-01'])->count())->toBe(1);
});

test('arma el listado de pagos desde Eloquent sin ofrecer acciones de escritura', function (): void {
    Schema::create('medios_pago', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->string('codigo');
        $table->string('nombre');
        $table->boolean('requiere_caja');
        $table->boolean('requiere_banco');
        $table->boolean('activo');
    });
    Schema::create('pagos', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->string('codigo');
        $table->string('tipo');
        $table->string('estado');
        $table->unsignedBigInteger('medio_pago_id');
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->unsignedInteger('pedido_id')->nullable();
        $table->unsignedBigInteger('venta_id')->nullable();
        $table->unsignedInteger('compra_id')->nullable();
        $table->char('moneda', 3);
        $table->decimal('importe', 15, 2);
        $table->string('referencia')->nullable();
        $table->dateTime('fecha_pago');
        $table->dateTime('creado_en');
    });
    DB::table('medios_pago')->insert(['id' => 1, 'codigo' => 'TARJETA', 'nombre' => 'Tarjeta', 'requiere_caja' => false, 'requiere_banco' => false, 'activo' => true]);
    DB::table('pagos')->insert([
        'empresa_id' => 1,
        'sede_id' => 1,
        'codigo' => 'PAG-UI-001',
        'tipo' => 'cobro',
        'estado' => 'registrado',
        'medio_pago_id' => 1,
        'moneda' => 'PEN',
        'importe' => 48.50,
        'fecha_pago' => '2026-11-03 10:00:00',
        'creado_en' => '2026-11-03 10:00:00',
    ]);
    $authorization = prepareFinanzasAuthorization();
    $actor = new User;
    $actor->setRawAttributes(['id' => 7, 'nombre' => 'Usuario de prueba']);
    $request = Mockery::mock(FiltroPagosRequest::class)->makePartial();
    $request->shouldReceive('validated')->once()->andReturn([]);
    $request->shouldReceive('user')->andReturn($actor);

    $view = (new FinanzasController)->pagos($request, new ServicioConsultasFinancieras($authorization), $authorization);
    $module = $view->getData()['module'];

    expect($module['connected'])->toBeTrue()
        ->and($module['mode'])->toBe('consult')
        ->and($module['total_count'])->toBe(1)
        ->and($module['records'][0]['cells'][0])->toBe('PAG-UI-001')
        ->and($module['records'][0]['cells'][4])->toBe('Tarjeta')
        ->and($module['export_route'])->toBeNull();
});

function prepareFinancialOperationsTables(): void
{
    Schema::create('pedidos', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->decimal('total', 10, 2);
        $table->string('estado');
    });
    Schema::create('ventas', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedInteger('pedido_id')->nullable();
        $table->decimal('total', 15, 2);
        $table->char('moneda', 3);
        $table->string('estado');
    });
    Schema::create('compras', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->decimal('total', 12, 2);
        $table->char('moneda', 3);
        $table->string('estado');
        $table->boolean('activo');
    });
    Schema::create('medios_pago', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->string('codigo');
        $table->string('nombre');
        $table->boolean('requiere_caja');
        $table->boolean('requiere_banco');
        $table->boolean('activo');
    });
    Schema::create('pagos', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->string('codigo');
        $table->string('tipo');
        $table->string('estado');
        $table->unsignedBigInteger('medio_pago_id');
        $table->unsignedBigInteger('sesion_caja_id')->nullable();
        $table->unsignedBigInteger('cuenta_bancaria_id')->nullable();
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->unsignedInteger('pedido_id')->nullable();
        $table->unsignedBigInteger('venta_id')->nullable();
        $table->unsignedInteger('compra_id')->nullable();
        $table->char('moneda', 3);
        $table->decimal('importe', 15, 2);
        $table->string('referencia')->nullable();
        $table->dateTime('fecha_pago');
        $table->dateTime('creado_en');
        $table->unique(['empresa_id', 'codigo']);
    });
    Schema::create('devoluciones_garantias', function (Blueprint $table): void {
        $table->increments('id');
        $table->string('codigo');
        $table->unsignedInteger('pedido_id');
        $table->unsignedBigInteger('venta_id')->nullable();
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->string('tipo');
        $table->string('estado');
        $table->string('resolucion')->nullable();
        $table->boolean('activo');
    });
    Schema::create('reembolsos', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedInteger('devolucion_id');
        $table->unsignedBigInteger('pago_id')->unique();
        $table->decimal('importe', 15, 2);
        $table->string('estado');
        $table->unsignedBigInteger('autorizado_por_usuario_id')->nullable();
        $table->dateTime('creado_en');
    });
    Schema::create('movimientos_caja', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('sesion_caja_id');
        $table->unsignedBigInteger('pago_id')->nullable();
        $table->string('tipo')->default('registro_manual');
        $table->string('direccion');
        $table->decimal('importe', 15, 2);
        $table->string('descripcion')->nullable();
        $table->unsignedBigInteger('creado_por_usuario_id')->nullable();
        $table->dateTime('creado_en')->nullable();
    });
    Schema::create('cajas', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('sede_id');
        $table->string('codigo')->default('CAJA');
        $table->string('nombre')->default('Caja de prueba');
        $table->boolean('activa')->default(true);
        $table->dateTime('creado_en')->nullable();
    });
    Schema::create('sesiones_caja', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('caja_id');
        $table->unsignedBigInteger('usuario_apertura_id');
        $table->string('estado')->default('abierta');
        $table->decimal('saldo_inicial', 15, 2)->default(0);
        $table->decimal('saldo_cierre_declarado', 15, 2)->nullable();
        $table->dateTime('abierta_en')->nullable();
        $table->dateTime('cerrada_en')->nullable();
    });
    Schema::create('cuentas_bancarias', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('empresa_id');
        $table->string('codigo');
        $table->string('banco');
        $table->string('tipo_cuenta');
        $table->char('moneda', 3);
        $table->boolean('activa');
    });
    Schema::create('registros_auditoria', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->string('accion');
        $table->string('entidad');
        $table->unsignedBigInteger('entidad_id')->nullable();
        $table->text('valores_anteriores')->nullable();
        $table->text('valores_nuevos')->nullable();
        $table->string('direccion_ip')->nullable();
        $table->dateTime('creado_en');
    });
    DB::table('pedidos')->insert(['id' => 1, 'empresa_id' => 1, 'sede_id' => 1, 'total' => 50, 'estado' => 'Pendiente']);
    DB::table('cajas')->insert(['id' => 1, 'sede_id' => 1]);
    DB::table('sesiones_caja')->insert(['id' => 1, 'caja_id' => 1, 'usuario_apertura_id' => 7]);
    DB::table('medios_pago')->insert([
        ['id' => 1, 'codigo' => 'MANUAL', 'nombre' => 'Registro manual', 'requiere_caja' => false, 'requiere_banco' => false, 'activo' => true],
        ['id' => 2, 'codigo' => 'BANCO', 'nombre' => 'Banco', 'requiere_caja' => false, 'requiere_banco' => true, 'activo' => true],
        ['id' => 3, 'codigo' => 'EFECTIVO', 'nombre' => 'Efectivo', 'requiere_caja' => true, 'requiere_banco' => false, 'activo' => true],
    ]);
}

function prepareFinancialOperations(): array
{
    $authorization = prepareFinanzasAuthorization();
    prepareFinancialOperationsTables();
    $permissions = [
        3 => 'gestion.pagos.crear',
        4 => 'gestion.pagos.anular',
        5 => 'gestion.reembolsos.crear',
        6 => 'gestion.reembolsos.aprobar',
        7 => 'gestion.reembolsos.anular',
        8 => 'gestion.pagos.actualizar',
    ];
    foreach ($permissions as $id => $code) {
        DB::table('permisos')->insert(['id' => $id, 'codigo' => $code, 'modulo_id' => 1, 'activo' => true]);
        DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => $id, 'alcance' => 'empresa']);
    }
    $actor = new User;
    $actor->setRawAttributes(['id' => 7, 'nombre' => 'Operador']);

    $audit = new RegistradorAuditoria;

    return [new ServicioOperacionesFinancieras($authorization, $audit, new ServicioCajas($authorization, $audit)), $actor];
}

test('registra un pago pendiente una sola vez sin modificar el pedido', function (): void {
    [$service, $actor] = prepareFinancialOperations();
    $data = [
        'token' => '11111111-1111-4111-8111-111111111111',
        'origen' => 'pedido:1',
        'medio_pago_id' => 1,
        'importe' => '30.00',
        'referencia' => 'REF-001',
    ];

    $payment = $service->registrarPago($data, $actor, null);
    $retry = $service->registrarPago($data, $actor, null);

    expect($payment->getKey())->toBe($retry->getKey())
        ->and($payment->estado)->toBe('pendiente')
        ->and($payment->importe)->toBe('30.00')
        ->and(DB::table('pagos')->count())->toBe(1)
        ->and(DB::table('pedidos')->value('estado'))->toBe('Pendiente')
        ->and(DB::table('registros_auditoria')->where('accion', 'pago.solicitado')->count())->toBe(1);
});

test('confirma efectivo contado una sola vez y enlaza el movimiento de caja', function (): void {
    [$service, $actor] = prepareFinancialOperations();
    $payment = $service->registrarPago([
        'token' => 'a1111111-1111-4111-8111-111111111111',
        'origen' => 'pedido:1', 'medio_pago_id' => 3,
        'sesion_caja_id' => 1, 'importe' => '30.00',
    ], $actor, null);

    expect($payment->estado)->toBe('pendiente')
        ->and(DB::table('movimientos_caja')->count())->toBe(0);
    $confirmed = $service->confirmarPagoEfectivo($payment, '30.00', 'Efectivo contado en caja.', $actor, null);
    $retry = $service->confirmarPagoEfectivo($payment, '30.00', 'Efectivo contado en caja.', $actor, null);

    expect($confirmed->id)->toBe($retry->id)
        ->and($confirmed->estado)->toBe('registrado')
        ->and(DB::table('movimientos_caja')->where('pago_id', $payment->id)->count())->toBe(1)
        ->and(DB::table('movimientos_caja')->where('pago_id', $payment->id)->value('direccion'))->toBe('entrada')
        ->and(DB::table('registros_auditoria')->where('accion', 'pago.confirmado_efectivo')->count())->toBe(1);
});

test('mantiene una transferencia bancaria declarada pendiente y exige cuenta y referencia', function (): void {
    [$service, $actor] = prepareFinancialOperations();
    DB::table('cuentas_bancarias')->insert(['id' => 1, 'empresa_id' => 1, 'codigo' => 'CTA-01',
        'banco' => 'Banco de prueba', 'tipo_cuenta' => 'corriente', 'moneda' => 'PEN', 'activa' => true]);
    $data = ['token' => 'a2222222-2222-4222-8222-222222222222', 'origen' => 'pedido:1',
        'medio_pago_id' => 2, 'cuenta_bancaria_id' => 1, 'importe' => '20.00'];

    expect(fn () => $service->registrarPago($data, $actor, null))->toThrow(HttpException::class);
    $declared = $service->registrarPago(array_replace($data, ['referencia' => 'COMPROBANTE-01']), $actor, null);
    expect($declared->estado)->toBe('pendiente')
        ->and((int) $declared->cuenta_bancaria_id)->toBe(1)
        ->and(DB::table('movimientos_caja')->count())->toBe(0)
        ->and(DB::table('pedidos')->value('estado'))->toBe('Pendiente');
    expect(fn () => $service->confirmarPagoEfectivo($declared, '20.00', 'Revisado.', $actor, null))
        ->toThrow(HttpException::class);
});

test('impide confirmar efectivo distinto o en una caja cerrada', function (): void {
    [$service, $actor] = prepareFinancialOperations();
    $payment = $service->registrarPago([
        'token' => 'a3333333-3333-4333-8333-333333333333',
        'origen' => 'pedido:1', 'medio_pago_id' => 3,
        'sesion_caja_id' => 1, 'importe' => '15.00',
    ], $actor, null);
    expect(fn () => $service->confirmarPagoEfectivo($payment, '14.00', 'Conteo incompleto.', $actor, null))
        ->toThrow(HttpException::class);
    DB::table('sesiones_caja')->where('id', 1)->update(['estado' => 'cerrada']);
    expect(fn () => $service->confirmarPagoEfectivo($payment, '15.00', 'Efectivo contado.', $actor, null))
        ->toThrow(HttpException::class);
    expect(DB::table('pagos')->value('estado'))->toBe('pendiente')
        ->and(DB::table('movimientos_caja')->count())->toBe(0);
});

test('revierte la confirmación y el movimiento si falla la auditoría', function (): void {
    [$service, $actor] = prepareFinancialOperations();
    $payment = $service->registrarPago([
        'token' => 'a4444444-4444-4444-8444-444444444444',
        'origen' => 'pedido:1', 'medio_pago_id' => 3,
        'sesion_caja_id' => 1, 'importe' => '10.00',
    ], $actor, null);
    Schema::drop('registros_auditoria');

    expect(fn () => $service->confirmarPagoEfectivo($payment, '10.00', 'Efectivo contado.', $actor, null))
        ->toThrow(QueryException::class);
    expect(DB::table('pagos')->value('estado'))->toBe('pendiente')
        ->and(DB::table('movimientos_caja')->count())->toBe(0);
});

test('rechaza reutilizar identificadores, superar saldo o usar medios sin conciliación', function (): void {
    [$service, $actor] = prepareFinancialOperations();
    $data = ['token' => '22222222-2222-4222-8222-222222222222', 'origen' => 'pedido:1',
        'medio_pago_id' => 1, 'importe' => '30.00'];
    $service->registrarPago($data, $actor, null);

    expect(fn () => $service->registrarPago(array_replace($data, ['importe' => '20.00']), $actor, null))
        ->toThrow(HttpException::class);
    expect(fn () => $service->registrarPago(array_replace($data, ['token' => '33333333-3333-4333-8333-333333333333', 'importe' => '30.00']), $actor, null))
        ->toThrow(HttpException::class);
    expect(fn () => $service->registrarPago(array_replace($data, ['token' => '44444444-4444-4444-8444-444444444444', 'medio_pago_id' => 2, 'importe' => '10.00']), $actor, null))
        ->toThrow(HttpException::class);
    expect(DB::table('pagos')->count())->toBe(1);
});

test('impide registrar pagos sin permiso o sobre un documento de otra sede', function (): void {
    [$service, $actor] = prepareFinancialOperations();
    $data = ['token' => '77777777-7777-4777-8777-777777777777', 'origen' => 'pedido:1',
        'medio_pago_id' => 1, 'importe' => '10.00'];
    DB::table('roles_permisos')->where('permiso_id', 3)->delete();

    $denied = null;
    try {
        $service->registrarPago($data, $actor, null);
    } catch (HttpException $exception) {
        $denied = $exception;
    }
    expect($denied?->getStatusCode())->toBe(403);

    DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => 3, 'alcance' => 'empresa']);
    DB::table('pedidos')->insert(['id' => 2, 'empresa_id' => 1, 'sede_id' => 2, 'total' => 50, 'estado' => 'Pendiente']);
    $outsideSite = null;
    try {
        $service->registrarPago(array_replace($data, ['origen' => 'pedido:2']), $actor, null);
    } catch (HttpException $exception) {
        $outsideSite = $exception;
    }
    expect($outsideSite?->getStatusCode())->toBe(403)
        ->and(DB::table('pagos')->count())->toBe(0);
});

test('solicita y aprueba un reembolso una sola vez con ingreso de caja comprobado', function (): void {
    [$service, $actor] = prepareFinancialOperations();
    $payment = Pago::query()->create(['empresa_id' => 1, 'sede_id' => 1, 'codigo' => 'COB-001', 'tipo' => 'cobro',
        'estado' => 'registrado', 'medio_pago_id' => 1, 'sesion_caja_id' => 1, 'pedido_id' => 1, 'moneda' => 'PEN',
        'importe' => '40.00', 'fecha_pago' => now(), 'creado_en' => now()]);
    DB::table('devoluciones_garantias')->insert(['id' => 1, 'codigo' => 'DEV-001', 'pedido_id' => 1,
        'empresa_id' => 1, 'sede_id' => 1, 'tipo' => 'Devolución', 'estado' => 'Resuelta',
        'resolucion' => 'Producto recibido y revisado.', 'activo' => true]);
    $data = ['devolucion_id' => 1, 'pago_id' => $payment->id, 'importe' => '25.00'];

    $refund = $service->solicitarReembolso($data, $actor, null);
    $retry = $service->solicitarReembolso($data, $actor, null);
    expect($refund->id)->toBe($retry->id)
        ->and(DB::table('reembolsos')->count())->toBe(1);
    expect(fn () => $service->aprobarReembolso($refund, $actor, null))->toThrow(HttpException::class);
    expect(DB::table('reembolsos')->value('estado'))->toBe('pendiente');

    DB::table('movimientos_caja')->insert(['sesion_caja_id' => 1, 'pago_id' => $payment->id, 'direccion' => 'salida', 'importe' => '40.00']);
    expect(fn () => $service->aprobarReembolso($refund, $actor, null))->toThrow(HttpException::class);
    DB::table('movimientos_caja')->insert(['sesion_caja_id' => 1, 'pago_id' => $payment->id, 'direccion' => 'entrada', 'importe' => '40.00']);
    $approved = $service->aprobarReembolso($refund, $actor, null);
    $approvedRetry = $service->aprobarReembolso($refund, $actor, null);
    expect($approved->id)->toBe($approvedRetry->id)
        ->and($approved->estado)->toBe('aprobado')
        ->and((int) $approved->autorizado_por_usuario_id)->toBe(7)
        ->and(DB::table('pagos')->count())->toBe(1)
        ->and(DB::table('movimientos_caja')->count())->toBe(2)
        ->and(DB::table('registros_auditoria')->where('entidad', 'reembolsos')->count())->toBe(2);
});

test('impide reembolsar un cobro de otro pedido', function (): void {
    [$service, $actor] = prepareFinancialOperations();
    $payment = Pago::query()->create(['empresa_id' => 1, 'sede_id' => 1, 'codigo' => 'COB-002', 'tipo' => 'cobro',
        'estado' => 'registrado', 'medio_pago_id' => 1, 'pedido_id' => 1, 'moneda' => 'PEN',
        'importe' => '40.00', 'fecha_pago' => now(), 'creado_en' => now()]);
    DB::table('devoluciones_garantias')->insert(['id' => 2, 'codigo' => 'DEV-002', 'pedido_id' => 2,
        'empresa_id' => 1, 'sede_id' => 1, 'tipo' => 'Devolución', 'estado' => 'Resuelta',
        'resolucion' => 'Producto recibido y revisado.', 'activo' => true]);

    $mismatch = null;
    try {
        $service->solicitarReembolso(['devolucion_id' => 2, 'pago_id' => $payment->id, 'importe' => '10.00'], $actor, null);
    } catch (HttpException $exception) {
        $mismatch = $exception;
    }
    expect($mismatch?->getStatusCode())->toBe(422)
        ->and(DB::table('reembolsos')->count())->toBe(0);
});

test('ofrece formularios únicamente con documentos y medios elegibles de la sede', function (): void {
    [$service, $actor] = prepareFinancialOperations();
    Schema::create('almacenes', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('sede_id');
        $table->boolean('activo');
    });
    DB::table('almacenes')->insert([
        ['sede_id' => 1, 'activo' => true],
        ['sede_id' => 1, 'activo' => true],
    ]);
    $queries = new ServicioConsultasFinancieras(new ServicioAutorizacion);
    $paymentOptions = $queries->opcionesPago($actor);

    expect($paymentOptions['medios']->pluck('id')->all())->toBe([2, 3, 1])
        ->and($paymentOptions['sesiones']->pluck('id')->all())->toBe([1])
        ->and($paymentOptions['cuentas']->isEmpty())->toBeTrue()
        ->and($paymentOptions['documentos']->pluck('value')->all())->toBe(['pedido:1']);
    $pending = $service->registrarPago([
        'token' => '88888888-8888-4888-8888-888888888888',
        'origen' => 'pedido:1',
        'medio_pago_id' => 1,
        'importe' => '10.00',
    ], $actor, null);
    expect($pending->estado)->toBe('pendiente');

    $payment = Pago::query()->create(['empresa_id' => 1, 'sede_id' => 1, 'codigo' => 'COB-003', 'tipo' => 'cobro',
        'estado' => 'registrado', 'medio_pago_id' => 1, 'pedido_id' => 1, 'moneda' => 'PEN',
        'importe' => '40.00', 'fecha_pago' => now(), 'creado_en' => now()]);
    DB::table('devoluciones_garantias')->insert(['id' => 3, 'codigo' => 'DEV-003', 'pedido_id' => 1,
        'empresa_id' => 1, 'sede_id' => 1, 'tipo' => 'Devolución', 'estado' => 'Resuelta',
        'resolucion' => 'Producto recibido y revisado.', 'activo' => true]);
    $refundOptions = $queries->opcionesReembolso($actor);
    expect($refundOptions['devoluciones']->pluck('codigo')->all())->toBe(['DEV-003'])
        ->and($refundOptions['cobros']->pluck('codigo')->all())->toBe(['COB-003']);

    $service->solicitarReembolso(['devolucion_id' => 3, 'pago_id' => $payment->id, 'importe' => '10.00'], $actor, null);
    expect($queries->opcionesReembolso($actor)['cobros']->isEmpty())->toBeTrue();
});

test('anula solicitudes pendientes de forma idempotente sin cambiar dinero ni documentos', function (): void {
    [$service, $actor] = prepareFinancialOperations();
    $payment = $service->registrarPago([
        'token' => '55555555-5555-4555-8555-555555555555',
        'origen' => 'pedido:1',
        'medio_pago_id' => 1,
        'importe' => '15.00',
    ], $actor, null);

    $cancelled = $service->anularPago($payment, $actor, 'Solicitud duplicada', null);
    $retry = $service->anularPago($payment, $actor, 'Solicitud duplicada', null);
    expect($cancelled->id)->toBe($retry->id)
        ->and($cancelled->estado)->toBe('anulado')
        ->and(DB::table('pedidos')->value('estado'))->toBe('Pendiente')
        ->and(DB::table('registros_auditoria')->where('entidad', 'pagos')->count())->toBe(2);
});

test('revierte el registro de pago si falla la auditoría dentro de la transacción', function (): void {
    [$service, $actor] = prepareFinancialOperations();
    Schema::drop('registros_auditoria');

    expect(fn () => $service->registrarPago([
        'token' => '66666666-6666-4666-8666-666666666666',
        'origen' => 'pedido:1',
        'medio_pago_id' => 1,
        'importe' => '10.00',
    ], $actor, null))->toThrow(QueryException::class);
    expect(DB::table('pagos')->count())->toBe(0);
});
