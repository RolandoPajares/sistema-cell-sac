<?php

use App\Http\Controllers\Gestion\CajaController;
use App\Http\Requests\FiltroCajasRequest;
use App\Http\Requests\GuardarCajaRequest;
use App\Models\Finanzas\Caja;
use App\Models\Finanzas\SesionCaja;
use App\Models\User;
use App\Servicios\RegistradorAuditoria;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioCajas;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

/** @return array{ServicioCajas, User} */
function prepareCajaFixture(): array
{
    Schema::create('empresas', function (Blueprint $table): void {
        $table->id();
        $table->boolean('activo');
    });
    Schema::create('sedes', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('empresa_id');
        $table->string('codigo');
        $table->string('nombre');
        $table->boolean('activa');
        $table->timestamp('creado_en')->nullable();
        $table->timestamp('actualizado_en')->nullable();
    });
    Schema::create('cajas', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('sede_id');
        $table->string('codigo', 40);
        $table->string('nombre', 120);
        $table->boolean('activa');
        $table->dateTime('creado_en');
        $table->unique(['sede_id', 'codigo']);
    });
    Schema::create('sesiones_caja', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('caja_id');
        $table->unsignedBigInteger('usuario_apertura_id');
        $table->unsignedBigInteger('usuario_cierre_id')->nullable();
        $table->string('estado');
        $table->decimal('saldo_inicial', 15, 2);
        $table->decimal('saldo_cierre_declarado', 15, 2)->nullable();
        $table->dateTime('abierta_en');
        $table->dateTime('cerrada_en')->nullable();
    });
    Schema::create('movimientos_caja', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('sesion_caja_id');
        $table->unsignedBigInteger('pago_id')->nullable();
        $table->string('tipo');
        $table->string('direccion');
        $table->decimal('importe', 15, 2);
        $table->string('descripcion')->nullable();
        $table->unsignedBigInteger('creado_por_usuario_id')->nullable();
        $table->dateTime('creado_en');
    });
    Schema::create('registros_auditoria', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->string('accion');
        $table->string('entidad');
        $table->unsignedBigInteger('entidad_id')->nullable();
        $table->text('valores_anteriores')->nullable();
        $table->text('valores_nuevos')->nullable();
        $table->string('direccion_ip')->nullable();
        $table->dateTime('creado_en');
        $table->uuid('identificador_universal_evento')->nullable()->unique();
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
    Schema::create('menus', fn (Blueprint $table) => $table->id());
    Schema::create('roles_menus', fn (Blueprint $table) => $table->id());
    Schema::create('elementos_menu', fn (Blueprint $table) => $table->id());

    DB::table('empresas')->insert(['id' => 1, 'activo' => true]);
    DB::table('sedes')->insert([
        ['id' => 1, 'empresa_id' => 1, 'codigo' => 'LIM', 'nombre' => 'Lima', 'activa' => true],
        ['id' => 2, 'empresa_id' => 2, 'codigo' => 'EXT', 'nombre' => 'Externa', 'activa' => true],
    ]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'administrador', 'alcance_permitido' => 'empresa', 'activo' => true]);
    DB::table('modulos')->insert(['id' => 1, 'habilitado' => true]);
    DB::table('permisos')->insert([
        ['id' => 1, 'codigo' => 'gestion.pagos.crear', 'modulo_id' => 1, 'activo' => true],
        ['id' => 2, 'codigo' => 'gestion.pagos.actualizar', 'modulo_id' => 1, 'activo' => true],
        ['id' => 3, 'codigo' => 'gestion.pagos.consultar', 'modulo_id' => 1, 'activo' => true],
    ]);
    DB::table('roles_permisos')->insert([
        ['rol_id' => 1, 'permiso_id' => 1, 'alcance' => 'empresa'],
        ['rol_id' => 1, 'permiso_id' => 2, 'alcance' => 'empresa'],
        ['rol_id' => 1, 'permiso_id' => 3, 'alcance' => 'empresa'],
    ]);
    DB::table('usuarios_roles')->insert(['usuario_id' => 7, 'rol_id' => 1, 'activo' => true]);
    $actor = new User;
    $actor->setRawAttributes(['id' => 7, 'nombre' => 'Administrador']);

    return [new ServicioCajas(new ServicioAutorizacion, new RegistradorAuditoria), $actor];
}

test('crea, edita y desactiva cajas reales con auditoría e idempotencia', function (): void {
    [$service, $actor] = prepareCajaFixture();
    $box = $service->crear(['sede_id' => 1, 'codigo' => 'caja-01', 'nombre' => 'Caja principal'], $actor, null);
    $updated = $service->actualizar($box, ['sede_id' => 1, 'codigo' => 'CAJA-01', 'nombre' => 'Caja recepción'], $actor, null);
    $service->cambiarEstado($updated, false, $actor, null);
    $service->cambiarEstado($updated, false, $actor, null);

    expect($box->codigo)->toBe('CAJA-01')
        ->and(Caja::query()->whereKey($box->id)->value('nombre'))->toBe('Caja recepción')
        ->and((int) Caja::query()->whereKey($box->id)->value('activa'))->toBe(0)
        ->and(DB::table('registros_auditoria')->count())->toBe(3);
});

test('impide duplicados, sedes ajenas y ausencia de permiso', function (): void {
    [$service, $actor] = prepareCajaFixture();
    Caja::factory()->create(['sede_id' => 1, 'codigo' => 'CAJA-01', 'nombre' => 'Existente']);

    expect(fn () => $service->crear(['sede_id' => 1, 'codigo' => 'caja-01', 'nombre' => 'Duplicada'], $actor, null))
        ->toThrow(HttpException::class);
    expect(fn () => $service->crear(['sede_id' => 2, 'codigo' => 'CAJA-02', 'nombre' => 'Ajena'], $actor, null))
        ->toThrow(HttpException::class);
    DB::table('roles_permisos')->where('permiso_id', 1)->delete();
    expect(fn () => $service->crear(['sede_id' => 1, 'codigo' => 'CAJA-03', 'nombre' => 'Sin permiso'], $actor, null))
        ->toThrow(HttpException::class);
    expect(DB::table('cajas')->count())->toBe(1);
});

test('bloquea cambios con sesión abierta y revierte el registro cuando falla la auditoría', function (): void {
    [$service, $actor] = prepareCajaFixture();
    $box = Caja::factory()->create(['sede_id' => 1, 'codigo' => 'CAJA-01', 'nombre' => 'Principal']);
    DB::table('sesiones_caja')->insert([
        'caja_id' => $box->id, 'usuario_apertura_id' => 7, 'estado' => 'abierta',
        'saldo_inicial' => '0.00', 'abierta_en' => now(),
    ]);

    expect(fn () => $service->actualizar($box, ['sede_id' => 1, 'codigo' => 'CAJA-02', 'nombre' => 'Nueva'], $actor, null))
        ->toThrow(HttpException::class);
    expect(fn () => $service->cambiarEstado($box, false, $actor, null))->toThrow(HttpException::class);
    expect(Caja::query()->whereKey($box->id)->value('codigo'))->toBe('CAJA-01');

    Schema::drop('registros_auditoria');
    expect(fn () => $service->crear(['sede_id' => 1, 'codigo' => 'CAJA-03', 'nombre' => 'Nueva'], $actor, null))
        ->toThrow(QueryException::class);
    expect(DB::table('cajas')->count())->toBe(1);
});

test('lista y filtra únicamente las cajas visibles por sede', function (): void {
    [, $actor] = prepareCajaFixture();
    DB::table('sedes')->insert(['id' => 3, 'empresa_id' => 1, 'codigo' => 'ARE', 'nombre' => 'Arequipa', 'activa' => true]);
    Caja::factory()->create(['sede_id' => 1, 'codigo' => 'CAJA-01', 'nombre' => 'Principal']);
    Caja::factory()->create(['sede_id' => 3, 'codigo' => 'CAJA-03', 'nombre' => 'Externa']);
    DB::table('roles_permisos')->where('permiso_id', 3)->update(['alcance' => 'sede']);
    DB::table('usuarios_sedes_roles')->insert(['usuario_id' => 7, 'sede_id' => 1, 'rol_id' => 1, 'activo' => true]);

    $request = FiltroCajasRequest::create('/gestion/finanzas/diseno/cajas', 'GET', ['buscar' => 'CAJA']);
    $request->setUserResolver(static fn (): User => $actor);
    $request->setValidator(Validator::make($request->all(), $request->rules()));
    $data = (new CajaController)->index($request, new ServicioAutorizacion)->getData();

    expect($data['boxes']->total())->toBe(1)
        ->and($data['boxes']->first()->codigo)->toBe('CAJA-01')
        ->and($data['totalBoxes'])->toBe(1)
        ->and($data['sites']->pluck('id')->all())->toBe([1]);
});

test('valida código, sede y nombre con mensajes en español', function (): void {
    prepareCajaFixture();
    $request = GuardarCajaRequest::create('/gestion/finanzas/cajas', 'POST', [
        'sede_id' => 99, 'codigo' => 'Código inválido', 'nombre' => '',
    ]);
    $validator = Validator::make($request->all(), $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('sede_id'))->toBe('La sede seleccionada no existe.')
        ->and($validator->errors()->first('codigo'))->toBe('El código solo admite letras, números, puntos, guiones y guiones bajos.')
        ->and($validator->errors()->first('nombre'))->toBe('Ingresa el nombre de la caja.');
});

test('abre una sesión una sola vez y no admite otra simultánea para la misma caja', function (): void {
    [$service, $actor] = prepareCajaFixture();
    $box = Caja::factory()->create(['sede_id' => 1, 'codigo' => 'CAJA-01']);
    $token = '11111111-1111-4111-8111-111111111111';
    $opened = $service->abrirSesion($box, '100.00', $token, $actor, null);
    $retry = $service->abrirSesion($box, '100.00', $token, $actor, null);

    expect($retry->id)->toBe($opened->id)
        ->and($opened->estado)->toBe('abierta')
        ->and(DB::table('sesiones_caja')->count())->toBe(1)
        ->and(DB::table('registros_auditoria')->where('accion', 'caja.abierta')->count())->toBe(1);
    expect(fn () => $service->abrirSesion($box, '50.00', '22222222-2222-4222-8222-222222222222', $actor, null))
        ->toThrow(HttpException::class);
    expect(DB::table('sesiones_caja')->count())->toBe(1);
});

test('registra entradas y salidas idempotentes y bloquea importes inválidos o sobregiros', function (): void {
    [$service, $actor] = prepareCajaFixture();
    $box = Caja::factory()->create(['sede_id' => 1, 'codigo' => 'CAJA-01']);
    $session = $service->abrirSesion($box, '100.00', '11111111-1111-4111-8111-111111111111', $actor, null);
    $token = '22222222-2222-4222-8222-222222222222';
    $income = $service->registrarMovimiento($session, 'entrada', '20.00', 'Ingreso contado', $token, $actor, null);
    $retry = $service->registrarMovimiento($session, 'entrada', '20.00', 'Ingreso contado', $token, $actor, null);
    $service->registrarMovimiento($session, 'salida', '30.00', 'Retiro contado',
        '33333333-3333-4333-8333-333333333333', $actor, null);

    expect($retry->id)->toBe($income->id)
        ->and($service->arqueo($session)['saldo_esperado'])->toBe('90.00')
        ->and(DB::table('movimientos_caja')->count())->toBe(2);
    expect(fn () => $service->registrarMovimiento($session, 'salida', '91.00', 'Sobregiro',
        '44444444-4444-4444-8444-444444444444', $actor, null))->toThrow(HttpException::class);
    expect(fn () => $service->registrarMovimiento($session, 'entrada', '0', 'Cero',
        '55555555-5555-4555-8555-555555555555', $actor, null))->toThrow(HttpException::class);
    expect(DB::table('movimientos_caja')->count())->toBe(2);
});

test('detecta diferencia de arqueo y cierra solo con efectivo conforme', function (): void {
    [$service, $actor] = prepareCajaFixture();
    $box = Caja::factory()->create(['sede_id' => 1, 'codigo' => 'CAJA-01']);
    $session = $service->abrirSesion($box, '100.00', '11111111-1111-4111-8111-111111111111', $actor, null);
    $service->registrarMovimiento($session, 'salida', '25.00', 'Retiro',
        '22222222-2222-4222-8222-222222222222', $actor, null);

    expect(fn () => $service->cerrarSesion($session, '74.00',
        '33333333-3333-4333-8333-333333333333', $actor, null))->toThrow(HttpException::class);
    expect(SesionCaja::query()->findOrFail($session->id)->estado)->toBe('abierta');

    $closed = $service->cerrarSesion($session, '75.00',
        '44444444-4444-4444-8444-444444444444', $actor, null);
    $retry = $service->cerrarSesion($session, '75.00',
        '44444444-4444-4444-8444-444444444444', $actor, null);
    expect($retry->id)->toBe($closed->id)
        ->and($closed->estado)->toBe('cerrada')
        ->and($service->arqueo($closed)['diferencia'])->toBe('0.00')
        ->and(DB::table('registros_auditoria')->where('accion', 'caja.cerrada')->count())->toBe(1);
    expect(fn () => $service->registrarMovimiento($closed, 'entrada', '1.00', 'Tardío',
        '55555555-5555-4555-8555-555555555555', $actor, null))->toThrow(HttpException::class);
});

test('revierte una entrada de caja cuando falla el registro de auditoría', function (): void {
    [$service, $actor] = prepareCajaFixture();
    $box = Caja::factory()->create(['sede_id' => 1, 'codigo' => 'CAJA-01']);
    $session = $service->abrirSesion($box, '10.00', '11111111-1111-4111-8111-111111111111', $actor, null);
    DB::statement("CREATE TRIGGER bloquear_auditoria_caja BEFORE INSERT ON registros_auditoria BEGIN SELECT RAISE(ABORT, 'auditoria no disponible'); END");

    expect(fn () => $service->registrarMovimiento($session, 'entrada', '5.00', 'Ingreso contado',
        '22222222-2222-4222-8222-222222222222', $actor, null))->toThrow(QueryException::class);
    expect(DB::table('movimientos_caja')->count())->toBe(0)
        ->and($service->arqueo($session)['saldo_esperado'])->toBe('10.00');
});
