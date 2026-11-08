<?php

use App\Http\Controllers\Auth\AutenticacionController;
use App\Http\Controllers\Cuenta\CuentaAdministracionController;
use App\Http\Controllers\Cuenta\CuentaController;
use App\Http\Controllers\Gestion\CajaController;
use App\Http\Controllers\Gestion\Campanias\CampaniaController;
use App\Http\Controllers\Gestion\Campanias\PublicidadInteligenteController;
use App\Http\Controllers\Gestion\Categorias\CategoriaController;
use App\Http\Controllers\Gestion\Clientes\ClienteController;
use App\Http\Controllers\Gestion\Compras\CompraController;
use App\Http\Controllers\Gestion\ConfiguracionSistemaController;
use App\Http\Controllers\Gestion\Cotizaciones\CotizacionController;
use App\Http\Controllers\Gestion\Cupones\CuponController;
use App\Http\Controllers\Gestion\DevolucionesGarantias\DevolucionGarantiaController;
use App\Http\Controllers\Gestion\FacturacionController;
use App\Http\Controllers\Gestion\Finanzas\CuentasFinancierasController;
use App\Http\Controllers\Gestion\FinanzasController;
use App\Http\Controllers\Gestion\Inventario\InventarioController;
use App\Http\Controllers\Gestion\Inventario\TransferenciaExistenciaController;
use App\Http\Controllers\Gestion\Organizacion\AlmacenController;
use App\Http\Controllers\Gestion\Organizacion\EmpresaController;
use App\Http\Controllers\Gestion\Organizacion\PersonalSedeController;
use App\Http\Controllers\Gestion\Organizacion\ResponsableOrganizacionalController;
use App\Http\Controllers\Gestion\Organizacion\SedeController;
use App\Http\Controllers\Gestion\Productos\GestionProductosController;
use App\Http\Controllers\Gestion\Proveedores\ProveedorController;
use App\Http\Controllers\Gestion\Reportes\PanelAnalisisAuditoriaController;
use App\Http\Controllers\Gestion\Reportes\ReporteController;
use App\Http\Controllers\Gestion\Segmentos\SegmentoClienteController;
use App\Http\Controllers\Gestion\Seguridad\AsignacionRolesController;
use App\Http\Controllers\Gestion\Seguridad\MenusController;
use App\Http\Controllers\Gestion\Seguridad\RolesPermisosController;
use App\Http\Controllers\Gestion\SesionCajaController;
use App\Http\Controllers\Gestion\SoporteReclamos\SoporteReclamoController;
use App\Http\Controllers\Gestion\Usuarios\GestionUsuariosController;
use App\Http\Controllers\Gestion\Ventas\VentaController;
use App\Http\Controllers\Paneles\Administrador\AlertasGlobalesController;
use App\Http\Controllers\Paneles\Administrador\DashboardController as AdministradorDashboardController;
use App\Http\Controllers\Paneles\Administrador\DashboardGlobalPedidosController;
use App\Http\Controllers\Paneles\Administrador\DashboardGlobalSedesController;
use App\Http\Controllers\Paneles\Administrador\DashboardGlobalVentasController;
use App\Http\Controllers\Paneles\Administrador\DispositivosController;
use App\Http\Controllers\Paneles\Administrador\EnviosPedidosController;
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

Route::middleware(['auth', 'cuenta.activa', 'sesion.usuario'])->group(function (): void {
    Route::post('/catalogo/{product}/favorito', [CatalogoController::class, 'toggleFavorite'])
        ->whereNumber('product')->name('catalog.favorite');
    Route::post('/catalogo/{product}/resena', [CatalogoController::class, 'storeReview'])
        ->whereNumber('product')->name('catalog.review.store');
});
Route::post('/campanas/{campaign}/evento', [CampaniaPublicaController::class, 'track'])
    ->whereNumber('campaign')->middleware('throttle:60,1')->name('campaigns.track');
Route::get('/nosotros', [PaginaController::class, 'about'])->name('pages.about');
Route::get('/contacto', [PaginaController::class, 'contact'])->name('pages.contact');
Route::post('/contacto', [PaginaController::class, 'sendContact'])->middleware('throttle:contact')->name('pages.contact.store');
Route::get('/libro-de-reclamaciones', [PaginaController::class, 'claimsBook'])->name('pages.claims');
Route::get('/politicas-privacidad', [PaginaController::class, 'privacyPolicy'])->name('pages.privacy');
Route::get('/terminos-condiciones', [PaginaController::class, 'terms'])->name('pages.terms');
Route::get('/preguntas-frecuentes', [PaginaController::class, 'frequentlyAskedQuestions'])->name('pages.faq');

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

Route::middleware(['auth', 'cuenta.activa', 'sesion.usuario'])->group(function (): void {
    Route::get('/mi-cuenta', [CuentaController::class, 'dashboard'])->name('dashboard');
    Route::get('/mi-cuenta/perfil', [CuentaController::class, 'profile'])->name('account.profile.show');
    Route::put('/mi-cuenta/perfil', [CuentaAdministracionController::class, 'updateProfile'])->name('account.profile');
    Route::put('/mi-cuenta/password', [CuentaAdministracionController::class, 'updatePassword'])->name('account.password');

    Route::get('/carrito', [CarritoController::class, 'index'])->name('cart.index');
    Route::post('/carrito/{product}/agregar', [CarritoController::class, 'add'])
        ->whereNumber('product')->middleware('carrito.ecommerce')->name('cart.add');
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
        ->middleware('permiso:gestion.cotizaciones.consultar')->name('smart.wholesale');

    Route::get('/cotizaciones', [CotizacionController::class, 'index'])
        ->middleware('permiso:gestion.cotizaciones.consultar')->name('quotations.index');
    Route::post('/cotizaciones', [CotizacionController::class, 'store'])
        ->middleware('permiso:gestion.cotizaciones.crear')->name('quotations.store');
});

Route::get('/gestion/configuracion-comercial', static fn (): RedirectResponse => redirect()->route('management.products.preview.store_settings'))
    ->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])
    ->name('management.commercial_settings');

Route::get('/gestion/analitica-clientes', static fn (): RedirectResponse => redirect()->route('management.reports', ['vista' => 'clientes']))
    ->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])
    ->name('management.customer_analytics');
Route::get('/gestion/metricas-marketing', static fn (): RedirectResponse => redirect()->route('management.reports', ['vista' => 'campanas']))
    ->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])
    ->name('management.marketing_metrics');
Route::get('/gestion/reportes-financieros', static fn (): RedirectResponse => redirect()->route('management.reports', ['vista' => 'ventas']))
    ->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])
    ->name('management.financial_reports');

Route::prefix('gestion')->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('management.')->group(function (): void {
    Route::get('/', AdministradorDashboardController::class)->name('dashboard');
    Route::get('/dashboard-global/ventas', [DashboardGlobalVentasController::class, 'index'])
        ->middleware('permiso:ruta')->name('dashboard.sales');
    Route::get('/dashboard-global/ventas/exportar/{format}', [DashboardGlobalVentasController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->middleware('permiso:ruta')->name('reports.dashboard_sales.export');
    Route::get('/dashboard-global/pedidos', [DashboardGlobalPedidosController::class, 'index'])
        ->middleware('permiso:ruta')->name('dashboard.orders');
    Route::get('/dashboard-global/pedidos/exportar/{format}', [DashboardGlobalPedidosController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->middleware('permiso:ruta')->name('reports.dashboard_orders.export');
    Route::get('/dashboard-global/sedes', [DashboardGlobalSedesController::class, 'index'])
        ->middleware('permiso:ruta')->name('dashboard.sites');
    Route::get('/dashboard-global/sedes/exportar/{format}', [DashboardGlobalSedesController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->middleware('permiso:ruta')->name('reports.dashboard_sites.export');
    Route::get('/dashboard-global/alertas', [AlertasGlobalesController::class, 'mostrar'])
        ->middleware('permiso:ruta')->name('dashboard.alerts');
    Route::get('/dashboard-global/alertas/exportar/{format}', [AlertasGlobalesController::class, 'exportar'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->middleware('permiso:ruta')->name('reports.dashboard_alerts.export');
    Route::get('/organizacion/empresa', [EmpresaController::class, 'index'])
        ->middleware(['permiso:ruta', 'permiso:ruta'])->name('company_settings.data');
    Route::put('/organizacion/empresa', [EmpresaController::class, 'update'])
        ->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.data.update');
    Route::get('/organizacion/sedes', [SedeController::class, 'index'])
        ->middleware(['permiso:ruta', 'permiso:ruta'])->name('company_settings.sites');
    Route::post('/organizacion/sedes', [SedeController::class, 'store'])
        ->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.sites.store');
    Route::put('/organizacion/sedes/{sede}', [SedeController::class, 'update'])
        ->whereNumber('sede')->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.sites.update');
    Route::patch('/organizacion/sedes/{sede}/estado', [SedeController::class, 'cambiarEstado'])
        ->whereNumber('sede')->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.sites.status');
    Route::get('/organizacion/almacenes', [AlmacenController::class, 'index'])
        ->middleware(['permiso:ruta', 'permiso:ruta'])->name('company_settings.warehouses');
    Route::post('/organizacion/almacenes', [AlmacenController::class, 'store'])
        ->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.warehouses.store');
    Route::put('/organizacion/almacenes/{almacen}', [AlmacenController::class, 'update'])
        ->whereNumber('almacen')->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.warehouses.update');
    Route::patch('/organizacion/almacenes/{almacen}/estado', [AlmacenController::class, 'cambiarEstado'])
        ->whereNumber('almacen')->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.warehouses.status');
    Route::get('/organizacion/personal', [PersonalSedeController::class, 'listar'])
        ->middleware(['permiso:ruta', 'permiso:ruta', 'permiso:pantalla.admin.staff.consultar'])->name('company_settings.staff');
    Route::get('/organizacion/responsables', [ResponsableOrganizacionalController::class, 'index'])
        ->middleware(['permiso:ruta', 'permiso:ruta', 'permiso:pantalla.admin.staff.consultar', 'permiso:pantalla.admin.responsibles.consultar'])->name('company_settings.responsibles');
    Route::post('/organizacion/responsables', [ResponsableOrganizacionalController::class, 'store'])
        ->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar', 'permiso:pantalla.admin.responsibles.consultar'])->name('company_settings.responsibles.store');
    Route::get('/cotizaciones', [CotizacionController::class, 'index'])->name('quotations');
    Route::get('/cotizaciones/exportar/{format}', [CotizacionController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('quotations.export.list');
    Route::get('/cotizaciones/{quotation}/descargar', [CotizacionController::class, 'descargar'])->whereNumber('quotation')->name('quotations.export');
    Route::post('/cotizaciones/{quotation}/envio-manual', [CotizacionController::class, 'registrarEnvioManual'])->whereNumber('quotation')->name('quotations.update.send');
    Route::post('/cotizaciones/{quotation}/aprobar', [CotizacionController::class, 'aprobar'])->whereNumber('quotation')->name('quotations.approve');
    Route::put('/cotizaciones/{quotation}', [CotizacionController::class, 'update'])->whereNumber('quotation')->name('quotations.update');
    Route::delete('/cotizaciones/{quotation}', [CotizacionController::class, 'deactivate'])->whereNumber('quotation')->name('quotations.deactivate');

    Route::get('/productos', [GestionProductosController::class, 'index'])->name('products');
    Route::get('/ecommerce/diseno/precios', [GestionProductosController::class, 'preciosCanales'])
        ->middleware('permiso:ruta')->name('products.preview.prices');
    Route::put('/ecommerce/precios-mayoristas', [GestionProductosController::class, 'guardarPrecioMayorista'])
        ->middleware(['permiso:ruta', 'permiso:gestion.productos.actualizar'])->name('products.wholesale_prices.update');
    Route::post('/ecommerce/precios-mayoristas/importar', [GestionProductosController::class, 'importarPreciosMayoristas'])
        ->middleware(['permiso:ruta', 'permiso:gestion.productos.actualizar'])->name('products.wholesale_prices.import');
    Route::get('/ecommerce/diseno/tienda', [GestionProductosController::class, 'configuracionTienda'])
        ->middleware('permiso:ruta')->name('products.preview.store_settings');
    Route::put('/ecommerce/tienda/configuracion', [GestionProductosController::class, 'guardarConfiguracionTienda'])
        ->middleware(['permiso:ruta', 'permiso:gestion.configuracion.actualizar'])->name('commercial_settings.store.update');
    Route::get('/productos/exportar/{format}', [GestionProductosController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('products.export');
    Route::post('/productos', [GestionProductosController::class, 'store'])->name('products.store');
    Route::post('/productos/{product}/variantes', [GestionProductosController::class, 'registrarVariante'])
        ->whereNumber('product')->name('products.variants.store');
    Route::put('/productos/{product}/variantes/{variante}', [GestionProductosController::class, 'actualizarVariante'])
        ->whereNumber('product')->whereNumber('variante')->name('products.variants.update');
    Route::delete('/productos/{product}/variantes/{variante}', [GestionProductosController::class, 'desactivarVariante'])
        ->whereNumber('product')->whereNumber('variante')->name('products.variants.deactivate');
    Route::post('/productos/{product}/variantes/{variante}/imagenes', [GestionProductosController::class, 'registrarImagenVariante'])
        ->whereNumber('product')->whereNumber('variante')->name('products.variants.images.store');
    Route::delete('/productos/{product}/variantes/{variante}/imagenes/{imagen}', [GestionProductosController::class, 'eliminarImagenVariante'])
        ->whereNumber('product')->whereNumber('variante')->whereNumber('imagen')->name('products.variants.images.delete');
    Route::put('/productos/{product}', [GestionProductosController::class, 'update'])->whereNumber('product')->name('products.update');
    Route::delete('/productos/{product}', [GestionProductosController::class, 'deactivate'])->whereNumber('product')->name('products.deactivate');

    Route::get('/usuarios', [GestionUsuariosController::class, 'index'])->name('users');
    Route::get('/usuarios/exportar/{format}', [GestionUsuariosController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('users.export');
    Route::post('/usuarios', [GestionUsuariosController::class, 'store'])->name('users.store');
    Route::put('/usuarios/{user}', [GestionUsuariosController::class, 'update'])->whereNumber('user')->name('users.update');
    Route::patch('/usuarios/{user}/estado', [GestionUsuariosController::class, 'updateStatus'])->whereNumber('user')->name('users.activate');
    Route::get('/auditoria', [PanelAnalisisAuditoriaController::class, 'operaciones'])->name('audit');
    Route::get('/analisis/diseno/auditoria-operaciones', [PanelAnalisisAuditoriaController::class, 'operaciones'])
        ->middleware('permiso:ruta')->name('audit.preview.operations');

    Route::get('/categorias', [CategoriaController::class, 'index'])->name('categories');
    Route::get('/categorias/exportar/{format?}', [CategoriaController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('categories.export');
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categories.store');
    Route::put('/categorias/{category}', [CategoriaController::class, 'update'])->name('categories.update');
    Route::delete('/categorias/{category}', [CategoriaController::class, 'deactivate'])->name('categories.deactivate');

    Route::get('/clientes', [ClienteController::class, 'index'])->name('customers');
    Route::get('/clientes/exportar/{format}', [ClienteController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('customers.export');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('customers.store');
    Route::post('/clientes/importar', [ClienteController::class, 'import'])->name('customers.import');
    Route::put('/clientes/{customer}', [ClienteController::class, 'update'])->name('customers.update');
    Route::delete('/clientes/{customer}', [ClienteController::class, 'deactivate'])->name('customers.deactivate');

    Route::get('/pedidos', [PedidoController::class, 'index'])->name('orders');
    Route::get('/pedidos/exportar/{format}', [PedidoController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('orders.export');
    Route::patch('/pedidos/{order}/estado', [PedidoController::class, 'updateStatus'])
        ->whereNumber('order')->name('orders.status');
    Route::get('/ventas', [VentaController::class, 'index'])->name('sales');
    Route::get('/ventas/diseno/minoristas', [VentaController::class, 'panelMinorista'])
        ->middleware('permiso:ruta')->name('sales.preview.retail');
    Route::get('/ventas/diseno/mayoristas', [VentaController::class, 'panelMayorista'])
        ->middleware('permiso:ruta')->name('sales.preview.wholesale');
    Route::get('/ventas/exportar/{format?}', [VentaController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('sales.export');
    Route::post('/ventas', [VentaController::class, 'store'])->name('sales.store');
    Route::post('/ventas/cotizaciones/{quotation}/convertir', [VentaController::class, 'convertirCotizacion'])->whereNumber('quotation')->name('sales.convert_quotation');
    Route::post('/ventas/{venta}/confirmar', [VentaController::class, 'confirmarBorrador'])->whereNumber('venta')->name('sales.confirm');
    Route::put('/ventas/{order}', [VentaController::class, 'update'])->whereNumber('order')->name('sales.update');
    Route::post('/ventas/{venta}/anular', [VentaController::class, 'cancel'])->whereNumber('venta')->name('sales.cancel');
    Route::get('/devoluciones-garantias', [DevolucionGarantiaController::class, 'index'])->name('returns');
    Route::get('/devoluciones-garantias/exportar/{format?}', [DevolucionGarantiaController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('returns.export');
    Route::post('/devoluciones-garantias', [DevolucionGarantiaController::class, 'store'])->name('returns.store');
    Route::put('/devoluciones-garantias/{devolucionGarantia}', [DevolucionGarantiaController::class, 'update'])->whereNumber('devolucionGarantia')->name('returns.update');
    Route::delete('/devoluciones-garantias/{devolucionGarantia}', [DevolucionGarantiaController::class, 'destroy'])->whereNumber('devolucionGarantia')->name('returns.destroy');
    Route::get('/soporte-reclamos', [SoporteReclamoController::class, 'index'])->name('support');
    Route::post('/soporte-reclamos', [SoporteReclamoController::class, 'store'])->name('support.store');
    Route::put('/soporte-reclamos/{casoSoporte}', [SoporteReclamoController::class, 'update'])->whereNumber('casoSoporte')->name('support.update');
    Route::patch('/soporte-reclamos/{casoSoporte}/cerrar', [SoporteReclamoController::class, 'deactivate'])->whereNumber('casoSoporte')->name('support.close');
    Route::get('/soporte-reclamos/exportar/{format?}', [SoporteReclamoController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('support.export');
});

Route::middleware(['auth', 'cuenta.activa', 'sesion.usuario'])->group(function (): void {
    Route::get('/gestion/cuenta', [CuentaController::class, 'dashboard'])->name('management.account');
    Route::put('/gestion/cuenta/perfil', [CuentaAdministracionController::class, 'updateProfile'])->name('management.account.profile');
    Route::put('/gestion/cuenta/password', [CuentaAdministracionController::class, 'updatePassword'])->name('management.account.password');
    Route::put('/gestion/cuenta/fotografia', [CuentaAdministracionController::class, 'updateAvatar'])->name('management.account.avatar');
    Route::get('/gestion/cuenta/seguridad', [CuentaAdministracionController::class, 'security'])->name('management.account.security');
    Route::get('/gestion/cuenta/sesiones', [CuentaAdministracionController::class, 'sessions'])->name('management.account.sessions');
    Route::delete('/gestion/cuenta/sesiones/{sessionId}', [CuentaAdministracionController::class, 'revokeSession'])->whereNumber('sessionId')->name('management.account.sessions.revoke');
    Route::get('/gestion/cuenta/preferencias', [CuentaAdministracionController::class, 'preferences'])->name('management.account.preferences');
    Route::put('/gestion/cuenta/preferencias', [CuentaAdministracionController::class, 'updatePreferences'])->name('management.account.preferences.update');
    Route::get('/gestion/cuenta/actividad', [CuentaAdministracionController::class, 'activity'])->name('management.account.activity');
    Route::get('/gestion/cuenta/notificaciones', [CuentaAdministracionController::class, 'notifications'])->name('management.account.notifications');
    Route::patch('/gestion/cuenta/notificaciones/{notificationId}', [CuentaAdministracionController::class, 'markNotificationRead'])->name('management.account.notifications.read');
});

Route::get('/gestion/publicidad-inteligente', [PublicidadInteligenteController::class, 'ads'])
    ->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('smart.ads');

Route::prefix('gestion')->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('management.')->group(function (): void {
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventory');
    Route::get('/inventario/movimientos', [InventarioController::class, 'index'])->name('inventory.movements');
    Route::get('/inventario/diseno/transferencias', [TransferenciaExistenciaController::class, 'index'])->name('inventory.preview.transfers');
    Route::get('/inventario/transferencias/exportar/{format?}', [TransferenciaExistenciaController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('inventory.transfers_export');
    Route::post('/inventario/transferencias', [TransferenciaExistenciaController::class, 'store'])->name('inventory.transfer_request');
    Route::post('/inventario/transferencias/{transfer}/approve', [TransferenciaExistenciaController::class, 'approve'])->whereNumber('transfer')->name('inventory.transfer_approve');
    Route::post('/inventario/transferencias/{transfer}/dispatch', [TransferenciaExistenciaController::class, 'dispatch'])->whereNumber('transfer')->name('inventory.transfer_dispatch');
    Route::post('/inventario/transferencias/{transfer}/receive', [TransferenciaExistenciaController::class, 'receive'])->whereNumber('transfer')->name('inventory.transfer_receive');
    Route::post('/inventario/transferencias/{transfer}/return', [TransferenciaExistenciaController::class, 'returnToOrigin'])->whereNumber('transfer')->name('inventory.transfer_return');
    Route::post('/inventario/transferencias/{transfer}/reconcile', [TransferenciaExistenciaController::class, 'reconcileDifference'])->whereNumber('transfer')->name('inventory.transfer_reconcile');
    Route::post('/inventario/transferencias/{transfer}/reject', [TransferenciaExistenciaController::class, 'reject'])->whereNumber('transfer')->name('inventory.transfer_reject');
    Route::post('/inventario/transferencias/{transfer}/cancel', [TransferenciaExistenciaController::class, 'cancel'])->whereNumber('transfer')->name('inventory.transfer_cancel');
    Route::post('/inventario/transferencias/{transfer}/close', [TransferenciaExistenciaController::class, 'close'])->whereNumber('transfer')->name('inventory.transfer_close');
    Route::get('/inventario/diseno/envios', [EnviosPedidosController::class, 'mostrar'])
        ->middleware(['permiso:ruta', 'permiso:ruta', 'permiso:pantalla.admin.shipments.consultar'])->name('inventory.preview.shipments');
    Route::patch('/inventario/envios/{envioPedido}/estado', [EnviosPedidosController::class, 'actualizarEstado'])
        ->whereNumber('envioPedido')
        ->middleware(['permiso:ruta', 'permiso:ruta'])
        ->name('inventory.shipments.status.update');
    Route::get('/inventario/diseno/envios/{envio}', [EnviosPedidosController::class, 'detalle'])
        ->whereNumber('envio')
        ->middleware(['permiso:ruta', 'permiso:ruta', 'permiso:pantalla.admin.shipments.consultar'])->name('inventory.preview.shipments.show');
    Route::get('/inventario/diseno/envios/exportar/{format?}', [EnviosPedidosController::class, 'exportar'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])
        ->middleware(['permiso:ruta', 'permiso:ruta'])->name('inventory.preview.shipments.export');
    Route::get('/inventario/diseno/series', [DispositivosController::class, 'mostrar'])
        ->middleware(['permiso:ruta', 'permiso:ruta', 'permiso:pantalla.admin.imei.consultar'])->name('inventory.preview.serials');
    Route::post('/inventario/{product}', [InventarioController::class, 'adjust'])->whereNumber('product')->name('inventory.adjust');
});

Route::prefix('gestion')->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('management.')->group(function (): void {
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('suppliers');
    Route::get('/proveedores/exportar/{format?}', [ProveedorController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('suppliers.export');
    Route::post('/proveedores', [ProveedorController::class, 'store'])->name('suppliers.store');
    Route::put('/proveedores/{supplier}', [ProveedorController::class, 'update'])->name('suppliers.update');
    Route::delete('/proveedores/{supplier}', [ProveedorController::class, 'deactivate'])->name('suppliers.deactivate');
    Route::get('/compras', [CompraController::class, 'index'])->name('purchases');
    Route::get('/compras/exportar/{format}', [CompraController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('purchases.export');
    Route::post('/compras', [CompraController::class, 'store'])->name('purchases.store');
    Route::post('/compras/importar', [CompraController::class, 'import'])->name('purchases.import');
    Route::post('/compras/{compra}/aprobar', [CompraController::class, 'approve'])->whereNumber('compra')->name('purchases.approve');
    Route::post('/compras/{compra}/anular', [CompraController::class, 'cancel'])->whereNumber('compra')->name('purchases.cancel');
    Route::post('/compras/{compra}/recibir', [CompraController::class, 'receive'])->whereNumber('compra')->name('purchases.receive');
    Route::post('/compras/{compra}/recepcion-parcial', [CompraController::class, 'recibirParcial'])->whereNumber('compra')->name('purchases.receive_partial');
});

Route::prefix('gestion')->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('management.')->group(function (): void {
    Route::get('/campanas', [CampaniaController::class, 'index'])->name('campaigns');
    Route::get('/campanas/exportar', [CampaniaController::class, 'export'])->name('campaigns.export');
    Route::post('/campanas', [CampaniaController::class, 'store'])->name('campaigns.store');
    Route::post('/campanas/importar', [CampaniaController::class, 'import'])->name('campaigns.import');
    Route::put('/campanas/{campaign}', [CampaniaController::class, 'update'])->name('campaigns.update');
    Route::delete('/campanas/{campaign}', [CampaniaController::class, 'deactivate'])->name('campaigns.deactivate');
    Route::get('/cupones', [CuponController::class, 'index'])->name('coupons');
    Route::get('/cupones/exportar/{format?}', [CuponController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('coupons.export');
    Route::post('/cupones', [CuponController::class, 'store'])->name('coupons.store');
    Route::put('/cupones/{coupon}', [CuponController::class, 'update'])->whereNumber('coupon')->name('coupons.update');
    Route::delete('/cupones/{coupon}', [CuponController::class, 'deactivate'])->whereNumber('coupon')->name('coupons.deactivate');
    Route::get('/segmentos', [SegmentoClienteController::class, 'index'])->name('segments');
    Route::get('/segmentos/exportar/{format?}', [SegmentoClienteController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('segments.export');
    Route::post('/segmentos', [SegmentoClienteController::class, 'store'])->name('segments.store');
    Route::put('/segmentos/{segment}', [SegmentoClienteController::class, 'update'])->whereNumber('segment')->name('segments.update');
    Route::delete('/segmentos/{segment}', [SegmentoClienteController::class, 'deactivate'])->whereNumber('segment')->name('segments.deactivate');
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reports');
    Route::get('/analisis/diseno/reportes-globales', [PanelAnalisisAuditoriaController::class, 'globales'])
        ->middleware('permiso:ruta')->name('reports.preview.global');
    Route::get('/analisis/diseno/{tipo}/exportar/{format?}', [PanelAnalisisAuditoriaController::class, 'exportar'])
        ->whereIn('tipo', ['globales', 'operaciones'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])
        ->middleware('permiso:ruta')->name('reports.preview.export');
    Route::get('/reportes/exportar/{format?}', [ReporteController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('reports.export');
});

Route::prefix('gestion')->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('management.')->group(function (): void {
    Route::get('/pagos', [FinanzasController::class, 'pagos'])->name('payments');
    Route::get('/pagos/exportar/{format}', [FinanzasController::class, 'exportarPagos'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('payments.export');
    Route::post('/pagos', [FinanzasController::class, 'registrarPago'])->name('payments.store');
    Route::get('/pagos/{pago}', [FinanzasController::class, 'detallePago'])->whereNumber('pago')->name('payments.show');
    Route::patch('/pagos/{pago}/anular', [FinanzasController::class, 'anularPago'])->whereNumber('pago')->name('payments.cancel');
    Route::patch('/pagos/{pago}/confirmar-efectivo', [FinanzasController::class, 'confirmarPagoEfectivo'])->whereNumber('pago')->name('payments.cash.update');
    Route::get('/facturacion', [FacturacionController::class, 'index'])->name('billing');
    Route::get('/facturacion/exportar/{format}', [FacturacionController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('billing.export');
    Route::get('/reembolsos', [FinanzasController::class, 'reembolsos'])->name('refunds');
    Route::get('/reembolsos/exportar/{format}', [FinanzasController::class, 'exportarReembolsos'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('refunds.export');
    Route::post('/reembolsos', [FinanzasController::class, 'solicitarReembolso'])->name('refunds.store');
    Route::get('/reembolsos/{reembolso}', [FinanzasController::class, 'detalleReembolso'])->whereNumber('reembolso')->name('refunds.show');
    Route::patch('/reembolsos/{reembolso}/aprobar', [FinanzasController::class, 'aprobarReembolso'])->whereNumber('reembolso')->name('refunds.approve');
    Route::patch('/reembolsos/{reembolso}/anular', [FinanzasController::class, 'anularReembolso'])->whereNumber('reembolso')->name('refunds.cancel');
    Route::get('/finanzas/diseno/pagos', static fn () => redirect()->route('management.payments'))->middleware('permiso:ruta')->name('payments.preview.receipts');
    Route::get('/finanzas/diseno/cajas', [CajaController::class, 'index'])->middleware('permiso:ruta')->name('payments.preview.cash_registers');
    Route::post('/finanzas/cajas', [CajaController::class, 'store'])->middleware('permiso:ruta')->name('payments.cash_registers.store');
    Route::put('/finanzas/cajas/{caja}', [CajaController::class, 'update'])->whereNumber('caja')->middleware('permiso:ruta')->name('payments.cash_registers.update');
    Route::patch('/finanzas/cajas/{caja}/estado', [CajaController::class, 'updateStatus'])->whereNumber('caja')->middleware('permiso:ruta')->name('payments.cash_registers.status.update');
    Route::get('/finanzas/diseno/aperturas-cierres', [SesionCajaController::class, 'index'])->middleware('permiso:ruta')->name('payments.preview.cash_sessions');
    Route::post('/finanzas/sesiones-caja', [SesionCajaController::class, 'store'])->middleware('permiso:ruta')->name('payments.cash_sessions.store');
    Route::get('/finanzas/sesiones-caja/{sesionCaja}', [SesionCajaController::class, 'show'])->whereNumber('sesionCaja')->middleware('permiso:ruta')->name('payments.cash_sessions.show');
    Route::post('/finanzas/sesiones-caja/{sesionCaja}/movimientos', [SesionCajaController::class, 'storeMovement'])->whereNumber('sesionCaja')->middleware('permiso:ruta')->name('payments.cash_sessions.movements.store');
    Route::patch('/finanzas/sesiones-caja/{sesionCaja}', [SesionCajaController::class, 'update'])->whereNumber('sesionCaja')->middleware('permiso:ruta')->name('payments.cash_sessions.update');
    Route::get('/finanzas/diseno/cuentas', [CuentasFinancierasController::class, 'index'])->middleware('permiso:ruta')->name('payments.preview.accounts');
    Route::post('/finanzas/cuentas-por-pagar', [CuentasFinancierasController::class, 'crearCuentaPorPagar'])->name('payments.accounts.payables.store');
    Route::post('/finanzas/cuentas-por-pagar/aplicar-pago', [CuentasFinancierasController::class, 'aplicarPago'])
        ->name('payments.accounts.payables.update');
    Route::post('/finanzas/cuentas-por-cobrar/aplicar-cobro', [CuentasFinancierasController::class, 'aplicarCobro'])
        ->name('payments.accounts.receivables.update');
    Route::get('/finanzas/diseno/reembolsos', static fn () => redirect()->route('management.refunds'))->middleware('permiso:ruta')->name('refunds.preview');
    Route::get('/roles-permisos', [RolesPermisosController::class, 'index'])->name('roles_permissions');
    Route::get('/matriz-privilegios', [RolesPermisosController::class, 'matrix'])->name('permissions_matrix');
    Route::post('/roles-permisos', [RolesPermisosController::class, 'store'])->name('roles_permissions.store');
    Route::put('/roles-permisos/{role}', [RolesPermisosController::class, 'update'])->whereNumber('role')->name('roles_permissions.update');
    Route::patch('/roles-permisos/{role}/estado', [RolesPermisosController::class, 'updateStatus'])->whereNumber('role')->name('roles_permissions.status');
    Route::put('/roles-permisos/{role}/permisos', [RolesPermisosController::class, 'updatePermissions'])->whereNumber('role')->name('roles_permissions.permissions');
    Route::get('/asignaciones-roles', [AsignacionRolesController::class, 'index'])->name('user_assignments');
    Route::post('/usuarios/{user}/roles', [AsignacionRolesController::class, 'store'])->whereNumber('user')->name('user_assignments.store');
    Route::put('/usuarios/{user}/roles', [AsignacionRolesController::class, 'sync'])->whereNumber('user')->name('user_assignments.sync');
    Route::delete('/usuarios/{user}/roles/{role}', [AsignacionRolesController::class, 'revokeGlobal'])->whereNumber('user')->whereNumber('role')->name('user_assignments.revoke');
    Route::delete('/usuarios/{user}/roles/{role}/sedes/{site}', [AsignacionRolesController::class, 'revokeAtSite'])->whereNumber('user')->whereNumber('role')->whereNumber('site')->name('user_assignments.revoke_site');
    Route::get('/menus', [MenusController::class, 'index'])->name('menus');
    Route::patch('/menus/{menu}', [MenusController::class, 'updateMenu'])->whereNumber('menu')->name('menus.update');
    Route::put('/menus/{menu}/roles', [MenusController::class, 'updateRoles'])->whereNumber('menu')->name('menus.roles');
    Route::patch('/menus/elementos/{element}', [MenusController::class, 'updateElement'])->whereNumber('element')->name('menus.elements.update');
    Route::get('/modulos', [MenusController::class, 'modules'])->name('modules');
    Route::patch('/modulos/{module}', [MenusController::class, 'updateModule'])->whereNumber('module')->name('modules.update');
    Route::patch('/pantallas/{screen}', [MenusController::class, 'updateScreen'])->whereNumber('screen')->name('modules.screens.update');
    Route::get('/notificaciones', [ConfiguracionSistemaController::class, 'notificaciones'])->name('notifications');
    Route::get('/integraciones', [ConfiguracionSistemaController::class, 'integraciones'])->name('integrations');
    Route::post('/integraciones', [ConfiguracionSistemaController::class, 'guardarIntegracion'])->name('integrations.store');
    Route::put('/integraciones/{integracion}', [ConfiguracionSistemaController::class, 'editarIntegracion'])
        ->whereNumber('integracion')->name('integrations.update');
    Route::patch('/integraciones/{integracion}/estado', [ConfiguracionSistemaController::class, 'actualizarIntegracion'])
        ->whereNumber('integracion')->name('integrations.status.update');
    Route::get('/respaldos-mantenimiento', [ConfiguracionSistemaController::class, 'respaldos'])->name('backups');
    Route::post('/respaldos-mantenimiento', [ConfiguracionSistemaController::class, 'crearRespaldo'])->name('backups.store');
    Route::get('/respaldos-mantenimiento/{archivo}/descargar', [ConfiguracionSistemaController::class, 'descargarRespaldo'])
        ->where('archivo', '[A-Za-z0-9_.-]+')
        ->name('backups.download');
    Route::get('/configuracion-empresa', static fn (): RedirectResponse => redirect()->route('management.company_settings.data'))->name('company_settings');
    Route::get('/configuracion-seguridad', [ConfiguracionSistemaController::class, 'seguridad'])->name('security_settings');
    Route::get('/configuracion-general', [ConfiguracionSistemaController::class, 'general'])->name('general_settings');
    Route::get('/configuracion/diseno/general', [ConfiguracionSistemaController::class, 'general'])
        ->middleware('permiso:ruta')->name('general_settings.preview');
    Route::get('/configuracion/diseno/integraciones', [ConfiguracionSistemaController::class, 'integraciones'])
        ->middleware('permiso:ruta')->name('integrations.preview');
    Route::get('/configuracion/diseno/notificaciones', [ConfiguracionSistemaController::class, 'notificaciones'])
        ->middleware('permiso:ruta')->name('notifications.preview');
    Route::get('/configuracion/diseno/seguridad', [ConfiguracionSistemaController::class, 'seguridad'])
        ->middleware('permiso:ruta')->name('security_settings.preview');
    Route::get('/configuracion/diseno/respaldos', [ConfiguracionSistemaController::class, 'respaldos'])
        ->middleware('permiso:ruta')->name('backups.preview');
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
    ->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('legacy.ads');
Route::get('/about', [PaginaController::class, 'about'])->name('legacy.about');
Route::get('/contact', [PaginaController::class, 'contact'])->name('legacy.contact');
Route::post('/contact', [PaginaController::class, 'sendContact'])->middleware('throttle:contact')->name('legacy.contact.store');
Route::get('/login', [AutenticacionController::class, 'loginForm'])->middleware('guest')->name('legacy.login');
Route::post('/login', [AutenticacionController::class, 'login'])->middleware(['guest', 'throttle:login'])->name('legacy.login.store');
Route::get('/register', [AutenticacionController::class, 'registerForm'])->middleware('guest')->name('legacy.register');
Route::post('/register', [AutenticacionController::class, 'register'])->middleware(['guest', 'throttle:register'])->name('legacy.register.store');
Route::get('/logout', fn (): RedirectResponse => redirect()->route('login'))->name('legacy.logout.form');
Route::post('/logout', [AutenticacionController::class, 'logout'])->middleware('auth')->name('legacy.logout');

Route::middleware(['auth', 'cuenta.activa', 'sesion.usuario'])->group(function (): void {
    Route::get('/account', [CuentaController::class, 'dashboard'])->name('legacy.account');
    Route::get('/cart', [CarritoController::class, 'index'])->name('legacy.cart');
    Route::post('/cart', [CarritoController::class, 'updateLegacy'])->name('legacy.cart.update');
    Route::get('/mayorista', [InteligenciaComercialController::class, 'wholesale'])
        ->middleware('permiso:ruta')->name('legacy.wholesale');
});

Route::get('/smart/assistant', [AsistenteController::class, 'index'])->name('legacy.assistant');
Route::post('/smart/assistant/reply', [AsistenteController::class, 'ask'])->middleware('throttle:10,1')->name('legacy.assistant.reply');

Route::prefix('admin')->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('legacy.admin.')->group(function (): void {
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
    Route::get('/audit', [PanelAnalisisAuditoriaController::class, 'operaciones'])->name('audit');
});

Route::middleware(['auth', 'cuenta.activa', 'sesion.usuario'])->group(function (): void {
    Route::get('/panel', [CuentaController::class, 'dashboard'])->name('legacy.panel');
    Route::get('/panel/{module}', [CuentaController::class, 'dashboard'])->name('legacy.panel.module');
});
