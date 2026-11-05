<?php

use App\Models\Inventario\TransferenciaExistencia;
use App\Models\User;
use App\Servicios\ServicioTransferenciasExistencias;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    foreach ([
        'movimientos_dispositivos', 'dispositivos', 'movimientos_inventario', 'registros_auditoria',
        'detalles_transferencias_existencias', 'transferencias_existencias', 'existencias_almacen',
        'variantes_productos', 'productos', 'almacenes', 'sedes', 'empresas', 'usuarios_sedes_roles',
        'usuarios_roles', 'roles_permisos', 'permisos', 'pantallas', 'modulos', 'roles', 'roles_menus', 'elementos_menu', 'menus',
    ] as $table) {
        Schema::dropIfExists($table);
    }

    Schema::create('empresas', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->string('codigo');
        $table->boolean('activo')->default(true);
    });
    Schema::create('sedes', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->boolean('activa')->default(true);
    });
    Schema::create('almacenes', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('sede_id');
        $table->string('codigo');
        $table->string('nombre');
        $table->boolean('activo')->default(true);
    });
    Schema::create('productos', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedBigInteger('empresa_id');
        $table->string('nombre');
        $table->string('marca');
        $table->unsignedInteger('existencias')->default(0);
        $table->boolean('activo')->default(true);
    });
    Schema::create('variantes_productos', function (Blueprint $table): void {
        $table->unsignedInteger('id')->primary();
        $table->unsignedInteger('producto_id');
        $table->string('nombre_color')->nullable();
        $table->string('codigo_sku')->nullable();
        $table->unsignedInteger('existencias')->default(0);
        $table->boolean('controla_serie')->default(false);
    });
    Schema::create('existencias_almacen', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('almacen_id');
        $table->unsignedInteger('variante_id')->nullable();
        $table->unsignedInteger('producto_id');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_id');
        $table->decimal('cantidad_fisica', 18, 6)->default(0);
        $table->decimal('cantidad_reservada', 18, 6)->default(0);
    });
    Schema::create('transferencias_existencias', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('empresa_id');
        $table->unsignedBigInteger('sede_origen_id');
        $table->unsignedBigInteger('almacen_origen_id');
        $table->unsignedBigInteger('sede_destino_id');
        $table->unsignedBigInteger('almacen_destino_id');
        $table->string('codigo')->unique();
        $table->string('estado')->default('borrador');
        $table->unsignedBigInteger('solicitado_por_usuario_id')->nullable();
        $table->unsignedBigInteger('aprobado_por_usuario_id')->nullable();
        $table->dateTime('enviado_en')->nullable();
        $table->dateTime('recibido_en')->nullable();
        $table->dateTime('creado_en');
    });
    Schema::create('detalles_transferencias_existencias', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('transferencia_id');
        $table->unsignedInteger('producto_id');
        $table->unsignedInteger('variante_id')->nullable();
        $table->unsignedInteger('variante_clave')->default(0);
        $table->decimal('cantidad_solicitada', 18, 6);
        $table->decimal('cantidad_enviada', 18, 6)->default(0);
        $table->decimal('cantidad_recibida', 18, 6)->default(0);
        $table->decimal('cantidad_devuelta', 18, 6)->default(0);
        $table->decimal('cantidad_conciliada', 18, 6)->default(0);
    });
    Schema::create('movimientos_inventario', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedInteger('producto_id');
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->string('tipo_movimiento');
        $table->decimal('cantidad', 18, 6);
        $table->string('notas', 500);
        $table->dateTime('creado_en');
        $table->unsignedBigInteger('empresa_id')->nullable();
        $table->unsignedBigInteger('sede_id')->nullable();
        $table->unsignedBigInteger('almacen_id')->nullable();
        $table->unsignedInteger('variante_id')->nullable();
        $table->string('documento_tipo', 60)->nullable();
        $table->string('documento_id', 80)->nullable();
    });
    Schema::create('dispositivos', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedInteger('producto_id');
        $table->unsignedInteger('variante_id')->nullable();
        $table->string('imei', 20)->nullable()->unique();
        $table->string('numero_serie', 100)->nullable()->unique();
        $table->unsignedBigInteger('almacen_actual_id')->nullable();
        $table->string('estado', 30)->default('disponible');
        $table->dateTime('fecha_salida')->nullable();
    });
    Schema::create('movimientos_dispositivos', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('dispositivo_id');
        $table->unsignedBigInteger('movimiento_inventario_id');
        $table->string('estado_anterior')->nullable();
        $table->string('estado_nuevo');
        $table->dateTime('creado_en');
    });
    Schema::create('registros_auditoria', function (Blueprint $table): void {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('usuario_id')->nullable();
        $table->string('accion', 80);
        $table->string('entidad', 80);
        $table->unsignedBigInteger('entidad_id')->nullable();
        $table->json('valores_anteriores')->nullable();
        $table->json('valores_nuevos')->nullable();
        $table->string('direccion_ip', 45)->nullable();
        $table->timestamp('creado_en')->useCurrent();
        $table->uuid('identificador_universal_evento')->nullable()->unique();
        $table->unsignedBigInteger('empresa_id')->nullable();
        $table->unsignedBigInteger('sede_id')->nullable();
        $table->string('modulo', 80)->nullable();
        $table->string('resultado', 20)->nullable();
        $table->text('observaciones')->nullable();
        $table->string('origen', 20)->default('laravel');
    });

    createTransferSyntheticAuthorization();

    DB::table('empresas')->insert(['id' => 1, 'codigo' => 'SINTETICA', 'activo' => true]);
    DB::table('sedes')->insert([
        ['id' => 1, 'empresa_id' => 1, 'nombre' => 'Origen', 'activa' => true],
        ['id' => 2, 'empresa_id' => 1, 'nombre' => 'Destino', 'activa' => true],
    ]);
    DB::table('almacenes')->insert([
        ['id' => 10, 'sede_id' => 1, 'codigo' => 'ALM-A', 'nombre' => 'Origen', 'activo' => true],
        ['id' => 20, 'sede_id' => 2, 'codigo' => 'ALM-B', 'nombre' => 'Destino', 'activo' => true],
    ]);
    DB::table('productos')->insert(['id' => 100, 'empresa_id' => 1, 'nombre' => 'Equipo sintético', 'marca' => 'Prueba', 'existencias' => 10, 'activo' => true]);
    DB::table('existencias_almacen')->insert([
        'almacen_id' => 10, 'producto_id' => 100, 'empresa_id' => 1, 'sede_id' => 1,
        'cantidad_fisica' => 10, 'cantidad_reservada' => 0,
    ]);
});

function createTransferSyntheticAuthorization(): void
{
    Schema::create('modulos', function (Blueprint $table): void {
        $table->id();
        $table->boolean('habilitado');
    });
    Schema::create('pantallas', function (Blueprint $table): void {
        $table->id();
        $table->boolean('habilitada');
    });
    Schema::create('roles', function (Blueprint $table): void {
        $table->id();
        $table->string('codigo');
        $table->string('alcance_permitido');
        $table->boolean('activo');
    });
    Schema::create('permisos', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('modulo_id');
        $table->unsignedBigInteger('pantalla_id')->nullable();
        $table->string('codigo');
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
        $table->dateTime('vigente_hasta')->nullable();
    });
    Schema::create('usuarios_sedes_roles', function (Blueprint $table): void {
        $table->unsignedBigInteger('usuario_id');
        $table->unsignedBigInteger('sede_id');
        $table->unsignedBigInteger('rol_id');
        $table->boolean('activo');
        $table->dateTime('vigente_hasta')->nullable();
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
    DB::table('modulos')->insert(['id' => 1, 'habilitado' => true]);
    DB::table('roles')->insert(['id' => 1, 'codigo' => 'administrador', 'alcance_permitido' => 'empresa', 'activo' => true]);
    $codes = ['gestion.inventario.consultar', 'gestion.inventario.transferir', 'gestion.inventario.aprobar', 'gestion.inventario.exportar'];
    foreach ($codes as $index => $code) {
        DB::table('permisos')->insert(['id' => $index + 1, 'modulo_id' => 1, 'codigo' => $code, 'activo' => true]);
        DB::table('roles_permisos')->insert(['rol_id' => 1, 'permiso_id' => $index + 1, 'alcance' => 'empresa']);
    }
    DB::table('usuarios_roles')->insert(['usuario_id' => 51, 'rol_id' => 1, 'activo' => true]);
}

function transferTestActor(): User
{
    $actor = new User;
    $actor->setAttribute('id', 51);
    $actor->setAttribute('rol', 'administrador');

    return $actor;
}

function createTransferForTest(ServicioTransferenciasExistencias $service, int $quantity = 4): TransferenciaExistencia
{
    return $service->request([
        'clave_idempotencia' => (string) Str::uuid(),
        'almacen_origen_id' => 10,
        'almacen_destino_id' => 20,
        'observaciones' => 'Prueba sintetica',
        'items' => [['producto_id' => 100, 'variante_id' => null, 'cantidad' => $quantity]],
    ], transferTestActor(), '127.0.0.1');
}

it('moves only dispatched and received units, supports partial receipts, and closes after full reconciliation', function (): void {
    $service = app(ServicioTransferenciasExistencias::class);
    $actor = transferTestActor();
    $transfer = createTransferForTest($service);
    expect((int) DB::table('existencias_almacen')->where('almacen_id', 10)->value('cantidad_fisica'))->toBe(10)
        ->and($transfer->estado)->toBe('solicitada');

    $service->approve($transfer, $actor, (string) Str::uuid(), null);
    $lineId = (int) $transfer->detalles->sole()->id;
    $firstDispatchKey = (string) Str::uuid();
    $service->dispatch($transfer->fresh(), [
        'clave_idempotencia' => $firstDispatchKey,
        'items' => [['detalle_id' => $lineId, 'cantidad' => 3]],
    ], $actor, null);
    $service->dispatch($transfer->fresh(), [
        'clave_idempotencia' => $firstDispatchKey,
        'items' => [['detalle_id' => $lineId, 'cantidad' => 3]],
    ], $actor, null);

    expect((int) DB::table('existencias_almacen')->where('almacen_id', 10)->value('cantidad_fisica'))->toBe(7)
        ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'transferencia_despacho')->count())->toBe(1);

    $service->receive($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 2]],
    ], $actor, null);
    expect($transfer->fresh()->estado)->toBe('recibida parcialmente')
        ->and((int) DB::table('existencias_almacen')->where('almacen_id', 20)->value('cantidad_fisica'))->toBe(2);

    expect(fn () => $service->receive($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 2]],
    ], $actor, null))->toThrow(HttpException::class);
    expect((int) DB::table('existencias_almacen')->where('almacen_id', 20)->value('cantidad_fisica'))->toBe(2);

    $service->receive($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 1]],
    ], $actor, null);
    $service->dispatch($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 1]],
    ], $actor, null);
    $service->receive($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 1]],
    ], $actor, null);
    expect($transfer->fresh()->estado)->toBe('recibida totalmente');
    $service->close($transfer->fresh(), ['clave_idempotencia' => (string) Str::uuid()], $actor, null);

    expect($transfer->fresh()->estado)->toBe('cerrada')
        ->and((int) DB::table('existencias_almacen')->where('almacen_id', 10)->value('cantidad_fisica'))->toBe(6)
        ->and((int) DB::table('existencias_almacen')->where('almacen_id', 20)->value('cantidad_fisica'))->toBe(4)
        ->and(DB::table('registros_auditoria')->where('entidad', 'transferencias_existencias')->count())->toBe(10);
});

it('requires identified serials at dispatch and receipt and records device movements', function (): void {
    DB::table('productos')->insert(['id' => 101, 'empresa_id' => 1, 'nombre' => 'Telefono sintetico', 'marca' => 'Prueba', 'existencias' => 1, 'activo' => true]);
    DB::table('variantes_productos')->insert(['id' => 501, 'producto_id' => 101, 'nombre_color' => 'Negro', 'codigo_sku' => 'TEL-01', 'existencias' => 1, 'controla_serie' => true]);
    DB::table('existencias_almacen')->insert([
        'almacen_id' => 10, 'variante_id' => 501, 'producto_id' => 101, 'empresa_id' => 1, 'sede_id' => 1,
        'cantidad_fisica' => 1, 'cantidad_reservada' => 0,
    ]);
    DB::table('dispositivos')->insert(['id' => 601, 'producto_id' => 101, 'variante_id' => 501, 'imei' => '356000000000001', 'almacen_actual_id' => 10, 'estado' => 'disponible']);

    $service = app(ServicioTransferenciasExistencias::class);
    $actor = transferTestActor();
    $transfer = $service->request([
        'clave_idempotencia' => (string) Str::uuid(), 'almacen_origen_id' => 10, 'almacen_destino_id' => 20,
        'items' => [['producto_id' => 101, 'variante_id' => 501, 'cantidad' => 1]],
    ], $actor, null);
    $service->approve($transfer, $actor, (string) Str::uuid(), null);
    $lineId = (int) $transfer->detalles->sole()->id;

    expect(fn () => $service->dispatch($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 1]],
    ], $actor, null))->toThrow(HttpException::class);
    expect((int) DB::table('existencias_almacen')->where('almacen_id', 10)->where('variante_id', 501)->value('cantidad_fisica'))->toBe(1);

    $service->dispatch($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(),
        'items' => [['detalle_id' => $lineId, 'cantidad' => 1, 'series' => '356000000000001']],
    ], $actor, null);
    $service->receive($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(),
        'items' => [['detalle_id' => $lineId, 'cantidad' => 1, 'series' => '356000000000001']],
    ], $actor, null);

    expect(DB::table('dispositivos')->where('id', 601)->value('estado'))->toBe('disponible')
        ->and((int) DB::table('dispositivos')->where('id', 601)->value('almacen_actual_id'))->toBe(20)
        ->and(DB::table('movimientos_dispositivos')->count())->toBe(2)
        ->and($transfer->fresh()->estado)->toBe('recibida totalmente');
});

it('blocks discrepancy returns until the independent quantity reconciliation column exists', function (): void {
    Schema::table('detalles_transferencias_existencias', function (Blueprint $table): void {
        $table->dropColumn('cantidad_devuelta');
    });
    $service = app(ServicioTransferenciasExistencias::class);
    $actor = transferTestActor();
    $transfer = createTransferForTest($service, 1);
    $service->approve($transfer, $actor, (string) Str::uuid(), null);
    $lineId = (int) $transfer->detalles->sole()->id;
    $service->dispatch($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 1]],
    ], $actor, null);

    expect(fn () => $service->returnToOrigin($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 1]], 'motivo' => 'Prueba',
    ], $actor, null))->toThrow(HttpException::class)
        ->and(fn () => $service->reconcileDifference($transfer->fresh(), [
            'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 1]], 'motivo' => 'Faltante revisado',
        ], $actor, null))->toThrow(HttpException::class)
        ->and((int) DB::table('existencias_almacen')->where('almacen_id', 10)->value('cantidad_fisica'))->toBe(9);
});

it('closes a short receipt only after a documented quantity reconciliation without adding stock', function (): void {
    $service = app(ServicioTransferenciasExistencias::class);
    $actor = transferTestActor();
    $transfer = createTransferForTest($service, 5);
    $service->approve($transfer, $actor, (string) Str::uuid(), null);
    $lineId = (int) $transfer->detalles->sole()->id;
    $service->dispatch($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 4]],
    ], $actor, null);
    $service->receive($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 1]],
    ], $actor, null);

    expect(fn () => $service->close($transfer->fresh(), ['clave_idempotencia' => (string) Str::uuid()], $actor, null))
        ->toThrow(HttpException::class);
    $reconciliation = [
        'clave_idempotencia' => (string) Str::uuid(),
        'items' => [['detalle_id' => $lineId, 'cantidad' => 3]],
        'motivo' => 'Tres unidades no llegaron al destino.',
    ];
    $service->reconcileDifference($transfer->fresh(), $reconciliation, $actor, null);
    $service->reconcileDifference($transfer->fresh(), $reconciliation, $actor, null);

    expect((int) DB::table('existencias_almacen')->where('almacen_id', 10)->value('cantidad_fisica'))->toBe(6)
        ->and((int) DB::table('existencias_almacen')->where('almacen_id', 20)->value('cantidad_fisica'))->toBe(1)
        ->and((int) DB::table('detalles_transferencias_existencias')->where('id', $lineId)->value('cantidad_conciliada'))->toBe(3)
        ->and(DB::table('movimientos_inventario')->where('documento_id', (string) $transfer->id)->count())->toBe(2);

    expect(fn () => $service->close($transfer->fresh(), ['clave_idempotencia' => (string) Str::uuid()], $actor, null))
        ->toThrow(HttpException::class);
    $service->close($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'motivo' => 'Diferencia documentada por el destino.',
    ], $actor, null);

    expect($transfer->fresh()->estado)->toBe('cerrada')
        ->and((int) DB::table('existencias_almacen')->where('almacen_id', 10)->value('cantidad_fisica'))->toBe(6)
        ->and((int) DB::table('existencias_almacen')->where('almacen_id', 20)->value('cantidad_fisica'))->toBe(1)
        ->and(DB::table('registros_auditoria')->where('accion', 'transfer.close')->where('entidad_id', $transfer->id)->value('observaciones'))
        ->toBe('Diferencia documentada por el destino.');
});

it('requires the IMEI when reconciling a serialized transfer discrepancy', function (): void {
    DB::table('productos')->insert(['id' => 101, 'empresa_id' => 1, 'nombre' => 'Telefono sintetico', 'marca' => 'Prueba', 'existencias' => 1, 'activo' => true]);
    DB::table('variantes_productos')->insert(['id' => 501, 'producto_id' => 101, 'nombre_color' => 'Negro', 'codigo_sku' => 'TEL-01', 'existencias' => 1, 'controla_serie' => true]);
    DB::table('existencias_almacen')->insert([
        'almacen_id' => 10, 'variante_id' => 501, 'producto_id' => 101, 'empresa_id' => 1, 'sede_id' => 1,
        'cantidad_fisica' => 1, 'cantidad_reservada' => 0,
    ]);
    DB::table('dispositivos')->insert(['id' => 601, 'producto_id' => 101, 'variante_id' => 501, 'imei' => '356000000000001', 'almacen_actual_id' => 10, 'estado' => 'disponible']);

    $service = app(ServicioTransferenciasExistencias::class);
    $actor = transferTestActor();
    $transfer = $service->request([
        'clave_idempotencia' => (string) Str::uuid(), 'almacen_origen_id' => 10, 'almacen_destino_id' => 20,
        'items' => [['producto_id' => 101, 'variante_id' => 501, 'cantidad' => 1]],
    ], $actor, null);
    $service->approve($transfer, $actor, (string) Str::uuid(), null);
    $lineId = (int) $transfer->detalles->sole()->id;
    $service->dispatch($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(),
        'items' => [['detalle_id' => $lineId, 'cantidad' => 1, 'series' => '356000000000001']],
    ], $actor, null);

    expect(fn () => $service->reconcileDifference($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 1]],
        'motivo' => 'Dispositivo extraviado.',
    ], $actor, null))->toThrow(HttpException::class);

    $service->reconcileDifference($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(),
        'items' => [['detalle_id' => $lineId, 'cantidad' => 1, 'series' => '356000000000001']],
        'motivo' => 'IMEI no localizado en destino.',
    ], $actor, null);

    expect(DB::table('dispositivos')->where('id', 601)->value('estado'))->toBe('discrepancia_transferencia')
        ->and(DB::table('movimientos_dispositivos')->count())->toBe(2)
        ->and((int) DB::table('existencias_almacen')->where('almacen_id', 20)->value('cantidad_fisica'))->toBe(0);
});

it('closes a fully received partial dispatch only with a documented unshipped difference', function (): void {
    $service = app(ServicioTransferenciasExistencias::class);
    $actor = transferTestActor();
    $transfer = createTransferForTest($service, 3);
    $service->approve($transfer, $actor, (string) Str::uuid(), null);
    $lineId = (int) $transfer->detalles->sole()->id;
    $service->dispatch($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 2]],
    ], $actor, null);
    $service->receive($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 2]],
    ], $actor, null);

    expect($transfer->fresh()->estado)->toBe('recibida parcialmente')
        ->and(fn () => $service->close($transfer->fresh(), ['clave_idempotencia' => (string) Str::uuid()], $actor, null))
        ->toThrow(HttpException::class);

    $service->close($transfer->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'motivo' => 'Una unidad quedó sin autorizar para despacho.',
    ], $actor, null);

    expect($transfer->fresh()->estado)->toBe('cerrada');
});

it('cancels before dispatch without stock effects and rejects cancellation after dispatch', function (): void {
    $service = app(ServicioTransferenciasExistencias::class);
    $actor = transferTestActor();
    $cancelled = createTransferForTest($service, 1);
    $service->cancel($cancelled, [
        'clave_idempotencia' => (string) Str::uuid(), 'motivo' => 'Solicitud duplicada por el operador.',
    ], $actor, null);
    expect($cancelled->fresh()->estado)->toBe('cancelada')
        ->and((int) DB::table('existencias_almacen')->where('almacen_id', 10)->value('cantidad_fisica'))->toBe(10);

    $dispatched = createTransferForTest($service, 1);
    $service->approve($dispatched, $actor, (string) Str::uuid(), null);
    $lineId = (int) $dispatched->detalles->sole()->id;
    $service->dispatch($dispatched->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'items' => [['detalle_id' => $lineId, 'cantidad' => 1]],
    ], $actor, null);

    expect(fn () => $service->cancel($dispatched->fresh(), [
        'clave_idempotencia' => (string) Str::uuid(), 'motivo' => 'No se puede cancelar despues del despacho.',
    ], $actor, null))->toThrow(HttpException::class)
        ->and($dispatched->fresh()->estado)->toBe('en tránsito')
        ->and((int) DB::table('existencias_almacen')->where('almacen_id', 10)->value('cantidad_fisica'))->toBe(9);
});
