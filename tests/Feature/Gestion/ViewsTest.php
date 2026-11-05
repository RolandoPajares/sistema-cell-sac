<?php

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\Clientes\Cliente;
use App\Models\Comercio\Cotizacion;
use App\Models\Comercio\Venta;
use App\Models\Compras\Compra;
use App\Models\Compras\Proveedor;
use Illuminate\Support\Facades\DB;
use Tests\SpanishAccessFixture;

it('renders each management module with its canonical list view', function () {
    $admin = SpanishAccessFixture::user('administrador');
    $viewsByRoute = [
        'management.audit' => 'gestion.auditoria.listar',
        'management.campaigns' => 'gestion.campanias.listar',
        'management.categories' => 'gestion.categorias.listar',
        'management.customers' => 'gestion.clientes.listar',
        'management.purchases' => 'gestion.compras.listar',
        'quotations.index' => 'gestion.cotizaciones.listar',
        'management.quotations' => 'gestion.cotizaciones.administrador',
        'management.coupons' => 'gestion.cupones.listar',
        'management.inventory' => 'gestion.inventario.listar',
        'management.products' => 'gestion.productos.listar',
        'management.suppliers' => 'gestion.proveedores.listar',
        'management.reports' => 'gestion.reportes.listar',
        'management.segments' => 'gestion.segmentos.listar',
        'management.users' => 'gestion.usuarios.listar',
    ];

    foreach ($viewsByRoute as $routeName => $viewName) {
        $this->actingAs($admin)
            ->get(route($routeName))
            ->assertOk()
            ->assertViewIs($viewName);
    }
});

it('opens the existing product edit form for a catalog row selected from pricing', function () {
    $admin = SpanishAccessFixture::user('administrador');
    $alcance = SpanishAccessFixture::organization();
    $producto = Producto::factory()->create(['empresa_id' => $alcance['empresa_id'], 'precio' => 245.5]);

    $this->actingAs($admin)
        ->get(route('management.products', ['editar' => $producto->id]))
        ->assertOk()
        ->assertViewHas('products', fn ($productos): bool => $productos->total() === 1 && $productos->getCollection()->sole()->is($producto))
        ->assertSee('editar-producto-'.$producto->id, false)
        ->assertSee('name="precio"', false);
});

it('filters the administrator quotation dashboard and calculates real status totals', function () {
    $admin = SpanishAccessFixture::user('administrador');
    $scope = SpanishAccessFixture::organization();
    $customer = Cliente::create([
        'tipo' => 'mayorista',
        'documento' => 'RUC-900001',
        'nombre_contacto' => 'Cliente de prueba',
        'correo' => 'cliente.cotizaciones@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
    ]);

    Cotizacion::create([
        'cliente_id' => $customer->id,
        'creado_por_usuario_id' => $admin->id,
        'total' => 320,
        'estado' => 'Borrador',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
        'sede_id' => $scope['sede_id'],
    ]);
    $approved = Cotizacion::create([
        'cliente_id' => $customer->id,
        'creado_por_usuario_id' => $admin->id,
        'total' => 680,
        'estado' => 'Aprobada',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
        'sede_id' => $scope['sede_id'],
    ]);

    $this->actingAs($admin)
        ->get(route('management.quotations', ['estado' => 'Aprobada']))
        ->assertOk()
        ->assertViewIs('gestion.cotizaciones.administrador')
        ->assertViewHas('totalQuotations', 2)
        ->assertViewHas('pendingQuotations', 1)
        ->assertViewHas('approvedQuotations', 1)
        ->assertViewHas('quotedTotal', 1000.0)
        ->assertViewHas('quotations', fn ($quotations): bool => $quotations->total() === 1
            && $quotations->getCollection()->sole()->is($approved))
        ->assertSee('Gestión de cotizaciones')
        ->assertSee('Nueva cotización')
        ->assertSee('aria-label="Buscar cotización, cliente o producto"', false)
        ->assertSee('aria-label="Cliente mayorista"', false)
        ->assertSee('aria-label="Estado de cotización"', false);
});

it('rejects a quotation that mixes a customer company with another company product', function (): void {
    $admin = SpanishAccessFixture::user('administrador');
    $scope = SpanishAccessFixture::organization();
    $customer = Cliente::create([
        'tipo' => 'mayorista', 'documento' => 'RUC-900099', 'nombre_contacto' => 'Cliente aislado',
        'correo' => 'cliente.aislado@example.test', 'activo' => true, 'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
    ]);
    $otraEmpresaId = DB::table('empresas')->insertGetId([
        'codigo' => 'OTRA-EMPRESA', 'razon_social' => 'Empresa ajena', 'activo' => false,
        'creado_en' => now(), 'actualizado_en' => now(),
    ]);
    $productoAjeno = Producto::factory()->create(['empresa_id' => $otraEmpresaId]);

    $this->actingAs($admin)
        ->post(route('quotations.store'), [
            'cliente_id' => $customer->id,
            'items' => [['producto_id' => $productoAjeno->id, 'cantidad' => 1]],
        ])
        ->assertUnprocessable();

    expect(DB::table('cotizaciones')->where('cliente_id', $customer->id)->count())->toBe(0);
});

it('approves a pending quotation within RBAC scope and records the audit event', function () {
    $admin = SpanishAccessFixture::user('administrador');
    $scope = SpanishAccessFixture::organization();
    $customer = Cliente::create([
        'tipo' => 'mayorista',
        'documento' => 'RUC-900003',
        'nombre_contacto' => 'Cliente para aprobación',
        'correo' => 'aprobacion@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
    ]);
    $product = Producto::factory()->create(['empresa_id' => $scope['empresa_id']]);
    $quotation = Cotizacion::create([
        'cliente_id' => $customer->id,
        'creado_por_usuario_id' => $admin->id,
        'total' => 120,
        'estado' => 'Pendiente',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
        'sede_id' => $scope['sede_id'],
    ]);
    $quotation->details()->create(['producto_id' => $product->id, 'cantidad' => 1, 'precio_unitario' => 120]);

    $this->actingAs($admin)
        ->post(route('management.quotations.approve', $quotation))
        ->assertRedirect();

    expect($quotation->fresh()->estado)->toBe('Aprobada')
        ->and(DB::table('registros_auditoria')->where('accion', 'cotizacion.aprobada')->where('entidad_id', $quotation->id)->exists())->toBeTrue();
});

it('downloads a real quotation PDF and records a manually confirmed email send once', function () {
    $admin = SpanishAccessFixture::user('administrador');
    $scope = SpanishAccessFixture::organization();
    $customer = Cliente::create([
        'tipo' => 'mayorista',
        'documento' => 'RUC-900004',
        'nombre_contacto' => 'Cliente con correo',
        'correo' => 'destinatario@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
    ]);
    $product = Producto::factory()->create(['empresa_id' => $scope['empresa_id']]);
    $quotation = Cotizacion::create([
        'cliente_id' => $customer->id,
        'creado_por_usuario_id' => $admin->id,
        'total' => 120,
        'estado' => 'Borrador',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
        'sede_id' => $scope['sede_id'],
    ]);
    $quotation->details()->create(['producto_id' => $product->id, 'cantidad' => 1, 'precio_unitario' => 120]);

    $this->actingAs($admin)
        ->get(route('management.quotations'))
        ->assertOk()
        ->assertSee('data-quotation-action="enviar"', false)
        ->assertSee('data-quotation-dialog', false)
        ->assertSee('Descargar PDF');

    $this->actingAs($admin)
        ->get(route('management.quotations.export', $quotation))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $datosEnvio = ['confirmar_envio_manual' => '1'];
    $this->post(route('management.quotations.update.send', $quotation), $datosEnvio)->assertRedirect();
    $this->post(route('management.quotations.update.send', $quotation), $datosEnvio)->assertRedirect();

    expect($quotation->fresh()->estado)->toBe('Enviada')
        ->and(DB::table('registros_auditoria')->where('accion', 'cotizacion.descargada')->where('entidad_id', $quotation->id)->exists())->toBeTrue()
        ->and(DB::table('registros_auditoria')->where('accion', 'cotizacion.envio_manual_registrado')->where('entidad_id', $quotation->id)->count())->toBe(1);
});

it('converts an approved quotation to a draft and confirms its inventory movement once', function () {
    $admin = SpanishAccessFixture::user('administrador');
    $scope = SpanishAccessFixture::organization();
    $customer = Cliente::create([
        'tipo' => 'mayorista',
        'documento' => 'RUC-900005',
        'nombre_contacto' => 'Cliente de conversion',
        'correo' => 'conversion@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
    ]);
    $product = Producto::factory()->create(['empresa_id' => $scope['empresa_id'], 'existencias' => 5]);
    $quotation = Cotizacion::create([
        'cliente_id' => $customer->id,
        'creado_por_usuario_id' => $admin->id,
        'total' => 240,
        'estado' => 'Aprobada',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
        'sede_id' => $scope['sede_id'],
    ]);
    $quotation->details()->create(['producto_id' => $product->id, 'cantidad' => 2, 'precio_unitario' => 120]);
    DB::table('existencias_almacen')->insert([
        'almacen_id' => $scope['almacen_id'], 'producto_id' => $product->id,
        'empresa_id' => $scope['empresa_id'], 'sede_id' => $scope['sede_id'],
        'cantidad_fisica' => 5, 'cantidad_reservada' => 0, 'actualizado_en' => now(),
    ]);

    $respuestaConversion = $this->actingAs($admin)
        ->post(route('management.sales.convert_quotation', $quotation), ['almacen_id' => $scope['almacen_id']]);
    $respuestaConversion->assertRedirect(route('management.sales'));
    $this->post(route('management.sales.convert_quotation', $quotation), ['almacen_id' => $scope['almacen_id']])
        ->assertRedirect(route('management.sales'));

    $venta = Venta::query()->where('codigo', 'COT-'.$quotation->id)->sole();
    $pedidoId = (int) $venta->pedido_id;
    expect($quotation->fresh()->estado)->toBe('Convertida')
        ->and($venta->estado)->toBe('borrador')
        ->and(DB::table('pedidos')->where('id', $pedidoId)->value('estado'))->toBe('Pendiente')
        ->and(DB::table('pedidos')->where('id', $pedidoId)->value('canal'))->toBe('presencial')
        ->and(DB::table('pedidos')->where('id', $pedidoId)->value('tipo_venta'))->toBe('fisica')
        ->and((float) DB::table('existencias_almacen')->where('almacen_id', $scope['almacen_id'])->where('producto_id', $product->id)->value('cantidad_fisica'))->toBe(5.0)
        ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'venta')->where('documento_id', (string) $venta->id)->count())->toBe(0);

    $this->post(route('management.sales.confirm', $venta))->assertRedirect(route('management.sales'));
    $this->post(route('management.sales.confirm', $venta))->assertRedirect(route('management.sales'));

    expect($venta->fresh()->estado)->toBe('confirmada')
        ->and((float) DB::table('existencias_almacen')->where('almacen_id', $scope['almacen_id'])->where('producto_id', $product->id)->value('cantidad_fisica'))->toBe(3.0)
        ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'venta')->where('documento_id', (string) $venta->id)->where('tipo_movimiento', 'salida')->count())->toBe(1)
        ->and(DB::table('registros_auditoria')->where('accion', 'cotizacion.convertida_en_venta_borrador')->where('entidad_id', $quotation->id)->count())->toBe(1)
        ->and(DB::table('registros_auditoria')->where('accion', 'venta.confirmada_desde_cotizacion')->where('entidad_id', $venta->id)->count())->toBe(1);
});

it('associates available serialized devices to a sale exactly once during confirmation', function () {
    $admin = SpanishAccessFixture::user('administrador');
    $scope = SpanishAccessFixture::organization();
    $customer = Cliente::create([
        'tipo' => 'mayorista', 'documento' => 'RUC-900006', 'nombre_contacto' => 'Cliente serializado',
        'correo' => 'serializado@example.test', 'activo' => true, 'creado_en' => now(), 'empresa_id' => $scope['empresa_id'],
    ]);
    $product = Producto::factory()->create(['empresa_id' => $scope['empresa_id'], 'existencias' => 1]);
    $variantId = DB::table('variantes_productos')->insertGetId([
        'producto_id' => $product->id, 'existencias' => 1, 'controla_serie' => true, 'estado' => 'activo',
        'fecha_creacion' => now(),
    ]);
    $quotation = Cotizacion::create([
        'cliente_id' => $customer->id, 'creado_por_usuario_id' => $admin->id, 'total' => 120,
        'estado' => 'Aprobada', 'activo' => true, 'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'], 'sede_id' => $scope['sede_id'],
    ]);
    $quotation->details()->create([
        'producto_id' => $product->id, 'variante_id' => $variantId, 'cantidad' => 1,
        'precio_unitario' => 120, 'total_linea' => 120,
    ]);
    DB::table('existencias_almacen')->insert([
        'almacen_id' => $scope['almacen_id'], 'producto_id' => $product->id, 'variante_id' => $variantId,
        'empresa_id' => $scope['empresa_id'], 'sede_id' => $scope['sede_id'],
        'cantidad_fisica' => 1, 'cantidad_reservada' => 0, 'actualizado_en' => now(),
    ]);
    $dispositivoId = DB::table('dispositivos')->insertGetId([
        'producto_id' => $product->id, 'variante_id' => $variantId, 'imei' => '490154203237518',
        'almacen_actual_id' => $scope['almacen_id'], 'estado' => 'disponible', 'fecha_ingreso' => now(),
    ]);

    $this->actingAs($admin)->post(route('management.sales.convert_quotation', $quotation), ['almacen_id' => $scope['almacen_id']]);
    $venta = Venta::query()->where('codigo', 'COT-'.$quotation->id)->sole();
    $detalleId = (int) $venta->detalles()->value('id');
    $this->actingAs($admin)->get(route('management.sales'))->assertOk()->assertSee('490154203237518');
    $datosConfirmacion = ['dispositivos' => [$detalleId => [$dispositivoId]]];
    $respuestaConfirmacion = $this->post(route('management.sales.confirm', $venta), $datosConfirmacion);
    $respuestaConfirmacion->assertSessionHasNoErrors()->assertRedirect(route('management.sales'));
    $this->post(route('management.sales.confirm', $venta), $datosConfirmacion)->assertRedirect(route('management.sales'));

    expect($venta->fresh()->estado)->toBe('confirmada')
        ->and(DB::table('existencias_almacen')->where('almacen_id', $scope['almacen_id'])->where('producto_id', $product->id)->where('variante_id', $variantId)->value('cantidad_fisica'))->toBe(0)
        ->and(DB::table('detalles_ventas_dispositivos')->where('detalle_venta_id', $detalleId)->where('dispositivo_id', $dispositivoId)->count())->toBe(1)
        ->and(DB::table('dispositivos')->where('id', $dispositivoId)->value('estado'))->toBe('vendido')
        ->and(DB::table('movimientos_dispositivos')->where('dispositivo_id', $dispositivoId)->where('estado_nuevo', 'vendido')->count())->toBe(1)
        ->and(DB::table('movimientos_inventario')->where('documento_tipo', 'venta')->where('documento_id', (string) $venta->id)->count())->toBe(1);
});

it('opens the independent inventory movement history within RBAC scope', function () {
    $admin = SpanishAccessFixture::user('administrador');
    $scope = SpanishAccessFixture::organization();
    $product = Producto::factory()->create(['empresa_id' => $scope['empresa_id']]);
    DB::table('movimientos_inventario')->insert([
        'producto_id' => $product->id,
        'usuario_id' => $admin->id,
        'tipo_movimiento' => 'salida',
        'cantidad' => 1.5,
        'notas' => 'Movimiento aislado de prueba',
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
        'sede_id' => $scope['sede_id'],
        'almacen_id' => $scope['almacen_id'],
    ]);

    $this->withoutVite()->actingAs($admin)
        ->get(route('management.inventory.movements'))
        ->assertOk()
        ->assertViewIs('gestion.inventario.listar')
        ->assertViewHas('verHistorialMovimientos', true)
        ->assertViewHas('recentMovements', fn ($movimientos): bool => $movimientos->total() === 1)
        ->assertSee('Historial de movimientos')
        ->assertSee('Movimiento aislado de')
        ->assertSee('data-open-movement-detail', false)
        ->assertSee('name="mov_buscar"', false)
        ->assertSee('-1.5');
});

it('filtra el historial independiente de movimientos dentro del alcance autorizado y muestra el detalle completo', function () {
    $admin = SpanishAccessFixture::user('administrador');
    $scope = SpanishAccessFixture::organization();
    $product = Producto::factory()->create(['empresa_id' => $scope['empresa_id'], 'nombre' => 'Equipo de inventario filtrable']);

    foreach ([
        ['tipo_movimiento' => 'entrada', 'notas' => 'Recepción documentada', 'documento_tipo' => 'compra', 'documento_id' => 'COMPRA-77'],
        ['tipo_movimiento' => 'salida', 'notas' => 'Salida de otro documento', 'documento_tipo' => 'venta', 'documento_id' => 'VENTA-88'],
    ] as $movimiento) {
        DB::table('movimientos_inventario')->insert([
            ...$movimiento,
            'producto_id' => $product->id,
            'usuario_id' => $admin->id,
            'cantidad' => 1,
            'creado_en' => now(),
            'empresa_id' => $scope['empresa_id'],
            'sede_id' => $scope['sede_id'],
            'almacen_id' => $scope['almacen_id'],
        ]);
    }

    $this->withoutVite()->actingAs($admin)
        ->get(route('management.inventory.movements', ['mov_buscar' => 'COMPRA-77', 'mov_tipo' => 'entrada']))
        ->assertOk()
        ->assertViewHas('recentMovements', fn ($movimientos): bool => $movimientos->total() === 1)
        ->assertSee('Recepción documentada')
        ->assertSee('COMPRA-77')
        ->assertDontSee('VENTA-88');
});

it('filters purchases and calculates their dashboard totals', function () {
    $administrator = SpanishAccessFixture::user('administrador');
    $scope = SpanishAccessFixture::organization();
    $firstSupplier = Proveedor::create([
        'nombre' => 'Proveedor principal',
        'ruc' => 'RUC-900101',
        'correo' => 'principal@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
    ]);
    $secondSupplier = Proveedor::create([
        'nombre' => 'Proveedor secundario',
        'ruc' => 'RUC-900102',
        'correo' => 'secundario@example.test',
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
    ]);
    $category = Categoria::factory()->create();
    $product = Producto::factory()->create(['categoria_id' => $category->id]);
    $received = Compra::create([
        'proveedor_id' => $firstSupplier->id,
        'creado_por_usuario_id' => $administrator->id,
        'total' => 320,
        'estado' => 'Recibida',
        'fecha_compra' => now()->toDateString(),
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
        'sede_id' => $scope['sede_id'],
        'almacen_id' => $scope['almacen_id'],
    ]);
    $received->details()->create([
        'producto_id' => $product->id,
        'cantidad' => 2,
        'costo_unitario' => 160,
    ]);
    $pending = Compra::create([
        'proveedor_id' => $secondSupplier->id,
        'creado_por_usuario_id' => $administrator->id,
        'total' => 180,
        'estado' => 'Pendiente',
        'fecha_compra' => now()->toDateString(),
        'activo' => true,
        'creado_en' => now(),
        'empresa_id' => $scope['empresa_id'],
        'sede_id' => $scope['sede_id'],
        'almacen_id' => $scope['almacen_id'],
    ]);
    $pending->details()->create([
        'producto_id' => $product->id,
        'cantidad' => 1,
        'costo_unitario' => 180,
    ]);

    $this->actingAs($administrator)
        ->get(route('management.purchases', [
            'proveedor' => $firstSupplier->id,
            'estado' => 'Recibida',
        ]))
        ->assertOk()
        ->assertViewIs('gestion.compras.listar')
        ->assertViewHas('totalPurchases', 2)
        ->assertViewHas('pendingPurchases', 1)
        ->assertViewHas('receivedPurchases', 1)
        ->assertViewHas('monthlyPurchaseTotal', 500.0)
        ->assertViewHas('monthlyPurchaseCount', 2)
        ->assertViewHas('purchases', fn ($purchases): bool => $purchases->total() === 1
            && $purchases->getCollection()->sole()->is($received))
        ->assertSee('Gestión de compras')
        ->assertSee('Nueva compra')
        ->assertSee('aria-label="Buscar orden, proveedor o producto"', false)
        ->assertSee($product->nombre);
});
