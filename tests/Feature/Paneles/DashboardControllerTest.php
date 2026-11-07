<?php

use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\SpanishAccessFixture;

it('renders the role dashboard from the shared account entry point', function (string $role, string $view): void {
    $user = SpanishAccessFixture::user($role);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewIs($view)
        ->assertViewHas('role', $role);
})->with([
    ['marketing', 'paneles.dashboard-publico'],
    ['compras_logistica', 'paneles.dashboard-publico'],
    ['ventas_mayoristas', 'paneles.dashboard-publico'],
    ['ventas_minoristas', 'paneles.dashboard-publico'],
    ['cliente_mayorista', 'paneles.dashboard-publico'],
    ['cliente_minorista', 'paneles.dashboard-publico'],
]);

it('renders the administrator dashboard through both existing admin entry points', function (): void {
    $user = SpanishAccessFixture::user('administrador');

    $this->actingAs($user)
        ->get(route('management.dashboard'))
        ->assertOk()
        ->assertViewIs('paneles.dashboard')
        ->assertViewHas('products', 0)
        ->assertSee('No hay productos registrados');

    $this->actingAs($user)
        ->get(route('legacy.admin.dashboard'))
        ->assertOk()
        ->assertViewIs('paneles.dashboard');
});

it('keeps the administrator account configuration on the account route', function (): void {
    $user = SpanishAccessFixture::user('administrador');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewIs('paneles.perfil.editar');
});

it('renders the unified profile for every configured role', function (string $role): void {
    $user = SpanishAccessFixture::user($role);

    $this->actingAs($user)
        ->get($role === 'administrador' ? route('management.account') : route('account.profile.show'))
        ->assertOk()
        ->assertSee('Mi perfil')
        ->assertSee('Datos personales')
        ->assertSee('Seguridad de la cuenta')
        ->assertSee('Preferencias')
        ->assertSee('Sesiones activas');
})->with(User::ROLES);

it('forbids non administrators from the administrator dashboard route', function (): void {
    $user = User::factory()->create(['rol' => 'marketing']);

    $this->actingAs($user)
        ->get(route('management.dashboard'))
        ->assertForbidden();
});

it('muestra ventas confirmadas y existencias reales por empresa en el dashboard', function (): void {
    $organizacion = SpanishAccessFixture::organization();
    $administrador = SpanishAccessFixture::user('administrador');
    $categoria = Categoria::factory()->create(['empresa_id' => $organizacion['empresa_id']]);
    $producto = Producto::factory()->create([
        'categoria_id' => $categoria->id,
        'empresa_id' => $organizacion['empresa_id'],
        'nombre' => 'Equipo de inventario',
        'existencias' => 99,
    ]);

    DB::table('existencias_almacen')->insert([
        'almacen_id' => $organizacion['almacen_id'],
        'sede_id' => $organizacion['sede_id'],
        'empresa_id' => $organizacion['empresa_id'],
        'producto_id' => $producto->id,
        'cantidad_fisica' => 0,
        'cantidad_reservada' => 0,
        'actualizado_en' => now(),
    ]);

    $ventasPrueba = [
        ['codigo' => 'V-ACTUAL', 'estado' => 'confirmada', 'moneda' => 'PEN', 'total' => 450, 'cantidad' => 2, 'fecha' => now()->startOfMonth()->addDays(2)],
        ['codigo' => 'V-ANTERIOR', 'estado' => 'confirmada', 'moneda' => 'PEN', 'total' => 300, 'cantidad' => 3, 'fecha' => now()->subMonthNoOverflow()->startOfMonth()->addDays(2)],
        ['codigo' => 'V-ANULADA', 'estado' => 'anulada', 'moneda' => 'PEN', 'total' => 900, 'cantidad' => 9, 'fecha' => now()->startOfMonth()->addDays(3)],
        ['codigo' => 'V-OTRA-MONEDA', 'estado' => 'confirmada', 'moneda' => 'USD', 'total' => 700, 'cantidad' => 7, 'fecha' => now()->startOfMonth()->addDays(4)],
    ];

    foreach ($ventasPrueba as $ventaPrueba) {
        $ventaId = DB::table('ventas')->insertGetId([
            'empresa_id' => $organizacion['empresa_id'],
            'sede_id' => $organizacion['sede_id'],
            'almacen_id' => $organizacion['almacen_id'],
            'usuario_id' => $administrador->id,
            'codigo' => $ventaPrueba['codigo'],
            'estado' => $ventaPrueba['estado'],
            'moneda' => $ventaPrueba['moneda'],
            'subtotal' => $ventaPrueba['total'],
            'descuento' => 0,
            'impuestos' => 0,
            'total' => $ventaPrueba['total'],
            'fecha_venta' => $ventaPrueba['fecha'],
            'creado_en' => $ventaPrueba['fecha'],
            'actualizado_en' => $ventaPrueba['fecha'],
            'canal' => 'presencial',
        ]);

        DB::table('detalles_ventas')->insert([
            'venta_id' => $ventaId,
            'producto_id' => $producto->id,
            'descripcion_historica' => 'Equipo de inventario',
            'cantidad' => $ventaPrueba['cantidad'],
            'precio_unitario' => $ventaPrueba['total'] / $ventaPrueba['cantidad'],
            'descuento' => 0,
            'tasa_impuesto' => 0,
            'impuesto' => 0,
            'total_linea' => $ventaPrueba['total'],
        ]);
    }

    $respuesta = $this->withoutVite()
        ->actingAs($administrador)
        ->get(route('management.dashboard'));

    $respuesta->assertOk()
        ->assertViewHas('salesThisMonth', 450.0)
        ->assertViewHas('ordersCount', 0)
        ->assertViewHas('lowStock', 1)
        ->assertSee('Productos sin existencias')
        ->assertSee('0 unidades')
        ->assertSee('5 unidades vendidas')
        ->assertSee('S/ 750.00');
});
