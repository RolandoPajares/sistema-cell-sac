<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\SpanishAccessFixture;

function prepararEsquemaEnviosParaPrueba(): void
{
    if (! Schema::hasTable('envios_pedidos')) {
        Schema::create('envios_pedidos', function (Blueprint $tabla): void {
            $tabla->bigIncrements('id');
            $tabla->unsignedBigInteger('empresa_id');
            $tabla->unsignedBigInteger('sede_id');
            $tabla->unsignedInteger('pedido_id');
            $tabla->unsignedBigInteger('almacen_id');
            $tabla->unsignedBigInteger('guia_remision_id')->nullable();
            $tabla->string('codigo', 60);
            $tabla->string('modalidad', 30);
            $tabla->string('transportista', 120)->nullable();
            $tabla->string('servicio', 120)->nullable();
            $tabla->string('numero_seguimiento', 160)->nullable();
            $tabla->string('estado', 30)->default('pendiente');
            $tabla->char('moneda', 3)->default('PEN');
            $tabla->decimal('costo_envio', 15, 2)->default(0);
            $tabla->text('destino_historico');
            $tabla->dateTime('estimado_entrega_en', 6)->nullable();
            $tabla->dateTime('despachado_en', 6)->nullable();
            $tabla->dateTime('entregado_en', 6)->nullable();
            $tabla->unsignedBigInteger('creado_por_usuario_id')->nullable();
            $tabla->dateTime('creado_en', 6)->useCurrent();
        });
    }

    if (! Schema::hasTable('eventos_envios_pedidos')) {
        Schema::create('eventos_envios_pedidos', function (Blueprint $tabla): void {
            $tabla->bigIncrements('id');
            $tabla->unsignedBigInteger('envio_pedido_id');
            $tabla->string('estado', 30);
            $tabla->string('descripcion', 500)->nullable();
            $tabla->string('fuente', 30)->default('interno');
            $tabla->string('identificador_externo', 160)->nullable();
            $tabla->dateTime('ocurrido_en', 6);
            $tabla->dateTime('registrado_en', 6)->useCurrent();
            $tabla->text('datos_seguros')->nullable();
        });
    }
}

/** @param array<string, int> $organizacion */
function crearEnvioParaPrueba(array $organizacion, string $codigo, string $estado = 'pendiente', ?string $seguimiento = null): int
{
    return (int) DB::table('envios_pedidos')->insertGetId([
        'empresa_id' => $organizacion['empresa_id'],
        'sede_id' => $organizacion['sede_id'],
        'pedido_id' => 9001,
        'almacen_id' => $organizacion['almacen_id'],
        'codigo' => $codigo,
        'modalidad' => 'modalidad_de_prueba',
        'transportista' => 'transportista_de_prueba',
        'servicio' => 'servicio_de_prueba',
        'numero_seguimiento' => $seguimiento,
        'estado' => $estado,
        'moneda' => 'PEN',
        'costo_envio' => 0,
        'destino_historico' => json_encode(['referencia' => 'destino aislado de prueba'], JSON_THROW_ON_ERROR),
        'creado_en' => now(),
    ]);
}

it('muestra cero y un estado vacío cuando no hay envíos para la sede', function (): void {
    prepararEsquemaEnviosParaPrueba();
    SpanishAccessFixture::organization();
    $administrador = SpanishAccessFixture::user('administrador');
    SpanishAccessFixture::grantScreenReadPermission($administrador, 'admin.shipments', 'management.inventory.preview.shipments');

    $respuesta = $this->withoutVite()->actingAs($administrador)->get(route('management.inventory.preview.shipments'));

    $respuesta->assertOk()
        ->assertViewHas('resumen', fn (array $resumen): bool => $resumen['total'] === 0 && $resumen['con_seguimiento'] === 0)
        ->assertSee('No hay envíos registrados para las sedes autorizadas.')
        ->assertDontSee('Olva Courier')
        ->assertDontSee('ENV-008726');
});

it('filtra envíos reales por texto estado y modalidad', function (): void {
    prepararEsquemaEnviosParaPrueba();
    $organizacion = SpanishAccessFixture::organization();
    $administrador = SpanishAccessFixture::user('administrador');
    SpanishAccessFixture::grantScreenReadPermission($administrador, 'admin.shipments', 'management.inventory.preview.shipments');
    $envioId = crearEnvioParaPrueba($organizacion, 'ENV-REAL-001', 'despachado', 'SEGUIMIENTO-REAL-001');
    DB::table('eventos_envios_pedidos')->insert([
        'envio_pedido_id' => $envioId,
        'estado' => 'despachado',
        'descripcion' => 'Evento de prueba anterior',
        'fuente' => 'interno',
        'ocurrido_en' => now()->subMinute(),
        'registrado_en' => now()->subMinute(),
    ]);
    DB::table('eventos_envios_pedidos')->insert([
        'envio_pedido_id' => $envioId,
        'estado' => 'actualizado',
        'descripcion' => 'Evento vigente de prueba',
        'fuente' => 'interno',
        'ocurrido_en' => now(),
        'registrado_en' => now(),
    ]);
    crearEnvioParaPrueba($organizacion, 'ENV-REAL-002', 'pendiente');

    $respuesta = $this->withoutVite()->actingAs($administrador)->get(route('management.inventory.preview.shipments', [
        'buscar' => 'SEGUIMIENTO-REAL-001',
        'estado' => 'despachado',
        'modalidad' => 'modalidad_de_prueba',
    ]));

    $respuesta->assertOk()
        ->assertViewHas('resumen', fn (array $resumen): bool => $resumen['total'] === 2 && $resumen['con_seguimiento'] === 1)
        ->assertViewHas('envios', fn ($envios): bool => $envios->total() === 1)
        ->assertSee('ENV-REAL-001')
        ->assertSee('SEGUIMIENTO-REAL-001')
        ->assertSee('Evento vigente de prueba')
        ->assertDontSee('Evento de prueba anterior')
        ->assertDontSee('ENV-REAL-002');
});

it('limita los envíos a las sedes autorizadas de la sesión', function (): void {
    prepararEsquemaEnviosParaPrueba();
    $organizacion = SpanishAccessFixture::organization();
    $administrador = SpanishAccessFixture::user('administrador');
    SpanishAccessFixture::grantScreenReadPermission($administrador, 'admin.shipments', 'management.inventory.preview.shipments');
    crearEnvioParaPrueba($organizacion, 'ENV-SEDE-PRINCIPAL');

    $sedeAlternaId = DB::table('sedes')->insertGetId([
        'empresa_id' => $organizacion['empresa_id'],
        'codigo' => 'ENV-ALTERNATIVA',
        'nombre' => 'Sede alternativa de prueba',
        'activa' => true,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    $almacenAlternoId = DB::table('almacenes')->insertGetId([
        'sede_id' => $sedeAlternaId,
        'codigo' => 'ENV-ALTERNATIVO',
        'nombre' => 'Almacén alternativo de prueba',
        'tipo' => 'general',
        'permite_venta' => true,
        'activo' => true,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    crearEnvioParaPrueba([
        ...$organizacion,
        'sede_id' => $sedeAlternaId,
        'almacen_id' => $almacenAlternoId,
    ], 'ENV-SEDE-ALTERNATIVA');

    $respuesta = $this->withoutVite()
        ->actingAs($administrador)
        ->withSession(['sede_actual_id' => $organizacion['sede_id']])
        ->get(route('management.inventory.preview.shipments'));

    $respuesta->assertOk()
        ->assertViewHas('envios', fn ($envios): bool => $envios->total() === 1)
        ->assertSee('ENV-SEDE-PRINCIPAL')
        ->assertDontSee('ENV-SEDE-ALTERNATIVA');
});

it('pagina los envíos consultados y deniega a roles sin acceso administrativo', function (): void {
    prepararEsquemaEnviosParaPrueba();
    $organizacion = SpanishAccessFixture::organization();
    $administrador = SpanishAccessFixture::user('administrador');
    SpanishAccessFixture::grantScreenReadPermission($administrador, 'admin.shipments', 'management.inventory.preview.shipments');

    foreach (range(1, 21) as $numero) {
        crearEnvioParaPrueba($organizacion, 'ENV-PAGINA-'.str_pad((string) $numero, 3, '0', STR_PAD_LEFT));
    }

    $respuesta = $this->withoutVite()->actingAs($administrador)->get(route('management.inventory.preview.shipments'));
    $respuesta->assertOk()
        ->assertViewHas('envios', fn ($envios): bool => $envios->total() === 21 && $envios->count() === 20)
        ->assertSee('page=2');

    $usuarioMarketing = SpanishAccessFixture::user('marketing');
    $this->actingAs($usuarioMarketing)
        ->get(route('management.inventory.preview.shipments'))
        ->assertForbidden();
});
