<?php

/*


modulos
  @MODULO 01  Inicio y catálogo público
  @MODULO 05  Inicio de sesión y registro
  @MODULO 08  Carrito y checkout
  @MODULO 11  Panel de gestión privado
  @MODULO 14  Cotizaciones
  @MODULO 15  Productos y variantes
  @MODULO 21  Ventas
  @MODULO 26  Inventario
  @MODULO 28  Envíos
  @MODULO 30  Compras y proveedores
  @MODULO 32  Finanzas
  @MODULO 33  Roles, permisos y asignaciones (RBAC)
  @MODULO 35  Configuración y respaldos
  @MODULO 36  Enlaces de compatibilidad (legacy)


*/

// @MODULO 00 | IMPORTACIÓN DE CONTROLADORES
// Las clases importadas permiten conectar una URL con un método del controlador.

// Rutas web de la aplicación.
use App\Http\Controllers\Auth\AutenticacionController;
use App\Http\Controllers\Cuenta\Perfil\CuentaAdministracionController;
use App\Http\Controllers\Cuenta\Perfil\CuentaController;
use App\Http\Controllers\Gestion\Administracion\Organizacion\EmpresaController;
use App\Http\Controllers\Gestion\Administracion\Organizacion\SedeController;
use App\Http\Controllers\Gestion\Administracion\Seguridad\AsignacionRolesController;
use App\Http\Controllers\Gestion\Administracion\Seguridad\GestionUsuariosController;
use App\Http\Controllers\Gestion\Administracion\Seguridad\MenusController;
use App\Http\Controllers\Gestion\Administracion\Seguridad\PersonalSedeController;
use App\Http\Controllers\Gestion\Administracion\Seguridad\ResponsableOrganizacionalController;
use App\Http\Controllers\Gestion\Administracion\Seguridad\RolesPermisosController;
use App\Http\Controllers\Gestion\Analitica\AuditoriaOperaciones\PanelAnalisisAuditoriaController;
use App\Http\Controllers\Gestion\Analitica\DashboardGlobal\AlertasGlobalesController;
use App\Http\Controllers\Gestion\Analitica\DashboardGlobal\DashboardGlobalPedidosController;
use App\Http\Controllers\Gestion\Analitica\DashboardGlobal\DashboardGlobalSedesController;
use App\Http\Controllers\Gestion\Analitica\DashboardGlobal\DashboardGlobalVentasController;
use App\Http\Controllers\Gestion\Analitica\Reportes\ReporteController;
use App\Http\Controllers\Gestion\Comercial\Clientes\ClienteController;
use App\Http\Controllers\Gestion\Comercial\Compras\CompraController;
use App\Http\Controllers\Gestion\Comercial\Cotizaciones\CotizacionController;
use App\Http\Controllers\Gestion\Comercial\DevolucionesGarantias\DevolucionGarantiaController;
use App\Http\Controllers\Gestion\Comercial\Proveedores\ProveedorController;
use App\Http\Controllers\Gestion\Comercial\Ventas\VentaController;
use App\Http\Controllers\Gestion\Configuracion\ConfiguracionSistemaController;
use App\Http\Controllers\Gestion\EcommerceMarketing\Campanas\CampaniaController;
use App\Http\Controllers\Gestion\EcommerceMarketing\Campanas\PublicidadInteligenteController;
use App\Http\Controllers\Gestion\EcommerceMarketing\PromocionesCupones\CuponController;
use App\Http\Controllers\Gestion\EcommerceMarketing\Segmentos\SegmentoClienteController;
use App\Http\Controllers\Gestion\Finanzas\AperturasCierres\SesionCajaController;
use App\Http\Controllers\Gestion\Finanzas\Cajas\CajaController;
use App\Http\Controllers\Gestion\Finanzas\Comprobantes\FacturacionController;
use App\Http\Controllers\Gestion\Finanzas\CuentasCobrarPagar\CuentasFinancierasController;
use App\Http\Controllers\Gestion\Finanzas\FinanzasController;
use App\Http\Controllers\Gestion\InventarioLogistica\Almacenes\AlmacenController;
use App\Http\Controllers\Gestion\InventarioLogistica\Categorias\CategoriaController;
use App\Http\Controllers\Gestion\InventarioLogistica\EnviosEntregas\EnviosPedidosController;
use App\Http\Controllers\Gestion\InventarioLogistica\ExistenciasMovimientos\InventarioController;
use App\Http\Controllers\Gestion\InventarioLogistica\Marcas\MarcaController;
use App\Http\Controllers\Gestion\InventarioLogistica\Productos\GestionProductosController;
use App\Http\Controllers\Gestion\InventarioLogistica\Series\DispositivosController;
use App\Http\Controllers\Gestion\InventarioLogistica\Transferencias\TransferenciaExistenciaController;
use App\Http\Controllers\Gestion\Soporte\Reclamos\SoporteReclamoController;
use App\Http\Controllers\Inteligente\Asistente\AsistenteController;
use App\Http\Controllers\Inteligente\Comprador\ComparadorController;
use App\Http\Controllers\Inteligente\Comprador\RecomendacionController;
use App\Http\Controllers\Inteligente\Cotizador\InteligenciaComercialController;
use App\Http\Controllers\Paneles\PanelDashboardController;
use App\Http\Controllers\Publico\Catalogo\CatalogoController;
use App\Http\Controllers\Publico\Comercio\CampaniaPublicaController;
use App\Http\Controllers\Publico\Comercio\CarritoController;
use App\Http\Controllers\Publico\Comercio\FinalizarCompraController;
use App\Http\Controllers\Publico\Comercio\PedidoController;
use App\Http\Controllers\Publico\Comercio\SeguimientoPedidoController;
use App\Http\Controllers\Publico\Inicio\InicioController;
use App\Http\Controllers\Publico\Paginas\PaginaController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

// @MODULO 01 | SITIO PÚBLICO: INICIO Y CATÁLOGO
// Las rutas GET muestran páginas; el identificador {product} se restringe a números.

// Rutas públicas de la aplicación.
// [GET /] Muestra la página de inicio de la tienda. Controlador: InicioController::__invoke(). Nombre: home.
Route::get('/', InicioController::class)->name('home');
// [GET /catalogo] Abre el catálogo de productos. Controlador: CatalogoController::index(). Nombre: catalog.index.
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalog.index');
// [GET /catalogo/{product}] Muestra el detalle de un producto del catálogo. Controlador: CatalogoController::show(). Nombre: catalog.show. Parámetro(s) numérico(s).
Route::get('/catalogo/{product}', [CatalogoController::class, 'show'])->whereNumber('product')->name('catalog.show');

// @MODULO 02 | CATÁLOGO: FAVORITOS Y RESEÑAS
// Acciones disponibles en estas rutas solo tras pasar los middleware de la cuenta.

// GRUPO AUTENTICADO: auth + middleware personalizados de cuenta y sesión.
Route::middleware(['auth', 'cuenta.activa', 'sesion.usuario'])->group(function (): void {
    // [POST /catalogo/{product}/favorito] Agrega o quita un producto de favoritos. Controlador: CatalogoController::toggleFavorite(). Nombre: catalog.favorite. Parámetro(s) numérico(s).
    Route::post('/catalogo/{product}/favorito', [CatalogoController::class, 'toggleFavorite'])
        ->whereNumber('product')->name('catalog.favorite');
    // [POST /catalogo/{product}/resena] Envía una reseña del producto. Controlador: CatalogoController::storeReview(). Nombre: catalog.review.store. Parámetro(s) numérico(s).
    Route::post('/catalogo/{product}/resena', [CatalogoController::class, 'storeReview'])
        ->whereNumber('product')->name('catalog.review.store');
});

// @MODULO 03 | CAMPAÑAS PÚBLICAS Y PÁGINAS INFORMATIVAS
// Incluye seguimiento de eventos, contacto, reclamaciones y páginas legales.

// [POST /campanas/{campaign}/evento] Registra un evento de interacción de campaña. Controlador: CampaniaPublicaController::track(). Nombre: campaigns.track. Parámetro(s) numérico(s). Límite de solicitudes (throttle).
Route::post('/campanas/{campaign}/evento', [CampaniaPublicaController::class, 'track'])
    ->whereNumber('campaign')->middleware('throttle:60,1')->name('campaigns.track');
// [GET /nosotros] Muestra la página Nosotros. Controlador: PaginaController::about(). Nombre: pages.about.
Route::get('/nosotros', [PaginaController::class, 'about'])->name('pages.about');
// [GET /contacto] Muestra el formulario de contacto. Controlador: PaginaController::contact(). Nombre: pages.contact.
Route::get('/contacto', [PaginaController::class, 'contact'])->name('pages.contact');
// [POST /contacto] Envía el formulario de contacto. Controlador: PaginaController::sendContact(). Nombre: pages.contact.store. Límite de solicitudes (throttle).
Route::post('/contacto', [PaginaController::class, 'sendContact'])->middleware('throttle:contact')->name('pages.contact.store');
// [GET /libro-de-reclamaciones] Muestra el libro de reclamaciones. Controlador: PaginaController::claimsBook(). Nombre: pages.claims.
Route::get('/libro-de-reclamaciones', [PaginaController::class, 'claimsBook'])->name('pages.claims');
// [GET /politicas-privacidad] Muestra la política de privacidad. Controlador: PaginaController::privacyPolicy(). Nombre: pages.privacy.
Route::get('/politicas-privacidad', [PaginaController::class, 'privacyPolicy'])->name('pages.privacy');
// [GET /terminos-condiciones] Muestra los términos y condiciones. Controlador: PaginaController::terms(). Nombre: pages.terms.
Route::get('/terminos-condiciones', [PaginaController::class, 'terms'])->name('pages.terms');
// [GET /preguntas-frecuentes] Muestra las preguntas frecuentes. Controlador: PaginaController::frequentlyAskedQuestions(). Nombre: pages.faq.
Route::get('/preguntas-frecuentes', [PaginaController::class, 'frequentlyAskedQuestions'])->name('pages.faq');

// @MODULO 04 | HERRAMIENTAS INTELIGENTES PÚBLICAS
// Páginas de recomendador, comparador, optimizador y asistente.

// [GET /recomendador] Muestra el recomendador de productos. Controlador: RecomendacionController::index(). Nombre: recommendations.
Route::get('/recomendador', [RecomendacionController::class, 'index'])->name('recommendations');
// [GET /comparador] Muestra el comparador de productos. Controlador: ComparadorController::index(). Nombre: comparison.
Route::get('/comparador', [ComparadorController::class, 'index'])->name('comparison');
// [GET /optimizador] Muestra el optimizador comercial. Controlador: InteligenciaComercialController::optimizer(). Nombre: smart.optimizer.
Route::get('/optimizador', [InteligenciaComercialController::class, 'optimizer'])->name('smart.optimizer');
// [GET /asistente] Muestra la interfaz del asistente. Controlador: AsistenteController::index(). Nombre: assistant.
Route::get('/asistente', [AsistenteController::class, 'index'])->name('assistant');
// [POST /asistente] Envía una consulta al asistente. Controlador: AsistenteController::ask(). Nombre: assistant.ask. Límite de solicitudes (throttle).
Route::post('/asistente', [AsistenteController::class, 'ask'])->middleware('throttle:10,1')->name('assistant.ask');

// @MODULO 05 | AUTENTICACIÓN: LOGIN, REGISTRO Y LOGOUT
// guest restringe el grupo a visitantes sin sesión; auth protege el cierre de sesión.

// ruta de inicio de sesión, registro y cierre de sesión
// GRUPO GUEST: no requiere sesión iniciada; limita el acceso a visitantes.
Route::middleware('guest')->group(function (): void {
    // [GET /iniciar-sesion] Muestra el formulario de inicio de sesión. Controlador: AutenticacionController::loginForm(). Nombre: login.
    Route::get('/iniciar-sesion', [AutenticacionController::class, 'loginForm'])->name('login');
    // [POST /iniciar-sesion] Envía las credenciales para iniciar sesión. Controlador: AutenticacionController::login(). Nombre: login.store. Límite de solicitudes (throttle).
    Route::post('/iniciar-sesion', [AutenticacionController::class, 'login'])->middleware('throttle:login')->name('login.store');
    // [GET /registrarse] Muestra el formulario de registro de usuario. Controlador: AutenticacionController::registerForm(). Nombre: register.
    Route::get('/registrarse', [AutenticacionController::class, 'registerForm'])->name('register');
    // [POST /registrarse] Envía los datos para registrar una cuenta. Controlador: AutenticacionController::register(). Nombre: register.store. Límite de solicitudes (throttle).
    Route::post('/registrarse', [AutenticacionController::class, 'register'])->middleware('throttle:register')->name('register.store');
});

// [POST /cerrar-sesion] Solicita cerrar la sesión. Controlador: AutenticacionController::logout(). Nombre: logout.
Route::post('/cerrar-sesion', [AutenticacionController::class, 'logout'])->middleware('auth')->name('logout');

// @MODULO 06 | MI CUENTA Y MIS PEDIDOS
// Acceso protegido por sesión iniciada y los dos middleware de estado de cuenta/sesión.

// GRUPO AUTENTICADO: auth + middleware personalizados de cuenta y sesión.
Route::middleware(['auth', 'cuenta.activa', 'sesion.usuario'])->group(function (): void {
    // [GET /mi-cuenta] Abre el panel de cuenta del usuario. Controlador: CuentaController::dashboard(). Nombre: dashboard.
    Route::get('/mi-cuenta', [CuentaController::class, 'dashboard'])->name('dashboard');
    // [GET /mi-cuenta/perfil] Muestra el perfil del usuario. Controlador: CuentaController::profile(). Nombre: account.profile.show.
    Route::get('/mi-cuenta/perfil', [CuentaController::class, 'profile'])->name('account.profile.show');
    // [PUT /mi-cuenta/perfil] Solicita actualizar los datos del perfil. Controlador: CuentaAdministracionController::updateProfile(). Nombre: account.profile.
    Route::put('/mi-cuenta/perfil', [CuentaAdministracionController::class, 'updateProfile'])->name('account.profile');
    // [PUT /mi-cuenta/password] Solicita cambiar la contraseña. Controlador: CuentaAdministracionController::updatePassword(). Nombre: account.password.
    Route::put('/mi-cuenta/password', [CuentaAdministracionController::class, 'updatePassword'])->name('account.password');

    // [GET /mis-pedidos] Abre el listado o pantalla principal de los pedidos. Controlador: PedidoController::index(). Nombre: orders.index.
    Route::get('/mis-pedidos', [PedidoController::class, 'index'])->name('orders.index');
    // [GET /mis-pedidos/{order}] Consulta el detalle de los pedidos. Controlador: PedidoController::show(). Nombre: orders.show. Parámetro(s) numérico(s).
    Route::get('/mis-pedidos/{order}', [PedidoController::class, 'show'])->whereNumber('order')->name('orders.show');
    // [PATCH /mis-pedidos/{order}/estado] Solicita cambiar el estado de un pedido. Controlador: PedidoController::updateStatus(). Nombre: orders.status. Parámetro(s) numérico(s).
    Route::patch('/mis-pedidos/{order}/estado', [PedidoController::class, 'updateStatus'])->whereNumber('order')->name('orders.status');

    // @MODULO 07 | PORTAL MAYORISTA Y COTIZACIONES DE CUENTA
    // Se añade un permiso explícito para consultar y/o crear cotizaciones.

    // [GET /portal-mayorista] Muestra el portal mayorista. Controlador: InteligenciaComercialController::wholesale(). Nombre: smart.wholesale. Permiso adicional indicado en la ruta.
    Route::get('/portal-mayorista', [InteligenciaComercialController::class, 'wholesale'])
        ->middleware('permiso:gestion.cotizaciones.consultar')->name('smart.wholesale');

    // [GET /cotizaciones] Muestra el listado de cotizaciones. Controlador: CotizacionController::index(). Nombre: quotations.index. Permiso adicional indicado en la ruta.
    Route::get('/cotizaciones', [CotizacionController::class, 'index'])
        ->middleware('permiso:gestion.cotizaciones.consultar')->name('quotations.index');
    // [POST /cotizaciones] Registra una cotización. Controlador: CotizacionController::store(). Nombre: quotations.store. Permiso adicional indicado en la ruta.
    Route::post('/cotizaciones', [CotizacionController::class, 'store'])
        ->middleware('permiso:gestion.cotizaciones.crear')->name('quotations.store');
});

// @MODULO 08 | CARRITO Y PROCESO DE CHECKOUT
// Todas estas rutas usan el middleware carrito.ecommerce; el procesamiento reside en los controladores.

// GRUPO CARRITO: estas rutas usan el middleware personalizado carrito.ecommerce.
Route::middleware(['carrito.ecommerce'])->group(function (): void {
    // [GET /carrito] Abre el listado o pantalla principal del carrito. Controlador: CarritoController::index(). Nombre: cart.index.
    Route::get('/carrito', [CarritoController::class, 'index'])->name('cart.index');
    // [POST /carrito/{product}/agregar] Agrega un producto al carrito. Controlador: CarritoController::add(). Nombre: cart.add. Parámetro(s) numérico(s).
    Route::post('/carrito/{product}/agregar', [CarritoController::class, 'add'])->whereNumber('product')->name('cart.add');
    // [PATCH /carrito/{product}] Actualiza la cantidad o datos de un producto del carrito. Controlador: CarritoController::update(). Nombre: cart.update. Parámetro(s) numérico(s).
    Route::patch('/carrito/{product}', [CarritoController::class, 'update'])->whereNumber('product')->name('cart.update');
    // [POST /carrito/{product}/quitar] Quita un producto del carrito. Controlador: CarritoController::remove(). Nombre: cart.remove. Parámetro(s) numérico(s).
    Route::post('/carrito/{product}/quitar', [CarritoController::class, 'remove'])->whereNumber('product')->name('cart.remove');
    // [POST /carrito/vaciar] Vacía el carrito. Controlador: CarritoController::clear(). Nombre: cart.clear.
    Route::post('/carrito/vaciar', [CarritoController::class, 'clear'])->name('cart.clear');
    // [POST /carrito/cupon] Aplica un cupón en el carrito. Controlador: CarritoController::applyCoupon(). Nombre: cart.coupon.
    Route::post('/carrito/cupon', [CarritoController::class, 'applyCoupon'])->name('cart.coupon');
    // [GET /checkout] Muestra el formulario de finalizar compra. Controlador: FinalizarCompraController::index(). Nombre: checkout.form.
    Route::get('/checkout', [FinalizarCompraController::class, 'index'])->name('checkout.form');
    // [POST /checkout] Envía la compra al procesamiento de checkout. Controlador: FinalizarCompraController::store(). Nombre: checkout. Límite de solicitudes (throttle).
    Route::post('/checkout', [FinalizarCompraController::class, 'store'])->middleware('throttle:checkout')->name('checkout');
});

// @MODULO 09 | SEGUIMIENTO DE PEDIDOS
// La ruta aquí no declara auth: revisar el controlador para conocer validaciones de acceso.

// [GET /seguimiento-pedido/{order}] Consulta el seguimiento de un pedido. Controlador: SeguimientoPedidoController::show(). Nombre: orders.tracking. Parámetro(s) numérico(s).
Route::get('/seguimiento-pedido/{order}', [SeguimientoPedidoController::class, 'show'])
    ->whereNumber('order')->name('orders.tracking');

// @MODULO 10 | REDIRECCIONES A MÓDULOS DE GESTIÓN
// Puntos de acceso que redirigen a la pantalla canónica y verifican middleware.

// [GET /gestion/configuracion-comercial] Redirige a management.products.preview.store_settings. Controlador: redirección. Nombre: management.commercial_settings. Permiso adicional indicado en la ruta.
Route::get('/gestion/configuracion-comercial', static fn (): RedirectResponse => redirect()->route('management.products.preview.store_settings'))
    ->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])
    ->name('management.commercial_settings');

// [GET /gestion/analitica-clientes] Redirige a management.reports. Controlador: redirección. Nombre: management.customer_analytics. Permiso adicional indicado en la ruta.
Route::get('/gestion/analitica-clientes', static fn (): RedirectResponse => redirect()->route('management.reports', ['vista' => 'clientes']))
    ->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])
    ->name('management.customer_analytics');
// [GET /gestion/metricas-marketing] Redirige a management.reports. Controlador: redirección. Nombre: management.marketing_metrics. Permiso adicional indicado en la ruta.
Route::get('/gestion/metricas-marketing', static fn (): RedirectResponse => redirect()->route('management.reports', ['vista' => 'campanas']))
    ->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])
    ->name('management.marketing_metrics');
// [GET /gestion/reportes-financieros] Redirige a management.reports. Controlador: redirección. Nombre: management.financial_reports. Permiso adicional indicado en la ruta.
Route::get('/gestion/reportes-financieros', static fn (): RedirectResponse => redirect()->route('management.reports', ['vista' => 'ventas']))
    ->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])
    ->name('management.financial_reports');

// @MODULO 11 | GESTIÓN PRIVADA: DASHBOARD Y ORGANIZACIÓN
// El grupo agrega /gestion, antepone management. al nombre y protege rutas con middleware.

// GRUPO PROTEGIDO: todas las rutas internas comienzan por /gestion y management.
Route::prefix('gestion')->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('management.')->group(function (): void {
    // [GET /gestion/] Muestra el panel principal de administración. Controlador: PanelDashboardController::__invoke(). Nombre: management.dashboard.
    Route::get('/', PanelDashboardController::class)->name('dashboard');

    // @MODULO 12 | DASHBOARD GLOBAL, EXPORTACIONES Y ALERTAS
    // Paneles y reportes descargables para ventas, pedidos, sedes y alertas.

    // [GET /gestion/dashboard-global/ventas] Abre el listado o pantalla principal del panel global de ventas. Controlador: DashboardGlobalVentasController::index(). Nombre: management.dashboard.sales. Permiso adicional indicado en la ruta.
    Route::get('/dashboard-global/ventas', [DashboardGlobalVentasController::class, 'index'])
        ->middleware('permiso:ruta')->name('dashboard.sales');
    // [GET /gestion/dashboard-global/ventas/exportar/{format}] Solicita exportar datos del panel global de ventas. Controlador: DashboardGlobalVentasController::export(). Nombre: management.reports.dashboard_sales.export. Permiso adicional indicado en la ruta.
    Route::get('/dashboard-global/ventas/exportar/{format}', [DashboardGlobalVentasController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->middleware('permiso:ruta')->name('reports.dashboard_sales.export');
    // [GET /gestion/dashboard-global/pedidos] Abre el listado o pantalla principal del panel global de pedidos. Controlador: DashboardGlobalPedidosController::index(). Nombre: management.dashboard.orders. Permiso adicional indicado en la ruta.
    Route::get('/dashboard-global/pedidos', [DashboardGlobalPedidosController::class, 'index'])
        ->middleware('permiso:ruta')->name('dashboard.orders');
    // [GET /gestion/dashboard-global/pedidos/exportar/{format}] Solicita exportar datos del panel global de pedidos. Controlador: DashboardGlobalPedidosController::export(). Nombre: management.reports.dashboard_orders.export. Permiso adicional indicado en la ruta.
    Route::get('/dashboard-global/pedidos/exportar/{format}', [DashboardGlobalPedidosController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->middleware('permiso:ruta')->name('reports.dashboard_orders.export');
    // [GET /gestion/dashboard-global/sedes] Abre el listado o pantalla principal del panel global de sedes. Controlador: DashboardGlobalSedesController::index(). Nombre: management.dashboard.sites. Permiso adicional indicado en la ruta.
    Route::get('/dashboard-global/sedes', [DashboardGlobalSedesController::class, 'index'])
        ->middleware('permiso:ruta')->name('dashboard.sites');
    // [GET /gestion/dashboard-global/sedes/exportar/{format}] Solicita exportar datos del panel global de sedes. Controlador: DashboardGlobalSedesController::export(). Nombre: management.reports.dashboard_sites.export. Permiso adicional indicado en la ruta.
    Route::get('/dashboard-global/sedes/exportar/{format}', [DashboardGlobalSedesController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->middleware('permiso:ruta')->name('reports.dashboard_sites.export');
    // [GET /gestion/dashboard-global/alertas] Muestra la pantalla de las alertas globales. Controlador: AlertasGlobalesController::mostrar(). Nombre: management.dashboard.alerts. Permiso adicional indicado en la ruta.
    Route::get('/dashboard-global/alertas', [AlertasGlobalesController::class, 'mostrar'])
        ->middleware('permiso:ruta')->name('dashboard.alerts');
    // [GET /gestion/dashboard-global/alertas/exportar/{format}] Solicita exportar datos. Controlador: AlertasGlobalesController::exportar(). Nombre: management.reports.dashboard_alerts.export. Permiso adicional indicado en la ruta.
    Route::get('/dashboard-global/alertas/exportar/{format}', [AlertasGlobalesController::class, 'exportar'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->middleware('permiso:ruta')->name('reports.dashboard_alerts.export');

    // @MODULO 13 | ORGANIZACIÓN: EMPRESA, SEDES, ALMACENES Y PERSONAL
    // Administración organizacional con permisos explícitos adicionales en algunas acciones.

    // [GET /gestion/organizacion/empresa] Abre el listado o pantalla principal de la empresa. Controlador: EmpresaController::index(). Nombre: management.company_settings.data. Permiso adicional indicado en la ruta.
    Route::get('/organizacion/empresa', [EmpresaController::class, 'index'])
        ->middleware(['permiso:ruta', 'permiso:ruta'])->name('company_settings.data');
    // [PUT /gestion/organizacion/empresa] Envía una actualización de la empresa. Controlador: EmpresaController::update(). Nombre: management.company_settings.data.update. Permiso adicional indicado en la ruta.
    Route::put('/organizacion/empresa', [EmpresaController::class, 'update'])
        ->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.data.update');
    // [GET /gestion/organizacion/sedes] Abre el listado o pantalla principal de las sedes. Controlador: SedeController::index(). Nombre: management.company_settings.sites. Permiso adicional indicado en la ruta.
    Route::get('/organizacion/sedes', [SedeController::class, 'index'])
        ->middleware(['permiso:ruta', 'permiso:ruta'])->name('company_settings.sites');
    // [POST /gestion/organizacion/sedes] Envía al controlador la creación/registro de las sedes. Controlador: SedeController::store(). Nombre: management.company_settings.sites.store. Permiso adicional indicado en la ruta.
    Route::post('/organizacion/sedes', [SedeController::class, 'store'])
        ->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.sites.store');
    // [PUT /gestion/organizacion/sedes/{sede}] Envía una actualización de las sedes. Controlador: SedeController::update(). Nombre: management.company_settings.sites.update. Parámetro(s) numérico(s). Permiso adicional indicado en la ruta.
    Route::put('/organizacion/sedes/{sede}', [SedeController::class, 'update'])
        ->whereNumber('sede')->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.sites.update');
    // [PATCH /gestion/organizacion/sedes/{sede}/estado] Solicita cambiar el estado del registro. Controlador: SedeController::cambiarEstado(). Nombre: management.company_settings.sites.status. Parámetro(s) numérico(s). Permiso adicional indicado en la ruta.
    Route::patch('/organizacion/sedes/{sede}/estado', [SedeController::class, 'cambiarEstado'])
        ->whereNumber('sede')->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.sites.status');
    // [GET /gestion/organizacion/almacenes] Abre el listado o pantalla principal de los almacenes. Controlador: AlmacenController::index(). Nombre: management.company_settings.warehouses. Permiso adicional indicado en la ruta.
    Route::get('/organizacion/almacenes', [AlmacenController::class, 'index'])
        ->middleware(['permiso:ruta', 'permiso:ruta'])->name('company_settings.warehouses');
    // [POST /gestion/organizacion/almacenes] Envía al controlador la creación/registro de los almacenes. Controlador: AlmacenController::store(). Nombre: management.company_settings.warehouses.store. Permiso adicional indicado en la ruta.
    Route::post('/organizacion/almacenes', [AlmacenController::class, 'store'])
        ->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.warehouses.store');
    // [PUT /gestion/organizacion/almacenes/{almacen}] Envía una actualización de los almacenes. Controlador: AlmacenController::update(). Nombre: management.company_settings.warehouses.update. Parámetro(s) numérico(s). Permiso adicional indicado en la ruta.
    Route::put('/organizacion/almacenes/{almacen}', [AlmacenController::class, 'update'])
        ->whereNumber('almacen')->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.warehouses.update');
    // [PATCH /gestion/organizacion/almacenes/{almacen}/estado] Solicita cambiar el estado del registro. Controlador: AlmacenController::cambiarEstado(). Nombre: management.company_settings.warehouses.status. Parámetro(s) numérico(s). Permiso adicional indicado en la ruta.
    Route::patch('/organizacion/almacenes/{almacen}/estado', [AlmacenController::class, 'cambiarEstado'])
        ->whereNumber('almacen')->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar'])->name('company_settings.warehouses.status');
    // [GET /gestion/organizacion/personal] Lista el personal por sede. Controlador: PersonalSedeController::listar(). Nombre: management.company_settings.staff. Permiso adicional indicado en la ruta.
    Route::get('/organizacion/personal', [PersonalSedeController::class, 'listar'])
        ->middleware(['permiso:ruta', 'permiso:ruta', 'permiso:pantalla.admin.staff.consultar'])->name('company_settings.staff');
    // [GET /gestion/organizacion/responsables] Abre el listado o pantalla principal de los responsables organizacionales. Controlador: ResponsableOrganizacionalController::index(). Nombre: management.company_settings.responsibles. Permiso adicional indicado en la ruta.
    Route::get('/organizacion/responsables', [ResponsableOrganizacionalController::class, 'index'])
        ->middleware(['permiso:ruta', 'permiso:ruta', 'permiso:pantalla.admin.staff.consultar', 'permiso:pantalla.admin.responsibles.consultar'])->name('company_settings.responsibles');
    // [POST /gestion/organizacion/responsables] Envía al controlador la creación/registro de los responsables organizacionales. Controlador: ResponsableOrganizacionalController::store(). Nombre: management.company_settings.responsibles.store. Permiso adicional indicado en la ruta.
    Route::post('/organizacion/responsables', [ResponsableOrganizacionalController::class, 'store'])
        ->middleware(['permiso:ruta', 'permiso:gestion.organizacion.actualizar', 'permiso:pantalla.admin.responsibles.consultar'])->name('company_settings.responsibles.store');

    // @MODULO 14 | COTIZACIONES ADMINISTRATIVAS
    // Consulta, exportación, edición, aprobación, envío manual y desactivación de cotizaciones.

    // [GET /gestion/cotizaciones] Muestra el listado de cotizaciones. Controlador: CotizacionController::index(). Nombre: management.quotations.
    Route::get('/cotizaciones', [CotizacionController::class, 'index'])->name('quotations');
    // [GET /gestion/cotizaciones/exportar/{format}] Solicita exportar datos de las cotizaciones. Controlador: CotizacionController::export(). Nombre: management.quotations.export.list.
    Route::get('/cotizaciones/exportar/{format}', [CotizacionController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('quotations.export.list');
    // [GET /gestion/cotizaciones/{quotation}/editar] Abre la edición de las cotizaciones. Controlador: CotizacionController::edit(). Nombre: management.quotations.edit. Parámetro(s) numérico(s).
    Route::get('/cotizaciones/{quotation}/editar', [CotizacionController::class, 'edit'])
        ->whereNumber('quotation')->name('quotations.edit');
    // [GET /gestion/cotizaciones/{quotation}/descargar] Solicita descargar la cotización. Controlador: CotizacionController::descargar(). Nombre: management.quotations.export. Parámetro(s) numérico(s).
    Route::get('/cotizaciones/{quotation}/descargar', [CotizacionController::class, 'descargar'])->whereNumber('quotation')->name('quotations.export');
    // [POST /gestion/cotizaciones/{quotation}/envio-manual] Registra un envío manual de la cotización. Controlador: CotizacionController::registrarEnvioManual(). Nombre: management.quotations.update.send. Parámetro(s) numérico(s).
    Route::post('/cotizaciones/{quotation}/envio-manual', [CotizacionController::class, 'registrarEnvioManual'])->whereNumber('quotation')->name('quotations.update.send');
    // [POST /gestion/cotizaciones/{quotation}/aprobar] Solicita aprobar una cotización. Controlador: CotizacionController::aprobar(). Nombre: management.quotations.approve. Parámetro(s) numérico(s).
    Route::post('/cotizaciones/{quotation}/aprobar', [CotizacionController::class, 'aprobar'])->whereNumber('quotation')->name('quotations.approve');
    // [PUT /gestion/cotizaciones/{quotation}] Envía una actualización de las cotizaciones. Controlador: CotizacionController::update(). Nombre: management.quotations.update. Parámetro(s) numérico(s).
    Route::put('/cotizaciones/{quotation}', [CotizacionController::class, 'update'])->whereNumber('quotation')->name('quotations.update');
    // [DELETE /gestion/cotizaciones/{quotation}] Desactiva una cotización (no declara borrado físico). Controlador: CotizacionController::deactivate(). Nombre: management.quotations.deactivate. Parámetro(s) numérico(s).
    Route::delete('/cotizaciones/{quotation}', [CotizacionController::class, 'deactivate'])->whereNumber('quotation')->name('quotations.deactivate');

    // @MODULO 15 | PRODUCTOS, VARIANTES, PRECIOS Y TIENDA
    // Catálogo interno, precios mayoristas, configuración de tienda e imágenes de variantes.

    // [GET /gestion/productos] Abre el listado o pantalla principal de los productos. Controlador: GestionProductosController::index(). Nombre: management.products.
    Route::get('/productos', [GestionProductosController::class, 'index'])->name('products');
    // [GET /gestion/ecommerce/diseno/precios] Muestra los precios por canal. Controlador: GestionProductosController::preciosCanales(). Nombre: management.products.preview.prices. Permiso adicional indicado en la ruta.
    Route::get('/ecommerce/diseno/precios', [GestionProductosController::class, 'preciosCanales'])
        ->middleware('permiso:ruta')->name('products.preview.prices');
    // [PUT /gestion/ecommerce/precios-mayoristas] Solicita guardar un precio mayorista. Controlador: GestionProductosController::guardarPrecioMayorista(). Nombre: management.products.wholesale_prices.update. Permiso adicional indicado en la ruta.
    Route::put('/ecommerce/precios-mayoristas', [GestionProductosController::class, 'guardarPrecioMayorista'])
        ->middleware(['permiso:ruta', 'permiso:gestion.productos.actualizar'])->name('products.wholesale_prices.update');
    // [POST /gestion/ecommerce/precios-mayoristas/importar] Solicita importar precios mayoristas. Controlador: GestionProductosController::importarPreciosMayoristas(). Nombre: management.products.wholesale_prices.import. Permiso adicional indicado en la ruta.
    Route::post('/ecommerce/precios-mayoristas/importar', [GestionProductosController::class, 'importarPreciosMayoristas'])
        ->middleware(['permiso:ruta', 'permiso:gestion.productos.actualizar'])->name('products.wholesale_prices.import');
    // [GET /gestion/ecommerce/diseno/tienda] Muestra la configuración visual/comercial de tienda. Controlador: GestionProductosController::configuracionTienda(). Nombre: management.products.preview.store_settings. Permiso adicional indicado en la ruta.
    Route::get('/ecommerce/diseno/tienda', [GestionProductosController::class, 'configuracionTienda'])
        ->middleware('permiso:ruta')->name('products.preview.store_settings');
    // [PUT /gestion/ecommerce/tienda/configuracion] Guarda la configuración comercial de la tienda. Controlador: GestionProductosController::guardarConfiguracionTienda(). Nombre: management.commercial_settings.store.update. Permiso adicional indicado en la ruta.
    Route::put('/ecommerce/tienda/configuracion', [GestionProductosController::class, 'guardarConfiguracionTienda'])
        ->middleware(['permiso:ruta', 'permiso:gestion.configuracion.actualizar'])->name('commercial_settings.store.update');
    // [GET /gestion/productos/exportar/{format}] Solicita exportar datos de los productos. Controlador: GestionProductosController::export(). Nombre: management.products.export.
    Route::get('/productos/exportar/{format}', [GestionProductosController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('products.export');
    // [GET /gestion/productos/plantilla-importacion] Descarga la plantilla de importación. Controlador: GestionProductosController::importTemplate(). Nombre: management.products.import_template.
    Route::get('/productos/plantilla-importacion', [GestionProductosController::class, 'importTemplate'])
        ->name('products.import_template');
    // [POST /gestion/productos] Envía al controlador la creación/registro de los productos. Controlador: GestionProductosController::store(). Nombre: management.products.store.
    Route::post('/productos', [GestionProductosController::class, 'store'])->name('products.store');
    // [POST /gestion/productos/importar] Solicita importar datos de los productos. Controlador: GestionProductosController::import(). Nombre: management.products.import.
    Route::post('/productos/importar', [GestionProductosController::class, 'import'])->name('products.import');
    // [POST /gestion/productos/{product}/variantes] Registra una variante de producto. Controlador: GestionProductosController::registrarVariante(). Nombre: management.products.variants.store. Parámetro(s) numérico(s).
    Route::post('/productos/{product}/variantes', [GestionProductosController::class, 'registrarVariante'])
        ->whereNumber('product')->name('products.variants.store');
    // [PUT /gestion/productos/{product}/variantes/{variante}] Actualiza una variante de producto. Controlador: GestionProductosController::actualizarVariante(). Nombre: management.products.variants.update. Parámetro(s) numérico(s).
    Route::put('/productos/{product}/variantes/{variante}', [GestionProductosController::class, 'actualizarVariante'])
        ->whereNumber('product')->whereNumber('variante')->name('products.variants.update');
    // [DELETE /gestion/productos/{product}/variantes/{variante}] Desactiva una variante de producto. Controlador: GestionProductosController::desactivarVariante(). Nombre: management.products.variants.deactivate. Parámetro(s) numérico(s).
    Route::delete('/productos/{product}/variantes/{variante}', [GestionProductosController::class, 'desactivarVariante'])
        ->whereNumber('product')->whereNumber('variante')->name('products.variants.deactivate');
    // [POST /gestion/productos/{product}/variantes/{variante}/imagenes] Registra una imagen de variante. Controlador: GestionProductosController::registrarImagenVariante(). Nombre: management.products.variants.images.store. Parámetro(s) numérico(s).
    Route::post('/productos/{product}/variantes/{variante}/imagenes', [GestionProductosController::class, 'registrarImagenVariante'])
        ->whereNumber('product')->whereNumber('variante')->name('products.variants.images.store');
    // [DELETE /gestion/productos/{product}/variantes/{variante}/imagenes/{imagen}] Solicita eliminar una imagen de variante. Controlador: GestionProductosController::eliminarImagenVariante(). Nombre: management.products.variants.images.delete. Parámetro(s) numérico(s).
    Route::delete('/productos/{product}/variantes/{variante}/imagenes/{imagen}', [GestionProductosController::class, 'eliminarImagenVariante'])
        ->whereNumber('product')->whereNumber('variante')->whereNumber('imagen')->name('products.variants.images.delete');
    // [PUT /gestion/productos/{product}] Envía una actualización de los productos. Controlador: GestionProductosController::update(). Nombre: management.products.update. Parámetro(s) numérico(s).
    Route::put('/productos/{product}', [GestionProductosController::class, 'update'])->whereNumber('product')->name('products.update');
    // [DELETE /gestion/productos/{product}] Desactiva un producto (no declara borrado físico). Controlador: GestionProductosController::deactivate(). Nombre: management.products.deactivate. Parámetro(s) numérico(s).
    Route::delete('/productos/{product}', [GestionProductosController::class, 'deactivate'])->whereNumber('product')->name('products.deactivate');

    // @MODULO 16 | USUARIOS Y AUDITORÍA
    // Gestión de usuarios, estado de cuentas y acceso a la auditoría de operaciones.

    // [GET /gestion/usuarios] Abre el listado o pantalla principal de los usuarios. Controlador: GestionUsuariosController::index(). Nombre: management.users.
    Route::get('/usuarios', [GestionUsuariosController::class, 'index'])->name('users');
    // [GET /gestion/usuarios/exportar/{format}] Solicita exportar datos de los usuarios. Controlador: GestionUsuariosController::export(). Nombre: management.users.export.
    Route::get('/usuarios/exportar/{format}', [GestionUsuariosController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('users.export');
    // [POST /gestion/usuarios] Envía al controlador la creación/registro de los usuarios. Controlador: GestionUsuariosController::store(). Nombre: management.users.store.
    Route::post('/usuarios', [GestionUsuariosController::class, 'store'])->name('users.store');
    // [PUT /gestion/usuarios/{user}] Envía una actualización de los usuarios. Controlador: GestionUsuariosController::update(). Nombre: management.users.update. Parámetro(s) numérico(s).
    Route::put('/usuarios/{user}', [GestionUsuariosController::class, 'update'])->whereNumber('user')->name('users.update');
    // [PATCH /gestion/usuarios/{user}/estado] Solicita activar/desactivar un usuario. Controlador: GestionUsuariosController::updateStatus(). Nombre: management.users.activate. Parámetro(s) numérico(s).
    Route::patch('/usuarios/{user}/estado', [GestionUsuariosController::class, 'updateStatus'])->whereNumber('user')->name('users.activate');
    // [GET /gestion/auditoria] Muestra la auditoría de operaciones. Controlador: PanelAnalisisAuditoriaController::operaciones(). Nombre: management.audit.
    Route::get('/auditoria', [PanelAnalisisAuditoriaController::class, 'operaciones'])->name('audit');
    // [GET /gestion/analisis/diseno/auditoria-operaciones] Muestra la auditoría de operaciones. Controlador: PanelAnalisisAuditoriaController::operaciones(). Nombre: management.audit.preview.operations. Permiso adicional indicado en la ruta.
    Route::get('/analisis/diseno/auditoria-operaciones', [PanelAnalisisAuditoriaController::class, 'operaciones'])
        ->middleware('permiso:ruta')->name('audit.preview.operations');

    // @MODULO 17 | CATEGORÍAS
    // Operaciones de consulta, alta, actualización y desactivación.

    // [GET /gestion/categorias] Abre el listado o pantalla principal de las categorías. Controlador: CategoriaController::index(). Nombre: management.categories.
    Route::get('/categorias', [CategoriaController::class, 'index'])->name('categories');
    // [GET /gestion/categorias/exportar/{format?}] Solicita exportar datos de las categorías. Controlador: CategoriaController::export(). Nombre: management.categories.export.
    Route::get('/categorias/exportar/{format?}', [CategoriaController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('categories.export');
    // [POST /gestion/categorias] Envía al controlador la creación/registro de las categorías. Controlador: CategoriaController::store(). Nombre: management.categories.store.
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categories.store');
    // [PUT /gestion/categorias/{category}] Envía una actualización de las categorías. Controlador: CategoriaController::update(). Nombre: management.categories.update.
    Route::put('/categorias/{category}', [CategoriaController::class, 'update'])->name('categories.update');
    // [DELETE /gestion/categorias/{category}] Solicita desactivar las categorías. Controlador: CategoriaController::deactivate(). Nombre: management.categories.deactivate.
    Route::delete('/categorias/{category}', [CategoriaController::class, 'deactivate'])->name('categories.deactivate');

    // @MODULO 18 | MARCAS
    // Operaciones de consulta, alta, actualización y desactivación.

    // [GET /gestion/marcas] Abre el listado o pantalla principal de las marcas. Controlador: MarcaController::index(). Nombre: management.brands.
    Route::get('/marcas', [MarcaController::class, 'index'])->name('brands');
    // [POST /gestion/marcas] Envía al controlador la creación/registro de las marcas. Controlador: MarcaController::store(). Nombre: management.brands.store.
    Route::post('/marcas', [MarcaController::class, 'store'])->name('brands.store');
    // [PUT /gestion/marcas/{marca}] Envía una actualización de las marcas. Controlador: MarcaController::update(). Nombre: management.brands.update. Parámetro(s) numérico(s).
    Route::put('/marcas/{marca}', [MarcaController::class, 'update'])->whereNumber('marca')->name('brands.update');
    // [DELETE /gestion/marcas/{marca}] Solicita desactivar las marcas. Controlador: MarcaController::deactivate(). Nombre: management.brands.deactivate. Parámetro(s) numérico(s).
    Route::delete('/marcas/{marca}', [MarcaController::class, 'deactivate'])->whereNumber('marca')->name('brands.deactivate');

    // @MODULO 19 | CLIENTES
    // Consulta, importación/exportación, creación, actualización y desactivación.

    // [GET /gestion/clientes] Muestra el panel de clientes. Controlador: ClienteController::index(). Nombre: management.customers.
    Route::get('/clientes', [ClienteController::class, 'index'])->name('customers');
    // [GET /gestion/clientes/exportar/{format}] Solicita exportar datos de los clientes. Controlador: ClienteController::export(). Nombre: management.customers.export.
    Route::get('/clientes/exportar/{format}', [ClienteController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('customers.export');
    // [POST /gestion/clientes] Envía al controlador la creación/registro de los clientes. Controlador: ClienteController::store(). Nombre: management.customers.store.
    Route::post('/clientes', [ClienteController::class, 'store'])->name('customers.store');
    // [POST /gestion/clientes/importar] Solicita importar datos de los clientes. Controlador: ClienteController::import(). Nombre: management.customers.import.
    Route::post('/clientes/importar', [ClienteController::class, 'import'])->name('customers.import');
    // [PUT /gestion/clientes/{customer}] Envía una actualización de los clientes. Controlador: ClienteController::update(). Nombre: management.customers.update.
    Route::put('/clientes/{customer}', [ClienteController::class, 'update'])->name('customers.update');
    // [DELETE /gestion/clientes/{customer}] Solicita desactivar los clientes. Controlador: ClienteController::deactivate(). Nombre: management.customers.deactivate.
    Route::delete('/clientes/{customer}', [ClienteController::class, 'deactivate'])->name('customers.deactivate');

    // @MODULO 20 | PEDIDOS EN ADMINISTRACIÓN
    // Listado, exportación y cambios de estado de pedidos.

    // [GET /gestion/pedidos] Abre el listado o pantalla principal de los pedidos. Controlador: PedidoController::index(). Nombre: management.orders.
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('orders');
    // [GET /gestion/pedidos/exportar/{format}] Solicita exportar datos de los pedidos. Controlador: PedidoController::export(). Nombre: management.orders.export.
    Route::get('/pedidos/exportar/{format}', [PedidoController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('orders.export');
    // [PATCH /gestion/pedidos/{order}/estado] Solicita cambiar el estado de un pedido. Controlador: PedidoController::updateStatus(). Nombre: management.orders.status. Parámetro(s) numérico(s).
    Route::patch('/pedidos/{order}/estado', [PedidoController::class, 'updateStatus'])
        ->whereNumber('order')->name('orders.status');

    // @MODULO 21 | VENTAS
    // Ventas minoristas/mayoristas, conversión de cotizaciones, confirmación y anulación.

    // [GET /gestion/ventas] Abre el listado o pantalla principal de las ventas. Controlador: VentaController::index(). Nombre: management.sales.
    Route::get('/ventas', [VentaController::class, 'index'])->name('sales');
    // [GET /gestion/ventas/diseno/minoristas] Muestra el panel de ventas minoristas. Controlador: VentaController::panelMinorista(). Nombre: management.sales.preview.retail. Permiso adicional indicado en la ruta.
    Route::get('/ventas/diseno/minoristas', [VentaController::class, 'panelMinorista'])
        ->middleware('permiso:ruta')->name('sales.preview.retail');
    // [GET /gestion/ventas/diseno/mayoristas] Muestra el panel de ventas mayoristas. Controlador: VentaController::panelMayorista(). Nombre: management.sales.preview.wholesale. Permiso adicional indicado en la ruta.
    Route::get('/ventas/diseno/mayoristas', [VentaController::class, 'panelMayorista'])
        ->middleware('permiso:ruta')->name('sales.preview.wholesale');
    // [GET /gestion/ventas/exportar/{format?}] Solicita exportar datos de las ventas. Controlador: VentaController::export(). Nombre: management.sales.export.
    Route::get('/ventas/exportar/{format?}', [VentaController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('sales.export');
    // [POST /gestion/ventas] Registra una venta. Controlador: VentaController::store(). Nombre: management.sales.store.
    Route::post('/ventas', [VentaController::class, 'store'])->name('sales.store');
    // [POST /gestion/ventas/cotizaciones/{quotation}/convertir] Solicita convertir una cotización en venta. Controlador: VentaController::convertirCotizacion(). Nombre: management.sales.convert_quotation. Parámetro(s) numérico(s).
    Route::post('/ventas/cotizaciones/{quotation}/convertir', [VentaController::class, 'convertirCotizacion'])->whereNumber('quotation')->name('sales.convert_quotation');
    // [POST /gestion/ventas/{venta}/confirmar] Solicita confirmar una venta en borrador. Controlador: VentaController::confirmarBorrador(). Nombre: management.sales.confirm. Parámetro(s) numérico(s).
    Route::post('/ventas/{venta}/confirmar', [VentaController::class, 'confirmarBorrador'])->whereNumber('venta')->name('sales.confirm');
    // [PUT /gestion/ventas/{order}] Envía una actualización de las ventas. Controlador: VentaController::update(). Nombre: management.sales.update. Parámetro(s) numérico(s).
    Route::put('/ventas/{order}', [VentaController::class, 'update'])->whereNumber('order')->name('sales.update');
    // [POST /gestion/ventas/{venta}/anular] Solicita anular una venta. Controlador: VentaController::cancel(). Nombre: management.sales.cancel. Parámetro(s) numérico(s).
    Route::post('/ventas/{venta}/anular', [VentaController::class, 'cancel'])->whereNumber('venta')->name('sales.cancel');

    // @MODULO 22 | DEVOLUCIONES Y GARANTÍAS
    // Consulta, exportación, registro, actualización y método destroy.

    // [GET /gestion/devoluciones-garantias] Abre el listado o pantalla principal de las devoluciones y garantías. Controlador: DevolucionGarantiaController::index(). Nombre: management.returns.
    Route::get('/devoluciones-garantias', [DevolucionGarantiaController::class, 'index'])->name('returns');
    // [GET /gestion/devoluciones-garantias/exportar/{format?}] Solicita exportar datos de las devoluciones y garantías. Controlador: DevolucionGarantiaController::export(). Nombre: management.returns.export.
    Route::get('/devoluciones-garantias/exportar/{format?}', [DevolucionGarantiaController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('returns.export');
    // [POST /gestion/devoluciones-garantias] Envía al controlador la creación/registro de las devoluciones y garantías. Controlador: DevolucionGarantiaController::store(). Nombre: management.returns.store.
    Route::post('/devoluciones-garantias', [DevolucionGarantiaController::class, 'store'])->name('returns.store');
    // [PUT /gestion/devoluciones-garantias/{devolucionGarantia}] Envía una actualización de las devoluciones y garantías. Controlador: DevolucionGarantiaController::update(). Nombre: management.returns.update. Parámetro(s) numérico(s).
    Route::put('/devoluciones-garantias/{devolucionGarantia}', [DevolucionGarantiaController::class, 'update'])->whereNumber('devolucionGarantia')->name('returns.update');
    // [DELETE /gestion/devoluciones-garantias/{devolucionGarantia}] Solicita eliminar las devoluciones y garantías. Controlador: DevolucionGarantiaController::destroy(). Nombre: management.returns.destroy. Parámetro(s) numérico(s).
    Route::delete('/devoluciones-garantias/{devolucionGarantia}', [DevolucionGarantiaController::class, 'destroy'])->whereNumber('devolucionGarantia')->name('returns.destroy');

    // @MODULO 23 | SOPORTE Y RECLAMOS
    // Casos de soporte y cierre mediante actualización de estado.

    // [GET /gestion/soporte-reclamos] Abre el listado o pantalla principal de los casos de soporte. Controlador: SoporteReclamoController::index(). Nombre: management.support.
    Route::get('/soporte-reclamos', [SoporteReclamoController::class, 'index'])->name('support');
    // [POST /gestion/soporte-reclamos] Envía al controlador la creación/registro de los casos de soporte. Controlador: SoporteReclamoController::store(). Nombre: management.support.store.
    Route::post('/soporte-reclamos', [SoporteReclamoController::class, 'store'])->name('support.store');
    // [PUT /gestion/soporte-reclamos/{casoSoporte}] Envía una actualización de los casos de soporte. Controlador: SoporteReclamoController::update(). Nombre: management.support.update. Parámetro(s) numérico(s).
    Route::put('/soporte-reclamos/{casoSoporte}', [SoporteReclamoController::class, 'update'])->whereNumber('casoSoporte')->name('support.update');
    // [PATCH /gestion/soporte-reclamos/{casoSoporte}/cerrar] Cierra un caso de soporte. Controlador: SoporteReclamoController::deactivate(). Nombre: management.support.close. Parámetro(s) numérico(s).
    Route::patch('/soporte-reclamos/{casoSoporte}/cerrar', [SoporteReclamoController::class, 'deactivate'])->whereNumber('casoSoporte')->name('support.close');
    // [GET /gestion/soporte-reclamos/exportar/{format?}] Solicita exportar datos de los casos de soporte. Controlador: SoporteReclamoController::export(). Nombre: management.support.export.
    Route::get('/soporte-reclamos/exportar/{format?}', [SoporteReclamoController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('support.export');
});

// @MODULO 24 | CUENTA PRIVADA: PERFIL, SEGURIDAD Y SESIONES
// Cuenta de gestión, contraseña, avatar, preferencias, sesiones, actividad y avisos.

// GRUPO AUTENTICADO: auth + middleware personalizados de cuenta y sesión.
Route::middleware(['auth', 'cuenta.activa', 'sesion.usuario'])->group(function (): void {
    // [GET /gestion/cuenta] Abre el panel de cuenta del usuario. Controlador: CuentaController::dashboard(). Nombre: management.account.
    Route::get('/gestion/cuenta', [CuentaController::class, 'dashboard'])->name('management.account');
    // [GET /gestion/cuenta/perfil] Muestra el perfil del usuario. Controlador: CuentaController::profile(). Nombre: management.account.profile.show.
    Route::get('/gestion/cuenta/perfil', [CuentaController::class, 'profile'])->name('management.account.profile.show');
    // [PUT /gestion/cuenta/perfil] Solicita actualizar los datos del perfil. Controlador: CuentaAdministracionController::updateProfile(). Nombre: management.account.profile.
    Route::put('/gestion/cuenta/perfil', [CuentaAdministracionController::class, 'updateProfile'])->name('management.account.profile');
    // [PUT /gestion/cuenta/password] Solicita cambiar la contraseña. Controlador: CuentaAdministracionController::updatePassword(). Nombre: management.account.password.
    Route::put('/gestion/cuenta/password', [CuentaAdministracionController::class, 'updatePassword'])->name('management.account.password');
    // [PUT /gestion/cuenta/fotografia] Solicita actualizar la fotografía de perfil. Controlador: CuentaAdministracionController::updateAvatar(). Nombre: management.account.avatar.
    Route::put('/gestion/cuenta/fotografia', [CuentaAdministracionController::class, 'updateAvatar'])->name('management.account.avatar');
    // [DELETE /gestion/cuenta/fotografia] Solicita quitar la fotografía de perfil. Controlador: CuentaAdministracionController::deleteAvatar(). Nombre: management.account.avatar.delete.
    Route::delete('/gestion/cuenta/fotografia', [CuentaAdministracionController::class, 'deleteAvatar'])->name('management.account.avatar.delete');
    // [GET /gestion/cuenta/seguridad] Muestra la pantalla de seguridad de cuenta. Controlador: CuentaAdministracionController::security(). Nombre: management.account.security.
    Route::get('/gestion/cuenta/seguridad', [CuentaAdministracionController::class, 'security'])->name('management.account.security');
    // [GET /gestion/cuenta/sesiones] Muestra las sesiones de usuario. Controlador: CuentaAdministracionController::sessions(). Nombre: management.account.sessions.
    Route::get('/gestion/cuenta/sesiones', [CuentaAdministracionController::class, 'sessions'])->name('management.account.sessions');
    // [DELETE /gestion/cuenta/sesiones/{sessionId}] Solicita revocar una sesión existente. Controlador: CuentaAdministracionController::revokeSession(). Nombre: management.account.sessions.revoke. Parámetro(s) numérico(s).
    Route::delete('/gestion/cuenta/sesiones/{sessionId}', [CuentaAdministracionController::class, 'revokeSession'])->whereNumber('sessionId')->name('management.account.sessions.revoke');
    // [GET /gestion/cuenta/preferencias] Muestra las preferencias de usuario. Controlador: CuentaAdministracionController::preferences(). Nombre: management.account.preferences.
    Route::get('/gestion/cuenta/preferencias', [CuentaAdministracionController::class, 'preferences'])->name('management.account.preferences');
    // [PUT /gestion/cuenta/preferencias] Solicita guardar las preferencias. Controlador: CuentaAdministracionController::updatePreferences(). Nombre: management.account.preferences.update.
    Route::put('/gestion/cuenta/preferencias', [CuentaAdministracionController::class, 'updatePreferences'])->name('management.account.preferences.update');
    // [GET /gestion/cuenta/actividad] Muestra la actividad de la cuenta. Controlador: CuentaAdministracionController::activity(). Nombre: management.account.activity.
    Route::get('/gestion/cuenta/actividad', [CuentaAdministracionController::class, 'activity'])->name('management.account.activity');
    // [GET /gestion/cuenta/notificaciones] Muestra las notificaciones de la cuenta. Controlador: CuentaAdministracionController::notifications(). Nombre: management.account.notifications.
    Route::get('/gestion/cuenta/notificaciones', [CuentaAdministracionController::class, 'notifications'])->name('management.account.notifications');
    // [PATCH /gestion/cuenta/notificaciones/{notificationId}] Marca una notificación como leída. Controlador: CuentaAdministracionController::markNotificationRead(). Nombre: management.account.notifications.read.
    Route::patch('/gestion/cuenta/notificaciones/{notificationId}', [CuentaAdministracionController::class, 'markNotificationRead'])->name('management.account.notifications.read');
});

// @MODULO 25 | PUBLICIDAD INTELIGENTE DE GESTIÓN
// Acceso protegido a la pantalla de publicidad inteligente.

// [GET /gestion/publicidad-inteligente] Muestra la administración de publicidad inteligente. Controlador: PublicidadInteligenteController::ads(). Nombre: smart.ads. Permiso adicional indicado en la ruta.
Route::get('/gestion/publicidad-inteligente', [PublicidadInteligenteController::class, 'ads'])
    ->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('smart.ads');

// @MODULO 26 | INVENTARIO Y LOGÍSTICA
// Nuevo grupo /gestion con autenticación y permiso por ruta.

// GRUPO PROTEGIDO: todas las rutas internas comienzan por /gestion y management.
Route::prefix('gestion')->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('management.')->group(function (): void {
    // [GET /gestion/inventario] Muestra el inventario y sus movimientos. Controlador: InventarioController::index(). Nombre: management.inventory.
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventory');
    // [GET /gestion/inventario/movimientos] Muestra el inventario y sus movimientos. Controlador: InventarioController::index(). Nombre: management.inventory.movements.
    Route::get('/inventario/movimientos', [InventarioController::class, 'index'])->name('inventory.movements');

    // @MODULO 27 | TRANSFERENCIAS DE EXISTENCIAS
    // Solicitud, aprobación, despacho, recepción, conciliación, rechazo, devolución y cierre.

    // [GET /gestion/inventario/diseno/transferencias] Abre el listado o pantalla principal de las transferencias de existencias. Controlador: TransferenciaExistenciaController::index(). Nombre: management.inventory.preview.transfers.
    Route::get('/inventario/diseno/transferencias', [TransferenciaExistenciaController::class, 'index'])->name('inventory.preview.transfers');
    // [GET /gestion/inventario/transferencias/exportar/{format?}] Solicita exportar datos de las transferencias de existencias. Controlador: TransferenciaExistenciaController::export(). Nombre: management.inventory.transfers_export.
    Route::get('/inventario/transferencias/exportar/{format?}', [TransferenciaExistenciaController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('inventory.transfers_export');
    // [POST /gestion/inventario/transferencias] Crea una solicitud de transferencia de inventario. Controlador: TransferenciaExistenciaController::store(). Nombre: management.inventory.transfer_request.
    Route::post('/inventario/transferencias', [TransferenciaExistenciaController::class, 'store'])->name('inventory.transfer_request');
    // [POST /gestion/inventario/transferencias/{transfer}/approve] Solicita aprobar una transferencia. Controlador: TransferenciaExistenciaController::approve(). Nombre: management.inventory.transfer_approve. Parámetro(s) numérico(s).
    Route::post('/inventario/transferencias/{transfer}/approve', [TransferenciaExistenciaController::class, 'approve'])->whereNumber('transfer')->name('inventory.transfer_approve');
    // [POST /gestion/inventario/transferencias/{transfer}/dispatch] Solicita despachar una transferencia. Controlador: TransferenciaExistenciaController::dispatch(). Nombre: management.inventory.transfer_dispatch. Parámetro(s) numérico(s).
    Route::post('/inventario/transferencias/{transfer}/dispatch', [TransferenciaExistenciaController::class, 'dispatch'])->whereNumber('transfer')->name('inventory.transfer_dispatch');
    // [POST /gestion/inventario/transferencias/{transfer}/receive] Solicita confirmar la recepción de una transferencia. Controlador: TransferenciaExistenciaController::receive(). Nombre: management.inventory.transfer_receive. Parámetro(s) numérico(s).
    Route::post('/inventario/transferencias/{transfer}/receive', [TransferenciaExistenciaController::class, 'receive'])->whereNumber('transfer')->name('inventory.transfer_receive');
    // [POST /gestion/inventario/transferencias/{transfer}/return] Solicita devolver una transferencia al origen. Controlador: TransferenciaExistenciaController::returnToOrigin(). Nombre: management.inventory.transfer_return. Parámetro(s) numérico(s).
    Route::post('/inventario/transferencias/{transfer}/return', [TransferenciaExistenciaController::class, 'returnToOrigin'])->whereNumber('transfer')->name('inventory.transfer_return');
    // [POST /gestion/inventario/transferencias/{transfer}/reconcile] Solicita conciliar diferencias de transferencia. Controlador: TransferenciaExistenciaController::reconcileDifference(). Nombre: management.inventory.transfer_reconcile. Parámetro(s) numérico(s).
    Route::post('/inventario/transferencias/{transfer}/reconcile', [TransferenciaExistenciaController::class, 'reconcileDifference'])->whereNumber('transfer')->name('inventory.transfer_reconcile');
    // [POST /gestion/inventario/transferencias/{transfer}/reject] Solicita rechazar una transferencia. Controlador: TransferenciaExistenciaController::reject(). Nombre: management.inventory.transfer_reject. Parámetro(s) numérico(s).
    Route::post('/inventario/transferencias/{transfer}/reject', [TransferenciaExistenciaController::class, 'reject'])->whereNumber('transfer')->name('inventory.transfer_reject');
    // [POST /gestion/inventario/transferencias/{transfer}/cancel] Solicita cancelar una transferencia. Controlador: TransferenciaExistenciaController::cancel(). Nombre: management.inventory.transfer_cancel. Parámetro(s) numérico(s).
    Route::post('/inventario/transferencias/{transfer}/cancel', [TransferenciaExistenciaController::class, 'cancel'])->whereNumber('transfer')->name('inventory.transfer_cancel');
    // [POST /gestion/inventario/transferencias/{transfer}/close] Solicita cerrar una transferencia. Controlador: TransferenciaExistenciaController::close(). Nombre: management.inventory.transfer_close. Parámetro(s) numérico(s).
    Route::post('/inventario/transferencias/{transfer}/close', [TransferenciaExistenciaController::class, 'close'])->whereNumber('transfer')->name('inventory.transfer_close');

    // @MODULO 28 | ENVÍOS Y ENTREGAS
    // Vista de envíos, detalle, exportación y cambios de estado.

    // [GET /gestion/inventario/diseno/envios] Muestra el panel de envíos. Controlador: EnviosPedidosController::mostrar(). Nombre: management.inventory.preview.shipments. Permiso adicional indicado en la ruta.
    Route::get('/inventario/diseno/envios', [EnviosPedidosController::class, 'mostrar'])
        ->middleware(['permiso:ruta', 'permiso:ruta', 'permiso:pantalla.admin.shipments.consultar'])->name('inventory.preview.shipments');
    // [PATCH /gestion/inventario/envios/{envioPedido}/estado] Solicita actualizar el estado del envío. Controlador: EnviosPedidosController::actualizarEstado(). Nombre: management.inventory.shipments.status.update. Parámetro(s) numérico(s). Permiso adicional indicado en la ruta.
    Route::patch('/inventario/envios/{envioPedido}/estado', [EnviosPedidosController::class, 'actualizarEstado'])
        ->whereNumber('envioPedido')
        ->middleware(['permiso:ruta', 'permiso:ruta'])
        ->name('inventory.shipments.status.update');
    // [GET /gestion/inventario/diseno/envios/{envio}] Muestra el detalle del envío. Controlador: EnviosPedidosController::detalle(). Nombre: management.inventory.preview.shipments.show. Parámetro(s) numérico(s). Permiso adicional indicado en la ruta.
    Route::get('/inventario/diseno/envios/{envio}', [EnviosPedidosController::class, 'detalle'])
        ->whereNumber('envio')
        ->middleware(['permiso:ruta', 'permiso:ruta', 'permiso:pantalla.admin.shipments.consultar'])->name('inventory.preview.shipments.show');
    // [GET /gestion/inventario/diseno/envios/exportar/{format?}] Exporta información de envíos. Controlador: EnviosPedidosController::exportar(). Nombre: management.inventory.preview.shipments.export. Permiso adicional indicado en la ruta.
    Route::get('/inventario/diseno/envios/exportar/{format?}', [EnviosPedidosController::class, 'exportar'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])
        ->middleware(['permiso:ruta', 'permiso:ruta'])->name('inventory.preview.shipments.export');

    // @MODULO 29 | NÚMEROS DE SERIE Y AJUSTES DE INVENTARIO
    // Consultas de series/IMEI y ajuste de existencias.

    // [GET /gestion/inventario/diseno/series] Muestra la consulta de números de serie/IMEI. Controlador: DispositivosController::mostrar(). Nombre: management.inventory.preview.serials. Permiso adicional indicado en la ruta.
    Route::get('/inventario/diseno/series', [DispositivosController::class, 'mostrar'])
        ->middleware(['permiso:ruta', 'permiso:ruta', 'permiso:pantalla.admin.imei.consultar'])->name('inventory.preview.serials');
    // [POST /gestion/inventario/{product}] Envía un ajuste de inventario. Controlador: InventarioController::adjust(). Nombre: management.inventory.adjust. Parámetro(s) numérico(s).
    Route::post('/inventario/{product}', [InventarioController::class, 'adjust'])->whereNumber('product')->name('inventory.adjust');
});

// @MODULO 30 | PROVEEDORES Y COMPRAS
// Grupo comercial de abastecimiento; incluye recepción parcial y total.

// GRUPO PROTEGIDO: todas las rutas internas comienzan por /gestion y management.
Route::prefix('gestion')->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('management.')->group(function (): void {
    // [GET /gestion/proveedores] Abre el listado o pantalla principal de los proveedores. Controlador: ProveedorController::index(). Nombre: management.suppliers.
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('suppliers');
    // [GET /gestion/proveedores/exportar/{format?}] Solicita exportar datos de los proveedores. Controlador: ProveedorController::export(). Nombre: management.suppliers.export.
    Route::get('/proveedores/exportar/{format?}', [ProveedorController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('suppliers.export');
    // [POST /gestion/proveedores] Envía al controlador la creación/registro de los proveedores. Controlador: ProveedorController::store(). Nombre: management.suppliers.store.
    Route::post('/proveedores', [ProveedorController::class, 'store'])->name('suppliers.store');
    // [PUT /gestion/proveedores/{supplier}] Envía una actualización de los proveedores. Controlador: ProveedorController::update(). Nombre: management.suppliers.update.
    Route::put('/proveedores/{supplier}', [ProveedorController::class, 'update'])->name('suppliers.update');
    // [DELETE /gestion/proveedores/{supplier}] Solicita desactivar los proveedores. Controlador: ProveedorController::deactivate(). Nombre: management.suppliers.deactivate.
    Route::delete('/proveedores/{supplier}', [ProveedorController::class, 'deactivate'])->name('suppliers.deactivate');
    // [GET /gestion/compras] Abre el listado o pantalla principal de las compras. Controlador: CompraController::index(). Nombre: management.purchases.
    Route::get('/compras', [CompraController::class, 'index'])->name('purchases');
    // [GET /gestion/compras/exportar/{format}] Solicita exportar datos de las compras. Controlador: CompraController::export(). Nombre: management.purchases.export.
    Route::get('/compras/exportar/{format}', [CompraController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('purchases.export');
    // [GET /gestion/compras/plantilla-importacion] Descarga la plantilla de importación. Controlador: CompraController::importTemplate(). Nombre: management.purchases.import_template.
    Route::get('/compras/plantilla-importacion', [CompraController::class, 'importTemplate'])->name('purchases.import_template');
    // [POST /gestion/compras] Registra una compra. Controlador: CompraController::store(). Nombre: management.purchases.store.
    Route::post('/compras', [CompraController::class, 'store'])->name('purchases.store');
    // [PUT /gestion/compras/{compra}] Envía una actualización de las compras. Controlador: CompraController::update(). Nombre: management.purchases.update. Parámetro(s) numérico(s).
    Route::put('/compras/{compra}', [CompraController::class, 'update'])->whereNumber('compra')->name('purchases.update');
    // [POST /gestion/compras/importar] Solicita importar datos de las compras. Controlador: CompraController::import(). Nombre: management.purchases.import.
    Route::post('/compras/importar', [CompraController::class, 'import'])->name('purchases.import');
    // [POST /gestion/compras/{compra}/aprobar] Solicita aprobar una compra. Controlador: CompraController::approve(). Nombre: management.purchases.approve. Parámetro(s) numérico(s).
    Route::post('/compras/{compra}/aprobar', [CompraController::class, 'approve'])->whereNumber('compra')->name('purchases.approve');
    // [POST /gestion/compras/{compra}/anular] Solicita anular una compra. Controlador: CompraController::cancel(). Nombre: management.purchases.cancel. Parámetro(s) numérico(s).
    Route::post('/compras/{compra}/anular', [CompraController::class, 'cancel'])->whereNumber('compra')->name('purchases.cancel');
    // [POST /gestion/compras/{compra}/recibir] Solicita registrar la recepción de una compra. Controlador: CompraController::receive(). Nombre: management.purchases.receive. Parámetro(s) numérico(s).
    Route::post('/compras/{compra}/recibir', [CompraController::class, 'receive'])->whereNumber('compra')->name('purchases.receive');
    // [POST /compras/{compra}/recepcion-parcial] Solicita registrar una recepción parcial. Controlador: CompraController::recibirParcial(). Nombre: purchases.receive_partial. Parámetro(s) numérico(s).
    Route::post('/compras/{compra}/recepcion-parcial', [CompraController::class, 'recibirParcial'])->whereNumber('compra')->name('purchases.receive_partial');
});

// @MODULO 31 | MARKETING, SEGMENTOS Y REPORTES
// Campañas, cupones, segmentación, métricas y exportaciones.

// GRUPO PROTEGIDO: todas las rutas internas comienzan por /gestion y management.
Route::prefix('gestion')->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('management.')->group(function (): void {
    // [GET /gestion/campanas] Abre el listado o pantalla principal de las campañas. Controlador: CampaniaController::index(). Nombre: management.campaigns.
    Route::get('/campanas', [CampaniaController::class, 'index'])->name('campaigns');
    // [GET /gestion/campanas/exportar] Solicita exportar datos de las campañas. Controlador: CampaniaController::export(). Nombre: management.campaigns.export.
    Route::get('/campanas/exportar', [CampaniaController::class, 'export'])->name('campaigns.export');
    // [POST /gestion/campanas] Envía al controlador la creación/registro de las campañas. Controlador: CampaniaController::store(). Nombre: management.campaigns.store.
    Route::post('/campanas', [CampaniaController::class, 'store'])->name('campaigns.store');
    // [POST /gestion/campanas/importar] Solicita importar datos de las campañas. Controlador: CampaniaController::import(). Nombre: management.campaigns.import.
    Route::post('/campanas/importar', [CampaniaController::class, 'import'])->name('campaigns.import');
    // [PUT /gestion/campanas/{campaign}] Envía una actualización de las campañas. Controlador: CampaniaController::update(). Nombre: management.campaigns.update.
    Route::put('/campanas/{campaign}', [CampaniaController::class, 'update'])->name('campaigns.update');
    // [DELETE /gestion/campanas/{campaign}] Solicita desactivar las campañas. Controlador: CampaniaController::deactivate(). Nombre: management.campaigns.deactivate.
    Route::delete('/campanas/{campaign}', [CampaniaController::class, 'deactivate'])->name('campaigns.deactivate');
    // [GET /gestion/cupones] Abre el listado o pantalla principal de los cupones. Controlador: CuponController::index(). Nombre: management.coupons.
    Route::get('/cupones', [CuponController::class, 'index'])->name('coupons');
    // [GET /gestion/cupones/exportar/{format?}] Solicita exportar datos de los cupones. Controlador: CuponController::export(). Nombre: management.coupons.export.
    Route::get('/cupones/exportar/{format?}', [CuponController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('coupons.export');
    // [POST /gestion/cupones] Envía al controlador la creación/registro de los cupones. Controlador: CuponController::store(). Nombre: management.coupons.store.
    Route::post('/cupones', [CuponController::class, 'store'])->name('coupons.store');
    // [PUT /gestion/cupones/{coupon}] Envía una actualización de los cupones. Controlador: CuponController::update(). Nombre: management.coupons.update. Parámetro(s) numérico(s).
    Route::put('/cupones/{coupon}', [CuponController::class, 'update'])->whereNumber('coupon')->name('coupons.update');
    // [DELETE /gestion/cupones/{coupon}] Solicita desactivar los cupones. Controlador: CuponController::deactivate(). Nombre: management.coupons.deactivate. Parámetro(s) numérico(s).
    Route::delete('/cupones/{coupon}', [CuponController::class, 'deactivate'])->whereNumber('coupon')->name('coupons.deactivate');
    // [GET /gestion/segmentos] Abre el listado o pantalla principal de los segmentos de clientes. Controlador: SegmentoClienteController::index(). Nombre: management.segments.
    Route::get('/segmentos', [SegmentoClienteController::class, 'index'])->name('segments');
    // [GET /gestion/segmentos/exportar/{format?}] Solicita exportar datos de los segmentos de clientes. Controlador: SegmentoClienteController::export(). Nombre: management.segments.export.
    Route::get('/segmentos/exportar/{format?}', [SegmentoClienteController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('segments.export');
    // [POST /gestion/segmentos] Envía al controlador la creación/registro de los segmentos de clientes. Controlador: SegmentoClienteController::store(). Nombre: management.segments.store.
    Route::post('/segmentos', [SegmentoClienteController::class, 'store'])->name('segments.store');
    // [PUT /gestion/segmentos/{segment}] Envía una actualización de los segmentos de clientes. Controlador: SegmentoClienteController::update(). Nombre: management.segments.update. Parámetro(s) numérico(s).
    Route::put('/segmentos/{segment}', [SegmentoClienteController::class, 'update'])->whereNumber('segment')->name('segments.update');
    // [DELETE /gestion/segmentos/{segment}] Solicita desactivar los segmentos de clientes. Controlador: SegmentoClienteController::deactivate(). Nombre: management.segments.deactivate. Parámetro(s) numérico(s).
    Route::delete('/segmentos/{segment}', [SegmentoClienteController::class, 'deactivate'])->whereNumber('segment')->name('segments.deactivate');
    // [GET /gestion/reportes] Muestra el panel de reportes. Controlador: ReporteController::index(). Nombre: management.reports.
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reports');
    // [GET /gestion/analisis/diseno/reportes-globales] Muestra el panel de reportes globales. Controlador: PanelAnalisisAuditoriaController::globales(). Nombre: management.reports.preview.global. Permiso adicional indicado en la ruta.
    Route::get('/analisis/diseno/reportes-globales', [PanelAnalisisAuditoriaController::class, 'globales'])
        ->middleware('permiso:ruta')->name('reports.preview.global');
    // [GET /gestion/analisis/diseno/{tipo}/exportar/{format?}] Solicita exportar datos. Controlador: PanelAnalisisAuditoriaController::exportar(). Nombre: management.reports.preview.export. Permiso adicional indicado en la ruta.
    Route::get('/analisis/diseno/{tipo}/exportar/{format?}', [PanelAnalisisAuditoriaController::class, 'exportar'])
        ->whereIn('tipo', ['globales', 'operaciones'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])
        ->middleware('permiso:ruta')->name('reports.preview.export');
    // [GET /gestion/reportes/exportar/{format?}] Solicita exportar datos de los reportes. Controlador: ReporteController::export(). Nombre: management.reports.export.
    Route::get('/reportes/exportar/{format?}', [ReporteController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('reports.export');
});

// @MODULO 32 | FINANZAS: PAGOS, REEMBOLSOS Y CAJAS
// Cobros y pagos, facturación, arqueos/sesiones y cuentas financieras.

// GRUPO PROTEGIDO: todas las rutas internas comienzan por /gestion y management.
Route::prefix('gestion')->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('management.')->group(function (): void {
    // [GET /gestion/pagos] Muestra los pagos. Controlador: FinanzasController::pagos(). Nombre: management.payments.
    Route::get('/pagos', [FinanzasController::class, 'pagos'])->name('payments');
    // [GET /gestion/pagos/exportar/{format}] Exporta pagos. Controlador: FinanzasController::exportarPagos(). Nombre: management.payments.export.
    Route::get('/pagos/exportar/{format}', [FinanzasController::class, 'exportarPagos'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('payments.export');
    // [POST /gestion/pagos] Registra un pago. Controlador: FinanzasController::registrarPago(). Nombre: management.payments.store.
    Route::post('/pagos', [FinanzasController::class, 'registrarPago'])->name('payments.store');
    // [GET /gestion/pagos/{pago}] Muestra el detalle de un pago. Controlador: FinanzasController::detallePago(). Nombre: management.payments.show. Parámetro(s) numérico(s).
    Route::get('/pagos/{pago}', [FinanzasController::class, 'detallePago'])->whereNumber('pago')->name('payments.show');
    // [PATCH /gestion/pagos/{pago}/anular] Solicita anular un pago. Controlador: FinanzasController::anularPago(). Nombre: management.payments.cancel. Parámetro(s) numérico(s).
    Route::patch('/pagos/{pago}/anular', [FinanzasController::class, 'anularPago'])->whereNumber('pago')->name('payments.cancel');
    // [PATCH /gestion/pagos/{pago}/confirmar-efectivo] Solicita confirmar un pago en efectivo. Controlador: FinanzasController::confirmarPagoEfectivo(). Nombre: management.payments.cash.update. Parámetro(s) numérico(s).
    Route::patch('/pagos/{pago}/confirmar-efectivo', [FinanzasController::class, 'confirmarPagoEfectivo'])->whereNumber('pago')->name('payments.cash.update');
    // [GET /gestion/facturacion] Abre el listado o pantalla principal de la facturación. Controlador: FacturacionController::index(). Nombre: management.billing.
    Route::get('/facturacion', [FacturacionController::class, 'index'])->name('billing');
    // [GET /gestion/facturacion/exportar/{format}] Solicita exportar datos de la facturación. Controlador: FacturacionController::export(). Nombre: management.billing.export.
    Route::get('/facturacion/exportar/{format}', [FacturacionController::class, 'export'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('billing.export');
    // [GET /gestion/reembolsos] Muestra los reembolsos. Controlador: FinanzasController::reembolsos(). Nombre: management.refunds.
    Route::get('/reembolsos', [FinanzasController::class, 'reembolsos'])->name('refunds');
    // [GET /gestion/reembolsos/exportar/{format}] Exporta los reembolsos. Controlador: FinanzasController::exportarReembolsos(). Nombre: management.refunds.export.
    Route::get('/reembolsos/exportar/{format}', [FinanzasController::class, 'exportarReembolsos'])
        ->whereIn('format', ['pdf', 'excel', 'csv'])->name('refunds.export');
    // [POST /gestion/reembolsos] Registra una solicitud de reembolso. Controlador: FinanzasController::solicitarReembolso(). Nombre: management.refunds.store.
    Route::post('/reembolsos', [FinanzasController::class, 'solicitarReembolso'])->name('refunds.store');
    // [GET /gestion/reembolsos/{reembolso}] Muestra el detalle de un reembolso. Controlador: FinanzasController::detalleReembolso(). Nombre: management.refunds.show. Parámetro(s) numérico(s).
    Route::get('/reembolsos/{reembolso}', [FinanzasController::class, 'detalleReembolso'])->whereNumber('reembolso')->name('refunds.show');
    // [PATCH /gestion/reembolsos/{reembolso}/aprobar] Solicita aprobar un reembolso. Controlador: FinanzasController::aprobarReembolso(). Nombre: management.refunds.approve. Parámetro(s) numérico(s).
    Route::patch('/reembolsos/{reembolso}/aprobar', [FinanzasController::class, 'aprobarReembolso'])->whereNumber('reembolso')->name('refunds.approve');
    // [PATCH /gestion/reembolsos/{reembolso}/anular] Solicita anular un reembolso. Controlador: FinanzasController::anularReembolso(). Nombre: management.refunds.cancel. Parámetro(s) numérico(s).
    Route::patch('/reembolsos/{reembolso}/anular', [FinanzasController::class, 'anularReembolso'])->whereNumber('reembolso')->name('refunds.cancel');
    // [GET /gestion/finanzas/diseno/pagos] Redirige a management.payments. Controlador: redirección. Nombre: management.payments.preview.receipts. Permiso adicional indicado en la ruta.
    Route::get('/finanzas/diseno/pagos', static fn () => redirect()->route('management.payments'))->middleware('permiso:ruta')->name('payments.preview.receipts');
    // [GET /gestion/finanzas/diseno/cajas] Abre el listado o pantalla principal de las cajas. Controlador: CajaController::index(). Nombre: management.payments.preview.cash_registers. Permiso adicional indicado en la ruta.
    Route::get('/finanzas/diseno/cajas', [CajaController::class, 'index'])->middleware('permiso:ruta')->name('payments.preview.cash_registers');
    // [POST /gestion/finanzas/cajas] Envía al controlador la creación/registro de las cajas. Controlador: CajaController::store(). Nombre: management.payments.cash_registers.store. Permiso adicional indicado en la ruta.
    Route::post('/finanzas/cajas', [CajaController::class, 'store'])->middleware('permiso:ruta')->name('payments.cash_registers.store');
    // [PUT /gestion/finanzas/cajas/{caja}] Envía una actualización de las cajas. Controlador: CajaController::update(). Nombre: management.payments.cash_registers.update. Parámetro(s) numérico(s). Permiso adicional indicado en la ruta.
    Route::put('/finanzas/cajas/{caja}', [CajaController::class, 'update'])->whereNumber('caja')->middleware('permiso:ruta')->name('payments.cash_registers.update');
    // [PATCH /gestion/finanzas/cajas/{caja}/estado] Solicita cambiar el estado de una caja. Controlador: CajaController::updateStatus(). Nombre: management.payments.cash_registers.status.update. Parámetro(s) numérico(s). Permiso adicional indicado en la ruta.
    Route::patch('/finanzas/cajas/{caja}/estado', [CajaController::class, 'updateStatus'])->whereNumber('caja')->middleware('permiso:ruta')->name('payments.cash_registers.status.update');
    // [GET /gestion/finanzas/diseno/aperturas-cierres] Abre el listado o pantalla principal de las sesiones de caja. Controlador: SesionCajaController::index(). Nombre: management.payments.preview.cash_sessions. Permiso adicional indicado en la ruta.
    Route::get('/finanzas/diseno/aperturas-cierres', [SesionCajaController::class, 'index'])->middleware('permiso:ruta')->name('payments.preview.cash_sessions');
    // [POST /gestion/finanzas/sesiones-caja] Envía al controlador la creación/registro de las sesiones de caja. Controlador: SesionCajaController::store(). Nombre: management.payments.cash_sessions.store. Permiso adicional indicado en la ruta.
    Route::post('/finanzas/sesiones-caja', [SesionCajaController::class, 'store'])->middleware('permiso:ruta')->name('payments.cash_sessions.store');
    // [GET /gestion/finanzas/sesiones-caja/{sesionCaja}] Consulta el detalle de las sesiones de caja. Controlador: SesionCajaController::show(). Nombre: management.payments.cash_sessions.show. Parámetro(s) numérico(s). Permiso adicional indicado en la ruta.
    Route::get('/finanzas/sesiones-caja/{sesionCaja}', [SesionCajaController::class, 'show'])->whereNumber('sesionCaja')->middleware('permiso:ruta')->name('payments.cash_sessions.show');
    // [POST /gestion/finanzas/sesiones-caja/{sesionCaja}/movimientos] Registra un movimiento en la sesión de caja. Controlador: SesionCajaController::storeMovement(). Nombre: management.payments.cash_sessions.movements.store. Parámetro(s) numérico(s). Permiso adicional indicado en la ruta.
    Route::post('/finanzas/sesiones-caja/{sesionCaja}/movimientos', [SesionCajaController::class, 'storeMovement'])->whereNumber('sesionCaja')->middleware('permiso:ruta')->name('payments.cash_sessions.movements.store');
    // [PATCH /gestion/finanzas/sesiones-caja/{sesionCaja}] Envía una actualización de las sesiones de caja. Controlador: SesionCajaController::update(). Nombre: management.payments.cash_sessions.update. Parámetro(s) numérico(s). Permiso adicional indicado en la ruta.
    Route::patch('/finanzas/sesiones-caja/{sesionCaja}', [SesionCajaController::class, 'update'])->whereNumber('sesionCaja')->middleware('permiso:ruta')->name('payments.cash_sessions.update');
    // [GET /gestion/finanzas/diseno/cuentas] Abre el listado o pantalla principal de las cuentas por pagar/cobrar. Controlador: CuentasFinancierasController::index(). Nombre: management.payments.preview.accounts. Permiso adicional indicado en la ruta.
    Route::get('/finanzas/diseno/cuentas', [CuentasFinancierasController::class, 'index'])->middleware('permiso:ruta')->name('payments.preview.accounts');
    // [POST /gestion/finanzas/cuentas-por-pagar] Registra una cuenta por pagar. Controlador: CuentasFinancierasController::crearCuentaPorPagar(). Nombre: management.payments.accounts.payables.store.
    Route::post('/finanzas/cuentas-por-pagar', [CuentasFinancierasController::class, 'crearCuentaPorPagar'])->name('payments.accounts.payables.store');
    // [POST /gestion/finanzas/cuentas-por-pagar/aplicar-pago] Aplica un pago a una cuenta por pagar. Controlador: CuentasFinancierasController::aplicarPago(). Nombre: management.payments.accounts.payables.update.
    Route::post('/finanzas/cuentas-por-pagar/aplicar-pago', [CuentasFinancierasController::class, 'aplicarPago'])
        ->name('payments.accounts.payables.update');
    // [POST /gestion/finanzas/cuentas-por-cobrar/aplicar-cobro] Aplica un cobro a una cuenta por cobrar. Controlador: CuentasFinancierasController::aplicarCobro(). Nombre: management.payments.accounts.receivables.update.
    Route::post('/finanzas/cuentas-por-cobrar/aplicar-cobro', [CuentasFinancierasController::class, 'aplicarCobro'])
        ->name('payments.accounts.receivables.update');
    // [GET /gestion/finanzas/diseno/reembolsos] Redirige a management.refunds. Controlador: redirección. Nombre: management.refunds.preview. Permiso adicional indicado en la ruta.
    Route::get('/finanzas/diseno/reembolsos', static fn () => redirect()->route('management.refunds'))->middleware('permiso:ruta')->name('refunds.preview');

    // @MODULO 33 | SEGURIDAD RBAC: ROLES, PERMISOS Y ASIGNACIONES
    // Administración de roles, permisos, privilegios por pantalla y asignaciones globales o por sede.

    // [GET /gestion/roles-permisos] Abre el listado o pantalla principal de los roles y permisos. Controlador: RolesPermisosController::index(). Nombre: management.roles_permissions.
    Route::get('/roles-permisos', [RolesPermisosController::class, 'index'])->name('roles_permissions');
    // [GET /gestion/matriz-privilegios] Muestra la matriz de privilegios. Controlador: RolesPermisosController::matrix(). Nombre: management.permissions_matrix.
    Route::get('/matriz-privilegios', [RolesPermisosController::class, 'matrix'])->name('permissions_matrix');
    // [POST /gestion/roles-permisos] Registra un nuevo rol. Controlador: RolesPermisosController::store(). Nombre: management.roles_permissions.store.
    Route::post('/roles-permisos', [RolesPermisosController::class, 'store'])->name('roles_permissions.store');
    // [POST /gestion/roles-permisos/campos-perfil] Registra un campo configurable de perfil. Controlador: RolesPermisosController::storeProfileField(). Nombre: management.roles_permissions.profile_fields.store.
    Route::post('/roles-permisos/campos-perfil', [RolesPermisosController::class, 'storeProfileField'])->name('roles_permissions.profile_fields.store');
    // [PUT /gestion/roles-permisos/{role}/campos-perfil] Actualiza campos de perfil asociados al rol. Controlador: RolesPermisosController::updateRoleProfileFields(). Nombre: management.roles_permissions.profile_fields.update. Parámetro(s) numérico(s).
    Route::put('/roles-permisos/{role}/campos-perfil', [RolesPermisosController::class, 'updateRoleProfileFields'])->whereNumber('role')->name('roles_permissions.profile_fields.update');
    // [PUT /gestion/roles-permisos/{role}] Actualiza los datos de un rol. Controlador: RolesPermisosController::update(). Nombre: management.roles_permissions.update. Parámetro(s) numérico(s).
    Route::put('/roles-permisos/{role}', [RolesPermisosController::class, 'update'])->whereNumber('role')->name('roles_permissions.update');
    // [PATCH /gestion/roles-permisos/{role}/estado] Solicita cambiar el estado de un rol. Controlador: RolesPermisosController::updateStatus(). Nombre: management.roles_permissions.status. Parámetro(s) numérico(s).
    Route::patch('/roles-permisos/{role}/estado', [RolesPermisosController::class, 'updateStatus'])->whereNumber('role')->name('roles_permissions.status');
    // [PUT /gestion/roles-permisos/{role}/permisos] Actualiza los permisos de un rol. Controlador: RolesPermisosController::updatePermissions(). Nombre: management.roles_permissions.permissions. Parámetro(s) numérico(s).
    Route::put('/roles-permisos/{role}/permisos', [RolesPermisosController::class, 'updatePermissions'])->whereNumber('role')->name('roles_permissions.permissions');
    // [PUT /gestion/roles-permisos/{role}/pantallas/permisos] Actualiza los permisos de pantallas de un rol. Controlador: RolesPermisosController::updateScreenPermissions(). Nombre: management.roles_permissions.screen_permissions. Parámetro(s) numérico(s).
    Route::put('/roles-permisos/{role}/pantallas/permisos', [RolesPermisosController::class, 'updateScreenPermissions'])->whereNumber('role')->name('roles_permissions.screen_permissions');
    // [GET /gestion/asignaciones-roles] Abre el listado o pantalla principal de las asignaciones de roles. Controlador: AsignacionRolesController::index(). Nombre: management.user_assignments.
    Route::get('/asignaciones-roles', [AsignacionRolesController::class, 'index'])->name('user_assignments');
    // [POST /gestion/usuarios/{user}/roles] Envía al controlador la creación/registro de las asignaciones de roles. Controlador: AsignacionRolesController::store(). Nombre: management.user_assignments.store. Parámetro(s) numérico(s).
    Route::post('/usuarios/{user}/roles', [AsignacionRolesController::class, 'store'])->whereNumber('user')->name('user_assignments.store');
    // [PUT /gestion/usuarios/{user}/roles] Sincroniza asignaciones de roles del usuario. Controlador: AsignacionRolesController::sync(). Nombre: management.user_assignments.sync. Parámetro(s) numérico(s).
    Route::put('/usuarios/{user}/roles', [AsignacionRolesController::class, 'sync'])->whereNumber('user')->name('user_assignments.sync');
    // [DELETE /gestion/usuarios/{user}/roles/{role}] Revoca un rol global de un usuario. Controlador: AsignacionRolesController::revokeGlobal(). Nombre: management.user_assignments.revoke. Parámetro(s) numérico(s).
    Route::delete('/usuarios/{user}/roles/{role}', [AsignacionRolesController::class, 'revokeGlobal'])->whereNumber('user')->whereNumber('role')->name('user_assignments.revoke');
    // [DELETE /gestion/usuarios/{user}/roles/{role}/sedes/{site}] Revoca un rol de un usuario en una sede. Controlador: AsignacionRolesController::revokeAtSite(). Nombre: management.user_assignments.revoke_site. Parámetro(s) numérico(s).
    Route::delete('/usuarios/{user}/roles/{role}/sedes/{site}', [AsignacionRolesController::class, 'revokeAtSite'])->whereNumber('user')->whereNumber('role')->whereNumber('site')->name('user_assignments.revoke_site');

    // @MODULO 34 | MENÚS, MÓDULOS Y PANTALLAS
    // Opciones que permiten configurar navegación y componentes organizacionales.

    // [GET /gestion/menus] Muestra la administración de menús. Controlador: MenusController::index(). Nombre: management.menus.
    Route::get('/menus', [MenusController::class, 'index'])->name('menus');
    // [PATCH /gestion/menus/{menu}] Actualiza un menú. Controlador: MenusController::updateMenu(). Nombre: management.menus.update. Parámetro(s) numérico(s).
    Route::patch('/menus/{menu}', [MenusController::class, 'updateMenu'])->whereNumber('menu')->name('menus.update');
    // [PUT /gestion/menus/{menu}/roles] Actualiza roles asociados a un menú. Controlador: MenusController::updateRoles(). Nombre: management.menus.roles. Parámetro(s) numérico(s).
    Route::put('/menus/{menu}/roles', [MenusController::class, 'updateRoles'])->whereNumber('menu')->name('menus.roles');
    // [PATCH /gestion/menus/elementos/{element}] Actualiza un elemento del menú. Controlador: MenusController::updateElement(). Nombre: management.menus.elements.update. Parámetro(s) numérico(s).
    Route::patch('/menus/elementos/{element}', [MenusController::class, 'updateElement'])->whereNumber('element')->name('menus.elements.update');
    // [GET /gestion/modulos] Muestra los módulos configurados. Controlador: MenusController::modules(). Nombre: management.modules.
    Route::get('/modulos', [MenusController::class, 'modules'])->name('modules');
    // [PATCH /gestion/modulos/{module}] Actualiza un módulo. Controlador: MenusController::updateModule(). Nombre: management.modules.update. Parámetro(s) numérico(s).
    Route::patch('/modulos/{module}', [MenusController::class, 'updateModule'])->whereNumber('module')->name('modules.update');
    // [PATCH /gestion/pantallas/{screen}] Actualiza una pantalla. Controlador: MenusController::updateScreen(). Nombre: management.modules.screens.update. Parámetro(s) numérico(s).
    Route::patch('/pantallas/{screen}', [MenusController::class, 'updateScreen'])->whereNumber('screen')->name('modules.screens.update');

    // @MODULO 35 | CONFIGURACIÓN, INTEGRACIONES Y RESPALDOS
    // Ajustes, notificaciones, integraciones, copias de seguridad y parámetros.

    // [GET /gestion/notificaciones] Muestra la configuración de notificaciones. Controlador: ConfiguracionSistemaController::notificaciones(). Nombre: management.notifications.
    Route::get('/notificaciones', [ConfiguracionSistemaController::class, 'notificaciones'])->name('notifications');
    // [GET /gestion/integraciones] Muestra las integraciones configuradas. Controlador: ConfiguracionSistemaController::integraciones(). Nombre: management.integrations.
    Route::get('/integraciones', [ConfiguracionSistemaController::class, 'integraciones'])->name('integrations');
    // [POST /gestion/integraciones] Registra una integración. Controlador: ConfiguracionSistemaController::guardarIntegracion(). Nombre: management.integrations.store.
    Route::post('/integraciones', [ConfiguracionSistemaController::class, 'guardarIntegracion'])->name('integrations.store');
    // [PUT /gestion/integraciones/{integracion}] Actualiza una integración. Controlador: ConfiguracionSistemaController::editarIntegracion(). Nombre: management.integrations.update. Parámetro(s) numérico(s).
    Route::put('/integraciones/{integracion}', [ConfiguracionSistemaController::class, 'editarIntegracion'])
        ->whereNumber('integracion')->name('integrations.update');
    // [PATCH /gestion/integraciones/{integracion}/estado] Actualiza el estado de una integración. Controlador: ConfiguracionSistemaController::actualizarIntegracion(). Nombre: management.integrations.status.update. Parámetro(s) numérico(s).
    Route::patch('/integraciones/{integracion}/estado', [ConfiguracionSistemaController::class, 'actualizarIntegracion'])
        ->whereNumber('integracion')->name('integrations.status.update');
    // [GET /gestion/respaldos-mantenimiento] Muestra los respaldos y mantenimiento. Controlador: ConfiguracionSistemaController::respaldos(). Nombre: management.backups.
    Route::get('/respaldos-mantenimiento', [ConfiguracionSistemaController::class, 'respaldos'])->name('backups');
    // [POST /gestion/respaldos-mantenimiento] Solicita generar un respaldo. Controlador: ConfiguracionSistemaController::crearRespaldo(). Nombre: management.backups.store.
    Route::post('/respaldos-mantenimiento', [ConfiguracionSistemaController::class, 'crearRespaldo'])->name('backups.store');
    // [GET /gestion/respaldos-mantenimiento/{archivo}/descargar] Solicita descargar un respaldo. Controlador: ConfiguracionSistemaController::descargarRespaldo(). Nombre: management.backups.download.
    Route::get('/respaldos-mantenimiento/{archivo}/descargar', [ConfiguracionSistemaController::class, 'descargarRespaldo'])
        ->where('archivo', '[A-Za-z0-9_.-]+')
        ->name('backups.download');
    // [GET /gestion/configuracion-empresa] Redirige a management.company_settings.data. Controlador: redirección. Nombre: management.company_settings.
    Route::get('/configuracion-empresa', static fn (): RedirectResponse => redirect()->route('management.company_settings.data'))->name('company_settings');
    // [GET /gestion/configuracion-seguridad] Muestra la configuración de seguridad. Controlador: ConfiguracionSistemaController::seguridad(). Nombre: management.security_settings.
    Route::get('/configuracion-seguridad', [ConfiguracionSistemaController::class, 'seguridad'])->name('security_settings');
    // [GET /gestion/configuracion-general] Muestra la configuración general. Controlador: ConfiguracionSistemaController::general(). Nombre: management.general_settings.
    Route::get('/configuracion-general', [ConfiguracionSistemaController::class, 'general'])->name('general_settings');
    // [GET /gestion/configuracion/parametros-comerciales] Consulta la configuración de un grupo de parámetros. Controlador: ConfiguracionSistemaController::configuracionGrupo(). Nombre: management.commercial_parameters.
    Route::get('/configuracion/parametros-comerciales', [ConfiguracionSistemaController::class, 'configuracionGrupo'])
        ->defaults('grupo', 'parametros-comerciales')->name('commercial_parameters');
    // [GET /gestion/configuracion/inventario] Consulta la configuración de un grupo de parámetros. Controlador: ConfiguracionSistemaController::configuracionGrupo(). Nombre: management.inventory_settings.
    Route::get('/configuracion/inventario', [ConfiguracionSistemaController::class, 'configuracionGrupo'])
        ->defaults('grupo', 'inventario')->name('inventory_settings');
    // [GET /gestion/configuracion/finanzas] Consulta la configuración de un grupo de parámetros. Controlador: ConfiguracionSistemaController::configuracionGrupo(). Nombre: management.finance_settings.
    Route::get('/configuracion/finanzas', [ConfiguracionSistemaController::class, 'configuracionGrupo'])
        ->defaults('grupo', 'finanzas')->name('finance_settings');
    // [PUT /gestion/configuracion/parametros] Guarda cambios en la configuración. Controlador: ConfiguracionSistemaController::actualizarConfiguracion(). Nombre: management.configuration.update. Permiso adicional indicado en la ruta.
    Route::put('/configuracion/parametros', [ConfiguracionSistemaController::class, 'actualizarConfiguracion'])
        ->middleware('permiso:gestion.configuracion.actualizar')->name('configuration.update');
    // [GET /gestion/configuracion/diseno/general] Muestra la configuración general. Controlador: ConfiguracionSistemaController::general(). Nombre: management.general_settings.preview. Permiso adicional indicado en la ruta.
    Route::get('/configuracion/diseno/general', [ConfiguracionSistemaController::class, 'general'])
        ->middleware('permiso:ruta')->name('general_settings.preview');
    // [GET /gestion/configuracion/diseno/integraciones] Muestra las integraciones configuradas. Controlador: ConfiguracionSistemaController::integraciones(). Nombre: management.integrations.preview. Permiso adicional indicado en la ruta.
    Route::get('/configuracion/diseno/integraciones', [ConfiguracionSistemaController::class, 'integraciones'])
        ->middleware('permiso:ruta')->name('integrations.preview');
    // [GET /gestion/configuracion/diseno/notificaciones] Muestra la configuración de notificaciones. Controlador: ConfiguracionSistemaController::notificaciones(). Nombre: management.notifications.preview. Permiso adicional indicado en la ruta.
    Route::get('/configuracion/diseno/notificaciones', [ConfiguracionSistemaController::class, 'notificaciones'])
        ->middleware('permiso:ruta')->name('notifications.preview');
    // [GET /gestion/configuracion/diseno/seguridad] Muestra la configuración de seguridad. Controlador: ConfiguracionSistemaController::seguridad(). Nombre: management.security_settings.preview. Permiso adicional indicado en la ruta.
    Route::get('/configuracion/diseno/seguridad', [ConfiguracionSistemaController::class, 'seguridad'])
        ->middleware('permiso:ruta')->name('security_settings.preview');
    // [GET /gestion/configuracion/diseno/respaldos] Muestra los respaldos y mantenimiento. Controlador: ConfiguracionSistemaController::respaldos(). Nombre: management.backups.preview. Permiso adicional indicado en la ruta.
    Route::get('/configuracion/diseno/respaldos', [ConfiguracionSistemaController::class, 'respaldos'])
        ->middleware('permiso:ruta')->name('backups.preview');
});

// @MODULO 36 | RUTAS LEGADAS / COMPATIBILIDAD
// Alias conservados para URLs antiguas. No son la organización canónica de /gestion.

// [GET /catalog] Abre el catálogo de productos. Controlador: CatalogoController::index(). Nombre: legacy.catalog.
Route::get('/catalog', [CatalogoController::class, 'index'])->name('legacy.catalog');
// [GET /products/{product}] Muestra el detalle de un producto del catálogo. Controlador: CatalogoController::show(). Nombre: legacy.product. Parámetro(s) numérico(s).
Route::get('/products/{product}', [CatalogoController::class, 'show'])->whereNumber('product')->name('legacy.product');
// [GET /smart/recommend] Muestra el recomendador de productos. Controlador: RecomendacionController::index(). Nombre: legacy.recommendations.
Route::get('/smart/recommend', [RecomendacionController::class, 'index'])->name('legacy.recommendations');
// [GET /smart/compare] Muestra el comparador de productos. Controlador: ComparadorController::index(). Nombre: legacy.comparison.
Route::get('/smart/compare', [ComparadorController::class, 'index'])->name('legacy.comparison');
// [GET /smart/optimizer] Muestra el optimizador comercial. Controlador: InteligenciaComercialController::optimizer(). Nombre: legacy.optimizer.
Route::get('/smart/optimizer', [InteligenciaComercialController::class, 'optimizer'])->name('legacy.optimizer');
// [GET /smart/ads] Muestra la administración de publicidad inteligente. Controlador: PublicidadInteligenteController::ads(). Nombre: legacy.ads. Permiso adicional indicado en la ruta.
Route::get('/smart/ads', [PublicidadInteligenteController::class, 'ads'])
    ->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('legacy.ads');
// [GET /about] Muestra la página Nosotros. Controlador: PaginaController::about(). Nombre: legacy.about.
Route::get('/about', [PaginaController::class, 'about'])->name('legacy.about');
// [GET /contact] Muestra el formulario de contacto. Controlador: PaginaController::contact(). Nombre: legacy.contact.
Route::get('/contact', [PaginaController::class, 'contact'])->name('legacy.contact');
// [POST /contact] Envía el formulario de contacto. Controlador: PaginaController::sendContact(). Nombre: legacy.contact.store. Límite de solicitudes (throttle).
Route::post('/contact', [PaginaController::class, 'sendContact'])->middleware('throttle:contact')->name('legacy.contact.store');
// [GET /login] Muestra el formulario de inicio de sesión. Controlador: AutenticacionController::loginForm(). Nombre: legacy.login.
Route::get('/login', [AutenticacionController::class, 'loginForm'])->middleware('guest')->name('legacy.login');
// [POST /login] Envía las credenciales para iniciar sesión. Controlador: AutenticacionController::login(). Nombre: legacy.login.store. Límite de solicitudes (throttle).
Route::post('/login', [AutenticacionController::class, 'login'])->middleware(['guest', 'throttle:login'])->name('legacy.login.store');
// [GET /register] Muestra el formulario de registro de usuario. Controlador: AutenticacionController::registerForm(). Nombre: legacy.register.
Route::get('/register', [AutenticacionController::class, 'registerForm'])->middleware('guest')->name('legacy.register');
// [POST /register] Envía los datos para registrar una cuenta. Controlador: AutenticacionController::register(). Nombre: legacy.register.store. Límite de solicitudes (throttle).
Route::post('/register', [AutenticacionController::class, 'register'])->middleware(['guest', 'throttle:register'])->name('legacy.register.store');
// [GET /logout] Redirige a login. Controlador: redirección. Nombre: legacy.logout.form.
Route::get('/logout', fn (): RedirectResponse => redirect()->route('login'))->name('legacy.logout.form');
// [POST /logout] Solicita cerrar la sesión. Controlador: AutenticacionController::logout(). Nombre: legacy.logout.
Route::post('/logout', [AutenticacionController::class, 'logout'])->middleware('auth')->name('legacy.logout');

// @MODULO 37 | COMPATIBILIDAD: CUENTA Y CARRITO ANTIGUOS
// Rutas de URLs previas protegidas con middleware de cuenta.

// GRUPO AUTENTICADO: auth + middleware personalizados de cuenta y sesión.
Route::middleware(['auth', 'cuenta.activa', 'sesion.usuario'])->group(function (): void {
    // [GET /account] Abre el panel de cuenta del usuario. Controlador: CuentaController::dashboard(). Nombre: legacy.account.
    Route::get('/account', [CuentaController::class, 'dashboard'])->name('legacy.account');
    // [GET /cart] Abre el listado o pantalla principal del carrito. Controlador: CarritoController::index(). Nombre: legacy.cart.
    Route::get('/cart', [CarritoController::class, 'index'])->name('legacy.cart');
    // [POST /cart] Actualiza el carrito mediante la ruta antigua. Controlador: CarritoController::updateLegacy(). Nombre: legacy.cart.update.
    Route::post('/cart', [CarritoController::class, 'updateLegacy'])->name('legacy.cart.update');
    // [GET /mayorista] Muestra el portal mayorista. Controlador: InteligenciaComercialController::wholesale(). Nombre: legacy.wholesale. Permiso adicional indicado en la ruta.
    Route::get('/mayorista', [InteligenciaComercialController::class, 'wholesale'])
        ->middleware('permiso:ruta')->name('legacy.wholesale');
});

// @MODULO 38 | COMPATIBILIDAD: ASISTENTE ANTIGUO
// Entradas antiguas hacia el mismo controlador del asistente.

// [GET /smart/assistant] Muestra la interfaz del asistente. Controlador: AsistenteController::index(). Nombre: legacy.assistant.
Route::get('/smart/assistant', [AsistenteController::class, 'index'])->name('legacy.assistant');
// [POST /smart/assistant/reply] Envía una consulta al asistente. Controlador: AsistenteController::ask(). Nombre: legacy.assistant.reply. Límite de solicitudes (throttle).
Route::post('/smart/assistant/reply', [AsistenteController::class, 'ask'])->middleware('throttle:10,1')->name('legacy.assistant.reply');

// @MODULO 39 | COMPATIBILIDAD: PANEL /admin
// Mismas acciones expuestas en rutas históricas para enlaces antiguos.

// GRUPO LEGADO PROTEGIDO: prefijo /admin y nombres legacy.admin.

Route::prefix('admin')->middleware(['auth', 'cuenta.activa', 'sesion.usuario', 'permiso:ruta'])->name('legacy.admin.')->group(function (): void {
    // [GET /admin/] Muestra el panel principal de administración. Controlador: PanelDashboardController::__invoke(). Nombre: legacy.admin.dashboard.
    Route::get('/', PanelDashboardController::class)->name('dashboard');
    // [GET /admin/account] Abre el panel de cuenta del usuario. Controlador: CuentaController::dashboard(). Nombre: legacy.admin.account.
    Route::get('/account', [CuentaController::class, 'dashboard'])->name('account');
    // [POST /admin/account/profile] Solicita actualizar los datos del perfil. Controlador: CuentaAdministracionController::updateProfile(). Nombre: legacy.admin.account.profile.
    Route::post('/account/profile', [CuentaAdministracionController::class, 'updateProfile'])->name('account.profile');
    // [POST /admin/account/password] Solicita cambiar la contraseña. Controlador: CuentaAdministracionController::updatePassword(). Nombre: legacy.admin.account.password.
    Route::post('/account/password', [CuentaAdministracionController::class, 'updatePassword'])->name('account.password');
    // [GET /admin/campaigns] Abre el listado o pantalla principal de las campañas. Controlador: CampaniaController::index(). Nombre: legacy.admin.campaigns.
    Route::get('/campaigns', [CampaniaController::class, 'index'])->name('campaigns');
    // [POST /admin/campaigns] Envía al controlador la creación/registro de las campañas. Controlador: CampaniaController::store(). Nombre: legacy.admin.campaigns.store.
    Route::post('/campaigns', [CampaniaController::class, 'store'])->name('campaigns.store');
    // [POST /admin/campaigns/{campaign}] Envía una actualización de las campañas. Controlador: CampaniaController::update(). Nombre: legacy.admin.campaigns.update.
    Route::post('/campaigns/{campaign}', [CampaniaController::class, 'update'])->name('campaigns.update');
    // [POST /admin/campaigns/{campaign}/deactivate] Solicita desactivar las campañas. Controlador: CampaniaController::deactivate(). Nombre: legacy.admin.campaigns.deactivate.
    Route::post('/campaigns/{campaign}/deactivate', [CampaniaController::class, 'deactivate'])->name('campaigns.deactivate');
    // [GET /admin/products] Abre el listado o pantalla principal de los productos. Controlador: GestionProductosController::index(). Nombre: legacy.admin.products.
    Route::get('/products', [GestionProductosController::class, 'index'])->name('products');
    // [POST /admin/products] Envía al controlador la creación/registro de los productos. Controlador: GestionProductosController::store(). Nombre: legacy.admin.products.store.
    Route::post('/products', [GestionProductosController::class, 'store'])->name('products.store');
    // [POST/PUT /admin/products/{product}] Envía una actualización de los productos. Controlador: GestionProductosController::update(). Nombre: legacy.admin.products.update.
    Route::match(['post', 'put'], '/products/{product}', [GestionProductosController::class, 'update'])->name('products.update');
    // [POST /admin/products/{product}/deactivate] Desactiva un producto (no declara borrado físico). Controlador: GestionProductosController::deactivate(). Nombre: legacy.admin.products.deactivate.
    Route::post('/products/{product}/deactivate', [GestionProductosController::class, 'deactivate'])->name('products.deactivate');
    // [GET /admin/orders] Abre el listado o pantalla principal de los pedidos. Controlador: PedidoController::index(). Nombre: legacy.admin.orders.
    Route::get('/orders', [PedidoController::class, 'index'])->name('orders');
    // [GET /admin/orders/{order}] Consulta el detalle de los pedidos. Controlador: PedidoController::show(). Nombre: legacy.admin.orders.show. Parámetro(s) numérico(s).
    Route::get('/orders/{order}', [PedidoController::class, 'show'])->whereNumber('order')->name('orders.show');
    // [POST/PATCH /admin/orders/{order}/status] Solicita cambiar el estado de un pedido. Controlador: PedidoController::updateStatus(). Nombre: legacy.admin.orders.status. Parámetro(s) numérico(s).
    Route::match(['post', 'patch'], '/orders/{order}/status', [PedidoController::class, 'updateStatus'])->whereNumber('order')->name('orders.status');
    // [GET /admin/users] Abre el listado o pantalla principal de los usuarios. Controlador: GestionUsuariosController::index(). Nombre: legacy.admin.users.
    Route::get('/users', [GestionUsuariosController::class, 'index'])->name('users');
    // [POST /admin/users] Envía al controlador la creación/registro de los usuarios. Controlador: GestionUsuariosController::store(). Nombre: legacy.admin.users.store.
    Route::post('/users', [GestionUsuariosController::class, 'store'])->name('users.store');
    // [GET /admin/categories] Abre el listado o pantalla principal de las categorías. Controlador: CategoriaController::index(). Nombre: legacy.admin.categories.
    Route::get('/categories', [CategoriaController::class, 'index'])->name('categories');
    // [POST /admin/categories] Envía al controlador la creación/registro de las categorías. Controlador: CategoriaController::store(). Nombre: legacy.admin.categories.store.
    Route::post('/categories', [CategoriaController::class, 'store'])->name('categories.store');
    // [POST/PUT /admin/categories/{category}] Envía una actualización de las categorías. Controlador: CategoriaController::update(). Nombre: legacy.admin.categories.update.
    Route::match(['post', 'put'], '/categories/{category}', [CategoriaController::class, 'update'])->name('categories.update');
    // [POST /admin/categories/{category}/deactivate] Solicita desactivar las categorías. Controlador: CategoriaController::deactivate(). Nombre: legacy.admin.categories.deactivate.
    Route::post('/categories/{category}/deactivate', [CategoriaController::class, 'deactivate'])->name('categories.deactivate');
    // [GET /admin/suppliers] Abre el listado o pantalla principal de los proveedores. Controlador: ProveedorController::index(). Nombre: legacy.admin.suppliers.
    Route::get('/suppliers', [ProveedorController::class, 'index'])->name('suppliers');
    // [POST /admin/suppliers] Envía al controlador la creación/registro de los proveedores. Controlador: ProveedorController::store(). Nombre: legacy.admin.suppliers.store.
    Route::post('/suppliers', [ProveedorController::class, 'store'])->name('suppliers.store');
    // [POST /admin/suppliers/{supplier}] Envía una actualización de los proveedores. Controlador: ProveedorController::update(). Nombre: legacy.admin.suppliers.update.
    Route::post('/suppliers/{supplier}', [ProveedorController::class, 'update'])->name('suppliers.update');
    // [POST /admin/suppliers/{supplier}/deactivate] Solicita desactivar los proveedores. Controlador: ProveedorController::deactivate(). Nombre: legacy.admin.suppliers.deactivate.
    Route::post('/suppliers/{supplier}/deactivate', [ProveedorController::class, 'deactivate'])->name('suppliers.deactivate');
    // [GET /admin/customers] Muestra el panel de clientes. Controlador: ClienteController::index(). Nombre: legacy.admin.customers.
    Route::get('/customers', [ClienteController::class, 'index'])->name('customers');
    // [POST /admin/customers] Envía al controlador la creación/registro de los clientes. Controlador: ClienteController::store(). Nombre: legacy.admin.customers.store.
    Route::post('/customers', [ClienteController::class, 'store'])->name('customers.store');
    // [GET /admin/inventory] Muestra el inventario y sus movimientos. Controlador: InventarioController::index(). Nombre: legacy.admin.inventory.
    Route::get('/inventory', [InventarioController::class, 'index'])->name('inventory');
    // [POST /admin/inventory] Envía un ajuste de inventario por la URL antigua. Controlador: InventarioController::adjustLegacy(). Nombre: legacy.admin.inventory.store.
    Route::post('/inventory', [InventarioController::class, 'adjustLegacy'])->name('inventory.store');
    // [GET /admin/reports] Muestra el panel de reportes. Controlador: ReporteController::index(). Nombre: legacy.admin.reports.
    Route::get('/reports', [ReporteController::class, 'index'])->name('reports');
    // [GET /admin/audit] Muestra la auditoría de operaciones. Controlador: PanelAnalisisAuditoriaController::operaciones(). Nombre: legacy.admin.audit.
    Route::get('/audit', [PanelAnalisisAuditoriaController::class, 'operaciones'])->name('audit');
});

// @MODULO 40 | COMPATIBILIDAD: /panel
// Enlaces antiguos que apuntan al dashboard de cuenta.

// GRUPO AUTENTICADO: auth + middleware personalizados de cuenta y sesión.
Route::middleware(['auth', 'cuenta.activa', 'sesion.usuario'])->group(function (): void {
    // [GET /panel] Abre el panel de cuenta del usuario. Controlador: CuentaController::dashboard(). Nombre: legacy.panel.
    Route::get('/panel', [CuentaController::class, 'dashboard'])->name('legacy.panel');
    // [GET /panel/{module}] Abre el panel de cuenta del usuario. Controlador: CuentaController::dashboard(). Nombre: legacy.panel.module.
    Route::get('/panel/{module}', [CuentaController::class, 'dashboard'])->name('legacy.panel.module');
});
