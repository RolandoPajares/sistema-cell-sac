<?php

use App\Http\Controllers\Auth\AutenticacionController;
use App\Http\Controllers\Cuenta\CuentaAdministracionController;
use App\Http\Controllers\Cuenta\CuentaController;
use App\Http\Controllers\Gestion\Auditoria\AuditoriaController;
use App\Http\Controllers\Gestion\Campanias\CampaniaController;
use App\Http\Controllers\Gestion\Campanias\PublicidadInteligenteController;
use App\Http\Controllers\Gestion\Categorias\CategoriaController;
use App\Http\Controllers\Gestion\Clientes\ClienteController;
use App\Http\Controllers\Gestion\Compras\CompraController;
use App\Http\Controllers\Gestion\Cotizaciones\CotizacionController;
use App\Http\Controllers\Gestion\Cupones\CuponController;
use App\Http\Controllers\Gestion\Inventario\InventarioController;
use App\Http\Controllers\Gestion\Productos\GestionProductosController;
use App\Http\Controllers\Gestion\Proveedores\ProveedorController;
use App\Http\Controllers\Gestion\Reportes\ReporteController;
use App\Http\Controllers\Gestion\Segmentos\SegmentoClienteController;
use App\Http\Controllers\Gestion\Usuarios\GestionUsuariosController;
use App\Http\Controllers\Paneles\Administrador\DashboardController as AdministradorDashboardController;
use App\Http\Controllers\Publico\Asistente\AsistenteController;
use App\Http\Controllers\Publico\Catalogo\CatalogoController;
use App\Http\Controllers\Publico\Catalogo\ComparadorController;
use App\Http\Controllers\Publico\Catalogo\RecomendacionController;
use App\Http\Controllers\Publico\Comercio\CampaniaPublicaController;
use App\Http\Controllers\Publico\Comercio\CarritoController;
use App\Http\Controllers\Publico\Comercio\FinalizarCompraController;
use App\Http\Controllers\Publico\Comercio\PedidoController;
use App\Http\Controllers\Publico\Inicio\InicioController;
use App\Http\Controllers\Publico\Inteligente\InteligenciaComercialController;
use App\Http\Controllers\Publico\Paginas\PaginaController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', InicioController::class)->name('home');
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalog.index');
Route::get('/catalogo/{product}', [CatalogoController::class, 'show'])->whereNumber('product')->name('catalog.show');
Route::post('/campanas/{campaign}/evento', [CampaniaPublicaController::class, 'track'])
    ->whereNumber('campaign')->middleware('throttle:60,1')->name('campaigns.track');
Route::get('/nosotros', [PaginaController::class, 'about'])->name('pages.about');
Route::get('/contacto', [PaginaController::class, 'contact'])->name('pages.contact');
Route::post('/contacto', [PaginaController::class, 'sendContact'])->middleware('throttle:contact')->name('pages.contact.store');

Route::get('/recomendador', [RecomendacionController::class, 'index'])->name('recommendations');
Route::get('/comparador', [ComparadorController::class, 'index'])->name('comparison');
Route::get('/optimizador', [InteligenciaComercialController::class, 'optimizer'])->name('smart.optimizer');
Route::get('/asistente', [AsistenteController::class, 'index'])->name('assistant');
Route::post('/asistente', [AsistenteController::class, 'ask'])->middleware('throttle:10,1')->name('assistant.ask');

Route::middleware('guest')->group(function (): void {
    Route::get('/iniciar-sesion', [AutenticacionController::class, 'loginForm'])->name('login');
    Route::post('/iniciar-sesion', [AutenticacionController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('/registrarse', [AutenticacionController::class, 'registerForm'])->name('register');
    Route::post('/registrarse', [AutenticacionController::class, 'register'])->middleware('throttle:register')->name('register.store');
});

Route::post('/cerrar-sesion', [AutenticacionController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/mi-cuenta', [CuentaController::class, 'dashboard'])->name('dashboard');
    Route::put('/mi-cuenta/perfil', [CuentaAdministracionController::class, 'updateProfile'])->name('account.profile');
    Route::put('/mi-cuenta/password', [CuentaAdministracionController::class, 'updatePassword'])->name('account.password');

    Route::get('/carrito', [CarritoController::class, 'index'])->name('cart.index');
    Route::post('/carrito/{product}/agregar', [CarritoController::class, 'add'])->whereNumber('product')->name('cart.add');
    Route::patch('/carrito/{product}', [CarritoController::class, 'update'])->whereNumber('product')->name('cart.update');
    Route::post('/carrito/{product}/quitar', [CarritoController::class, 'remove'])->whereNumber('product')->name('cart.remove');
    Route::post('/carrito/vaciar', [CarritoController::class, 'clear'])->name('cart.clear');
    Route::post('/carrito/cupon', [CarritoController::class, 'applyCoupon'])->name('cart.coupon');
    Route::get('/checkout', [FinalizarCompraController::class, 'index'])->name('checkout.form');
    Route::post('/checkout', [FinalizarCompraController::class, 'store'])->middleware('throttle:checkout')->name('checkout');

    Route::get('/mis-pedidos', [PedidoController::class, 'index'])->name('orders.index');
    Route::get('/mis-pedidos/{order}', [PedidoController::class, 'show'])->whereNumber('order')->name('orders.show');
    Route::patch('/mis-pedidos/{order}/estado', [PedidoController::class, 'updateStatus'])->whereNumber('order')->name('orders.status');

    Route::get('/portal-mayorista', [InteligenciaComercialController::class, 'wholesale'])
        ->middleware('role:administrador,ventas_mayoristas,cliente_mayorista')->name('smart.wholesale');

    Route::get('/cotizaciones', [CotizacionController::class, 'index'])
        ->middleware('role:administrador,ventas_mayoristas,cliente_mayorista')->name('quotations.index');
    Route::post('/cotizaciones', [CotizacionController::class, 'store'])
        ->middleware('role:administrador,ventas_mayoristas,cliente_mayorista')->name('quotations.store');
});

Route::prefix('gestion')->middleware(['auth', 'role:administrador'])->name('management.')->group(function (): void {
    Route::get('/', AdministradorDashboardController::class)->name('dashboard');

    Route::get('/productos', [GestionProductosController::class, 'index'])->name('products');
    Route::post('/productos', [GestionProductosController::class, 'store'])->name('products.store');
    Route::put('/productos/{product}', [GestionProductosController::class, 'update'])->whereNumber('product')->name('products.update');
    Route::delete('/productos/{product}', [GestionProductosController::class, 'deactivate'])->whereNumber('product')->name('products.deactivate');

    Route::get('/usuarios', [GestionUsuariosController::class, 'index'])->name('users');
    Route::post('/usuarios', [GestionUsuariosController::class, 'store'])->name('users.store');
    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('audit');

    Route::get('/categorias', [CategoriaController::class, 'index'])->name('categories');
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categories.store');
    Route::put('/categorias/{category}', [CategoriaController::class, 'update'])->name('categories.update');
    Route::delete('/categorias/{category}', [CategoriaController::class, 'deactivate'])->name('categories.deactivate');

    Route::get('/clientes', [ClienteController::class, 'index'])->name('customers');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('customers.store');
    Route::put('/clientes/{customer}', [ClienteController::class, 'update'])->name('customers.update');
    Route::delete('/clientes/{customer}', [ClienteController::class, 'deactivate'])->name('customers.deactivate');

    Route::get('/pedidos', [PedidoController::class, 'index'])->name('orders');
    Route::get('/cuenta', [CuentaController::class, 'dashboard'])->name('account');
    Route::put('/cuenta/perfil', [CuentaAdministracionController::class, 'updateProfile'])->name('account.profile');
    Route::put('/cuenta/password', [CuentaAdministracionController::class, 'updatePassword'])->name('account.password');
});

Route::get('/gestion/publicidad-inteligente', [PublicidadInteligenteController::class, 'ads'])
    ->middleware(['auth', 'role:administrador,marketing'])->name('smart.ads');

Route::prefix('gestion')->middleware(['auth', 'role:administrador,compras_logistica,ventas_minoristas'])->name('management.')->group(function (): void {
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventory');
    Route::post('/inventario/{product}', [InventarioController::class, 'adjust'])->whereNumber('product')->name('inventory.adjust');
});

Route::prefix('gestion')->middleware(['auth', 'role:administrador,compras_logistica'])->name('management.')->group(function (): void {
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('suppliers');
    Route::post('/proveedores', [ProveedorController::class, 'store'])->name('suppliers.store');
    Route::put('/proveedores/{supplier}', [ProveedorController::class, 'update'])->name('suppliers.update');
    Route::delete('/proveedores/{supplier}', [ProveedorController::class, 'deactivate'])->name('suppliers.deactivate');
    Route::get('/compras', [CompraController::class, 'index'])->name('purchases');
    Route::post('/compras', [CompraController::class, 'store'])->name('purchases.store');
});

Route::prefix('gestion')->middleware(['auth', 'role:administrador,marketing'])->name('management.')->group(function (): void {
    Route::get('/campanas', [CampaniaController::class, 'index'])->name('campaigns');
    Route::post('/campanas', [CampaniaController::class, 'store'])->name('campaigns.store');
    Route::put('/campanas/{campaign}', [CampaniaController::class, 'update'])->name('campaigns.update');
    Route::delete('/campanas/{campaign}', [CampaniaController::class, 'deactivate'])->name('campaigns.deactivate');
    Route::get('/cupones', [CuponController::class, 'index'])->name('coupons');
    Route::post('/cupones', [CuponController::class, 'store'])->name('coupons.store');
    Route::get('/segmentos', [SegmentoClienteController::class, 'index'])->name('segments');
    Route::post('/segmentos', [SegmentoClienteController::class, 'store'])->name('segments.store');
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reports');
    Route::get('/reportes/exportar', [ReporteController::class, 'export'])->name('reports.export');
});

/*
|--------------------------------------------------------------------------
| Compatibility entry points
|--------------------------------------------------------------------------
| These aliases keep bookmarks and integrations from the previous application
| working while the canonical implementation remains in the routes above.
*/
Route::get('/catalog', [CatalogoController::class, 'index'])->name('legacy.catalog');
Route::get('/products/{product}', [CatalogoController::class, 'show'])->whereNumber('product')->name('legacy.product');
Route::get('/smart/recommend', [RecomendacionController::class, 'index'])->name('legacy.recommendations');
Route::get('/smart/compare', [ComparadorController::class, 'index'])->name('legacy.comparison');
Route::get('/smart/optimizer', [InteligenciaComercialController::class, 'optimizer'])->name('legacy.optimizer');
Route::get('/smart/ads', [PublicidadInteligenteController::class, 'ads'])
    ->middleware(['auth', 'role:administrador,marketing'])->name('legacy.ads');
Route::get('/about', [PaginaController::class, 'about'])->name('legacy.about');
Route::get('/contact', [PaginaController::class, 'contact'])->name('legacy.contact');
Route::post('/contact', [PaginaController::class, 'sendContact'])->middleware('throttle:contact')->name('legacy.contact.store');
Route::get('/login', [AutenticacionController::class, 'loginForm'])->middleware('guest')->name('legacy.login');
Route::post('/login', [AutenticacionController::class, 'login'])->middleware(['guest', 'throttle:login'])->name('legacy.login.store');
Route::get('/register', [AutenticacionController::class, 'registerForm'])->middleware('guest')->name('legacy.register');
Route::post('/register', [AutenticacionController::class, 'register'])->middleware(['guest', 'throttle:register'])->name('legacy.register.store');
Route::get('/logout', fn (): RedirectResponse => redirect()->route('login'))->name('legacy.logout.form');
Route::post('/logout', [AutenticacionController::class, 'logout'])->middleware('auth')->name('legacy.logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/account', [CuentaController::class, 'dashboard'])->name('legacy.account');
    Route::get('/cart', [CarritoController::class, 'index'])->name('legacy.cart');
    Route::post('/cart', [CarritoController::class, 'updateLegacy'])->name('legacy.cart.update');
    Route::get('/mayorista', [InteligenciaComercialController::class, 'wholesale'])
        ->middleware('role:administrador,ventas_mayoristas,cliente_mayorista')->name('legacy.wholesale');
});

Route::get('/smart/assistant', [AsistenteController::class, 'index'])->name('legacy.assistant');
Route::post('/smart/assistant/reply', [AsistenteController::class, 'ask'])->middleware('throttle:10,1')->name('legacy.assistant.reply');

Route::prefix('admin')->middleware(['auth', 'role:administrador'])->name('legacy.admin.')->group(function (): void {
    Route::get('/', AdministradorDashboardController::class)->name('dashboard');
    Route::get('/account', [CuentaController::class, 'dashboard'])->name('account');
    Route::post('/account/profile', [CuentaAdministracionController::class, 'updateProfile'])->name('account.profile');
    Route::post('/account/password', [CuentaAdministracionController::class, 'updatePassword'])->name('account.password');
    Route::get('/campaigns', [CampaniaController::class, 'index'])->name('campaigns');
    Route::post('/campaigns', [CampaniaController::class, 'store'])->name('campaigns.store');
    Route::post('/campaigns/{campaign}', [CampaniaController::class, 'update'])->name('campaigns.update');
    Route::post('/campaigns/{campaign}/deactivate', [CampaniaController::class, 'deactivate'])->name('campaigns.deactivate');
    Route::get('/products', [GestionProductosController::class, 'index'])->name('products');
    Route::post('/products', [GestionProductosController::class, 'store'])->name('products.store');
    Route::match(['post', 'put'], '/products/{product}', [GestionProductosController::class, 'update'])->name('products.update');
    Route::post('/products/{product}/deactivate', [GestionProductosController::class, 'deactivate'])->name('products.deactivate');
    Route::get('/orders', [PedidoController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [PedidoController::class, 'show'])->whereNumber('order')->name('orders.show');
    Route::match(['post', 'patch'], '/orders/{order}/status', [PedidoController::class, 'updateStatus'])->whereNumber('order')->name('orders.status');
    Route::get('/users', [GestionUsuariosController::class, 'index'])->name('users');
    Route::post('/users', [GestionUsuariosController::class, 'store'])->name('users.store');
    Route::get('/categories', [CategoriaController::class, 'index'])->name('categories');
    Route::post('/categories', [CategoriaController::class, 'store'])->name('categories.store');
    Route::match(['post', 'put'], '/categories/{category}', [CategoriaController::class, 'update'])->name('categories.update');
    Route::post('/categories/{category}/deactivate', [CategoriaController::class, 'deactivate'])->name('categories.deactivate');
    Route::get('/suppliers', [ProveedorController::class, 'index'])->name('suppliers');
    Route::post('/suppliers', [ProveedorController::class, 'store'])->name('suppliers.store');
    Route::post('/suppliers/{supplier}', [ProveedorController::class, 'update'])->name('suppliers.update');
    Route::post('/suppliers/{supplier}/deactivate', [ProveedorController::class, 'deactivate'])->name('suppliers.deactivate');
    Route::get('/customers', [ClienteController::class, 'index'])->name('customers');
    Route::post('/customers', [ClienteController::class, 'store'])->name('customers.store');
    Route::get('/inventory', [InventarioController::class, 'index'])->name('inventory');
    Route::post('/inventory', [InventarioController::class, 'adjustLegacy'])->name('inventory.store');
    Route::get('/reports', [ReporteController::class, 'index'])->name('reports');
    Route::get('/audit', [AuditoriaController::class, 'index'])->name('audit');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/panel', [CuentaController::class, 'dashboard'])->name('legacy.panel');
    Route::get('/panel/{module}', [CuentaController::class, 'dashboard'])->name('legacy.panel.module');
});
