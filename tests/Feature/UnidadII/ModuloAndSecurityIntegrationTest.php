<?php

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Catalogo\VarianteProducto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Cotizacion;
use App\Models\Comercio\Pedido;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use App\Models\Marketing\Campania;
use App\Models\User;
use App\Servicios\ServicioCompras;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\SpanishAccessFixture;

/**
 * @param  array<string, mixed>  $attributes
 */
function productoParaIntegracionUnidadDos(array $attributes = []): Producto
{
    $scope = SpanishAccessFixture::organization();
    $stock = $attributes['existencias'] ?? 4;
    $category = Categoria::factory()->create(['empresa_id' => $scope['empresa_id']]);

    $product = Producto::factory()->create($attributes + [
        'categoria_id' => $category->id,
        'categoria' => $category->nombre,
        'empresa_id' => $scope['empresa_id'],
    ]);

    DB::table('existencias_almacen')->insert([
        'almacen_id' => $scope['almacen_id'], 'sede_id' => $scope['sede_id'],
        'empresa_id' => $scope['empresa_id'], 'producto_id' => $product->id,
        'cantidad_fisica' => $stock, 'cantidad_reservada' => 0,
        'actualizado_en' => now(),
    ]);

    return $product;
}

/**
 * Registra la información recibida y coordina su persistencia.
 *
 * @param  mixed  $nombre  Parámetro recibido por la operación.
 * @return UploadedFile Resultado de la operación.
 */
function crearImagenPngParaPruebaCatalogo(string $nombre): UploadedFile
{
    $contenido = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=', true);

    return UploadedFile::fake()->createWithContent($nombre, $contenido === false ? '' : $contenido);
}

/**
 * Ejecuta la operación prepararEsquemaDispositivosRecepcionCompra definida por la clase ModuloAndSecurityIntegrationTest.
 *
 * @return void Resultado de la operación.
 */
function prepararEsquemaDispositivosRecepcionCompra(): void
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

    if (! Schema::hasTable('detalles_compras_dispositivos')) {
        Schema::create('detalles_compras_dispositivos', function (Blueprint $tabla): void {
            $tabla->unsignedBigInteger('detalle_compra_id');
            $tabla->unsignedBigInteger('dispositivo_id')->unique();
            $tabla->primary(['detalle_compra_id', 'dispositivo_id']);
        });
    }
}

it('completes the administrator category lifecycle and records audit events', function (): void {
    $administrator = SpanishAccessFixture::user('administrador');

    $this->actingAs($administrator)
        ->post(route('management.categories.store'), [
            'nombre' => 'Accesorios',
            'descripcion' => 'Accesorios para dispositivos.',
        ])
        ->assertRedirect();

    $category = Categoria::query()->where('nombre', 'Accesorios')->firstOrFail();
    expect((int) $category->empresa_id)->toBe(SpanishAccessFixture::organization()['empresa_id']);

    $this->assertDatabaseHas('registros_auditoria', [
        'accion' => 'category.created',
        'entidad' => 'categorias',
        'entidad_id' => $category->id,
    ]);

    $this->actingAs($administrator)
        ->put(route('management.categories.update', $category), [
            'nombre' => 'Accesorios premium',
            'descripcion' => 'Accesorios actualizados.',
        ])
        ->assertRedirect();

    $this->actingAs($administrator)
        ->delete(route('management.categories.deactivate', $category))
        ->assertRedirect();

    $this->assertDatabaseHas('categorias', [
        'id' => $category->id,
        'nombre' => 'Accesorios premium',
        'activo' => 0,
    ]);
});

it('mantiene la consulta y las operaciones de categorías dentro de la empresa autorizada', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();
    $empresaAjenaId = DB::table('empresas')->insertGetId([
        'codigo' => 'CATEGORIA-AJENA',
        'razon_social' => 'Empresa ajena para categorías',
        'activo' => false,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    $categoriaVisible = Categoria::factory()->create([
        'nombre' => 'Categoría de la empresa autorizada',
        'empresa_id' => $alcance['empresa_id'],
    ]);
    $categoriaAjena = Categoria::factory()->create([
        'nombre' => 'Categoría de otra empresa',
        'empresa_id' => $empresaAjenaId,
    ]);

    $this->actingAs($administrador)
        ->get(route('management.categories'))
        ->assertOk()
        ->assertSee($categoriaVisible->nombre)
        ->assertDontSee($categoriaAjena->nombre);

    $respuestaExportacion = $this->actingAs($administrador)->get(route('management.categories.export'));
    $respuestaExportacion->assertOk()->assertDownload('categorias.csv');
    expect($respuestaExportacion->streamedContent())
        ->toContain($categoriaVisible->nombre)
        ->not->toContain($categoriaAjena->nombre);

    $this->actingAs($administrador)
        ->put(route('management.categories.update', $categoriaAjena), ['nombre' => $categoriaAjena->nombre])
        ->assertNotFound();

    $this->actingAs($administrador)
        ->delete(route('management.categories.deactivate', $categoriaAjena))
        ->assertNotFound();
});

it('completes the administrator client lifecycle', function (): void {
    $administrator = SpanishAccessFixture::user('administrador');

    $this->actingAs($administrator)
        ->post(route('management.customers.store'), [
            'tipo' => 'minorista',
            'documento' => 'DNI-10001',
            'razon_social' => null,
            'nombre_contacto' => 'Cliente Integración',
            'correo' => 'cliente.integracion@example.test',
            'telefono' => '999111222',
            'ciudad' => 'Lima',
        ])
        ->assertRedirect();

    $customer = Cliente::query()->where('documento', 'DNI-10001')->firstOrFail();

    $this->actingAs($administrator)
        ->put(route('management.customers.update', $customer), [
            'tipo' => 'mayorista',
            'documento' => 'DNI-10001',
            'razon_social' => 'Empresa Integración',
            'nombre_contacto' => 'Cliente Actualizado',
            'correo' => 'cliente.actualizado@example.test',
            'telefono' => '999333444',
            'ciudad' => 'Arequipa',
        ])
        ->assertRedirect();

    $this->actingAs($administrator)
        ->delete(route('management.customers.deactivate', $customer))
        ->assertRedirect();

    $this->assertDatabaseHas('clientes', [
        'id' => $customer->id,
        'tipo' => 'mayorista',
        'activo' => 0,
    ]);
});

it('limita el listado, los indicadores y los cambios de clientes a la empresa autorizada', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();
    $usuarioPropio = User::factory()->create(['rol' => 'cliente_minorista']);
    $usuarioAjeno = User::factory()->create(['rol' => 'cliente_minorista']);
    $empresaAjenaId = DB::table('empresas')->insertGetId([
        'codigo' => 'CLIENTES-EMPRESA-AJENA',
        'razon_social' => 'Empresa inactiva para prueba de clientes',
        'activo' => false,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    $clientePropio = Cliente::create([
        'usuario_id' => $usuarioPropio->id,
        'tipo' => 'minorista',
        'documento' => 'DNI-ALC-1',
        'nombre_contacto' => 'Cliente de empresa autorizada',
        'correo' => 'cliente.alcance@example.test',
        'activo' => true,
        'empresa_id' => $alcance['empresa_id'],
    ]);
    $clienteAjeno = Cliente::create([
        'usuario_id' => $usuarioAjeno->id,
        'tipo' => 'minorista',
        'documento' => 'DNI-ALC-2',
        'nombre_contacto' => 'Cliente de otra empresa',
        'correo' => 'cliente.ajeno@example.test',
        'activo' => true,
        'empresa_id' => $empresaAjenaId,
    ]);
    DB::table('pedidos')->insert([
        [
            'usuario_id' => $usuarioPropio->id,
            'cliente_id' => $clientePropio->id,
            'empresa_id' => $alcance['empresa_id'],
            'sede_id' => $alcance['sede_id'],
            'total' => 100,
            'subtotal' => 100,
            'descuento' => 0,
            'estado' => 'Pendiente',
            'creado_en' => now(),
        ],
        [
            'usuario_id' => $usuarioAjeno->id,
            'cliente_id' => $clienteAjeno->id,
            'empresa_id' => $empresaAjenaId,
            'sede_id' => null,
            'total' => 900,
            'subtotal' => 900,
            'descuento' => 0,
            'estado' => 'Pendiente',
            'creado_en' => now(),
        ],
    ]);

    $this->actingAs($administrador)
        ->get(route('management.customers'))
        ->assertOk()
        ->assertSee($clientePropio->nombre_contacto)
        ->assertDontSee($clienteAjeno->nombre_contacto)
        ->assertViewHas('totalCustomers', 1)
        ->assertViewHas('totalPurchases', 100.0);

    $vendedor = SpanishAccessFixture::user('ventas_minoristas');
    $this->actingAs($vendedor)
        ->get(route('management.customers'))
        ->assertOk()
        ->assertSee($clientePropio->nombre_contacto)
        ->assertDontSee($clienteAjeno->nombre_contacto);

    $this->actingAs($administrador)
        ->put(route('management.customers.update', $clienteAjeno), [
            'tipo' => 'mayorista',
            'documento' => $clienteAjeno->documento,
            'nombre_contacto' => 'Intento de cambio fuera de empresa',
            'correo' => $clienteAjeno->correo,
        ])
        ->assertNotFound();
    $this->actingAs($administrador)
        ->delete(route('management.customers.deactivate', $clienteAjeno))
        ->assertNotFound();

    $this->assertDatabaseHas('clientes', [
        'id' => $clienteAjeno->id,
        'nombre_contacto' => 'Cliente de otra empresa',
        'activo' => 1,
    ]);
});

it('completes the administrator product lifecycle', function (): void {
    $administrator = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();
    $category = Categoria::factory()->create(['nombre' => 'Telefonía', 'empresa_id' => $alcance['empresa_id']]);

    $this->actingAs($administrator)
        ->post(route('management.products.store'), [
            'marca' => 'Samsung',
            'nombre' => 'Producto Integración',
            'categoria_id' => $category->id,
            'precio' => 700,
            'precio_oferta' => 650,
            'url_imagen' => null,
            'descripcion' => 'Producto de prueba de integración.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Producto guardado. Registra las existencias desde el módulo Inventario.');

    $product = Producto::query()->where('nombre', 'Producto Integración')->firstOrFail();
    expect((int) $product->existencias)->toBe(0)
        ->and((float) DB::table('existencias_almacen')->where('producto_id', $product->id)->sum('cantidad_fisica'))->toBe(0.0);

    $this->actingAs($administrator)
        ->put(route('management.products.update', $product), [
            'marca' => 'Samsung',
            'nombre' => 'Producto Integración actualizado',
            'categoria_id' => $category->id,
            'precio' => 750,
            'precio_oferta' => 700,
            'url_imagen' => null,
            'descripcion' => 'Producto actualizado.',
        ])
        ->assertRedirect();

    expect((float) DB::table('existencias_almacen')->where('producto_id', $product->id)->sum('cantidad_fisica'))->toBe(0.0);

    $this->actingAs($administrator)
        ->post(route('management.inventory.adjust', $product), [
            'almacen_id' => $alcance['almacen_id'],
            'tipo_movimiento' => 'entrada',
            'cantidad' => 5,
            'notas' => 'Registro de existencias desde inventario.',
        ])
        ->assertRedirect();

    expect((float) DB::table('existencias_almacen')->where('producto_id', $product->id)->sum('cantidad_fisica'))->toBe(5.0)
        ->and((int) $product->fresh()->existencias)->toBe(5)
        ->and(DB::table('movimientos_inventario')->where('producto_id', $product->id)->count())->toBe(1);

    $this->actingAs($administrator)
        ->delete(route('management.products.deactivate', $product))
        ->assertRedirect();

    $this->assertDatabaseHas('productos', [
        'id' => $product->id,
        'nombre' => 'Producto Integración actualizado',
        'activo' => 0,
    ]);
});

it('completa desde los formularios el flujo de categoría, producto, inventario y venta física', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();

    $this->actingAs($administrador)
        ->post(route('management.categories.store'), [
            'nombre' => 'Categoría del flujo operativo',
            'descripcion' => 'Categoría creada durante el recorrido de operación.',
        ])
        ->assertRedirect();

    $categoria = Categoria::query()->where('nombre', 'Categoría del flujo operativo')->firstOrFail();

    $this->actingAs($administrador)
        ->post(route('management.products.store'), [
            'marca' => 'Marca operativa',
            'nombre' => 'Producto del flujo operativo',
            'categoria_id' => $categoria->id,
            'precio' => 100,
            'descripcion' => 'Producto creado para verificar el flujo completo.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', 'Producto guardado. Registra las existencias desde el módulo Inventario.');

    $producto = Producto::query()->where('nombre', 'Producto del flujo operativo')->firstOrFail();
    expect((int) $producto->existencias)->toBe(0)
        ->and((float) DB::table('existencias_almacen')->where('producto_id', $producto->id)->sum('cantidad_fisica'))->toBe(0.0);

    $this->actingAs($administrador)
        ->get(route('management.inventory'))
        ->assertOk()
        ->assertSee($producto->nombre);

    $this->actingAs($administrador)
        ->post(route('management.inventory.adjust', $producto), [
            'almacen_id' => $alcance['almacen_id'],
            'tipo_movimiento' => 'entrada',
            'cantidad' => 5,
            'notas' => 'Ingreso inicial desde el módulo de inventario.',
        ])
        ->assertRedirect();

    $this->actingAs($administrador)
        ->post(route('management.customers.store'), [
            'tipo' => 'minorista',
            'documento' => 'DNI-FLUJO-10001',
            'nombre_contacto' => 'Cliente del flujo operativo',
            'correo' => 'cliente.flujo@example.test',
        ])
        ->assertRedirect();

    $cliente = Cliente::query()->where('documento', 'DNI-FLUJO-10001')->firstOrFail();
    $this->actingAs($administrador)
        ->get(route('management.sales'))
        ->assertOk()
        ->assertSee($producto->nombre);
    $tokenVenta = session()->get('venta_token');
    expect($tokenVenta)->toMatch('/\A[a-f0-9]{64}\z/');

    $respuestaVenta = $this->actingAs($administrador)
        ->withSession(['venta_token' => $tokenVenta])
        ->post(route('management.sales.store'), [
            'cliente_id' => $cliente->id,
            'tipo_venta' => 'fisica',
            'fecha_venta' => now()->toDateString(),
            'observaciones' => 'Venta del flujo operativo.',
            'venta_token' => $tokenVenta,
            'items' => [['producto_id' => $producto->id, 'cantidad' => 2]],
        ]);

    $venta = DB::table('ventas')->where('cliente_id', $cliente->id)->first();
    expect($venta)->not->toBeNull();
    $respuestaVenta->assertRedirect(route('management.sales'))
        ->assertSessionHas('status', 'Venta '.$venta->codigo.' confirmada. No se registró ningún pago.');
    $this->assertDatabaseHas('ventas', [
        'id' => $venta->id,
        'estado' => 'confirmada',
        'canal' => 'presencial',
        'total' => 200,
    ]);
    expect((float) DB::table('existencias_almacen')->where('producto_id', $producto->id)->sum('cantidad_fisica'))->toBe(3.0)
        ->and(DB::table('movimientos_inventario')->where('producto_id', $producto->id)->count())->toBe(2)
        ->and(DB::table('movimientos_inventario')->where('producto_id', $producto->id)->where('documento_tipo', 'venta')->count())->toBe(1)
        ->and(DB::table('detalles_ventas')->where('venta_id', $venta->id)->count())->toBe(1);

    $this->actingAs($administrador)
        ->get(route('management.sales'))
        ->assertOk()
        ->assertSee($producto->nombre)
        ->assertSee($cliente->nombre_contacto)
        ->assertSee($venta->codigo);
    $this->actingAs($administrador)
        ->get(route('management.dashboard'))
        ->assertOk()
        ->assertViewHas('salesThisMonth', 200.0);

    $this->actingAs($administrador)
        ->post(route('management.sales.cancel', ['venta' => $venta->id]), ['motivo' => 'La operación fue capturada por error.'])
        ->assertRedirect(route('management.sales'))
        ->assertSessionHas('status', 'La venta está anulada.');
    $this->actingAs($administrador)
        ->post(route('management.sales.cancel', ['venta' => $venta->id]), ['motivo' => 'Reintento de una solicitud ya completada.'])
        ->assertRedirect(route('management.sales'));

    $this->assertDatabaseHas('ventas', ['id' => $venta->id, 'estado' => 'anulada']);
    $this->assertDatabaseHas('movimientos_inventario', [
        'producto_id' => $producto->id,
        'tipo_movimiento' => 'entrada',
        'documento_tipo' => 'anulacion_venta',
        'documento_id' => (string) $venta->id,
        'cantidad' => 2,
    ]);
    expect((float) DB::table('existencias_almacen')->where('producto_id', $producto->id)->sum('cantidad_fisica'))->toBe(5.0)
        ->and(DB::table('movimientos_inventario')->where('producto_id', $producto->id)->count())->toBe(3)
        ->and(DB::table('movimientos_inventario')->where('producto_id', $producto->id)->where('documento_tipo', 'anulacion_venta')->count())->toBe(1);
});

it('sincroniza la portada del producto con la galería SQL al crear y actualizar', function (): void {
    Storage::fake('public');
    $administrador = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();
    $categoria = Categoria::factory()->create(['empresa_id' => $alcance['empresa_id']]);

    $this->actingAs($administrador)
        ->post(route('management.products.store'), [
            'marca' => 'Marca de prueba',
            'nombre' => 'Producto con portada',
            'categoria_id' => $categoria->id,
            'precio' => 20,
            'existencias' => 0,
            'imagen' => crearImagenPngParaPruebaCatalogo('portada.png'),
        ])
        ->assertRedirect();

    $producto = Producto::query()->where('nombre', 'Producto con portada')->firstOrFail();
    $rutaPortadaAnterior = (string) $producto->url_imagen;
    $this->assertDatabaseHas('referencias_imagenes_productos', [
        'producto_id' => $producto->id,
        'ruta_imagen' => $rutaPortadaAnterior,
        'orden' => 0,
    ]);
    Storage::disk('public')->assertExists(substr($rutaPortadaAnterior, strlen('storage/')));

    $this->actingAs($administrador)
        ->put(route('management.products.update', $producto), [
            'marca' => 'Marca de prueba',
            'nombre' => 'Producto con portada',
            'categoria_id' => $categoria->id,
            'precio' => 20,
            'existencias' => 0,
            'url_imagen' => $rutaPortadaAnterior,
            'imagen' => crearImagenPngParaPruebaCatalogo('portada-actualizada.png'),
        ])
        ->assertRedirect();

    $rutaPortadaActual = (string) $producto->fresh()->url_imagen;
    $this->assertDatabaseHas('referencias_imagenes_productos', [
        'producto_id' => $producto->id,
        'ruta_imagen' => $rutaPortadaActual,
        'orden' => 0,
    ]);
    $this->actingAs($administrador)
        ->get(route('catalog.show', $producto))
        ->assertOk()
        ->assertSee(asset($rutaPortadaActual), false);
    $this->assertDatabaseHas('referencias_imagenes_productos', [
        'producto_id' => $producto->id,
        'ruta_imagen' => $rutaPortadaAnterior,
        'orden' => 1,
    ]);

    $this->actingAs($administrador)
        ->put(route('management.products.update', $producto), [
            'marca' => 'Marca de prueba',
            'nombre' => 'Producto con portada',
            'categoria_id' => $categoria->id,
            'precio' => 20,
            'existencias' => 0,
            'url_imagen' => $rutaPortadaActual,
        ])
        ->assertRedirect();

    expect(DB::table('referencias_imagenes_productos')->where('producto_id', $producto->id)->count())->toBe(2);
    $this->assertDatabaseHas('referencias_imagenes_productos', [
        'producto_id' => $producto->id,
        'ruta_imagen' => $rutaPortadaAnterior,
        'orden' => 1,
    ]);
});

it('carga y elimina imágenes de variante, conserva una principal y aplica RBAC', function (): void {
    Storage::fake('public');
    $administrador = SpanishAccessFixture::user('administrador');
    $producto = productoParaIntegracionUnidadDos(['existencias' => 0]);

    $this->actingAs($administrador)
        ->post(route('management.products.variants.store', $producto), ['nombre_color' => 'Azul'])
        ->assertRedirect();

    $variante = VarianteProducto::query()->where('producto_id', $producto->id)->firstOrFail();
    $this->actingAs($administrador)
        ->post(route('management.products.variants.images.store', ['product' => $producto, 'variante' => $variante]), [
            'imagen' => crearImagenPngParaPruebaCatalogo('azul-principal.png'),
            'imagen_principal' => 1,
            'producto_modal_id' => $producto->id,
            'variante_modal_id' => $variante->id,
        ])
        ->assertRedirect();

    $imagenPrincipal = $variante->images()->firstOrFail();
    $rutaArchivoPrincipal = substr($imagenPrincipal->ruta_imagen, strlen('storage/'));
    $this->actingAs($administrador)
        ->post(route('management.products.variants.images.store', ['product' => $producto, 'variante' => $variante]), [
            'imagen' => crearImagenPngParaPruebaCatalogo('azul-secundaria.png'),
            'producto_modal_id' => $producto->id,
            'variante_modal_id' => $variante->id,
        ])
        ->assertRedirect();

    $imagenSecundaria = $variante->images()->orderBy('orden')->orderBy('id')->get()->last();
    expect($imagenPrincipal->fresh()->imagen_principal)->toBeTrue()
        ->and($imagenSecundaria->imagen_principal)->toBeFalse()
        ->and((int) $imagenSecundaria->orden)->toBe(1);
    $this->actingAs($administrador)
        ->get(route('management.products'))
        ->assertOk()
        ->assertSee(asset($imagenSecundaria->ruta_imagen), false);

    $this->actingAs($administrador)
        ->delete(route('management.products.variants.images.delete', [
            'product' => $producto,
            'variante' => $variante,
            'imagen' => $imagenPrincipal->id,
        ]))
        ->assertRedirect();

    expect($imagenSecundaria->fresh()->imagen_principal)->toBeTrue();
    Storage::disk('public')->assertMissing($rutaArchivoPrincipal);
    $this->assertDatabaseMissing('imagenes_variantes', ['id' => $imagenPrincipal->id]);
    $this->assertDatabaseHas('registros_auditoria', [
        'accion' => 'variante.imagen.registrada',
        'entidad' => 'imagenes_variantes',
        'entidad_id' => $imagenSecundaria->id,
    ]);
    $this->assertDatabaseHas('registros_auditoria', [
        'accion' => 'variante.imagen.eliminada',
        'entidad' => 'imagenes_variantes',
        'entidad_id' => $imagenPrincipal->id,
    ]);

    $this->actingAs($administrador)
        ->post(route('management.products.variants.images.store', ['product' => $producto, 'variante' => $variante]), [
            'producto_modal_id' => $producto->id,
            'variante_modal_id' => $variante->id,
        ])
        ->assertSessionHasErrors('imagen');

    $marketing = SpanishAccessFixture::user('marketing');
    $this->actingAs($marketing)
        ->post(route('management.products.variants.images.store', ['product' => $producto, 'variante' => $variante]), [
            'imagen' => crearImagenPngParaPruebaCatalogo('no-autorizada.png'),
        ])
        ->assertForbidden();
});

it('rechaza imágenes de variante si la variante no pertenece al producto de la ruta', function (): void {
    Storage::fake('public');
    $administrador = SpanishAccessFixture::user('administrador');
    $productoVisible = productoParaIntegracionUnidadDos(['existencias' => 0]);
    $productoDeOtraVariante = productoParaIntegracionUnidadDos(['existencias' => 0]);
    $varianteAjena = VarianteProducto::query()->create([
        'producto_id' => $productoDeOtraVariante->id,
        'nombre_color' => 'Rojo',
        'estado' => 'activo',
        'fecha_creacion' => now(),
    ]);

    $this->actingAs($administrador)
        ->post(route('management.products.variants.images.store', [
            'product' => $productoVisible,
            'variante' => $varianteAjena,
        ]), ['imagen' => crearImagenPngParaPruebaCatalogo('variante-ajena.png')])
        ->assertNotFound();

    expect(Storage::disk('public')->allFiles('variantes'))->toBe([])
        ->and(DB::table('imagenes_variantes')->count())->toBe(0);
});

it('registra, actualiza y desactiva variantes sin escribir proyecciones de stock', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();
    $producto = Producto::factory()->create([
        'nombre' => 'Equipo para variantes',
        'existencias' => 11,
        'empresa_id' => $alcance['empresa_id'],
    ]);
    DB::table('existencias_almacen')->insert([
        'almacen_id' => $alcance['almacen_id'],
        'producto_id' => $producto->id,
        'variante_id' => null,
        'empresa_id' => $alcance['empresa_id'],
        'sede_id' => $alcance['sede_id'],
        'cantidad_fisica' => 0,
        'cantidad_reservada' => 0,
    ]);

    $this->actingAs($administrador)
        ->post(route('management.products.variants.store', $producto), [
            'nombre_color' => 'Negro',
            'codigo_sku' => 'SKU-NEGRO',
            'codigo_barras' => 'BARRAS-NEGRO',
            'codigo_color' => '#000000',
        ])
        ->assertRedirect();

    $variante = VarianteProducto::query()->where('producto_id', $producto->id)->firstOrFail();
    expect($variante->estado)->toBe('activo')
        ->and((bool) $variante->controla_serie)->toBeFalse()
        ->and((int) $variante->existencias)->toBe(0)
        ->and((int) $producto->fresh()->existencias)->toBe(11);

    $this->actingAs($administrador)
        ->put(route('management.products.variants.update', ['product' => $producto, 'variante' => $variante]), [
            'nombre_color' => 'Negro',
            'codigo_sku' => 'SKU-NEGRO',
            'codigo_barras' => 'BARRAS-NEGRO',
            'codigo_color' => '#000000',
            'controla_serie' => '1',
            'estado' => 'activo',
        ])
        ->assertRedirect();

    expect((bool) $variante->fresh()->controla_serie)->toBeTrue();

    DB::table('existencias_almacen')->insert([
        'almacen_id' => $alcance['almacen_id'],
        'producto_id' => $producto->id,
        'variante_id' => $variante->id,
        'empresa_id' => $alcance['empresa_id'],
        'sede_id' => $alcance['sede_id'],
        'cantidad_fisica' => 5,
        'cantidad_reservada' => 2,
    ]);

    $this->actingAs($administrador)
        ->put(route('management.products.variants.update', ['product' => $producto, 'variante' => $variante]), [
            'nombre_color' => 'Negro mate',
            'codigo_sku' => 'SKU-NEGRO-MATE',
            'codigo_barras' => 'BARRAS-NEGRO-MATE',
            'codigo_color' => '#111111',
            'controla_serie' => '1',
            'estado' => 'activo',
        ])
        ->assertRedirect();

    $this->actingAs($administrador)
        ->put(route('management.products.variants.update', ['product' => $producto, 'variante' => $variante]), [
            'nombre_color' => 'Negro mate',
            'codigo_sku' => 'SKU-NEGRO-MATE',
            'codigo_barras' => 'BARRAS-NEGRO-MATE',
            'codigo_color' => '#111111',
            'controla_serie' => '0',
            'estado' => 'activo',
        ])
        ->assertStatus(409);

    expect((bool) $variante->fresh()->controla_serie)->toBeTrue();

    $this->actingAs($administrador)
        ->get(route('management.products'))
        ->assertOk()
        ->assertSee('SKU-NEGRO-MATE')
        ->assertSee('Negro mate')
        ->assertSee('Control de serie / IMEI')
        ->assertSee('2 reservadas');

    $this->actingAs($administrador)
        ->postJson(route('management.products.variants.store', $producto), [
            'nombre_color' => 'Variante duplicada',
            'codigo_sku' => 'SKU-NEGRO-MATE',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['codigo_sku']);

    $this->actingAs($administrador)
        ->delete(route('management.products.variants.deactivate', ['product' => $producto, 'variante' => $variante]))
        ->assertRedirect();

    $this->assertDatabaseHas('variantes_productos', [
        'id' => $variante->id,
        'estado' => 'inactivo',
        'existencias' => 0,
    ]);
    expect((float) DB::table('existencias_almacen')->where('variante_id', $variante->id)->sum('cantidad_fisica'))->toBe(5.0);
    $this->assertDatabaseHas('registros_auditoria', [
        'accion' => 'variant.created',
        'entidad' => 'variantes_productos',
        'entidad_id' => $variante->id,
    ]);
    $this->assertDatabaseHas('registros_auditoria', [
        'accion' => 'variant.updated',
        'entidad' => 'variantes_productos',
        'entidad_id' => $variante->id,
    ]);
    $this->assertDatabaseHas('registros_auditoria', [
        'accion' => 'variant.deactivated',
        'entidad' => 'variantes_productos',
        'entidad_id' => $variante->id,
    ]);
});

it('no permite asignar variantes a un producto con saldo base sin conciliar', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $producto = productoParaIntegracionUnidadDos(['existencias' => 4]);

    $this->actingAs($administrador)
        ->post(route('management.products.variants.store', $producto), [
            'nombre_color' => 'Azul',
            'codigo_sku' => 'SKU-AZUL-SALDO',
        ])
        ->assertStatus(409);

    expect(VarianteProducto::query()->where('producto_id', $producto->id)->count())->toBe(0);
});

it('impide cambiar el control de serie cuando existe historial o un dispositivo vinculado', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();
    $producto = productoParaIntegracionUnidadDos(['existencias' => 0]);
    $varianteConMovimiento = VarianteProducto::query()->create([
        'producto_id' => $producto->id,
        'nombre_color' => 'Con movimiento',
        'controla_serie' => false,
        'estado' => 'activo',
        'fecha_creacion' => now(),
    ]);
    $varianteConDispositivo = VarianteProducto::query()->create([
        'producto_id' => $producto->id,
        'nombre_color' => 'Con dispositivo',
        'controla_serie' => false,
        'estado' => 'activo',
        'fecha_creacion' => now(),
    ]);

    DB::table('movimientos_inventario')->insert([
        'producto_id' => $producto->id,
        'usuario_id' => $administrador->id,
        'tipo_movimiento' => 'ajuste',
        'cantidad' => 0,
        'notas' => 'Historial existente de la variante',
        'creado_en' => now(),
        'empresa_id' => $alcance['empresa_id'],
        'sede_id' => $alcance['sede_id'],
        'almacen_id' => $alcance['almacen_id'],
        'variante_id' => $varianteConMovimiento->id,
        'documento_tipo' => 'prueba',
        'documento_id' => 'HISTORIAL-VARIANTE',
    ]);
    DB::table('dispositivos')->insert([
        'producto_id' => $producto->id,
        'variante_id' => $varianteConDispositivo->id,
        'imei' => '123456789012345',
        'estado' => 'disponible',
    ]);

    foreach ([$varianteConMovimiento, $varianteConDispositivo] as $variante) {
        $this->actingAs($administrador)
            ->put(route('management.products.variants.update', ['product' => $producto, 'variante' => $variante]), [
                'nombre_color' => $variante->nombre_color,
                'controla_serie' => '1',
                'estado' => 'activo',
            ])
            ->assertStatus(409);

        expect((bool) $variante->fresh()->controla_serie)->toBeFalse();
    }
});

it('rechaza la gestión de variantes de productos de otra empresa', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $empresaAjenaId = DB::table('empresas')->insertGetId([
        'codigo' => 'PRODUCTO-VARIANTE-AJENO',
        'razon_social' => 'Empresa ajena para variantes',
        'activo' => false,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    $productoAjeno = Producto::factory()->create(['empresa_id' => $empresaAjenaId]);

    $this->actingAs($administrador)
        ->post(route('management.products.variants.store', $productoAjeno), [
            'nombre_color' => 'Verde',
            'codigo_sku' => 'SKU-VERDE-AJENO',
        ])
        ->assertNotFound();

    expect(VarianteProducto::query()->where('producto_id', $productoAjeno->id)->count())->toBe(0);
});

it('rejects category and supplier references from another company when creating a product', function (): void {
    $administrator = SpanishAccessFixture::user('administrador');
    $empresaAjenaId = DB::table('empresas')->insertGetId([
        'codigo' => 'EMPRESA-AJENA',
        'razon_social' => 'Empresa ajena de prueba',
        'activo' => false,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    $categoriaAjena = Categoria::factory()->create(['empresa_id' => $empresaAjenaId]);
    $proveedorAjeno = Proveedor::create([
        'nombre' => 'Proveedor de otra empresa',
        'ruc' => 'RUC-90000999',
        'correo' => 'proveedor.ajeno@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $empresaAjenaId,
    ]);

    $this->actingAs($administrator)
        ->postJson(route('management.products.store'), [
            'marca' => 'Marca de prueba',
            'nombre' => 'Producto rechazado por alcance',
            'categoria_id' => $categoriaAjena->id,
            'proveedor_id' => $proveedorAjeno->id,
            'precio' => 100,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['categoria_id', 'proveedor_id']);

    $this->assertDatabaseMissing('productos', ['nombre' => 'Producto rechazado por alcance']);
});

it('creates an administrator-managed wholesale user and customer profile atomically', function (): void {
    $administrator = SpanishAccessFixture::user('administrador');

    $this->actingAs($administrator)
        ->post(route('management.users.store'), [
            'nombre' => 'Usuario Mayorista',
            'correo' => 'mayorista.integracion@example.test',
            'rol' => 'cliente_mayorista',
            'password' => 'IntegracionSegura123',
            'password_confirmation' => 'IntegracionSegura123',
            'tipo_documento' => 'ruc',
            'documento' => '20123456789',
            'razon_social' => 'Empresa Mayorista',
            'telefono' => '999555666',
            'ciudad' => 'Lima',
        ])
        ->assertRedirect();

    $user = User::query()->where('correo', 'mayorista.integracion@example.test')->firstOrFail();

    expect(Hash::check('IntegracionSegura123', $user->getAuthPassword()))->toBeTrue();
    $this->assertDatabaseHas('clientes', [
        'usuario_id' => $user->id,
        'tipo' => 'mayorista',
        'documento' => '20123456789',
    ]);
});

it('completes the supplier lifecycle for purchasing roles', function (): void {
    $purchasing = SpanishAccessFixture::user('compras_logistica');

    $this->actingAs($purchasing)
        ->post(route('management.suppliers.store'), [
            'nombre' => 'Proveedor Integración',
            'ruc' => 'RUC-30001',
            'correo' => 'proveedor.integracion@example.test',
            'telefono' => '999777888',
            'ciudad' => 'Lima',
        ])
        ->assertRedirect();

    $supplier = Proveedor::query()->where('ruc', 'RUC-30001')->firstOrFail();

    $this->actingAs($purchasing)
        ->put(route('management.suppliers.update', $supplier), [
            'nombre' => 'Proveedor Actualizado',
            'ruc' => 'RUC-30001',
            'correo' => 'proveedor.actualizado@example.test',
            'telefono' => '999777999',
            'ciudad' => 'Cusco',
        ])
        ->assertRedirect();

    $this->actingAs($purchasing)
        ->delete(route('management.suppliers.deactivate', $supplier))
        ->assertForbidden();

    $administrator = SpanishAccessFixture::user('administrador');
    $this->actingAs($administrator)
        ->delete(route('management.suppliers.deactivate', $supplier))
        ->assertRedirect();

    $this->assertDatabaseHas('proveedores', [
        'id' => $supplier->id,
        'nombre' => 'Proveedor Actualizado',
        'activo' => 0,
    ]);
});

it('consulta proveedores por empresa y calcula sus alertas con el saldo físico', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();
    $empresaAjenaId = DB::table('empresas')->insertGetId([
        'codigo' => 'PROVEEDOR-AJENO',
        'razon_social' => 'Empresa ajena para proveedores',
        'activo' => false,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);
    $proveedorVisible = Proveedor::create([
        'nombre' => 'Proveedor autorizado',
        'ruc' => 'RUC-PROPIO-401',
        'correo' => 'propio.proveedor@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $alcance['empresa_id'],
    ]);
    $proveedorAjeno = Proveedor::create([
        'nombre' => 'Proveedor ajeno',
        'ruc' => 'RUC-AJENO-402',
        'correo' => 'ajeno.proveedor@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $empresaAjenaId,
    ]);
    $producto = Producto::factory()->create([
        'nombre' => 'Equipo sin saldo físico',
        'existencias' => 99,
        'empresa_id' => $alcance['empresa_id'],
    ]);
    $producto->suppliers()->attach($proveedorVisible->id);

    $this->actingAs($administrador)
        ->get(route('management.suppliers'))
        ->assertOk()
        ->assertSee($proveedorVisible->nombre)
        ->assertDontSee($proveedorAjeno->nombre)
        ->assertSee('Sin existencias: '.$producto->nombre)
        ->assertSee('0 unidades')
        ->assertDontSee('99 unidades');

    $respuestaExportacion = $this->actingAs($administrador)->get(route('management.suppliers.export'));
    $respuestaExportacion->assertOk()->assertDownload('proveedores.csv');
    expect($respuestaExportacion->streamedContent())
        ->toContain($proveedorVisible->nombre)
        ->not->toContain($proveedorAjeno->nombre);

    $this->actingAs($administrador)
        ->delete(route('management.suppliers.deactivate', $proveedorAjeno))
        ->assertNotFound();
});

it('adjusts inventory and rejects a movement that would create negative stock', function (): void {
    $administrator = SpanishAccessFixture::user('administrador');
    $product = productoParaIntegracionUnidadDos(['existencias' => 2]);

    $this->actingAs($administrator)
        ->post(route('management.inventory.adjust', $product), [
            'tipo_movimiento' => 'entrada',
            'cantidad' => 3,
            'notas' => 'Entrada de integración.',
        ])
        ->assertRedirect();

    expect((int) $product->fresh()->existencias)->toBe(5);
    $this->assertDatabaseHas('movimientos_inventario', [
        'producto_id' => $product->id,
        'tipo_movimiento' => 'entrada',
        'cantidad' => 3,
    ]);

    $this->actingAs($administrator)
        ->post(route('management.inventory.adjust', $product), [
            'tipo_movimiento' => 'salida',
            'cantidad' => 6,
            'notas' => 'Salida superior al stock.',
        ])
        ->assertUnprocessable();

    expect((int) $product->fresh()->existencias)->toBe(5);
});

it('receives a purchase, creates details and increments inventory', function (): void {
    $purchasing = SpanishAccessFixture::user('compras_logistica');
    $administrator = SpanishAccessFixture::user('administrador');
    $scope = SpanishAccessFixture::organization();
    $purchaseToken = bin2hex(random_bytes(32));
    $supplier = Proveedor::create([
        'nombre' => 'Proveedor Compra',
        'ruc' => 'RUC-40001',
        'correo' => 'compra@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
    ]);
    $product = productoParaIntegracionUnidadDos(['existencias' => 1, 'precio' => 30]);

    $this->actingAs($purchasing)->withSession(['compra_token' => $purchaseToken])
        ->post(route('management.purchases.store'), [
            'proveedor_id' => $supplier->id,
            'fecha_compra' => now()->toDateString(),
            'compra_token' => $purchaseToken,
            'items' => [[
                'producto_id' => $product->id,
                'cantidad' => 2,
                'costo_unitario' => '20.00',
            ]],
        ])
        ->assertRedirect();

    $purchase = Compra::query()->where('proveedor_id', $supplier->id)->firstOrFail();

    expect($purchase->estado)->toBe('Pendiente')
        ->and((int) $product->fresh()->existencias)->toBe(1);
    $this->actingAs($purchasing)->post(route('management.purchases.approve', $purchase))->assertRedirect();
    $this->actingAs($administrator)->post(route('management.purchases.receive', $purchase))->assertRedirect();
    $this->actingAs($administrator)->post(route('management.purchases.receive', $purchase))->assertRedirect();

    expect((float) $purchase->total)->toBe(40.0)
        ->and((int) $product->fresh()->existencias)->toBe(3);
    $this->assertDatabaseHas('detalles_compras', [
        'compra_id' => $purchase->id,
        'producto_id' => $product->id,
        'cantidad' => 2,
    ]);
    $this->assertDatabaseHas('movimientos_inventario', [
        'producto_id' => $product->id,
        'tipo_movimiento' => 'entrada',
        'cantidad' => 2,
    ]);
});

it('registra una recepcion parcial con su discrepancia y luego recibe solo el saldo pendiente', function (): void {
    $compras = SpanishAccessFixture::user('compras_logistica');
    $administrador = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();
    $tokenCompra = bin2hex(random_bytes(32));
    $proveedor = Proveedor::create([
        'nombre' => 'Proveedor de recepción parcial',
        'ruc' => 'RUC-40003',
        'correo' => 'recepcion@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $alcance['empresa_id'],
    ]);
    $producto = productoParaIntegracionUnidadDos(['existencias' => 3]);

    $this->actingAs($compras)->withSession(['compra_token' => $tokenCompra])
        ->post(route('management.purchases.store'), [
            'proveedor_id' => $proveedor->id,
            'fecha_compra' => now()->toDateString(),
            'compra_token' => $tokenCompra,
            'items' => [['producto_id' => $producto->id, 'cantidad' => 2, 'costo_unitario' => '20.00']],
        ])
        ->assertRedirect();

    $compra = Compra::query()->where('proveedor_id', $proveedor->id)->firstOrFail();
    $detalle = $compra->details()->firstOrFail();
    $this->actingAs($compras)->post(route('management.purchases.approve', $compra))->assertRedirect();
    $this->actingAs($administrador)->get(route('management.purchases'))
        ->assertOk()
        ->assertSee('Registrar recepción parcial')
        ->assertDontSee('Confirmar recepción (demostración)');

    $tokenRecepcion = $this->app['session.store']->get('compra_recepcion_tokens.'.$compra->id);
    expect($tokenRecepcion)->toMatch('/\A[a-f0-9]{64}\z/');

    $this->actingAs($administrador)->post(route('management.purchases.receive_partial', $compra), [
        'token_recepcion' => $tokenRecepcion,
        'items' => [['detalle_compra_id' => $detalle->id, 'cantidad' => 1]],
    ])->assertUnprocessable();
    expect((int) DB::table('existencias_almacen')->where('producto_id', $producto->id)->sum('cantidad_fisica'))->toBe(3)
        ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->where('documento_id', (string) $compra->id)->count())->toBe(0);

    $this->actingAs($administrador)->post(route('management.purchases.receive_partial', $compra), [
        'token_recepcion' => $tokenRecepcion,
        'items' => [['detalle_compra_id' => $detalle->id, 'cantidad' => 1]],
        'observaciones' => 'El proveedor entregó una unidad; queda una pendiente.',
    ])->assertRedirect()->assertSessionHas('status', 'La recepción parcial quedó registrada y la discrepancia se agregó a la auditoría.');

    expect($compra->fresh()->estado)->toBe('Aprobada')
        ->and((int) DB::table('existencias_almacen')->where('producto_id', $producto->id)->sum('cantidad_fisica'))->toBe(4)
        ->and((int) DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->where('documento_id', (string) $compra->id)->sum('cantidad'))->toBe(1);
    $this->assertDatabaseHas('registros_auditoria', [
        'accion' => 'compra.recepcion_parcial',
        'entidad' => 'compras',
        'entidad_id' => $compra->id,
        'observaciones' => 'El proveedor entregó una unidad; queda una pendiente.',
    ]);

    $this->actingAs($administrador)->post(route('management.purchases.receive', $compra))->assertRedirect();
    expect($compra->fresh()->estado)->toBe('Recibida')
        ->and((int) DB::table('existencias_almacen')->where('producto_id', $producto->id)->sum('cantidad_fisica'))->toBe(5)
        ->and((int) DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->where('documento_id', (string) $compra->id)->sum('cantidad'))->toBe(2)
        ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->where('documento_id', (string) $compra->id)->count())->toBe(2);
});

it('recibe compras de productos con variante en el saldo exacto del almacen', function (): void {
    $compras = SpanishAccessFixture::user('compras_logistica');
    $administrador = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();
    $tokenCompra = bin2hex(random_bytes(32));
    $proveedor = Proveedor::create([
        'nombre' => 'Proveedor de variantes',
        'ruc' => 'RUC-40004',
        'correo' => 'variantes@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $alcance['empresa_id'],
    ]);
    $producto = productoParaIntegracionUnidadDos(['existencias' => 0]);
    $variante = VarianteProducto::query()->create([
        'producto_id' => $producto->id,
        'nombre_color' => 'Azul oscuro',
        'codigo_sku' => 'SKU-COMPRA-AZUL',
        'estado' => 'activo',
    ]);

    $this->actingAs($compras)->withSession(['compra_token' => $tokenCompra])
        ->post(route('management.purchases.store'), [
            'proveedor_id' => $proveedor->id,
            'fecha_compra' => now()->toDateString(),
            'compra_token' => $tokenCompra,
            'items' => [[
                'producto_id' => $producto->id,
                'variante_id' => $variante->id,
                'cantidad' => 3,
                'costo_unitario' => '20.00',
            ]],
        ])
        ->assertRedirect();

    $compra = Compra::query()->where('proveedor_id', $proveedor->id)->firstOrFail();
    $this->actingAs($compras)->post(route('management.purchases.approve', $compra))->assertRedirect();
    $this->actingAs($administrador)->post(route('management.purchases.receive', $compra))->assertRedirect();

    $this->assertDatabaseHas('detalles_compras', [
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'variante_id' => $variante->id,
        'cantidad' => 3,
    ]);
    $this->assertDatabaseHas('movimientos_inventario', [
        'producto_id' => $producto->id,
        'variante_id' => $variante->id,
        'almacen_id' => $alcance['almacen_id'],
        'documento_tipo' => 'compra',
        'documento_id' => (string) $compra->id,
        'cantidad' => 3,
    ]);
    expect((float) DB::table('existencias_almacen')->where('producto_id', $producto->id)->where('variante_id', $variante->id)->sum('cantidad_fisica'))->toBe(3.0)
        ->and((float) DB::table('existencias_almacen')->where('producto_id', $producto->id)->whereNull('variante_id')->sum('cantidad_fisica'))->toBe(0.0)
        ->and((int) $producto->fresh()->existencias)->toBe(3);
});

it('registra equipos serializados desde la recepcion de compra sin duplicar inventario', function (): void {
    prepararEsquemaDispositivosRecepcionCompra();
    $compras = SpanishAccessFixture::user('compras_logistica');
    $administrador = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();
    $proveedor = Proveedor::create([
        'nombre' => 'Proveedor de equipos serializados',
        'ruc' => 'RUC-IMEI-001',
        'correo' => 'imei@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $alcance['empresa_id'],
    ]);
    $producto = productoParaIntegracionUnidadDos(['existencias' => 0]);
    $variante = VarianteProducto::query()->create([
        'producto_id' => $producto->id,
        'nombre_color' => 'Equipo serializado',
        'codigo_sku' => 'SKU-IMEI-001',
        'controla_serie' => true,
        'estado' => 'activo',
    ]);
    $tokenCompra = str_repeat('b', 64);
    $compra = app(ServicioCompras::class)->registrar([
        'proveedor_id' => $proveedor->id,
        'fecha_compra' => now()->toDateString(),
        'items' => [[
            'producto_id' => $producto->id,
            'variante_id' => $variante->id,
            'cantidad' => 2,
            'costo_unitario' => '10.00',
        ]],
    ], $compras, $tokenCompra, null);
    $servicioCompras = app(ServicioCompras::class);
    $servicioCompras->aprobar($compra, $compras, null);
    $detalle = $compra->details()->firstOrFail();
    $tokenRecepcion = str_repeat('c', 64);
    $items = [[
        'detalle_compra_id' => $detalle->id,
        'cantidad' => 2,
        'identificadores' => "IMEI:490154203237518\nSERIE:SERIE-IMEI-001",
    ]];

    $respuesta = $this->actingAs($administrador)->get(route('management.purchases'));
    $respuesta->assertOk()->assertSee('IMEI:15dígitos');
    $this->app['session.store']->put('compra_recepcion_tokens.'.$compra->id, $tokenRecepcion);
    $this->actingAs($administrador)->post(route('management.purchases.receive_partial', $compra), [
        'token_recepcion' => $tokenRecepcion,
        'items' => $items,
    ])->assertRedirect()->assertSessionHas('status', 'La recepción completó la compra y actualizó las existencias.');

    $movimiento = DB::table('movimientos_inventario')
        ->where('documento_tipo', 'compra')
        ->where('documento_id', (string) $compra->id)
        ->sole();
    $auditoriaRecepcion = DB::table('registros_auditoria')
        ->where('accion', 'compra.recepcion_parcial')
        ->where('entidad_id', $compra->id)
        ->sole();
    expect($compra->fresh()->estado)->toBe('Recibida')
        ->and((float) DB::table('existencias_almacen')->where('producto_id', $producto->id)->where('variante_id', $variante->id)->value('cantidad_fisica'))->toBe(2.0)
        ->and(DB::table('dispositivos')->where('detalle_compra_id', $detalle->id)->count())->toBe(2)
        ->and(DB::table('detalles_compras_dispositivos')->where('detalle_compra_id', $detalle->id)->count())->toBe(2)
        ->and(DB::table('movimientos_dispositivos')->where('movimiento_inventario_id', $movimiento->id)->count())->toBe(2)
        ->and($auditoriaRecepcion->valores_nuevos)->not->toContain('490154203237518')->not->toContain('SERIE-IMEI-001');

    $servicioCompras->registrarRecepcionParcial($compra, $items, null, $tokenRecepcion, $administrador, null);
    expect(DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->where('documento_id', (string) $compra->id)->count())->toBe(1)
        ->and(DB::table('dispositivos')->where('detalle_compra_id', $detalle->id)->count())->toBe(2)
        ->and((float) DB::table('existencias_almacen')->where('producto_id', $producto->id)->where('variante_id', $variante->id)->value('cantidad_fisica'))->toBe(2.0);

    $this->assertDatabaseHas('dispositivos', [
        'imei' => '490154203237518',
        'numero_serie' => null,
        'almacen_actual_id' => $alcance['almacen_id'],
        'estado' => 'disponible',
    ]);
    $this->assertDatabaseHas('dispositivos', [
        'imei' => null,
        'numero_serie' => 'SERIE-IMEI-001',
        'almacen_actual_id' => $alcance['almacen_id'],
        'estado' => 'disponible',
    ]);
});

it('no altera existencias cuando la recepcion serializada carece de identificadores', function (): void {
    prepararEsquemaDispositivosRecepcionCompra();
    $compras = SpanishAccessFixture::user('compras_logistica');
    $alcance = SpanishAccessFixture::organization();
    $proveedor = Proveedor::create([
        'nombre' => 'Proveedor sin identificadores',
        'ruc' => 'RUC-IMEI-002',
        'correo' => 'sin-imei@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $alcance['empresa_id'],
    ]);
    $producto = productoParaIntegracionUnidadDos(['existencias' => 0]);
    $variante = VarianteProducto::query()->create([
        'producto_id' => $producto->id,
        'nombre_color' => 'Equipo pendiente de serie',
        'codigo_sku' => 'SKU-IMEI-002',
        'controla_serie' => true,
        'estado' => 'activo',
    ]);
    $compra = app(ServicioCompras::class)->registrar([
        'proveedor_id' => $proveedor->id,
        'fecha_compra' => now()->toDateString(),
        'items' => [[
            'producto_id' => $producto->id,
            'variante_id' => $variante->id,
            'cantidad' => 1,
            'costo_unitario' => '10.00',
        ]],
    ], $compras, str_repeat('d', 64), null);
    app(ServicioCompras::class)->aprobar($compra, $compras, null);
    $detalle = $compra->details()->firstOrFail();
    $administrador = SpanishAccessFixture::user('administrador');

    expect(fn () => app(ServicioCompras::class)->registrarRecepcionParcial(
        $compra,
        [['detalle_compra_id' => $detalle->id, 'cantidad' => 1]],
        null,
        str_repeat('e', 64),
        $administrador,
        null,
    ))->toThrow(HttpException::class);
    expect(fn () => app(ServicioCompras::class)->registrarRecepcionParcial(
        $compra,
        [['detalle_compra_id' => $detalle->id, 'cantidad' => 1, 'identificadores' => 'IMEI:490154203237519']],
        null,
        str_repeat('f', 64),
        $administrador,
        null,
    ))->toThrow(HttpException::class);

    expect((int) DB::table('existencias_almacen')->where('producto_id', $producto->id)->where('variante_id', $variante->id)->value('cantidad_fisica'))->toBe(0)
        ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'compra')->where('documento_id', (string) $compra->id)->count())->toBe(0)
        ->and(DB::table('dispositivos')->where('detalle_compra_id', $detalle->id)->count())->toBe(0);
});

it('rolls back a purchase when its audit record cannot be stored', function (): void {
    $purchasing = SpanishAccessFixture::user('compras_logistica');
    $scope = SpanishAccessFixture::organization();
    $supplier = Proveedor::create([
        'nombre' => 'Proveedor Rollback',
        'ruc' => 'RUC-40002',
        'correo' => 'rollback@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
    ]);
    $product = productoParaIntegracionUnidadDos(['existencias' => 4]);

    DB::statement("CREATE TRIGGER rechazo_auditoria_compra BEFORE INSERT ON registros_auditoria WHEN NEW.accion = 'compra.registrada' BEGIN SELECT RAISE(ABORT, 'fallo de auditoría de prueba'); END");

    expect(fn (): Compra => app(ServicioCompras::class)->registrar([
        'proveedor_id' => $supplier->id,
        'fecha_compra' => now()->toDateString(),
        'items' => [
            ['producto_id' => $product->id, 'cantidad' => 1, 'costo_unitario' => '10.00'],
        ],
    ], $purchasing, bin2hex(random_bytes(32)), null))->toThrow(QueryException::class);

    expect(Compra::query()->count())->toBe(0)
        ->and((int) $product->fresh()->existencias)->toBe(4);
    $this->assertDatabaseHas('existencias_almacen', [
        'producto_id' => $product->id,
        'cantidad_fisica' => 4,
    ]);
});

it('cancels an order and restores its inventory', function (): void {
    $administrator = SpanishAccessFixture::user('administrador');
    $customer = SpanishAccessFixture::user('cliente_minorista');
    $product = productoParaIntegracionUnidadDos(['existencias' => 1]);
    $checkoutToken = bin2hex(random_bytes(32));

    $this->actingAs($customer)->withSession(['cart' => [$product->id => 1], 'checkout_token' => $checkoutToken])
        ->post(route('checkout'), ['checkout_token' => $checkoutToken])->assertRedirect();
    $order = Pedido::query()->sole();
    expect((int) $product->fresh()->existencias)->toBe(0);

    $this->actingAs($administrator)
        ->patch(route('orders.status', $order), ['estado' => 'Cancelado'])
        ->assertRedirect();

    expect($order->fresh()->estado)->toBe('Cancelado')
        ->and((int) $product->fresh()->existencias)->toBe(1);
    $this->assertDatabaseHas('movimientos_inventario', [
        'producto_id' => $product->id,
        'tipo_movimiento' => 'entrada',
        'cantidad' => 1,
    ]);
});

it('creates a wholesale quotation with details and ownership', function (): void {
    $wholesaleCustomer = SpanishAccessFixture::user('cliente_mayorista');
    $organization = SpanishAccessFixture::organization();
    $customer = Cliente::create([
        'usuario_id' => $wholesaleCustomer->id,
        'tipo' => 'mayorista',
        'documento' => 'RUC-50001',
        'razon_social' => 'Cliente Cotización',
        'nombre_contacto' => 'Contacto Mayorista',
        'correo' => $wholesaleCustomer->correo,
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $organization['empresa_id'],
    ]);
    $product = productoParaIntegracionUnidadDos(['precio' => 100]);

    $this->actingAs($wholesaleCustomer)
        ->post(route('quotations.store'), [
            'cliente_id' => $customer->id,
            'items' => [['producto_id' => $product->id, 'cantidad' => 2]],
            'notas' => 'Cotización de integración.',
        ])
        ->assertRedirect();

    $quotation = Cotizacion::query()->where('cliente_id', $customer->id)->firstOrFail();

    expect((float) $quotation->total)->toBe(200.0);
    $this->assertDatabaseHas('detalles_cotizaciones', [
        'cotizacion_id' => $quotation->id,
        'producto_id' => $product->id,
        'cantidad' => 2,
    ]);
});

it('creates campaigns, coupons and customer segments for marketing', function (): void {
    $marketing = SpanishAccessFixture::user('marketing');
    $dates = [
        'inicia_en' => now()->addDay()->toDateTimeString(),
        'finaliza_en' => now()->addDays(5)->toDateTimeString(),
    ];

    $this->actingAs($marketing)
        ->post(route('management.campaigns.store'), [
            'nombre' => 'Campaña Integración',
            'ubicacion' => 'emergente',
            'titulo' => 'Oferta de integración',
            'descripcion' => 'Campaña de prueba.',
            'texto_boton' => 'Ver oferta',
            'url_boton' => 'catalog.index',
            'url_imagen' => null,
            'precio_anterior' => 100,
            'precio_oferta' => 80,
            ...$dates,
        ])
        ->assertRedirect();

    $campaign = Campania::query()->where('nombre', 'Campaña Integración')->firstOrFail();

    $this->actingAs($marketing)
        ->put(route('management.campaigns.update', $campaign), [
            'nombre' => 'Campaña Integración actualizada',
            'ubicacion' => 'lateral',
            'titulo' => 'Oferta actualizada',
            'descripcion' => 'Campaña actualizada.',
            'texto_boton' => 'Comprar',
            'url_boton' => 'catalog.index',
            'url_imagen' => null,
            'precio_anterior' => 120,
            'precio_oferta' => 90,
            ...$dates,
        ])
        ->assertRedirect();

    $this->actingAs($marketing)
        ->delete(route('management.campaigns.deactivate', $campaign))
        ->assertRedirect();

    $this->actingAs($marketing)
        ->post(route('management.coupons.store'), [
            'codigo' => 'marketing10',
            'tipo_descuento' => 'porcentaje',
            'valor' => 10,
            'minimo_compra' => 50,
            'limite_usos' => 5,
            ...$dates,
        ])
        ->assertRedirect();

    $this->actingAs($marketing)
        ->post(route('management.segments.store'), [
            'nombre' => 'Clientes Integración',
            'roles' => ['cliente_minorista', 'cliente_mayorista'],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('campanas_publicitarias', [
        'id' => $campaign->id,
        'nombre' => 'Campaña Integración actualizada',
        'activo' => 0,
    ]);
    $this->assertDatabaseHas('cupones', ['codigo' => 'MARKETING10', 'usos' => 0]);
    $this->assertDatabaseHas('segmentos_clientes', ['nombre' => 'Clientes Integración']);
});

it('supports multiple suppliers for one product through the Eloquent pivot', function (): void {
    $product = productoParaIntegracionUnidadDos();
    $firstSupplier = Proveedor::create([
        'nombre' => 'Proveedor Pivot 1',
        'ruc' => 'RUC-60001',
        'correo' => 'pivot1@example.test',
        'activo' => true,
        'creado_en' => now(),
    ]);
    $secondSupplier = Proveedor::create([
        'nombre' => 'Proveedor Pivot 2',
        'ruc' => 'RUC-60002',
        'correo' => 'pivot2@example.test',
        'activo' => true,
        'creado_en' => now(),
    ]);

    $product->suppliers()->attach([$firstSupplier->id, $secondSupplier->id]);

    expect($product->fresh()->suppliers)->toHaveCount(2)
        ->and($firstSupplier->fresh()->products)->toHaveCount(1)
        ->and($secondSupplier->fresh()->products)->toHaveCount(1);
});

it('enforces the role matrix for protected module entry points', function (): void {
    $administrator = SpanishAccessFixture::user('administrador');
    $this->actingAs($administrator)->get(route('management.products'))->assertOk();

    $matrix = [
        'management.products' => ['administrador', 'compras_logistica', 'ventas_minoristas'],
        'management.customers' => ['administrador', 'ventas_mayoristas', 'ventas_minoristas'],
        'management.campaigns' => ['administrador', 'marketing'],
        'management.inventory' => ['administrador', 'compras_logistica', 'ventas_minoristas'],
        'management.purchases' => ['administrador', 'compras_logistica'],
        'management.suppliers' => ['administrador', 'compras_logistica'],
        'management.quotations' => ['administrador'],
        'management.segments' => ['administrador', 'marketing'],
        'management.reports' => ['administrador', 'marketing'],
        'quotations.index' => ['administrador', 'ventas_mayoristas', 'cliente_mayorista'],
    ];

    foreach ($matrix as $routeName => $allowedRoles) {
        foreach (User::availableRoleCodes() as $role) {
            $user = SpanishAccessFixture::user($role);
            $response = $this->actingAs($user)->get(route($routeName));

            expect($response->getStatusCode())->toBe(in_array($role, $allowedRoles, true) ? 200 : 403, $routeName.' / '.$role);

            if (in_array($role, $allowedRoles, true)) {
                $response->assertOk();
            } else {
                $response->assertForbidden();
            }
        }
    }
});

it('rejects unauthorized product writes before persistence', function (): void {
    $marketing = User::factory()->create(['rol' => 'marketing']);
    $category = Categoria::factory()->create();

    $this->actingAs($marketing)
        ->post(route('management.products.store'), [
            'marca' => 'No autorizado',
            'nombre' => 'Producto rechazado',
            'categoria_id' => $category->id,
            'precio' => 100,
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('productos', ['nombre' => 'Producto rechazado']);
});

it('muestra los estados vacíos financieros y limita las tres rutas al administrador autorizado', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $marketing = SpanishAccessFixture::user('marketing');

    $this->actingAs($administrador)
        ->get(route('management.payments'))
        ->assertOk()
        ->assertSee('No hay pagos y cobros para mostrar');
    $this->actingAs($administrador)
        ->get(route('management.payments.preview.cash_registers'))
        ->assertOk()
        ->assertSee('No hay cajas que coincidan con los filtros');
    $this->actingAs($administrador)
        ->get(route('management.refunds'))
        ->assertOk()
        ->assertSee('No hay reembolsos para mostrar');

    $this->actingAs($marketing)->get(route('management.payments'))->assertForbidden();
    $this->actingAs($marketing)->get(route('management.payments.preview.cash_registers'))->assertForbidden();
    $this->actingAs($marketing)->get(route('management.refunds'))->assertForbidden();
});

it('registra desde las rutas una caja y un cobro pendiente antes de confirmar efectivo una sola vez', function (): void {
    $administrador = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();
    $medioPagoId = DB::table('medios_pago')->insertGetId([
        'codigo' => 'EFECTIVO-LOCAL',
        'nombre' => 'Efectivo en caja',
        'requiere_caja' => true,
        'requiere_banco' => false,
        'activo' => true,
    ]);
    $ventaId = DB::table('ventas')->insertGetId([
        'empresa_id' => $alcance['empresa_id'],
        'sede_id' => $alcance['sede_id'],
        'almacen_id' => $alcance['almacen_id'],
        'codigo' => 'VTA-CAJA-001',
        'estado' => 'registrada',
        'moneda' => 'PEN',
        'subtotal' => '30.00',
        'descuento' => '0.00',
        'impuestos' => '0.00',
        'total' => '30.00',
        'fecha_venta' => now(),
        'canal' => 'fisica',
        'observaciones' => null,
        'creado_en' => now(),
        'actualizado_en' => now(),
    ]);

    $this->actingAs($administrador)
        ->post(route('management.payments.cash_registers.store'), [
            'sede_id' => $alcance['sede_id'],
            'codigo' => 'CAJA-PRINCIPAL',
            'nombre' => 'Caja principal',
        ])
        ->assertRedirect(route('management.payments.preview.cash_registers'));

    $cajaId = (int) DB::table('cajas')->where('codigo', 'CAJA-PRINCIPAL')->value('id');
    $this->actingAs($administrador)
        ->post(route('management.payments.cash_sessions.store'), [
            'caja_id' => $cajaId,
            'saldo_inicial' => '10.00',
            'token' => 'c1111111-1111-4111-8111-111111111111',
        ])
        ->assertRedirect();

    $sesionCajaId = (int) DB::table('sesiones_caja')->where('caja_id', $cajaId)->value('id');
    $this->actingAs($administrador)
        ->get(route('management.payments'))
        ->assertOk()
        ->assertSee('Venta VTA-CAJA-001')
        ->assertSee('Efectivo en caja');

    $this->actingAs($administrador)
        ->post(route('management.payments.store'), [
            'token' => 'c2222222-2222-4222-8222-222222222222',
            'origen' => 'venta:'.$ventaId,
            'medio_pago_id' => $medioPagoId,
            'sesion_caja_id' => $sesionCajaId,
            'importe' => '30.00',
        ])
        ->assertRedirect();

    $pagoId = (int) DB::table('pagos')->where('venta_id', $ventaId)->value('id');
    expect(DB::table('pagos')->where('id', $pagoId)->value('estado'))->toBe('pendiente')
        ->and(DB::table('movimientos_caja')->where('pago_id', $pagoId)->count())->toBe(0);

    $this->actingAs($administrador)
        ->get(route('management.payments.show', $pagoId))
        ->assertOk()
        ->assertSee('Verificación manual de efectivo')
        ->assertSee('name="importe_contado"', false);

    $datosConfirmacion = [
        'importe_contado' => '30.00',
        'observacion' => 'Efectivo contado contra la venta.',
    ];
    $rutaConfirmacion = route('management.payments.cash.update', $pagoId);
    $this->actingAs($administrador)->patch($rutaConfirmacion, $datosConfirmacion)->assertRedirect();
    $this->actingAs($administrador)->patch($rutaConfirmacion, $datosConfirmacion)->assertRedirect();

    $this->assertDatabaseHas('pagos', ['id' => $pagoId, 'estado' => 'registrado']);
    $this->assertDatabaseHas('movimientos_caja', [
        'pago_id' => $pagoId,
        'sesion_caja_id' => $sesionCajaId,
        'direccion' => 'entrada',
        'importe' => '30.00',
    ]);
    expect(DB::table('movimientos_caja')->where('pago_id', $pagoId)->count())->toBe(1)
        ->and(DB::table('registros_auditoria')->where('entidad', 'pagos')->where('entidad_id', $pagoId)
            ->where('accion', 'pago.confirmado_efectivo')->count())->toBe(1);
});
