<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\SpanishAccessFixture;

it('creates a payable account from a received purchase and applies a registered payment idempotently', function () {
    Schema::create('cuentas_por_pagar', function (Blueprint $tabla): void {
        $tabla->id();
        $tabla->unsignedInteger('compra_id')->unique();
        $tabla->decimal('monto_original', 15, 2);
        $tabla->char('moneda', 3)->default('PEN');
        $tabla->date('vence_en');
        $tabla->string('estado', 30)->default('pendiente');
        $tabla->dateTime('creado_en')->useCurrent();
    });
    Schema::create('aplicaciones_pago', function (Blueprint $tabla): void {
        $tabla->unsignedBigInteger('pago_id');
        $tabla->unsignedBigInteger('cuenta_pagar_id');
        $tabla->decimal('importe_aplicado', 15, 2);
        $tabla->dateTime('aplicado_en')->useCurrent();
        $tabla->primary(['pago_id', 'cuenta_pagar_id']);
    });

    $administrador = SpanishAccessFixture::user('administrador');
    $organizacion = SpanishAccessFixture::organization();
    $proveedorId = DB::table('proveedores')->insertGetId([
        'nombre' => 'Proveedor de cuenta aislada', 'ruc' => '20111111112', 'correo' => 'cuenta@example.test',
        'activo' => true, 'empresa_id' => $organizacion['empresa_id'], 'creado_en' => now(),
    ]);
    DB::table('compras')->insert([
        'id' => 9981, 'proveedor_id' => $proveedorId, 'total' => '275.50', 'estado' => 'Recibida',
        'fecha_compra' => now()->toDateString(), 'activo' => true, 'creado_en' => now(),
        'empresa_id' => $organizacion['empresa_id'], 'sede_id' => $organizacion['sede_id'], 'almacen_id' => $organizacion['almacen_id'],
        'moneda' => 'PEN',
    ]);
    $medioPagoId = DB::table('medios_pago')->insertGetId(['codigo' => 'TEST-CUENTA', 'nombre' => 'Medio de prueba', 'activo' => true]);
    $pagoId = DB::table('pagos')->insertGetId([
        'empresa_id' => $organizacion['empresa_id'], 'sede_id' => $organizacion['sede_id'], 'codigo' => 'PAGO-CUENTA-TEST',
        'tipo' => 'pago', 'estado' => 'registrado', 'medio_pago_id' => $medioPagoId, 'usuario_id' => $administrador->id,
        'compra_id' => 9981, 'moneda' => 'PEN', 'importe' => '275.50', 'fecha_pago' => now(), 'creado_en' => now(),
    ]);

    $this->actingAs($administrador)->post(route('management.payments.accounts.payables.store'), [
        'compra_id' => 9981, 'vence_en' => now()->addDays(10)->toDateString(),
    ])->assertRedirect(route('management.payments.preview.accounts'));
    $cuentaId = (int) DB::table('cuentas_por_pagar')->where('compra_id', 9981)->value('id');
    $datosAplicacion = ['cuenta_pagar_id' => $cuentaId, 'pago_id' => $pagoId, 'importe' => '275.50'];
    $this->post(route('management.payments.accounts.payables.update'), [...$datosAplicacion, 'importe' => '275.51'])
        ->assertUnprocessable();
    $this->post(route('management.payments.accounts.payables.update'), $datosAplicacion)->assertRedirect(route('management.payments.preview.accounts'));
    $this->post(route('management.payments.accounts.payables.update'), $datosAplicacion)->assertRedirect(route('management.payments.preview.accounts'));

    expect((float) DB::table('cuentas_por_pagar')->where('id', $cuentaId)->value('monto_original'))->toBe(275.5)
        ->and(DB::table('aplicaciones_pago')->where('pago_id', $pagoId)->where('cuenta_pagar_id', $cuentaId)->count())->toBe(1)
        ->and((float) DB::table('aplicaciones_pago')->where('pago_id', $pagoId)->sum('importe_aplicado'))->toBe(275.5)
        ->and(DB::table('registros_auditoria')->where('accion', 'cuenta_por_pagar.creada')->where('entidad_id', $cuentaId)->count())->toBe(1)
        ->and(DB::table('registros_auditoria')->where('accion', 'cuenta_por_pagar.pago_aplicado')->where('entidad_id', $cuentaId)->count())->toBe(1);
});

it('applies a registered collection to a receivable only once and within its balance', function () {
    if (! Schema::hasTable('cuentas_por_cobrar')) {
        Schema::create('cuentas_por_cobrar', function (Blueprint $tabla): void {
            $tabla->id();
            $tabla->unsignedBigInteger('documento_electronico_id')->unique();
            $tabla->decimal('monto_original', 15, 2);
            $tabla->char('moneda', 3)->default('PEN');
            $tabla->date('vence_en');
            $tabla->string('estado', 30)->default('pendiente');
            $tabla->dateTime('creado_en')->useCurrent();
        });
    }
    if (! Schema::hasTable('aplicaciones_cobro')) {
        Schema::create('aplicaciones_cobro', function (Blueprint $tabla): void {
            $tabla->unsignedBigInteger('pago_id');
            $tabla->unsignedBigInteger('cuenta_cobrar_id');
            $tabla->decimal('importe_aplicado', 15, 2);
            $tabla->dateTime('aplicado_en')->useCurrent();
            $tabla->primary(['pago_id', 'cuenta_cobrar_id']);
        });
    }
    foreach (['empresa_id', 'sede_id', 'venta_id'] as $columna) {
        if (! Schema::hasColumn('documentos_electronicos', $columna)) {
            Schema::table('documentos_electronicos', function (Blueprint $tabla) use ($columna): void {
                $tabla->unsignedBigInteger($columna)->nullable();
            });
        }
    }

    $administrador = SpanishAccessFixture::user('administrador');
    $organizacion = SpanishAccessFixture::organization();
    DB::table('ventas')->insert([
        'id' => 9951, 'empresa_id' => $organizacion['empresa_id'], 'sede_id' => $organizacion['sede_id'],
        'almacen_id' => $organizacion['almacen_id'], 'codigo' => 'VENTA-COBRO-PRUEBA', 'moneda' => 'PEN',
        'total' => '180.00', 'fecha_venta' => now(), 'creado_en' => now(), 'actualizado_en' => now(),
    ]);
    DB::table('documentos_electronicos')->insert([
        'id' => 9961, 'empresa_id' => $organizacion['empresa_id'], 'sede_id' => $organizacion['sede_id'],
        'venta_id' => 9951,
    ]);
    DB::table('cuentas_por_cobrar')->insert([
        'id' => 9971, 'documento_electronico_id' => 9961, 'monto_original' => '180.00', 'moneda' => 'PEN',
        'vence_en' => now()->addDays(5)->toDateString(), 'estado' => 'pendiente', 'creado_en' => now(),
    ]);
    $medioPagoId = DB::table('medios_pago')->insertGetId(['codigo' => 'TEST-COBRO', 'nombre' => 'Medio de prueba', 'activo' => true]);
    $pagoId = DB::table('pagos')->insertGetId([
        'empresa_id' => $organizacion['empresa_id'], 'sede_id' => $organizacion['sede_id'], 'codigo' => 'COBRO-PRUEBA',
        'tipo' => 'cobro', 'estado' => 'registrado', 'medio_pago_id' => $medioPagoId, 'usuario_id' => $administrador->id,
        'venta_id' => 9951, 'moneda' => 'PEN', 'importe' => '180.00', 'fecha_pago' => now(), 'creado_en' => now(),
    ]);
    $datosAplicacion = ['cuenta_cobrar_id' => 9971, 'pago_id' => $pagoId, 'importe' => '180.00'];

    $this->actingAs($administrador)->post(route('management.payments.accounts.receivables.update'), [...$datosAplicacion, 'importe' => '180.01'])
        ->assertUnprocessable();
    $this->post(route('management.payments.accounts.receivables.update'), $datosAplicacion)
        ->assertRedirect(route('management.payments.preview.accounts'));
    $this->post(route('management.payments.accounts.receivables.update'), $datosAplicacion)
        ->assertRedirect(route('management.payments.preview.accounts'));

    expect(DB::table('aplicaciones_cobro')->where('pago_id', $pagoId)->where('cuenta_cobrar_id', 9971)->count())->toBe(1)
        ->and((float) DB::table('aplicaciones_cobro')->where('pago_id', $pagoId)->sum('importe_aplicado'))->toBe(180.0)
        ->and(DB::table('pagos')->where('id', $pagoId)->value('estado'))->toBe('registrado')
        ->and(DB::table('registros_auditoria')->where('accion', 'cuenta_por_cobrar.cobro_aplicado')->where('entidad_id', 9971)->count())->toBe(1);
});
