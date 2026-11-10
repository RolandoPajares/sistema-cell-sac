<?php

use App\Models\Catalogo\Producto;
use App\Servicios\Gestion\InventarioLogistica\Productos\ServicioConfiguracionTienda;
use App\Servicios\Gestion\InventarioLogistica\Productos\ServicioPreciosMayoristas;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\SpanishAccessFixture;

/**
 * Ejecuta la operación prepararTablasEcommercePersistente definida por la clase EcommercePersistenciaTest.
 *
 * @return void Resultado de la operación.
 */
function prepararTablasEcommercePersistente(): void
{
    Schema::create('precios_mayoristas', function (Blueprint $tabla): void {
        $tabla->id();
        $tabla->unsignedBigInteger('empresa_id');
        $tabla->unsignedInteger('producto_id');
        $tabla->unsignedInteger('variante_id')->nullable();
        $tabla->decimal('precio', 10, 2);
        $tabla->dateTime('vigente_desde');
        $tabla->dateTime('vigente_hasta')->nullable();
        $tabla->string('motivo', 300);
        $tabla->unsignedBigInteger('creado_por_usuario_id')->nullable();
        $tabla->dateTime('creado_en')->nullable();
    });
    Schema::create('configuraciones_tienda', function (Blueprint $tabla): void {
        $tabla->id();
        $tabla->unsignedBigInteger('empresa_id')->unique();
        $tabla->json('configuracion_json');
        $tabla->unsignedBigInteger('actualizado_por_usuario_id')->nullable();
        $tabla->dateTime('creado_en')->nullable();
        $tabla->dateTime('actualizado_en')->nullable();
    });
}

it('registra precios mayoristas por empresa y conserva la vigencia anterior sin solapamientos', function (): void {
    prepararTablasEcommercePersistente();
    $alcance = SpanishAccessFixture::organization();
    $usuario = SpanishAccessFixture::user('administrador');
    $producto = Producto::factory()->create(['empresa_id' => $alcance['empresa_id']]);
    $servicio = app(ServicioPreciosMayoristas::class);

    $anterior = $servicio->registrar([
        'producto_id' => $producto->id, 'precio' => '450.00', 'vigente_desde' => '2026-01-01',
        'vigente_hasta' => null, 'motivo' => 'Precio inicial aprobado',
    ], $usuario, '127.0.0.1');
    $nuevo = $servicio->registrar([
        'producto_id' => $producto->id, 'precio' => '425.00', 'vigente_desde' => '2026-02-01',
        'vigente_hasta' => null, 'motivo' => 'Actualización comercial aprobada',
    ], $usuario, '127.0.0.1');

    expect(DB::table('precios_mayoristas')->where('empresa_id', $alcance['empresa_id'])->count())->toBe(2)
        ->and($anterior->fresh()->vigente_hasta->format('Y-m-d H:i:s'))->toBe('2026-01-31 23:59:59')
        ->and($nuevo->precio)->toBe('425.00')
        ->and(DB::table('registros_auditoria')->where('accion', 'precio_mayorista.registrado')->count())->toBe(2);

    DB::table('precios_mayoristas')->insert([
        'empresa_id' => $alcance['empresa_id'], 'producto_id' => $producto->id, 'variante_id' => null,
        'precio' => '400.00', 'vigente_desde' => '2026-03-01 00:00:00', 'vigente_hasta' => null,
        'motivo' => 'Vigencia futura reservada', 'creado_por_usuario_id' => $usuario->id,
    ]);

    expect(fn () => $servicio->registrar([
        'producto_id' => $producto->id, 'precio' => '410.00', 'vigente_desde' => '2026-02-15',
        'vigente_hasta' => null, 'motivo' => 'Intento de solapamiento',
    ], $usuario, '127.0.0.1'))->toThrow(HttpException::class);
});

it('guarda y combina secciones de tienda dentro de la empresa autorizada', function (): void {
    prepararTablasEcommercePersistente();
    $alcance = SpanishAccessFixture::organization();
    $usuario = SpanishAccessFixture::user('administrador');
    $servicio = app(ServicioConfiguracionTienda::class);

    $servicio->guardar([
        'seccion' => 'general',
        'configuracion' => ['nombre_tienda' => 'Tienda real', 'dominio' => null],
    ], $usuario, '127.0.0.1');
    $registro = $servicio->guardar([
        'seccion' => 'seo',
        'configuracion' => ['titulo' => 'Catálogo real', 'pixel_id' => null],
    ], $usuario, '127.0.0.1');

    expect(DB::table('configuraciones_tienda')->where('empresa_id', $alcance['empresa_id'])->count())->toBe(1)
        ->and($registro->configuracion_json)->toMatchArray([
            'general' => ['nombre_tienda' => 'Tienda real', 'dominio' => null],
            'seo' => ['titulo' => 'Catálogo real', 'pixel_id' => null],
        ])
        ->and(DB::table('registros_auditoria')->where('accion', 'configuracion_tienda.actualizada')->count())->toBe(2);
});

it('rechaza guardar preferencias de tienda sin el permiso vigente', function (): void {
    prepararTablasEcommercePersistente();
    SpanishAccessFixture::organization();
    $usuario = SpanishAccessFixture::user('cliente_minorista');

    expect(fn () => app(ServicioConfiguracionTienda::class)->guardar([
        'seccion' => 'general', 'configuracion' => ['nombre_tienda' => 'No autorizado'],
    ], $usuario, null))->toThrow(HttpException::class);

    expect(DB::table('configuraciones_tienda')->count())->toBe(0);
});
