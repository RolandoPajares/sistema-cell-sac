<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\CommerceIntelligenceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerSegmentController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManagementDashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductManagementController;
use App\Http\Controllers\PublicCampaignController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/catalogo', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogo/{product}', [CatalogController::class, 'show'])->whereNumber('product')->name('catalog.show');
Route::post('/campanas/{campaign}/evento', [PublicCampaignController::class, 'track'])
    ->whereNumber('campaign')->middleware('throttle:60,1')->name('campaigns.track');
Route::get('/nosotros', [PageController::class, 'about'])->name('pages.about');
Route::get('/contacto', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contacto', [PageController::class, 'sendContact'])->middleware('throttle:contact')->name('pages.contact.store');

Route::get('/recomendador', [RecommendationController::class, 'index'])->name('recommendations');
Route::get('/comparador', [ComparisonController::class, 'index'])->name('comparison');
Route::get('/optimizador', [CommerceIntelligenceController::class, 'optimizer'])->name('smart.optimizer');
Route::get('/asistente', [AssistantController::class, 'index'])->name('assistant');
Route::post('/asistente', [AssistantController::class, 'ask'])->middleware('throttle:10,1')->name('assistant.ask');

Route::middleware('guest')->group(function (): void {
    Route::get('/iniciar-sesion', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/iniciar-sesion', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('/registrarse', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/registrarse', [AuthController::class, 'register'])->middleware('throttle:register')->name('register.store');
});

Route::post('/cerrar-sesion', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/mi-cuenta', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::put('/mi-cuenta/perfil', [\App\Http\Controllers\AdminAccountController::class, 'updateProfile'])->name('account.profile');
    Route::put('/mi-cuenta/password', [\App\Http\Controllers\AdminAccountController::class, 'updatePassword'])->name('account.password');

    Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
    Route::post('/carrito/{product}/agregar', [CartController::class, 'add'])->whereNumber('product')->name('cart.add');
    Route::patch('/carrito/{product}', [CartController::class, 'update'])->whereNumber('product')->name('cart.update');
    Route::post('/carrito/{product}/quitar', [CartController::class, 'remove'])->whereNumber('product')->name('cart.remove');
    Route::post('/carrito/vaciar', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/carrito/cupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.form');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout');

    Route::get('/mis-pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/mis-pedidos/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');
    Route::patch('/mis-pedidos/{order}/estado', [OrderController::class, 'updateStatus'])->whereNumber('order')->name('orders.status');

    Route::get('/portal-mayorista', [CommerceIntelligenceController::class, 'wholesale'])
        ->middleware('role:administrador,ventas_mayoristas,cliente_mayorista')->name('smart.wholesale');

    Route::get('/cotizaciones', [QuotationController::class, 'index'])
        ->middleware('role:administrador,ventas_mayoristas,cliente_mayorista')->name('quotations.index');
    Route::post('/cotizaciones', [QuotationController::class, 'store'])
        ->middleware('role:administrador,ventas_mayoristas,cliente_mayorista')->name('quotations.store');
});

Route::prefix('gestion')->middleware(['auth', 'role:administrador'])->name('management.')->group(function (): void {
    Route::get('/', ManagementDashboardController::class)->name('dashboard');

    Route::get('/productos', [ProductManagementController::class, 'index'])->name('products');
    Route::post('/productos', [ProductManagementController::class, 'store'])->name('products.store');
    Route::put('/productos/{product}', [ProductManagementController::class, 'update'])->whereNumber('product')->name('products.update');
    Route::delete('/productos/{product}', [ProductManagementController::class, 'deactivate'])->whereNumber('product')->name('products.deactivate');

    Route::get('/usuarios', [UserManagementController::class, 'index'])->name('users');
    Route::post('/usuarios', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/auditoria', [AuditController::class, 'index'])->name('audit');

    Route::get('/categorias', [CategoryController::class, 'index'])->name('categories');
    Route::post('/categorias', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categorias/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categorias/{category}', [CategoryController::class, 'deactivate'])->name('categories.deactivate');

    Route::get('/clientes', [CustomerController::class, 'index'])->name('customers');
    Route::post('/clientes', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/clientes/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/clientes/{customer}', [CustomerController::class, 'deactivate'])->name('customers.deactivate');

    Route::get('/pedidos', [OrderController::class, 'index'])->name('orders');
    Route::get('/cuenta', [AccountController::class, 'dashboard'])->name('account');
    Route::put('/cuenta/perfil', [\App\Http\Controllers\AdminAccountController::class, 'updateProfile'])->name('account.profile');
    Route::put('/cuenta/password', [\App\Http\Controllers\AdminAccountController::class, 'updatePassword'])->name('account.password');
});

Route::get('/gestion/publicidad-inteligente', [CommerceIntelligenceController::class, 'ads'])
    ->middleware(['auth', 'role:administrador,marketing'])->name('smart.ads');

Route::prefix('gestion')->middleware(['auth', 'role:administrador,compras_logistica,ventas_minoristas'])->name('management.')->group(function (): void {
    Route::get('/inventario', [InventoryController::class, 'index'])->name('inventory');
    Route::post('/inventario/{product}', [InventoryController::class, 'adjust'])->whereNumber('product')->name('inventory.adjust');
});


Route::prefix('gestion')
    ->middleware(['auth', 'role:administrador,compras_logistica'])
    ->name('management.')
    ->group(function (): void {

        Route::get('/proveedores', [SupplierController::class, 'index'])
            ->name('suppliers');

        Route::post('/proveedores', [SupplierController::class, 'store'])
            ->name('suppliers.store');

        Route::put('/proveedores/{supplier}', [SupplierController::class, 'update'])
            ->name('suppliers.update');

        Route::delete('/proveedores/{supplier}', [SupplierController::class, 'deactivate'])
            ->name('suppliers.deactivate');

        // DAVID - Reactivar proveedores inactivos
        Route::patch('/proveedores/{supplier}/activar', [SupplierController::class, 'activate'])
            ->name('suppliers.activate');

        Route::get('/compras', [PurchaseController::class, 'index'])
            ->name('purchases');

        Route::post('/compras', [PurchaseController::class, 'store'])
            ->name('purchases.store');
    });


Route::prefix('gestion')->middleware(['auth', 'role:administrador,marketing'])->name('management.')->group(function (): void {
    Route::get('/campanas', [CampaignController::class, 'index'])->name('campaigns');
    Route::post('/campanas', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::put('/campanas/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
    Route::delete('/campanas/{campaign}', [CampaignController::class, 'deactivate'])->name('campaigns.deactivate');
    Route::get('/cupones', [CouponController::class, 'index'])->name('coupons');
    Route::post('/cupones', [CouponController::class, 'store'])->name('coupons.store');
    Route::get('/segmentos', [CustomerSegmentController::class, 'index'])->name('segments');
    Route::post('/segmentos', [CustomerSegmentController::class, 'store'])->name('segments.store');
    Route::get('/reportes', [ReportController::class, 'index'])->name('reports');
});

/*
|--------------------------------------------------------------------------
| Compatibility entry points
|--------------------------------------------------------------------------
| These aliases keep bookmarks and integrations from the previous application
| working while the canonical implementation remains in the routes above.
*/
Route::get('/catalog', [CatalogController::class, 'index'])->name('legacy.catalog');
Route::get('/products/{product}', [CatalogController::class, 'show'])->whereNumber('product')->name('legacy.product');
Route::get('/smart/recommend', [RecommendationController::class, 'index'])->name('legacy.recommendations');
Route::get('/smart/compare', [ComparisonController::class, 'index'])->name('legacy.comparison');
Route::get('/smart/optimizer', [CommerceIntelligenceController::class, 'optimizer'])->name('legacy.optimizer');
Route::get('/smart/ads', [CommerceIntelligenceController::class, 'ads'])
    ->middleware(['auth', 'role:administrador,marketing'])->name('legacy.ads');
Route::get('/about', [PageController::class, 'about'])->name('legacy.about');
Route::get('/contact', [PageController::class, 'contact'])->name('legacy.contact');
Route::post('/contact', [PageController::class, 'sendContact'])->middleware('throttle:contact')->name('legacy.contact.store');
Route::get('/login', [AuthController::class, 'loginForm'])->middleware('guest')->name('legacy.login');
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:login'])->name('legacy.login.store');
Route::get('/register', [AuthController::class, 'registerForm'])->middleware('guest')->name('legacy.register');
Route::post('/register', [AuthController::class, 'register'])->middleware(['guest', 'throttle:register'])->name('legacy.register.store');
Route::get('/logout', fn (): \Illuminate\Http\RedirectResponse => redirect()->route('login'))->name('legacy.logout.form');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('legacy.logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/account', [AccountController::class, 'dashboard'])->name('legacy.account');
    Route::get('/cart', [CartController::class, 'index'])->name('legacy.cart');
    Route::post('/cart', [CartController::class, 'updateLegacy'])->name('legacy.cart.update');
    Route::get('/mayorista', [CommerceIntelligenceController::class, 'wholesale'])
        ->middleware('role:administrador,ventas_mayoristas,cliente_mayorista')->name('legacy.wholesale');
});

Route::get('/smart/assistant', [AssistantController::class, 'index'])->name('legacy.assistant');
Route::post('/smart/assistant/reply', [AssistantController::class, 'ask'])->middleware('throttle:10,1')->name('legacy.assistant.reply');

Route::prefix('admin')->middleware(['auth', 'role:administrador'])->name('legacy.admin.')->group(function (): void {
    Route::get('/', ManagementDashboardController::class)->name('dashboard');
    Route::get('/account', [AccountController::class, 'dashboard'])->name('account');
    Route::post('/account/profile', [\App\Http\Controllers\AdminAccountController::class, 'updateProfile'])->name('account.profile');
    Route::post('/account/password', [\App\Http\Controllers\AdminAccountController::class, 'updatePassword'])->name('account.password');
    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns');
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::post('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
    Route::post('/campaigns/{campaign}/deactivate', [CampaignController::class, 'deactivate'])->name('campaigns.deactivate');
    Route::get('/products', [ProductManagementController::class, 'index'])->name('products');
    Route::post('/products', [ProductManagementController::class, 'store'])->name('products.store');
    Route::match(['post', 'put'], '/products/{product}', [ProductManagementController::class, 'update'])->name('products.update');
    Route::post('/products/{product}/deactivate', [ProductManagementController::class, 'deactivate'])->name('products.deactivate');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order')->name('orders.show');
    Route::match(['post', 'patch'], '/orders/{order}/status', [OrderController::class, 'updateStatus'])->whereNumber('order')->name('orders.status');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::match(['post', 'put'], '/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::post('/categories/{category}/deactivate', [CategoryController::class, 'deactivate'])->name('categories.deactivate');
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::post('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::post('/suppliers/{supplier}/deactivate', [SupplierController::class, 'deactivate'])->name('suppliers.deactivate');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory', [InventoryController::class, 'adjustLegacy'])->name('inventory.store');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/audit', [AuditController::class, 'index'])->name('audit');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/panel', [AccountController::class, 'dashboard'])->name('legacy.panel');
    Route::get('/panel/{module}', [AccountController::class, 'dashboard'])->name('legacy.panel.module');
});
