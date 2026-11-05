<?php

use App\Models\Catalogo\Producto;
use App\Models\Inventario\Dispositivo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\SpanishAccessFixture;

function prepararEsquemaDispositivosParaPrueba(): void
{
    if (! Schema::hasTable('dispositivos')) {
        Schema::create('dispositivos', function (Blueprint $tabla): void {
            $tabla->bigIncrements('id');
            $tabla->unsignedInteger('producto_id');
            $tabla->unsignedInteger('variante_id')->nullable();
            $tabla->string('imei', 20)->nullable()->unique();
            $tabla->string('numero_serie', 100)->nullable()->unique();
            $tabla->unsignedBigInteger('detalle_compra_id')->nullable();
            $tabla->unsignedBigInteger('almacen_actual_id')->nullable();
            $tabla->string('estado', 30)->default('disponible');
            $tabla->dateTime('fecha_ingreso', 6)->nullable();
            $tabla->dateTime('fecha_salida', 6)->nullable();
        });
    }

    if (! Schema::hasTable('movimientos_dispositivos')) {
        Schema::create('movimientos_dispositivos', function (Blueprint $tabla): void {
            $tabla->bigIncrements('id');
            $tabla->unsignedBigInteger('dispositivo_id');
            $tabla->unsignedBigInteger('movimiento_inventario_id');
            $tabla->string('estado_anterior', 30)->nullable();
            $tabla->string('estado_nuevo', 30);
            $tabla->dateTime('creado_en', 6)->useCurrent();
        });
    }
}

function crearDispositivoParaPrueba(Producto $producto, int $almacenId, string $identificador, string $estado = 'disponible'): Dispositivo
{
    return Dispositivo::factory()->create([
        'producto_id' => $producto->getKey(),
        'almacen_actual_id' => $almacenId,
        'imei' => $identificador,
        'estado' => $estado,
        'fecha_ingreso' => now(),
    ]);
}

it('muestra contadores cero y estado vacío cuando no hay dispositivos', function (): void {
    prepararEsquemaDispositivosParaPrueba();
    SpanishAccessFixture::organization();
    $administrador = SpanishAccessFixture::user('administrador');

    $respuesta = $this->withoutVite()->actingAs($administrador)->get(route('management.inventory.preview.serials'));

    $respuesta->assertOk()
        ->assertViewHas('resumen', fn (array $resumen): bool => $resumen['total'] === 0 && $resumen['con_imei'] === 0)
        ->assertSee('No hay dispositivos registrados para las sedes autorizadas.')
        ->assertSee('Los equipos serializados deben vincularse a una entrada real de inventario.')
        ->assertDontSee('iPhone 15')
        ->assertDontSee('12 846');
});

it('filtra por producto, estado y texto usando dispositivos reales', function (): void {
    prepararEsquemaDispositivosParaPrueba();
    $organizacion = SpanishAccessFixture::organization();
    $administrador = SpanishAccessFixture::user('administrador');
    $producto = Producto::factory()->create([
        'empresa_id' => $organizacion['empresa_id'],
        'marca' => 'Marca de prueba',
        'nombre' => 'Equipo serializado de prueba',
    ]);
    crearDispositivoParaPrueba($producto, $organizacion['almacen_id'], 'TEST-IMEI-1001');
    crearDispositivoParaPrueba($producto, $organizacion['almacen_id'], 'TEST-IMEI-1002', 'vendido');

    $respuesta = $this->withoutVite()->actingAs($administrador)->get(route('management.inventory.preview.serials', [
        'buscar' => '1001',
        'producto' => $producto->getKey(),
        'estado' => 'disponible',
    ]));

    $respuesta->assertOk()
        ->assertViewHas('resumen', fn (array $resumen): bool => $resumen['total'] === 2 && $resumen['con_imei'] === 2)
        ->assertViewHas('dispositivos', fn ($dispositivos): bool => $dispositivos->total() === 1)
        ->assertSee('TEST-IMEI-1001')
        ->assertDontSee('TEST-IMEI-1002')
        ->assertDontSee('iPhone 15');
});

it('restringe dispositivos al ámbito de la sede seleccionada en la sesión', function (): void {
    prepararEsquemaDispositivosParaPrueba();
    $organizacion = SpanishAccessFixture::organization();
    $administrador = SpanishAccessFixture::user('administrador');
    $producto = Producto::factory()->create(['empresa_id' => $organizacion['empresa_id']]);
    crearDispositivoParaPrueba($producto, $organizacion['almacen_id'], 'TEST-SEDE-PRINCIPAL');

    $sedeAlternaId = DB::table('sedes')->insertGetId([
        'empresa_id' => $organizacion['empresa_id'],
        'codigo' => 'ALTERNATIVA',
        'nombre' => 'Sede alternativa de prueba',
        'activa' => true,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    $almacenAlternoId = DB::table('almacenes')->insertGetId([
        'sede_id' => $sedeAlternaId,
        'codigo' => 'ALTERNATIVO',
        'nombre' => 'Almacén alternativo de prueba',
        'tipo' => 'general',
        'permite_venta' => true,
        'activo' => true,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    crearDispositivoParaPrueba($producto, $almacenAlternoId, 'TEST-SEDE-ALTERNATIVA');

    $respuesta = $this->withoutVite()
        ->actingAs($administrador)
        ->withSession(['sede_actual_id' => $organizacion['sede_id']])
        ->get(route('management.inventory.preview.serials'));

    $respuesta->assertOk()
        ->assertViewHas('dispositivos', fn ($dispositivos): bool => $dispositivos->total() === 1)
        ->assertSee('TEST-SEDE-PRINCIPAL')
        ->assertDontSee('TEST-SEDE-ALTERNATIVA');
});

it('pagina el listado de dispositivos consultados', function (): void {
    prepararEsquemaDispositivosParaPrueba();
    $organizacion = SpanishAccessFixture::organization();
    $administrador = SpanishAccessFixture::user('administrador');
    $producto = Producto::factory()->create(['empresa_id' => $organizacion['empresa_id']]);

    foreach (range(1, 21) as $numero) {
        crearDispositivoParaPrueba($producto, $organizacion['almacen_id'], 'TEST-SERIE-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT));
    }

    $respuesta = $this->withoutVite()->actingAs($administrador)->get(route('management.inventory.preview.serials'));

    $respuesta->assertOk()
        ->assertViewHas('dispositivos', fn ($dispositivos): bool => $dispositivos->total() === 21 && $dispositivos->count() === 20)
        ->assertSee('page=2');
});

it('deniega el panel de series a usuarios sin rol administrador', function (): void {
    prepararEsquemaDispositivosParaPrueba();
    SpanishAccessFixture::organization();
    $usuarioMarketing = SpanishAccessFixture::user('marketing');

    $this->actingAs($usuarioMarketing)
        ->get(route('management.inventory.preview.serials'))
        ->assertForbidden();
});
