<?php

use App\Contratos\Productos\AlcanceProductos;
use App\Contratos\Productos\AuditoriaProductos;
use App\Models\Catalogo\Producto;
use App\Models\User;
use App\Servicios\ServicioCatalogo;
use App\Servicios\ServicioGestionProductos;
use App\Servicios\ServicioInventario;
use App\Servicios\ServicioStockProducto;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function (): void {
    Schema::create('productos', function (Blueprint $table): void {
        $table->increments('id');
        $table->string('marca');
        $table->string('nombre');
        $table->unsignedInteger('categoria_id');
        $table->string('categoria');
        $table->decimal('precio', 10, 2);
        $table->decimal('precio_oferta', 10, 2)->nullable();
        $table->string('url_imagen')->nullable();
        $table->unsignedInteger('existencias')->default(0);
        $table->boolean('activo')->default(true);
        $table->timestamp('creado_en')->nullable();
        $table->unsignedBigInteger('empresa_id')->nullable();
    });
    Schema::create('productos_proveedores', function (Blueprint $table): void {
        $table->unsignedInteger('producto_id');
        $table->unsignedInteger('proveedor_id');
    });
    Schema::create('existencias_almacen', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedBigInteger('empresa_id')->nullable();
        $table->unsignedInteger('producto_id');
        $table->unsignedInteger('variante_id')->nullable();
        $table->decimal('cantidad_fisica', 12, 3)->default(0);
    });
    Schema::create('variantes_productos', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedInteger('producto_id');
    });
});

afterEach(function (): void {
    Schema::dropIfExists('productos_proveedores');
    Schema::dropIfExists('existencias_almacen');
    Schema::dropIfExists('variantes_productos');
    Schema::dropIfExists('productos');
});

/**
 * Ejecuta la operación usuarioGestionProductos definida por la clase ServicioGestionProductosTest.
 *
 * @return User Resultado de la operación.
 */
function usuarioGestionProductos(): User
{
    $usuario = new User;
    $usuario->setRawAttributes(['id' => 17]);

    return $usuario;
}

it('centraliza el alta y revierte la transacción cuando falla la auditoría', function (): void {
    $authorization = Mockery::mock(AlcanceProductos::class);
    $authorization->shouldReceive('atributosDeAlcance')->twice()->andReturn(['empresa_id' => 4]);
    $inventory = Mockery::mock(ServicioInventario::class);
    $inventory->shouldReceive('registerNewProduct')->twice();
    $stock = new ServicioStockProducto($inventory);
    $catalog = Mockery::mock(ServicioCatalogo::class);
    $catalog->shouldReceive('sincronizarImagenPortada')->times(3);
    $audit = Mockery::mock(AuditoriaProductos::class);
    $auditCalls = 0;
    $audit->shouldReceive('registrar')->times(3)->andReturnUsing(function () use (&$auditCalls): void {
        $auditCalls++;
        if ($auditCalls === 3) {
            throw new RuntimeException('auditoría no disponible');
        }
    });

    $service = new ServicioGestionProductos($authorization, $inventory, $catalog, $audit, $stock);
    $product = $service->crear([
        'marca' => 'Marca QA', 'nombre' => 'Producto QA', 'categoria_id' => 1,
        'categoria' => 'Móviles', 'precio' => '100.00', 'url_imagen' => 'storage/producto.jpg',
    ], null, usuarioGestionProductos(), '127.0.0.1');

    expect($product)->toBeInstanceOf(Producto::class)
        ->and(DB::table('productos')->where('id', $product->id)->value('empresa_id'))->toBe(4);

    $service->actualizar($product, ['nombre' => 'Producto actualizado'], [], ['nombre' => 'Producto QA'], usuarioGestionProductos(), '127.0.0.1');

    expect(DB::table('productos')->where('id', $product->id)->value('nombre'))->toBe('Producto actualizado');

    expect(fn (): Producto => $service->crear([
        'marca' => 'Marca QA', 'nombre' => 'Producto fallido', 'categoria_id' => 1,
        'categoria' => 'Móviles', 'precio' => '100.00',
    ], null, usuarioGestionProductos(), '127.0.0.1'))->toThrow(RuntimeException::class);

    expect(DB::table('productos')->where('nombre', 'Producto fallido')->exists())->toBeFalse()
        ->and(DB::table('productos')->count())->toBe(1);
});
