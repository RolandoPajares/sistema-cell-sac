<?php

use App\Servicios\Gestion\Finanzas\CuentasCobrarPagar\ServicioConsultasCuentas;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\SpanishAccessFixture;

it('consulta cuentas reales por cobrar y pagar y calcula saldos con pagos aplicados', function () {
    if (! Schema::hasTable('documentos_electronicos')) {
        Schema::create('documentos_electronicos', function (Blueprint $tabla): void {
            $tabla->id();
        });
    }
    Schema::table('documentos_electronicos', function (Blueprint $tabla): void {
        foreach (['empresa_id', 'sede_id', 'serie', 'correlativo', 'fecha_emision', 'receptor_historico'] as $columna) {
            if (! Schema::hasColumn('documentos_electronicos', $columna)) {
                match ($columna) {
                    'empresa_id', 'sede_id', 'correlativo' => $tabla->unsignedBigInteger($columna)->nullable(),
                    'fecha_emision' => $tabla->dateTime($columna)->nullable(),
                    'receptor_historico' => $tabla->text($columna)->nullable(),
                    default => $tabla->string($columna, 100)->nullable(),
                };
            }
        }
    });
    Schema::create('cuentas_por_cobrar', function (Blueprint $tabla): void {
        $tabla->id();
        $tabla->unsignedBigInteger('documento_electronico_id');
        $tabla->decimal('monto_original', 15, 2);
        $tabla->char('moneda', 3)->default('PEN');
        $tabla->date('vence_en');
        $tabla->string('estado', 30)->default('pendiente');
    });
    Schema::create('cuentas_por_pagar', function (Blueprint $tabla): void {
        $tabla->id();
        $tabla->unsignedInteger('compra_id');
        $tabla->decimal('monto_original', 15, 2);
        $tabla->char('moneda', 3)->default('PEN');
        $tabla->date('vence_en');
        $tabla->string('estado', 30)->default('pendiente');
    });
    Schema::create('aplicaciones_cobro', function (Blueprint $tabla): void {
        $tabla->unsignedBigInteger('pago_id');
        $tabla->unsignedBigInteger('cuenta_cobrar_id');
        $tabla->decimal('importe_aplicado', 15, 2);
    });
    Schema::create('aplicaciones_pago', function (Blueprint $tabla): void {
        $tabla->unsignedBigInteger('pago_id');
        $tabla->unsignedBigInteger('cuenta_pagar_id');
        $tabla->decimal('importe_aplicado', 15, 2);
    });

    $usuario = SpanishAccessFixture::user('administrador');
    $organizacion = SpanishAccessFixture::organization();
    $proveedorId = DB::table('proveedores')->insertGetId([
        'nombre' => 'Proveedor de prueba aislada', 'ruc' => '20111111111', 'correo' => 'proveedor@example.test',
        'activo' => true, 'empresa_id' => $organizacion['empresa_id'], 'creado_en' => now(),
    ]);
    DB::table('compras')->insert([
        'id' => 9711, 'proveedor_id' => $proveedorId, 'total' => 300, 'estado' => 'Recibida',
        'fecha_compra' => now()->toDateString(), 'activo' => true, 'creado_en' => now(),
        'empresa_id' => $organizacion['empresa_id'], 'sede_id' => $organizacion['sede_id'],
    ]);
    DB::table('documentos_electronicos')->insert([
        'id' => 9811, 'empresa_id' => $organizacion['empresa_id'], 'sede_id' => $organizacion['sede_id'],
        'serie' => 'F001', 'correlativo' => 45, 'fecha_emision' => now(),
        'receptor_historico' => json_encode(['razon_social' => 'Cliente de prueba aislada']),
    ]);
    DB::table('cuentas_por_cobrar')->insert([
        'id' => 9911, 'documento_electronico_id' => 9811, 'monto_original' => 250, 'moneda' => 'PEN',
        'vence_en' => now()->addDay()->toDateString(), 'estado' => 'pendiente',
    ]);
    DB::table('cuentas_por_pagar')->insert([
        'id' => 9912, 'compra_id' => 9711, 'monto_original' => 300, 'moneda' => 'PEN',
        'vence_en' => now()->subDay()->toDateString(), 'estado' => 'pendiente',
    ]);
    DB::table('pagos')->insert([
        'id' => 9812, 'empresa_id' => $organizacion['empresa_id'], 'sede_id' => $organizacion['sede_id'],
        'codigo' => 'PAGO-PRUEBA', 'tipo' => 'cobro', 'estado' => 'registrado', 'medio_pago_id' => 1,
        'moneda' => 'PEN', 'importe' => 50, 'fecha_pago' => now(), 'creado_en' => now(),
    ]);
    DB::table('aplicaciones_cobro')->insert(['pago_id' => 9812, 'cuenta_cobrar_id' => 9911, 'importe_aplicado' => 50]);

    $datos = app(ServicioConsultasCuentas::class)->obtenerVista($usuario);
    $porTipo = collect($datos['filas'])->keyBy(fn (array $fila): string => $fila['filtros']['tipo']);

    expect($datos['filas'])->toHaveCount(2)
        ->and($porTipo['Por cobrar']['celdas'][5])->toBe('PEN 200,00')
        ->and($porTipo['Por pagar']['celdas'][5])->toBe('PEN 300,00')
        ->and($datos['metricas'][0]['valor'])->toBe('PEN 200,00')
        ->and($datos['metricas'][3]['detalle'])->toBe('1 documentos');

    $this->actingAs($usuario)
        ->get(route('management.payments.preview.accounts'))
        ->assertOk()
        ->assertSee('Datos de MariaDB')
        ->assertSee('Cliente de prueba aislada')
        ->assertSee('Proveedor de prueba aislada')
        ->assertSee('PEN 200,00')
        ->assertSee('Consultar detalle real')
        ->assertSee('Aplicar cobro registrado');
});
