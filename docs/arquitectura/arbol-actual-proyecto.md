# Árbol actual del proyecto `sistema-cell-sac`

> Inventario generado el 10 de noviembre de 2026. Es una fotografía estructural del repositorio; no representa una reorganización recomendada.

## Alcance y exclusiones

Se incluyeron archivos visibles propios del proyecto y configuraciones presentes en el árbol actual. Se excluyeron `vendor/`, `node_modules/`, `.git/`, `.idea/`, `.vscode/`, `bootstrap/cache/`, `storage/`, `.backups/`, `public/build/`, `.agents/` y `.codex/`, además de compilados, logs, cachés, resultados temporales y datos runtime. Los archivos estándar de Laravel no se eliminaron del inventario cuando forman parte del árbol disponible; su grado de personalización debe verificarse mediante historial o revisión de contenido.

## 1. Árbol completo de `app/`

```text
app/
├── Console/
│   └── Commands/
│       ├── CrearUsuarioCell.php
│       ├── HabilitarSubmenusFinancieros.php
│       ├── HabilitarSubmenusOrganizacion.php
│       ├── InicializarCatalogoRbac.php
│       └── InicializarTechnologyCell.php
├── Contratos/
│   └── Productos/
│       ├── AlcanceProductos.php
│       └── AuditoriaProductos.php
├── Dominio/
│   └── Catalogo/
│       ├── ProductoDao.php
│       ├── ProductoEntidad.php
│       ├── ResultadoPaginado.php
│       └── VarianteProductoEntidad.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   └── CatalogoController.php
│   │   ├── Auth/
│   │   │   └── AutenticacionController.php
│   │   ├── Controller.php
│   │   ├── Cuenta/
│   │   │   ├── CuentaAdministracionController.php
│   │   │   └── CuentaController.php
│   │   ├── Gestion/
│   │   │   ├── CajaController.php
│   │   │   ├── Campanias/
│   │   │   │   ├── CampaniaController.php
│   │   │   │   └── PublicidadInteligenteController.php
│   │   │   ├── Categorias/
│   │   │   │   └── CategoriaController.php
│   │   │   ├── Clientes/
│   │   │   │   └── ClienteController.php
│   │   │   ├── Compras/
│   │   │   │   └── CompraController.php
│   │   │   ├── ConfiguracionSistemaController.php
│   │   │   ├── Cotizaciones/
│   │   │   │   └── CotizacionController.php
│   │   │   ├── Cupones/
│   │   │   │   └── CuponController.php
│   │   │   ├── DevolucionesGarantias/
│   │   │   │   └── DevolucionGarantiaController.php
│   │   │   ├── FacturacionController.php
│   │   │   ├── Finanzas/
│   │   │   │   └── CuentasFinancierasController.php
│   │   │   ├── FinanzasController.php
│   │   │   ├── Inventario/
│   │   │   │   ├── InventarioController.php
│   │   │   │   └── TransferenciaExistenciaController.php
│   │   │   ├── Organizacion/
│   │   │   │   ├── AlmacenController.php
│   │   │   │   ├── EmpresaController.php
│   │   │   │   ├── PersonalSedeController.php
│   │   │   │   ├── ResponsableOrganizacionalController.php
│   │   │   │   └── SedeController.php
│   │   │   ├── Productos/
│   │   │   │   └── GestionProductosController.php
│   │   │   ├── Proveedores/
│   │   │   │   └── ProveedorController.php
│   │   │   ├── Reportes/
│   │   │   │   ├── PanelAnalisisAuditoriaController.php
│   │   │   │   └── ReporteController.php
│   │   │   ├── Segmentos/
│   │   │   │   └── SegmentoClienteController.php
│   │   │   ├── Seguridad/
│   │   │   │   ├── AsignacionRolesController.php
│   │   │   │   ├── MenusController.php
│   │   │   │   └── RolesPermisosController.php
│   │   │   ├── SesionCajaController.php
│   │   │   ├── SoporteReclamos/
│   │   │   │   └── SoporteReclamoController.php
│   │   │   ├── Usuarios/
│   │   │   │   └── GestionUsuariosController.php
│   │   │   └── Ventas/
│   │   │       └── VentaController.php
│   │   ├── Paneles/
│   │   │   ├── Administrador/
│   │   │   │   ├── AlertasGlobalesController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── DashboardGlobalPedidosController.php
│   │   │   │   ├── DashboardGlobalSedesController.php
│   │   │   │   ├── DashboardGlobalVentasController.php
│   │   │   │   ├── DispositivosController.php
│   │   │   │   └── EnviosPedidosController.php
│   │   │   ├── ClienteMayorista/
│   │   │   │   └── DashboardController.php
│   │   │   ├── ClienteMinorista/
│   │   │   │   └── DashboardController.php
│   │   │   ├── ComprasLogistica/
│   │   │   │   └── DashboardController.php
│   │   │   ├── Marketing/
│   │   │   │   └── DashboardController.php
│   │   │   ├── PanelDashboardController.php
│   │   │   ├── ResolutorDashboard.php
│   │   │   ├── VentasMayoristas/
│   │   │   │   └── DashboardController.php
│   │   │   └── VentasMinoristas/
│   │   │       └── DashboardController.php
│   │   └── Publico/
│   │       ├── Asistente/
│   │       │   └── AsistenteController.php
│   │       ├── Catalogo/
│   │       │   ├── CatalogoController.php
│   │       │   ├── ComparadorController.php
│   │       │   └── RecomendacionController.php
│   │       ├── Comercio/
│   │       │   ├── CampaniaPublicaController.php
│   │       │   ├── CarritoController.php
│   │       │   ├── FinalizarCompraController.php
│   │       │   ├── PedidoController.php
│   │       │   └── SeguimientoPedidoController.php
│   │       ├── Inicio/
│   │       │   └── InicioController.php
│   │       ├── Inteligente/
│   │       │   └── InteligenciaComercialController.php
│   │       └── Paginas/
│   │           └── PaginaController.php
│   ├── Middleware/
│   │   ├── AsegurarCarritoEcommerce.php
│   │   ├── EstablecerContextoAuditoria.php
│   │   ├── RegistrarSesionUsuario.php
│   │   ├── VerificarCuentaActiva.php
│   │   └── VerificarPermiso.php
│   ├── Requests/
│   │   ├── AbrirSesionCajaRequest.php
│   │   ├── ActualizarAvatarRequest.php
│   │   ├── ActualizarCajaRequest.php
│   │   ├── ActualizarCampaniaRequest.php
│   │   ├── ActualizarCamposPerfilRolRequest.php
│   │   ├── ActualizarCarritoRequest.php
│   │   ├── ActualizarCasoSoporteRequest.php
│   │   ├── ActualizarCategoriaRequest.php
│   │   ├── ActualizarClienteRequest.php
│   │   ├── ActualizarCompraRequest.php
│   │   ├── ActualizarContrasenaRequest.php
│   │   ├── ActualizarCotizacionRequest.php
│   │   ├── ActualizarCuponRequest.php
│   │   ├── ActualizarDevolucionGarantiaRequest.php
│   │   ├── ActualizarEmpresaRequest.php
│   │   ├── ActualizarEstadoEnvioPedidoRequest.php
│   │   ├── ActualizarEstadoPedidoRequest.php
│   │   ├── ActualizarIntegracionRequest.php
│   │   ├── ActualizarPerfilRequest.php
│   │   ├── ActualizarPermisosRolRequest.php
│   │   ├── ActualizarPreferenciasRequest.php
│   │   ├── ActualizarProductoRequest.php
│   │   ├── ActualizarProveedorRequest.php
│   │   ├── ActualizarRolRequest.php
│   │   ├── ActualizarSegmentoClienteRequest.php
│   │   ├── ActualizarUsuarioRequest.php
│   │   ├── ActualizarVentaRequest.php
│   │   ├── AgregarCarritoRequest.php
│   │   ├── AjustarInventarioRequest.php
│   │   ├── AnularOperacionFinancieraRequest.php
│   │   ├── AnularVentaRequest.php
│   │   ├── AplicarCobroCuentaRequest.php
│   │   ├── AplicarCuponRequest.php
│   │   ├── AplicarPagoCuentaRequest.php
│   │   ├── AprobarReembolsoRequest.php
│   │   ├── AsignarRolUsuarioRequest.php
│   │   ├── AsistenteRequest.php
│   │   ├── CambiarEstadoAlmacenRequest.php
│   │   ├── CambiarEstadoCajaRequest.php
│   │   ├── CambiarEstadoSedeRequest.php
│   │   ├── CambiarEstadoUsuarioRequest.php
│   │   ├── CerrarSesionCajaRequest.php
│   │   ├── CheckoutInvitadoRequest.php
│   │   ├── ComparacionRequest.php
│   │   ├── ConfirmarPagoEfectivoRequest.php
│   │   ├── ConfirmarVentaCotizacionRequest.php
│   │   ├── ConvertirCotizacionRequest.php
│   │   ├── CrearCampoPerfilRequest.php
│   │   ├── CrearCuentaPorPagarRequest.php
│   │   ├── DesactivarCuponRequest.php
│   │   ├── FiltroAlmacenesRequest.php
│   │   ├── FiltroCajasRequest.php
│   │   ├── FiltroCatalogoRequest.php
│   │   ├── FiltroPagosRequest.php
│   │   ├── FiltroReembolsosRequest.php
│   │   ├── FiltroReporteRequest.php
│   │   ├── FiltroSedesRequest.php
│   │   ├── FiltroSesionesCajaRequest.php
│   │   ├── FiltroVentaRequest.php
│   │   ├── FinalizarCompraRequest.php
│   │   ├── Gestion/
│   │   │   ├── FiltroPersonalSedeRequest.php
│   │   │   ├── Inventario/
│   │   │   │   ├── AccionTransferenciaExistenciaRequest.php
│   │   │   │   └── GuardarTransferenciaExistenciaRequest.php
│   │   │   └── Organizacion/
│   │   │       └── GuardarResponsableOrganizacionalRequest.php
│   │   ├── GuardarAlmacenRequest.php
│   │   ├── GuardarCajaRequest.php
│   │   ├── GuardarCampaniaRequest.php
│   │   ├── GuardarCasoSoporteRequest.php
│   │   ├── GuardarCategoriaRequest.php
│   │   ├── GuardarClienteRequest.php
│   │   ├── GuardarCompraRequest.php
│   │   ├── GuardarConfiguracionSistemaRequest.php
│   │   ├── GuardarConfiguracionTiendaRequest.php
│   │   ├── GuardarCotizacionRequest.php
│   │   ├── GuardarCuponRequest.php
│   │   ├── GuardarDevolucionGarantiaRequest.php
│   │   ├── GuardarImagenVarianteRequest.php
│   │   ├── GuardarIntegracionRequest.php
│   │   ├── GuardarMensajeContactoRequest.php
│   │   ├── GuardarPrecioMayoristaRequest.php
│   │   ├── GuardarProductoRequest.php
│   │   ├── GuardarProveedorRequest.php
│   │   ├── GuardarResenaProductoRequest.php
│   │   ├── GuardarRolRequest.php
│   │   ├── GuardarSedeRequest.php
│   │   ├── GuardarSegmentoClienteRequest.php
│   │   ├── GuardarUsuarioRequest.php
│   │   ├── GuardarVarianteProductoRequest.php
│   │   ├── GuardarVentaRequest.php
│   │   ├── ImportarCampaniasRequest.php
│   │   ├── ImportarClientesRequest.php
│   │   ├── ImportarComprasRequest.php
│   │   ├── ImportarPreciosMayoristasRequest.php
│   │   ├── InicioSesionRequest.php
│   │   ├── ListadoDispositivosRequest.php
│   │   ├── ListadoEnviosPedidosRequest.php
│   │   ├── OptimizadorInteligenteRequest.php
│   │   ├── RecomendacionRequest.php
│   │   ├── RegistrarEnvioCotizacionRequest.php
│   │   ├── RegistrarMovimientoCajaRequest.php
│   │   ├── RegistrarPagoRequest.php
│   │   ├── RegistrarRecepcionParcialCompraRequest.php
│   │   ├── RegistroRequest.php
│   │   └── SolicitarReembolsoRequest.php
│   └── Resources/
│       └── RecursoProducto.php
├── Infraestructura/
│   └── Persistencia/
│       └── Pdo/
│           ├── PdoConexion.php
│           ├── PdoHidratador.php
│           ├── PdoMapaEntidad.php
│           └── PdoProductoDao.php
├── Models/
│   ├── Auditoria/
│   │   └── RegistroAuditoria.php
│   ├── Catalogo/
│   │   ├── CaracteristicaProducto.php
│   │   ├── Categoria.php
│   │   ├── FavoritoProducto.php
│   │   ├── ImagenProducto.php
│   │   ├── ImagenVariante.php
│   │   ├── PerfilInteligenciaProducto.php
│   │   ├── Producto.php
│   │   ├── ResenaProducto.php
│   │   └── VarianteProducto.php
│   ├── Clientes/
│   │   └── Cliente.php
│   ├── Comercio/
│   │   ├── CasoSoporte.php
│   │   ├── ConfiguracionTienda.php
│   │   ├── Cotizacion.php
│   │   ├── Cupon.php
│   │   ├── DetalleCotizacion.php
│   │   ├── DetallePedido.php
│   │   ├── DetalleVenta.php
│   │   ├── DevolucionGarantia.php
│   │   ├── EnvioPedido.php
│   │   ├── EventoEnvioPedido.php
│   │   ├── LogisticaMayorista.php
│   │   ├── Pedido.php
│   │   ├── PrecioMayorista.php
│   │   └── Venta.php
│   ├── Compras/
│   │   ├── Compra.php
│   │   ├── DetalleCompra.php
│   │   └── Proveedor.php
│   ├── Comunicacion/
│   │   ├── MensajeContacto.php
│   │   └── Notificacion.php
│   ├── ConfiguracionSistema.php
│   ├── Cuenta/
│   │   ├── PerfilCampo.php
│   │   └── UsuarioPerfilCampo.php
│   ├── Finanzas/
│   │   ├── Caja.php
│   │   ├── CuentaBancaria.php
│   │   ├── MedioPago.php
│   │   ├── MovimientoCaja.php
│   │   ├── Pago.php
│   │   ├── Reembolso.php
│   │   └── SesionCaja.php
│   ├── Inventario/
│   │   ├── DetalleTransferenciaExistencia.php
│   │   ├── Dispositivo.php
│   │   ├── MovimientoDispositivo.php
│   │   ├── MovimientoInventario.php
│   │   └── TransferenciaExistencia.php
│   ├── Marketing/
│   │   ├── Campania.php
│   │   ├── EventoCampania.php
│   │   └── SegmentoCliente.php
│   ├── Organizacion/
│   │   ├── Almacen.php
│   │   ├── Empresa.php
│   │   ├── ResponsableOrganizacional.php
│   │   └── Sede.php
│   ├── Seguridad/
│   │   ├── ElementoMenu.php
│   │   ├── Menu.php
│   │   ├── Modulo.php
│   │   ├── Pantalla.php
│   │   ├── Permiso.php
│   │   └── Rol.php
│   └── User.php
├── Policies/
│   ├── PedidoPolicy.php
│   └── ProductoPolicy.php
├── Providers/
│   └── AppServiceProvider.php
└── Servicios/
    ├── AdaptadorAlcanceProductos.php
    ├── AdaptadorAuditoriaProductos.php
    ├── ConsultaVentas.php
    ├── ExportadorTabular.php
    ├── RegistradorAuditoria.php
    ├── ServicioAutorizacion.php
    ├── ServicioCajas.php
    ├── ServicioCarrito.php
    ├── ServicioCatalogo.php
    ├── ServicioCompras.php
    ├── ServicioConfiguracionSistema.php
    ├── ServicioConfiguracionTienda.php
    ├── ServicioConsultasCuentas.php
    ├── ServicioConsultasFinancieras.php
    ├── ServicioContacto.php
    ├── ServicioCotizaciones.php
    ├── ServicioCuentasFinancieras.php
    ├── ServicioCupones.php
    ├── ServicioDelegacion.php
    ├── ServicioDispositivos.php
    ├── ServicioEnviosPedidos.php
    ├── ServicioGestionProductos.php
    ├── ServicioInteligenciaComercial.php
    ├── ServicioInventario.php
    ├── ServicioMenuDinamico.php
    ├── ServicioNotificaciones.php
    ├── ServicioOperacionesFinancieras.php
    ├── ServicioPaneles.php
    ├── ServicioPedidos.php
    ├── ServicioPerfil.php
    ├── ServicioPlantillasRbac.php
    ├── ServicioPreciosMayoristas.php
    ├── ServicioPresentacionCampanias.php
    ├── ServicioPresentacionCategorias.php
    ├── ServicioPresentacionConfiguracion.php
    ├── ServicioPresentacionFinanzasFacturacion.php
    ├── ServicioPresentacionInventario.php
    ├── ServicioPresentacionReembolsos.php
    ├── ServicioRecomendaciones.php
    ├── ServicioRespaldos.php
    ├── ServicioResponsablesOrganizacionales.php
    ├── ServicioStockProducto.php
    ├── ServicioTransferenciasExistencias.php
    ├── ServicioVariantesProducto.php
    └── ServicioVentas.php
```

## 2. Árbol completo de `resources/views/`

```text
resources/views/
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
├── components/
│   └── configuracion/
│       ├── general.blade.php
│       ├── integraciones.blade.php
│       ├── notificaciones.blade.php
│       ├── pagina.blade.php
│       ├── respaldos.blade.php
│       └── seguridad.blade.php
├── cuenta/
│   └── perfil/
│       ├── actividad.blade.php
│       ├── componentes/
│       │   ├── campos-dinamicos.blade.php
│       │   ├── datos-personales.blade.php
│       │   ├── foto-perfil.blade.php
│       │   └── funciones-rol.blade.php
│       ├── mostrar.blade.php
│       ├── navegacion.blade.php
│       ├── notificaciones.blade.php
│       ├── preferencias.blade.php
│       ├── seguridad.blade.php
│       └── sesiones.blade.php
├── errors/
│   └── 404.blade.php
├── gestion/
│   ├── administracion/
│   │   ├── organizacion/
│   │   │   ├── componentes/
│   │   │   │   ├── aviso.blade.php
│   │   │   │   ├── cabecera.blade.php
│   │   │   │   ├── filtros.blade.php
│   │   │   │   ├── metrica.blade.php
│   │   │   │   ├── modales.blade.php
│   │   │   │   └── tabla.blade.php
│   │   │   ├── datos-empresa.blade.php
│   │   │   └── sedes/
│   │   │       └── listar.blade.php
│   │   └── seguridad/
│   │       ├── asignaciones-delegaciones/
│   │       │   └── listar.blade.php
│   │       ├── matriz-privilegios/
│   │       │   └── listar.blade.php
│   │       ├── menus/
│   │       │   └── listar.blade.php
│   │       ├── modulos-pantallas/
│   │       │   └── listar.blade.php
│   │       ├── personal/
│   │       │   └── listar.blade.php
│   │       ├── responsables/
│   │       │   └── listar.blade.php
│   │       ├── roles-permisos/
│   │       │   ├── editar.blade.php
│   │       │   ├── listar.blade.php
│   │       │   └── registrar.blade.php
│   │       └── usuarios/
│   │           ├── listar.blade.php
│   │           └── registrar.blade.php
│   ├── analitica/
│   │   ├── auditoria-operaciones/
│   │   │   └── listar.blade.php
│   │   ├── clientes/
│   │   │   └── listar.blade.php
│   │   ├── dashboard-global/
│   │   │   ├── alertas/
│   │   │   │   └── listar.blade.php
│   │   │   ├── componentes/
│   │   │   │   ├── anillo.blade.php
│   │   │   │   ├── aviso-diseno.blade.php
│   │   │   │   ├── cabecera.blade.php
│   │   │   │   ├── grafico-barras.blade.php
│   │   │   │   ├── grafico-lineas.blade.php
│   │   │   │   ├── metrica.blade.php
│   │   │   │   └── vacio.blade.php
│   │   │   ├── pedidos/
│   │   │   │   └── listar.blade.php
│   │   │   ├── sedes/
│   │   │   │   └── listar.blade.php
│   │   │   └── ventas/
│   │   │       └── listar.blade.php
│   │   ├── reportes/
│   │   │   ├── _orders-table.blade.php
│   │   │   └── listar.blade.php
│   │   └── reportes-globales/
│   │       └── listar.blade.php
│   ├── comercial/
│   │   ├── clientes/
│   │   │   ├── editar.blade.php
│   │   │   ├── listar.blade.php
│   │   │   └── registrar.blade.php
│   │   ├── compras/
│   │   │   ├── editar.blade.php
│   │   │   ├── listar.blade.php
│   │   │   └── registrar.blade.php
│   │   ├── cotizaciones/
│   │   │   ├── editar.blade.php
│   │   │   ├── listar.blade.php
│   │   │   └── registrar.blade.php
│   │   ├── devoluciones-garantias/
│   │   │   ├── editar.blade.php
│   │   │   ├── listar.blade.php
│   │   │   └── registrar.blade.php
│   │   ├── pedidos/
│   │   │   └── listar.blade.php
│   │   ├── proveedores/
│   │   │   ├── editar.blade.php
│   │   │   ├── listar.blade.php
│   │   │   └── registrar.blade.php
│   │   └── ventas/
│   │       ├── componentes/
│   │       │   └── pagina-ventas.blade.php
│   │       ├── editar.blade.php
│   │       ├── listar.blade.php
│   │       ├── mayoristas/
│   │       │   └── listar.blade.php
│   │       ├── minoristas/
│   │       │   └── listar.blade.php
│   │       └── registrar.blade.php
│   ├── configuracion/
│   │   ├── finanzas/
│   │   │   └── configurar.blade.php
│   │   ├── general/
│   │   │   └── configurar.blade.php
│   │   ├── integraciones/
│   │   │   ├── configurar.blade.php
│   │   │   ├── legacy-administracion/
│   │   │   │   ├── crear.blade.php
│   │   │   │   ├── editar.blade.php
│   │   │   │   └── listar.blade.php
│   │   │   └── listar.blade.php
│   │   ├── inventario/
│   │   │   └── configurar.blade.php
│   │   ├── notificaciones/
│   │   │   ├── configurar.blade.php
│   │   │   └── legacy-administracion/
│   │   │       ├── crear.blade.php
│   │   │       ├── editar.blade.php
│   │   │       └── listar.blade.php
│   │   ├── parametros-comerciales/
│   │   │   └── configurar.blade.php
│   │   ├── respaldos/
│   │   │   ├── legacy-administracion/
│   │   │   │   └── listar.blade.php
│   │   │   └── listar.blade.php
│   │   └── seguridad/
│   │       └── configurar.blade.php
│   ├── ecommerce-marketing/
│   │   ├── campanas/
│   │   │   ├── editar.blade.php
│   │   │   ├── listar.blade.php
│   │   │   ├── publicidad-inteligente.blade.php
│   │   │   └── registrar.blade.php
│   │   ├── catalogo-publico/
│   │   │   └── listar.blade.php
│   │   ├── componentes/
│   │   │   ├── modal-precios.blade.php
│   │   │   └── modal-tienda.blade.php
│   │   ├── precios-venta/
│   │   │   └── listar.blade.php
│   │   ├── promociones-cupones/
│   │   │   ├── listar.blade.php
│   │   │   └── registrar.blade.php
│   │   └── segmentos/
│   │       ├── crear.blade.php
│   │       └── listar.blade.php
│   ├── finanzas/
│   │   ├── aperturas-cierres/
│   │   │   ├── detalle.blade.php
│   │   │   ├── listar.blade.php
│   │   │   └── operativa.blade.php
│   │   ├── cajas/
│   │   │   ├── listar.blade.php
│   │   │   └── operativa.blade.php
│   │   ├── comprobantes/
│   │   │   ├── editar.blade.php
│   │   │   ├── listar.blade.php
│   │   │   └── registrar.blade.php
│   │   ├── cuentas-cobrar-pagar/
│   │   │   └── listar.blade.php
│   │   ├── finanzas-facturacion/
│   │   │   └── pagina.blade.php
│   │   ├── pagos-cobros/
│   │   │   ├── detalle.blade.php
│   │   │   ├── editar.blade.php
│   │   │   ├── listar.blade.php
│   │   │   └── registrar.blade.php
│   │   └── reembolsos/
│   │       ├── detalle.blade.php
│   │       ├── editar.blade.php
│   │       ├── listar.blade.php
│   │       └── registrar.blade.php
│   ├── inventario-logistica/
│   │   ├── almacenes/
│   │   │   └── listar.blade.php
│   │   ├── categorias/
│   │   │   ├── editar.blade.php
│   │   │   ├── listar.blade.php
│   │   │   └── registrar.blade.php
│   │   ├── envios-entregas/
│   │   │   ├── editar.blade.php
│   │   │   ├── envios/
│   │   │   │   ├── detalle.blade.php
│   │   │   │   └── listar.blade.php
│   │   │   ├── listar.blade.php
│   │   │   └── registrar.blade.php
│   │   ├── existencias-movimientos/
│   │   │   ├── listar-existencias.blade.php
│   │   │   └── registrar-ajuste.blade.php
│   │   ├── productos/
│   │   │   ├── editar.blade.php
│   │   │   ├── listar.blade.php
│   │   │   └── registrar.blade.php
│   │   ├── series/
│   │   │   └── listar.blade.php
│   │   └── transferencias/
│   │       └── listar.blade.php
│   └── soporte/
│       └── reclamos/
│           ├── crear.blade.php
│           ├── editar.blade.php
│           ├── formulario.blade.php
│           └── listar.blade.php
├── inteligente/
│   ├── asistente/
│   │   ├── chat.blade.php
│   │   ├── flotante.blade.php
│   │   └── panel.blade.php
│   ├── comprador/
│   │   ├── comparacion.blade.php
│   │   └── recomendaciones.blade.php
│   └── cotizador/
│       ├── formulario.blade.php
│       └── resumen.blade.php
├── layouts/
│   ├── auth/
│   │   ├── encabezado.blade.php
│   │   ├── mensajes.blade.php
│   │   └── principal.blade.php
│   ├── panel/
│   │   ├── encabezado.blade.php
│   │   ├── menu-dinamico.blade.php
│   │   ├── notificaciones.blade.php
│   │   └── principal.blade.php
│   └── tienda/
│       ├── encabezado.blade.php
│       ├── pie-pagina.blade.php
│       └── principal.blade.php
├── paneles/
│   └── dashboard.blade.php
└── publico/
    ├── catalogo/
    │   ├── detalle.blade.php
    │   ├── index.blade.php
    │   └── paginacion.blade.php
    ├── comercio/
    │   ├── carrito.blade.php
    │   ├── confirmacion.blade.php
    │   ├── finalizar-compra.blade.php
    │   ├── pedido.blade.php
    │   ├── pedidos-cuenta.blade.php
    │   ├── pedidos.blade.php
    │   ├── seguimiento-invitado.blade.php
    │   └── seguimiento.blade.php
    └── paginas/
        ├── contacto.blade.php
        ├── inicio.blade.php
        ├── libro-reclamaciones.blade.php
        ├── nosotros.blade.php
        ├── politicas-privacidad.blade.php
        ├── preguntas-frecuentes.blade.php
        └── terminos-condiciones.blade.php
```

## 3. Árbol completo de `resources/css/`

```text
resources/css/
├── app.css
├── auth/
│   ├── login.css
│   └── register.css
├── base/
│   ├── reinicio.css
│   ├── tipografia.css
│   ├── utilidades.css
│   └── variables.css
├── componentes/
│   ├── comunes/
│   │   ├── alertas.css
│   │   ├── badges.css
│   │   ├── botones.css
│   │   ├── confirmacion.css
│   │   ├── estados.css
│   │   ├── formularios.css
│   │   ├── precios.css
│   │   ├── tablas.css
│   │   └── tarjetas.css
│   └── publico/
│       └── publicidad.css
├── cuenta/
│   └── perfil/
│       ├── componentes/
│       │   ├── campos-dinamicos.css
│       │   └── foto-perfil.css
│       └── perfil.css
├── gestion/
│   ├── administracion/
│   │   ├── integraciones/
│   │   │   └── integraciones.css
│   │   ├── notificaciones/
│   │   │   └── notificaciones.css
│   │   ├── organizacion/
│   │   │   └── organizacion.css
│   │   ├── respaldos-mantenimiento/
│   │   │   └── respaldos-mantenimiento.css
│   │   └── seguridad/
│   │       ├── roles-permisos.css
│   │       └── usuarios.css
│   ├── administracion-modulos.css
│   ├── analisis-modulos.css
│   ├── analitica/
│   │   ├── analisis-auditoria/
│   │   │   └── analisis-auditoria.css
│   │   ├── clientes/
│   │   │   └── clientes.css
│   │   ├── dashboard-global/
│   │   │   └── dashboard-global.css
│   │   ├── metricas-marketing/
│   │   │   └── metricas-marketing.css
│   │   ├── reportes/
│   │   │   └── reportes.css
│   │   └── reportes-financieros/
│   │       └── reportes-financieros.css
│   ├── comercial/
│   │   ├── clientes/
│   │   │   └── clientes.css
│   │   ├── compras/
│   │   │   └── compras.css
│   │   ├── cotizaciones/
│   │   │   └── cotizaciones.css
│   │   ├── pedidos/
│   │   │   └── pedidos.css
│   │   ├── proveedores/
│   │   │   └── proveedores.css
│   │   └── ventas/
│   │       ├── operaciones-ventas.css
│   │       └── ventas.css
│   ├── configuracion/
│   │   ├── comercial/
│   │   │   └── comercial.css
│   │   ├── configuracion-sistema.css
│   │   ├── empresa/
│   │   │   └── empresa.css
│   │   ├── general/
│   │   │   └── general.css
│   │   └── seguridad/
│   │       └── seguridad.css
│   ├── configuracion-modulos.css
│   ├── ecommerce-marketing/
│   │   ├── campanas.css
│   │   ├── ecommerce-marketing.css
│   │   └── publicidad-inteligente.css
│   ├── finanzas/
│   │   ├── comprobantes/
│   │   │   └── comprobantes.css
│   │   ├── finanzas-facturacion/
│   │   │   └── finanzas-facturacion.css
│   │   ├── pagos-cobros/
│   │   │   └── pagos-cobros.css
│   │   └── reembolsos/
│   │       └── reembolsos.css
│   ├── finanzas-modulos.css
│   ├── inventario/
│   │   └── inventario.css
│   ├── inventario-logistica/
│   │   ├── inventario-logistica.css
│   │   └── productos/
│   │       └── productos.css
│   ├── marketing-modulos.css
│   ├── modulos-pendientes.css
│   ├── reportes/
│   │   └── reportes.css
│   └── soporte/
│       └── reclamos/
│           └── reclamos.css
├── inteligente/
│   ├── asistente/
│   │   ├── chat.css
│   │   └── flotante.css
│   ├── comprador/
│   │   ├── comparacion.css
│   │   └── recomendaciones.css
│   └── cotizador/
│       ├── formulario.css
│       └── resumen.css
├── layouts/
│   ├── administracion.css
│   ├── auth/
│   │   ├── encabezado.css
│   │   ├── mensajes.css
│   │   └── principal.css
│   ├── panel/
│   │   ├── encabezado.css
│   │   ├── menu-dinamico.css
│   │   ├── menu-lateral-desplegable.css
│   │   ├── notificaciones.css
│   │   └── principal.css
│   ├── responsive.css
│   ├── tienda/
│   │   ├── encabezado.css
│   │   ├── pie-pagina.css
│   │   └── principal.css
│   └── tienda.css
├── modulos/
│   ├── pedidos.css
│   ├── seguridad-delegacion-extra.css
│   ├── seguridad-delegacion.css
│   └── seguridad-rbac-overrides.css
├── paneles/
│   ├── admin-panel.css
│   ├── dashboard.css
│   ├── modulos-panel.css
│   └── roles-panel.css
└── publico/
    ├── catalogo/
    │   ├── detalle.css
    │   └── index.css
    ├── comercio/
    │   ├── carrito.css
    │   └── pedidos.css
    ├── cuenta/
    │   ├── barra-lateral-cuenta.css
    │   └── cuenta.css
    ├── paginas/
    │   ├── contacto.css
    │   ├── inicio.css
    │   └── nosotros.css
    └── rediseño-referencias.css
```

## 4. Árbol completo de `resources/js/`

```text
resources/js/
├── auth/
│   ├── login.js
│   └── register.js
├── componentes/
│   ├── comunes/
│   │   ├── bloqueo-envio.js
│   │   ├── confirmacion.js
│   │   └── visibilidad-contrasenas.js
│   ├── gestion/
│   │   └── tabla-datos.js
│   └── publico/
│       └── publicidad.js
├── cuenta/
│   └── perfil/
│       ├── componentes/
│       │   ├── campos-dinamicos.js
│       │   └── foto-perfil.js
│       └── perfil.js
├── gestion/
│   ├── administracion/
│   │   ├── index.js
│   │   ├── integraciones/
│   │   │   └── index.js
│   │   ├── notificaciones/
│   │   │   └── index.js
│   │   ├── organizacion/
│   │   │   └── organizacion.js
│   │   ├── respaldos-mantenimiento/
│   │   │   └── index.js
│   │   ├── roles-permisos/
│   │   │   └── index.js
│   │   └── seguridad/
│   │       ├── menus.js
│   │       ├── roles-permisos/
│   │       │   ├── asignaciones.js
│   │       │   ├── listar.js
│   │       │   ├── matriz.js
│   │       │   └── modulos.js
│   │       └── usuarios/
│   │           └── listar.js
│   ├── analitica/
│   │   ├── analisis-auditoria/
│   │   │   └── analisis-auditoria.js
│   │   ├── clientes/
│   │   │   └── index.js
│   │   ├── index.js
│   │   ├── metricas-marketing/
│   │   │   └── index.js
│   │   ├── reportes/
│   │   │   └── index.js
│   │   └── reportes-financieros/
│   │       └── index.js
│   ├── comercial/
│   │   ├── clientes/
│   │   │   └── index.js
│   │   ├── compras/
│   │   │   └── index.js
│   │   ├── cotizaciones/
│   │   │   ├── administrador.js
│   │   │   └── formulario.js
│   │   ├── pedidos/
│   │   │   └── index.js
│   │   ├── proveedores/
│   │   │   └── index.js
│   │   └── ventas/
│   │       ├── index.js
│   │       └── operaciones-ventas.js
│   ├── configuracion/
│   │   ├── comercial/
│   │   │   └── index.js
│   │   ├── configuracion-sistema.js
│   │   ├── empresa/
│   │   │   └── index.js
│   │   ├── general/
│   │   │   └── index.js
│   │   ├── index.js
│   │   └── seguridad/
│   │       └── index.js
│   ├── ecommerce-marketing/
│   │   ├── campanas.js
│   │   ├── ecommerce-marketing.js
│   │   └── previsualizacion-campanas.js
│   ├── finanzas/
│   │   ├── comprobantes/
│   │   │   └── index.js
│   │   ├── finanzas-facturacion/
│   │   │   └── finanzas-facturacion.js
│   │   ├── index.js
│   │   ├── pagos-cobros/
│   │   │   └── index.js
│   │   └── reembolsos/
│   │       └── index.js
│   ├── index.js
│   ├── inventario-logistica/
│   │   ├── categorias/
│   │   │   └── listar.js
│   │   ├── existencias-movimientos/
│   │   │   └── listar-existencias.js
│   │   ├── inventario-logistica/
│   │   │   └── inventario-logistica.js
│   │   ├── productos/
│   │   │   └── listar.js
│   │   └── transferencias/
│   │       └── transferencias.js
│   └── vistas-interactivas.js
├── inteligente/
│   ├── asistente/
│   │   ├── chat.js
│   │   └── flotante.js
│   └── comprador/
│       └── comparacion.js
├── layouts/
│   ├── auth/
│   │   ├── encabezado.js
│   │   ├── mensajes.js
│   │   └── principal.js
│   ├── panel/
│   │   ├── encabezado.js
│   │   ├── menu-dinamico.js
│   │   ├── menu-lateral-desplegable.js
│   │   ├── notificaciones.js
│   │   └── principal.js
│   └── tienda/
│       ├── encabezado.js
│       ├── pie-pagina.js
│       └── principal.js
├── modulos/
│   └── gestion/
│       ├── dialogos-panel.js
│       ├── filtros-tablas.js
│       ├── filtros.js
│       ├── index.js
│       └── modulos-pendientes.js
├── nucleo/
│   ├── cargador-modulos.js
│   ├── cargador-recursos-panel.js
│   └── http.js
├── paneles/
│   └── dashboard.js
├── publico/
│   ├── catalogo/
│   │   ├── detalle.js
│   │   └── index.js
│   └── paginas/
│       └── index.js
├── utilidades/
│   ├── debounce.js
│   └── formato.js
└── vistas-inline-migradas.js
```

## 5. Árbol completo de `routes/`

```text
routes/
├── api.php
├── console.php
└── web.php
```

## 6. Árbol completo de `database/`

```text
database/
├── .gitignore
├── factories/
│   ├── CategoriaFactory.php
│   ├── Comunicacion/
│   │   └── NotificacionFactory.php
│   ├── Finanzas/
│   │   ├── CajaFactory.php
│   │   ├── CuentaBancariaFactory.php
│   │   ├── MedioPagoFactory.php
│   │   ├── MovimientoCajaFactory.php
│   │   ├── PagoFactory.php
│   │   ├── ReembolsoFactory.php
│   │   └── SesionCajaFactory.php
│   ├── Inventario/
│   │   ├── DetalleTransferenciaExistenciaFactory.php
│   │   ├── DispositivoFactory.php
│   │   └── TransferenciaExistenciaFactory.php
│   ├── Organizacion/
│   │   ├── AlmacenFactory.php
│   │   ├── EmpresaFactory.php
│   │   └── SedeFactory.php
│   ├── ProductoFactory.php
│   ├── Seguridad/
│   │   ├── ElementoMenuFactory.php
│   │   ├── MenuFactory.php
│   │   ├── ModuloFactory.php
│   │   ├── PantallaFactory.php
│   │   ├── PermisoFactory.php
│   │   └── RolFactory.php
│   └── UserFactory.php
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2026_10_10_171500_create_cell_sac_commerce_tables.php
│   ├── 2026_10_10_172618_migrate_legacy_users_to_laravel_users.php
│   ├── 2026_10_28_203424_create_devoluciones_garantias_table.php
│   ├── 2026_11_01_000753_create_enterprise_rbac_tables.php
│   ├── 2026_11_01_000754_add_admin_profile_security_fields.php
│   ├── 2026_11_01_013915_create_rbac_seed_versions_table.php
│   ├── 2026_11_01_014019_add_business_operation_site_scope_columns.php
│   ├── 2026_11_01_015103_reconcile_devoluciones_garantias_foreign_key_types.php
│   ├── 2026_11_01_015104_create_ecommerce_extension_tables.php
│   ├── 2026_11_01_015105_create_stock_sales_device_tables.php
│   ├── 2026_11_01_015106_create_electronic_billing_tables.php
│   ├── 2026_11_01_015107_create_finance_accounting_tables.php
│   ├── 2026_11_01_015108_create_logistics_returns_tables.php
│   ├── 2026_11_01_015109_create_support_legal_integration_tables.php
│   ├── 2026_11_01_015110_add_commerce_integration_columns.php
│   ├── 2026_11_01_015111_backfill_main_organization_and_stock.php
│   ├── 2026_11_01_015112_link_commerce_enterprise_foreign_keys.php
│   ├── 2026_11_01_015113_standardize_audit_events.php
│   ├── 2026_11_03_192348_agregar_canal_y_observaciones_a_ventas.php
│   ├── 2026_11_03_220504_add_cantidad_devuelta_to_detalles_transferencias_existencias.php
│   ├── 2026_11_05_120000_enable_admin_responsibles_screen.php
│   ├── 2026_11_05_120100_create_responsables_organizacionales_table.php
│   ├── 2026_11_05_120200_enable_configuration_screens.php
│   ├── 2026_11_05_120300_enable_billing_query_screen.php
│   ├── 2026_11_05_120400_enable_inventory_read_screens.php
│   ├── 2026_11_05_120500_enable_security_sessions_screen.php
│   ├── 2026_11_05_121000_enable_refunds_export_permission.php
│   ├── 2026_11_05_121100_enable_support_export_permission.php
│   ├── 2026_11_05_121200_unificar_menu_operaciones_comerciales.php
│   ├── 2026_11_05_130000_reduce_security_catalog_to_six_interfaces.php
│   ├── 2026_11_05_140000_add_tipo_documento_to_clientes.php
│   ├── 2026_11_05_140000_remove_organization_submenus.php
│   ├── 2026_11_05_235900_reduce_analysis_audit_menu_to_two_interfaces.php
│   ├── 2026_11_06_100000_create_product_reviews_favorites_tables.php
│   ├── 2026_11_06_100100_normalize_catalog_table_names.php
│   ├── 2026_11_09_000100_create_system_configurations_table.php
│   ├── 2026_11_10_144352_create_dynamic_profile_fields_tables.php
│   └── 2026_11_10_150656_add_guest_checkout_fields_to_pedidos_table.php
├── paquete_desbloqueo/
│   ├── 01_responsables.sql
│   ├── 02_seguridad_delegacion.sql
│   ├── 03_responsable_envios.sql
│   ├── 04_ecommerce.sql
│   └── 05_rbac_administrador.sql
└── seeders/
    ├── CategoriaSeeder.php
    ├── DatabaseSeeder.php
    └── RbacInicialSeeder.php
```

## 7. Árbol de recursos propios de `public/`

```text
public/
├── .htaccess
├── assets/
│   ├── cell-legacy/
│   │   └── img/
│   │       ├── marca/
│   │       │   ├── logo.jpeg
│   │       │   └── logoadmin.png
│   │       ├── productos/
│   │       │   ├── Accesorios/
│   │       │   │   ├── Apple/
│   │       │   │   │   ├── 001-apple-magsafe-charger-qi2-25w-principal.jpg
│   │       │   │   │   ├── 002-apple-40w-dynamic-power-adapter-with-60w-max-principal.jpg
│   │       │   │   │   ├── 004-apple-iphone-17-pro-max-techwoven-case-with-magsafe-principal.jpg
│   │       │   │   │   ├── 005-apple-iphone-17-pro-techwoven-case-with-magsafe-principal.jpg
│   │       │   │   │   ├── 006-apple-iphone-17-silicone-case-with-magsafe-principal.jpg
│   │       │   │   │   ├── 007-apple-iphone-air-case-with-magsafe-principal.jpg
│   │       │   │   │   ├── 008-apple-iphone-air-magsafe-battery-principal.jpg
│   │       │   │   │   ├── apple-airtag-frontal.jpg
│   │       │   │   │   ├── apple-bateria-magsafe-iphone-air-en-uso.jpg
│   │       │   │   │   ├── apple-bateria-magsafe-iphone-air-frontal.jpg
│   │       │   │   │   ├── apple-bateria-magsafe-iphone-air-lateral.jpg
│   │       │   │   │   ├── apple-cable-usb-c-60w-1m-detalle-conectores_fc3115cc.jpg
│   │       │   │   │   ├── apple-cable-usb-c-60w-1m-detalle-conectores.jpg
│   │       │   │   │   ├── apple-cable-usb-c-60w-1m-frontal_5659e941.jpg
│   │       │   │   │   ├── apple-cable-usb-c-60w-1m-frontal.jpg
│   │       │   │   │   ├── apple-cable-usb-c-a-lightning-1m-detalle-lightning.jpg
│   │       │   │   │   ├── apple-cable-usb-c-a-lightning-1m-detalle-usb-c.jpg
│   │       │   │   │   ├── apple-cable-usb-c-a-lightning-1m-frontal.jpg
│   │       │   │   │   ├── apple-cargador-20w-usb-c-detalle-puerto.jpg
│   │       │   │   │   ├── apple-cargador-20w-usb-c-frontal.jpg
│   │       │   │   │   ├── apple-cargador-20w-usb-c-lateral.jpg
│   │       │   │   │   ├── apple-cargador-35w-doble-usb-c-frontal.jpg
│   │       │   │   │   ├── apple-cargador-35w-doble-usb-c-lateral.jpg
│   │       │   │   │   ├── apple-cargador-35w-doble-usb-c-trasera.jpg
│   │       │   │   │   ├── apple-cargador-40w-dynamic-usb-c-frontal.jpg
│   │       │   │   │   ├── apple-cargador-40w-dynamic-usb-c-lateral.jpg
│   │       │   │   │   ├── apple-cargador-40w-dynamic-usb-c-trasera.jpg
│   │       │   │   │   ├── apple-cargador-magsafe-1m-detalle-conector.jpg
│   │       │   │   │   ├── apple-cargador-magsafe-1m-en-uso.jpg
│   │       │   │   │   ├── apple-cargador-magsafe-1m-frontal.jpg
│   │       │   │   │   ├── apple-cargador-magsafe-1m-lateral.jpg
│   │       │   │   │   ├── apple-cargador-magsafe-1m-perspectiva.jpg
│   │       │   │   │   ├── apple-cargador-usb-c-20w-comparacion-tamano.jpg
│   │       │   │   │   ├── apple-cargador-usb-c-20w-detalle-conector.jpg
│   │       │   │   │   ├── apple-cargador-usb-c-20w-frontal.jpg
│   │       │   │   │   ├── apple-cargador-usb-c-20w-perspectiva.jpg
│   │       │   │   │   ├── apple-funda-iphone-16-transparente-magsafe-trasera-negro.jpg
│   │       │   │   │   ├── apple-funda-iphone-16-transparente-magsafe-trasera.jpg
│   │       │   │   │   ├── apple-funda-iphone-17-silicona-magsafe-guava-trasera-2.jpg
│   │       │   │   │   ├── apple-funda-iphone-17-silicona-magsafe-guava-trasera.jpg
│   │       │   │   │   ├── apple-magsafe-1m-detalle-conector.jpg
│   │       │   │   │   ├── apple-magsafe-1m-frontal.jpg
│   │       │   │   │   ├── apple-magsafe-1m-perspectiva.jpg
│   │       │   │   │   ├── apple-magsafe-1m-trasera.jpg
│   │       │   │   │   ├── oppo-cable-usb-c-a-usb-c-supervooc-frontal.jpg
│   │       │   │   │   ├── oppo-cargador-magnetico-airvooc-50w-frontal.jpg
│   │       │   │   │   ├── oppo-cargador-magnetico-airvooc-50w-lateral.jpg
│   │       │   │   │   ├── oppo-cargador-magnetico-airvooc-50w-perspectiva.jpg
│   │       │   │   │   ├── oppo-cargador-magnetico-airvooc-50w-trasera.jpg
│   │       │   │   │   ├── oppo-cargador-supervooc-45w-detalle-puerto.jpg
│   │       │   │   │   ├── oppo-cargador-supervooc-45w-frontal.jpg
│   │       │   │   │   ├── oppo-cargador-supervooc-45w-lateral.jpg
│   │       │   │   │   ├── oppo-cargador-supervooc-45w-perspectiva.jpg
│   │       │   │   │   ├── oppo-cargador-supervooc-80w-detalle-puerto.jpg
│   │       │   │   │   ├── oppo-cargador-supervooc-80w-frontal.jpg
│   │       │   │   │   ├── oppo-cargador-supervooc-80w-lateral.jpg
│   │       │   │   │   ├── oppo-cargador-supervooc-80w-perspectiva.jpg
│   │       │   │   │   ├── oppo-cargador-supervooc-100w-doble-puerto-detalle-puerto.jpg
│   │       │   │   │   ├── oppo-cargador-supervooc-100w-doble-puerto-frontal.jpg
│   │       │   │   │   ├── oppo-cargador-supervooc-100w-doble-puerto-lateral.jpg
│   │       │   │   │   ├── oppo-cargador-supervooc-100w-doble-puerto-perspectiva.jpg
│   │       │   │   │   ├── oppo-funda-find-x9-magnetica-gris-interior.jpg
│   │       │   │   │   ├── oppo-funda-find-x9-magnetica-gris-lateral.jpg
│   │       │   │   │   ├── oppo-funda-find-x9-magnetica-gris-trasera.jpg
│   │       │   │   │   ├── oppo-funda-find-x9-magnetica-negra-interior.jpg
│   │       │   │   │   ├── oppo-funda-find-x9-magnetica-negra-lateral.jpg
│   │       │   │   │   ├── oppo-funda-find-x9-magnetica-negra-trasera.jpg
│   │       │   │   │   ├── oppo-funda-reno16-magnetica-con-telefono.jpg
│   │       │   │   │   ├── oppo-funda-reno16-magnetica-lateral.jpg
│   │       │   │   │   ├── oppo-funda-reno16-magnetica-trasera.jpg
│   │       │   │   │   ├── samsung-cargador-25w-usb-c-negro-detalle-puerto.jpg
│   │       │   │   │   ├── samsung-cargador-25w-usb-c-negro-frontal.jpg
│   │       │   │   │   ├── samsung-cargador-25w-usb-c-negro-lateral.jpg
│   │       │   │   │   ├── samsung-cargador-25w-usb-c-negro-perspectiva.jpg
│   │       │   │   │   ├── samsung-cargador-45w-usb-c-negro-detalle-puerto.jpg
│   │       │   │   │   ├── samsung-cargador-45w-usb-c-negro-frontal.jpg
│   │       │   │   │   ├── samsung-cargador-45w-usb-c-negro-lateral.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-a27-con-tarjetero-negra-detalle-tarjetero.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-a27-con-tarjetero-negra-frontal.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-a27-con-tarjetero-negra-lateral.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-a27-con-tarjetero-negra-trasera.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-s26-fe-silicona-magnetica-negra-detalle-magnetico.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-s26-fe-silicona-magnetica-negra-frontal.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-s26-fe-silicona-magnetica-negra-lateral.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-s26-fe-silicona-magnetica-negra-trasera.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-s26-fe-transparente-frontal.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-s26-fe-transparente-lateral.jpg
│   │       │   │   │   └── samsung-funda-galaxy-s26-fe-transparente-trasera.jpg
│   │       │   │   ├── Oppo/
│   │       │   │   │   ├── 047-oppo-airvooc-50w-magnetic-charger-2-principal.jpg
│   │       │   │   │   ├── 048-oppo-supervooc-100w-power-adapter-kit-principal.jpg
│   │       │   │   │   ├── 049-oppo-supervooc-45w-charger-principal.jpg
│   │       │   │   │   ├── 050-oppo-supervooc-45w-dual-ports-gan-power-adapter-principal.jpg
│   │       │   │   │   ├── 051-oppo-supervooc-80w-charger-principal.jpg
│   │       │   │   │   ├── 052-oppo-supervooc-80w-dual-ports-gan-power-adapter-principal.jpg
│   │       │   │   │   ├── 053-oppo-find-x9-light-luxury-magnetic-case-principal.jpg
│   │       │   │   │   ├── 054-oppo-reno14-f-gradient-blue-magnetic-case-principal.jpg
│   │       │   │   │   ├── 055-oppo-reno14-gradient-blue-magnetic-case-principal.jpg
│   │       │   │   │   ├── 056-oppo-reno16-f-fs-magnetic-protective-case-principal.jpg
│   │       │   │   │   ├── 057-oppo-reno16-16-pro-magnetic-protective-case-principal.jpg
│   │       │   │   │   ├── 058-oppo-22-5w-magnetic-ring-power-bank-principal.jpg
│   │       │   │   │   ├── 059-oppo-magnetic-power-bank-air-5000mah-principal.jpg
│   │       │   │   │   ├── oppo-cable-supervooc-usb-c-8a-1m-detalle-conectores.jpg
│   │       │   │   │   └── oppo-cable-supervooc-usb-c-8a-1m-en-uso.jpg
│   │       │   │   ├── Samsung/
│   │       │   │   │   ├── 080-samsung-galaxy-s26-slim-magnet-case-principal.jpg
│   │       │   │   │   ├── 081-samsung-galaxy-s26-ultra-slim-magnet-case-principal.jpg
│   │       │   │   │   ├── 082-samsung-galaxy-s26-plus-slim-magnet-case-principal.jpg
│   │       │   │   │   ├── samsung-cable-usb-c-3a-1-8m-detalle-conectores.jpg
│   │       │   │   │   ├── samsung-cable-usb-c-3a-1-8m-detalle-contactos.jpg
│   │       │   │   │   ├── samsung-cable-usb-c-3a-1-8m-empaque.jpg
│   │       │   │   │   ├── samsung-cable-usb-c-3a-1-8m-frontal.jpg
│   │       │   │   │   ├── samsung-cargador-usb-c-25w-con-cable.jpg
│   │       │   │   │   ├── samsung-cargador-usb-c-25w-detalle-conector.jpg
│   │       │   │   │   ├── samsung-cargador-usb-c-25w-lateral.jpg
│   │       │   │   │   ├── samsung-cargador-usb-c-25w-perspectiva.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-a55-silicone-negra-frontal.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-a55-silicone-negra-interior.jpg
│   │       │   │   │   ├── samsung-funda-galaxy-a55-silicone-negra-perspectiva.jpg
│   │       │   │   │   └── samsung-funda-galaxy-a55-silicone-negra-trasera.jpg
│   │       │   │   └── Xiaomi/
│   │       │   │       ├── 109-xiaomi-67w-gan-charger-2c1a-principal.jpg
│   │       │   │       ├── 110-xiaomi-ultrathin-magnetic-power-bank-5000-15w-principal.jpg
│   │       │   │       ├── xiaomi-cable-usb-a-usb-c-6a-detalle-conectores.jpg
│   │       │   │       ├── xiaomi-cable-usb-a-usb-c-6a-en-uso.jpg
│   │       │   │       ├── xiaomi-power-bank-33w-20000mah-colores.jpg
│   │       │   │       ├── xiaomi-power-bank-33w-20000mah-detalle-puertos.jpg
│   │       │   │       ├── xiaomi-power-bank-33w-20000mah-en-uso.jpg
│   │       │   │       └── xiaomi-power-bank-33w-20000mah-frontal.jpg
│   │       │   ├── Audio/
│   │       │   │   ├── Apple/
│   │       │   │   │   ├── 003-apple-iphone-17-clear-case-with-magsafe-principal.jpg
│   │       │   │   │   ├── 009-apple-airpods-5-principal.jpg
│   │       │   │   │   ├── 010-apple-airpods-pro-3-principal.jpg
│   │       │   │   │   ├── 011-apple-airpods-max-2-principal.jpg
│   │       │   │   │   ├── AirPods5-2026-Apple-0.jpg
│   │       │   │   │   ├── AirPods5-2026-Apple-1.jpg
│   │       │   │   │   ├── AirPods5-2026-Apple-2.jpg
│   │       │   │   │   ├── AirPods5-2026-Apple-3.jpg
│   │       │   │   │   ├── AirPodsMax2-2026-Apple-0.jpg
│   │       │   │   │   ├── AirPodsMax2-2026-Apple-1.jpg
│   │       │   │   │   ├── AirPodsMax2-2026-Apple-2.jpg
│   │       │   │   │   ├── AirPodsMax2-2026-Apple-3.jpg
│   │       │   │   │   ├── AirPodsMax2-2026-Apple-4.jpg
│   │       │   │   │   ├── AirPodsPro3-2025-Apple-0.jpg
│   │       │   │   │   ├── AirPodsPro3-2025-Apple-1.jpg
│   │       │   │   │   ├── AirPodsPro3-2025-Apple-2.jpg
│   │       │   │   │   ├── apple-funda-iphone-16-clear-magsafe-frontal.jpg
│   │       │   │   │   └── apple-funda-iphone-16-clear-magsafe-trasera.jpg
│   │       │   │   ├── Honor/
│   │       │   │   │   ├── 019-honor-earbuds-4-principal.jpg
│   │       │   │   │   ├── 020-honor-earbuds-5e-principal.jpg
│   │       │   │   │   ├── 021-honor-earbuds-a-pro-principal.jpg
│   │       │   │   │   ├── 022-honor-earbuds-open-principal.jpg
│   │       │   │   │   ├── 023-honor-earbuds-x10-lite-principal.jpg
│   │       │   │   │   ├── honor-choice-earbuds-x-detalle-auricular.jpg
│   │       │   │   │   ├── honor-choice-earbuds-x-estuche.jpg
│   │       │   │   │   ├── honor-choice-earbuds-x-frontal.jpg
│   │       │   │   │   ├── HonorEarbuds4-Honor-2026-0.jpg
│   │       │   │   │   ├── HonorEarbuds4-Honor-2026-1.jpg
│   │       │   │   │   ├── HonorEarbuds4-Honor-2026-2.jpg
│   │       │   │   │   ├── HonorEarbuds4-Honor-2026-3.jpg
│   │       │   │   │   ├── HonorEarbuds4-Honor-2026-4.jpg
│   │       │   │   │   ├── honorEarBuds5e-Honor-2026-0.webp
│   │       │   │   │   ├── honorEarBuds5e-Honor-2026-1.webp
│   │       │   │   │   ├── honorEarBuds5e-Honor-2026-2.webp
│   │       │   │   │   ├── honorEarBuds5e-Honor-2026-3.webp
│   │       │   │   │   ├── HonorEarbudsAPro-Honor-2026-0.jpg
│   │       │   │   │   ├── HonorEarbudsAPro-Honor-2026-1.jpg
│   │       │   │   │   ├── HonorEarbudsAPro-Honor-2026-2.jpg
│   │       │   │   │   ├── HonorEarbudsAPro-Honor-2026-3.jpg
│   │       │   │   │   ├── HonorEarbudsOpen-Honor-2026-0.jpg
│   │       │   │   │   ├── HonorEarbudsOpen-Honor-2026-1.jpg
│   │       │   │   │   ├── HonorEarbudsOpen-Honor-2026-2.jpg
│   │       │   │   │   ├── HonorEarbudsX10Lite-Honor-2026-0.jpg
│   │       │   │   │   ├── HonorEarbudsX10Lite-Honor-2026-1.jpg
│   │       │   │   │   ├── HonorEarbudsX10Lite-Honor-2026-2.jpg
│   │       │   │   │   └── HonorEarbudsX10Lite-Honor-2026-3.jpg
│   │       │   │   ├── JBL/
│   │       │   │   │   ├── 039-jbl-live-680nc-plegado.jpg
│   │       │   │   │   ├── 039-jbl-live-680nc-principal.jpg
│   │       │   │   │   ├── 040-jbl-live-780nc-plegado.jpg
│   │       │   │   │   ├── 040-jbl-live-780nc-principal.jpg
│   │       │   │   │   ├── 041-jbl-tour-one-m3-principal.jpg
│   │       │   │   │   ├── 042-jbl-clip-5-principal.jpg
│   │       │   │   │   ├── 043-jbl-go-5-principal.jpg
│   │       │   │   │   ├── 044-jbl-charge-6-principal.jpg
│   │       │   │   │   ├── 045-jbl-flip-7-principal.jpg
│   │       │   │   │   ├── 046-jbl-grip-principal.jpg
│   │       │   │   │   ├── beats-cable-usb-c-3m-azul-detalle-conectores.jpg
│   │       │   │   │   ├── beats-cable-usb-c-3m-azul-en-uso.jpg
│   │       │   │   │   ├── beats-cable-usb-c-3m-azul-frontal.jpg
│   │       │   │   │   ├── beats-solo-4-rosa-frontal.jpg
│   │       │   │   │   ├── beats-solo-4-rosa-lateral.jpg
│   │       │   │   │   ├── beats-solo-4-rosa-plegado.jpg
│   │       │   │   │   ├── beats-solo-4-rosa-sobre-mesa.jpg
│   │       │   │   │   ├── beats-studio-pro-arena-con-estuche.jpg
│   │       │   │   │   ├── beats-studio-pro-arena-detalle-controles.jpg
│   │       │   │   │   ├── beats-studio-pro-arena-frontal.jpg
│   │       │   │   │   ├── beats-studio-pro-arena-perspectiva-trasera.jpg
│   │       │   │   │   ├── beats-studio-pro-arena-plegado.jpg
│   │       │   │   │   ├── Charge6-JBL-2025-0.jpg
│   │       │   │   │   ├── Charge6-JBL-2025-1.jpg
│   │       │   │   │   ├── Charge6-JBL-2025-2.jpg
│   │       │   │   │   ├── Charge6-JBL-2025-3.jpg
│   │       │   │   │   ├── Charge6-JBL-2025-4.jpg
│   │       │   │   │   ├── Charge6-JBL-2025-5.jpg
│   │       │   │   │   ├── Clip5-JBL-2024-0.jpg
│   │       │   │   │   ├── Clip5-JBL-2024-1.jpg
│   │       │   │   │   ├── Clip5-JBL-2024-2.jpg
│   │       │   │   │   ├── Clip5-JBL-2024-3.jpg
│   │       │   │   │   ├── Flip7-JBL-2025-0.jpg
│   │       │   │   │   ├── Flip7-JBL-2025-1.jpg
│   │       │   │   │   ├── Flip7-JBL-2025-2.jpg
│   │       │   │   │   ├── Flip7-JBL-2025-3.jpg
│   │       │   │   │   ├── Flip7-JBL-2025-4.jpg
│   │       │   │   │   ├── Go5-JBL-2026-0.jpg
│   │       │   │   │   ├── Go5-JBL-2026-1.jpg
│   │       │   │   │   ├── Go5-JBL-2026-2.jpg
│   │       │   │   │   ├── Go5-JBL-2026-3.jpg
│   │       │   │   │   ├── Grip-JBL-2025-0.jpg
│   │       │   │   │   ├── Grip-JBL-2025-1.jpg
│   │       │   │   │   ├── Grip-JBL-2025-2.jpg
│   │       │   │   │   ├── Grip-JBL-2025-3.jpg
│   │       │   │   │   ├── jbl-tune-520bt-blanco-detalle-almohadillas.jpg
│   │       │   │   │   ├── jbl-tune-520bt-blanco-frontal.jpg
│   │       │   │   │   ├── jbl-tune-520bt-blanco-lateral.jpg
│   │       │   │   │   ├── jbl-tune-520bt-blanco-perspectiva.jpg
│   │       │   │   │   ├── jbl-tune-520bt-blanco-plegado.jpg
│   │       │   │   │   ├── Live680NC-JBL-2026-0.jpg
│   │       │   │   │   ├── Live680NC-JBL-2026-1.jpg
│   │       │   │   │   ├── Live680NC-JBL-2026-2.jpg
│   │       │   │   │   ├── Live680NC-JBL-2026-3.jpg
│   │       │   │   │   ├── Live680NC-JBL-2026-4.jpg
│   │       │   │   │   ├── Live780NC-JBL-2026-0.jpg
│   │       │   │   │   ├── Live780NC-JBL-2026-1.jpg
│   │       │   │   │   ├── Live780NC-JBL-2026-2.jpg
│   │       │   │   │   ├── TourONEM3-JBL-2025-0.jpg
│   │       │   │   │   ├── TourONEM3-JBL-2025-1.jpg
│   │       │   │   │   └── TourONEM3-JBL-2025-2.jpg
│   │       │   │   ├── Oppo/
│   │       │   │   │   ├── 062-oppo-portable-wireless-speaker-principal.jpg
│   │       │   │   │   ├── oppo-enco-buds2-pro-colores.jpg
│   │       │   │   │   ├── oppo-enco-buds2-pro-detalle-auricular.jpg
│   │       │   │   │   ├── oppo-enco-buds2-pro-estuche.jpg
│   │       │   │   │   ├── oppo-enco-buds2-pro-frontal.jpg
│   │       │   │   │   ├── Speaker-OPPO-2025-0.jpg
│   │       │   │   │   └── Speaker-OPPO-2025-1.jpg
│   │       │   │   ├── Samsung/
│   │       │   │   │   ├── 083-samsung-galaxy-buds3-fe-principal.jpg
│   │       │   │   │   ├── 084-samsung-galaxy-buds3-pro-principal.jpg
│   │       │   │   │   ├── 085-samsung-galaxy-buds4-posterior.jpg
│   │       │   │   │   ├── 085-samsung-galaxy-buds4-principal.jpg
│   │       │   │   │   ├── 086-samsung-galaxy-buds4-pro-posterior.jpg
│   │       │   │   │   ├── 086-samsung-galaxy-buds4-pro-principal.jpg
│   │       │   │   │   ├── Buds3FE-SAMSUNG-2025-0.jpg
│   │       │   │   │   ├── Buds3FE-SAMSUNG-2025-1.jpg
│   │       │   │   │   ├── Buds3FE-SAMSUNG-2025-2.jpg
│   │       │   │   │   ├── Buds3Pro-SAMSUNG-2024-0.jpg
│   │       │   │   │   ├── Buds3Pro-SAMSUNG-2024-1.jpg
│   │       │   │   │   ├── Buds3Pro-SAMSUNG-2024-2.jpg
│   │       │   │   │   ├── Buds4-SAMSUNG-2026-0.jpg
│   │       │   │   │   ├── Buds4-SAMSUNG-2026-1.jpg
│   │       │   │   │   ├── Buds4-SAMSUNG-2026-2.jpg
│   │       │   │   │   ├── Buds4Pro-SAMSUNG-2026-0.jpg
│   │       │   │   │   └── Buds4Pro-SAMSUNG-2026-1.jpg
│   │       │   │   └── Xiaomi/
│   │       │   │       ├── 111-xiaomi-redmi-buds-8-principal.jpg
│   │       │   │       ├── 112-xiaomi-redmi-buds-8-active-principal.jpg
│   │       │   │       ├── 113-xiaomi-redmi-buds-8-lite-principal.jpg
│   │       │   │       ├── 114-xiaomi-redmi-buds-8-pro-principal.jpg
│   │       │   │       ├── 115-xiaomi-buds-5-pro-principal.jpg
│   │       │   │       ├── 116-xiaomi-buds-6-principal.jpg
│   │       │   │       ├── 117-xiaomi-openwear-stereo-pro-principal.jpg
│   │       │   │       ├── 118-xiaomi-redmi-headphones-neo-principal.jpg
│   │       │   │       ├── Buds5Pro-REDMI-2025-0.jpg
│   │       │   │       ├── Buds5Pro-REDMI-2025-1.jpg
│   │       │   │       ├── Buds6-REDMI-2026-0.jpg
│   │       │   │       ├── Buds6-REDMI-2026-1.jpg
│   │       │   │       ├── Buds6-REDMI-2026-2.jpg
│   │       │   │       ├── Buds8-REDMI-2026-0.jpg
│   │       │   │       ├── Buds8-REDMI-2026-1.jpg
│   │       │   │       ├── Buds8-REDMI-2026-2.jpg
│   │       │   │       ├── Buds8Active-REDMI-2026-0.jpg
│   │       │   │       ├── Buds8Active-REDMI-2026-1.jpg
│   │       │   │       ├── Buds8Active-REDMI-2026-2.jpg
│   │       │   │       ├── Buds8Active-REDMI-2026-3.jpg
│   │       │   │       ├── Buds8Lite-REDMI-2026-0.jpg
│   │       │   │       ├── Buds8Lite-REDMI-2026-1.jpg
│   │       │   │       ├── Buds8Lite-REDMI-2026-2.jpg
│   │       │   │       ├── Buds8Pro-REDMI-2026-0.jpg
│   │       │   │       ├── Buds8Pro-REDMI-2026-1.jpg
│   │       │   │       ├── HeadphonesNeo-REDMI-2026-0.jpg
│   │       │   │       ├── HeadphonesNeo-REDMI-2026-1.jpg
│   │       │   │       ├── HeadphonesNeo-REDMI-2026-2.jpg
│   │       │   │       ├── jbl-go-4-militar-detalle-conector.jpg
│   │       │   │       ├── jbl-go-4-militar-detalle-controles.jpg
│   │       │   │       ├── jbl-go-4-militar-empaque.jpg
│   │       │   │       ├── jbl-go-4-militar-frontal.jpg
│   │       │   │       ├── jbl-go-4-militar-perspectiva.jpg
│   │       │   │       ├── jbl-go-4-militar-trasera.jpg
│   │       │   │       ├── OpenWearStereoPro-REDMI-2025-0.jpg
│   │       │   │       └── OpenWearStereoPro-REDMI-2025-1.jpg
│   │       │   └── Celulares/
│   │       │       ├── Apple/
│   │       │       │   ├── 012-apple-iphone-17-principal.jpg
│   │       │       │   ├── 013-apple-iphone-17-pro-principal.jpg
│   │       │       │   ├── 014-apple-iphone-17-pro-max-principal.jpg
│   │       │       │   ├── 015-apple-iphone-17e-principal.jpg
│   │       │       │   ├── 016-apple-iphone-18-pro-principal.jpg
│   │       │       │   ├── 017-apple-iphone-18-pro-max-principal.jpg
│   │       │       │   ├── 018-apple-iphone-air-principal.jpg
│   │       │       │   ├── oppo-palo-selfie-magnetico-3-en-1-extendido.jpg
│   │       │       │   ├── oppo-palo-selfie-magnetico-3-en-1-frontal.jpg
│   │       │       │   ├── oppo-palo-selfie-magnetico-3-en-1-modo-tripode.jpg
│   │       │       │   ├── oppo-palo-selfie-magnetico-3-en-1-perspectiva.jpg
│   │       │       │   ├── oppo-tarjeta-enfriamiento-magnetica-frontal.jpg
│   │       │       │   ├── oppo-tarjeta-enfriamiento-magnetica-lateral.jpg
│   │       │       │   ├── oppo-tarjeta-enfriamiento-magnetica-perspectiva.jpg
│   │       │       │   ├── oppo-tarjeta-enfriamiento-magnetica-trasera.jpg
│   │       │       │   ├── samsung-galaxy-smarttag2-negro-frontal.jpg
│   │       │       │   ├── samsung-galaxy-smarttag2-negro-lateral.jpg
│   │       │       │   ├── samsung-galaxy-smarttag2-negro-perspectiva.jpg
│   │       │       │   └── samsung-galaxy-smarttag2-negro-trasera.jpg
│   │       │       ├── Honor/
│   │       │       │   ├── 024-honor-200-pro-posterior.jpg
│   │       │       │   ├── 024-honor-200-pro-principal.jpg
│   │       │       │   ├── 025-honor-400-principal.jpg
│   │       │       │   ├── 026-honor-400-lite-principal.jpg
│   │       │       │   ├── 027-honor-400-pro-principal.jpg
│   │       │       │   ├── 028-honor-600-posterior.jpg
│   │       │       │   ├── 028-honor-600-principal.jpg
│   │       │       │   ├── 029-honor-600-lite-posterior.jpg
│   │       │       │   ├── 029-honor-600-lite-principal.jpg
│   │       │       │   ├── 030-honor-600-pro-posterior.jpg
│   │       │       │   ├── 030-honor-600-pro-principal.jpg
│   │       │       │   ├── 031-honor-600-smart-5g-posterior.jpg
│   │       │       │   ├── 031-honor-600-smart-5g-principal.jpg
│   │       │       │   ├── 032-honor-magic-v5-principal.jpg
│   │       │       │   ├── 033-honor-magic-v6-principal.jpg
│   │       │       │   ├── 034-honor-magic7-pro-posterior.jpg
│   │       │       │   ├── 034-honor-magic7-pro-principal.jpg
│   │       │       │   ├── 035-honor-porsche-design-honor-magic7-rsr-posterior.jpg
│   │       │       │   ├── 035-honor-porsche-design-honor-magic7-rsr-principal.jpg
│   │       │       │   ├── 036-honor-magic8-pro-principal.jpg
│   │       │       │   ├── 037-honor-magic9-pro-max-posterior.jpg
│   │       │       │   ├── 037-honor-magic9-pro-max-principal.jpg
│   │       │       │   ├── 038-honor-robot-phone-principal.jpg
│   │       │       │   ├── honor-supercharge-66w-perspectiva.jpg
│   │       │       │   └── honor-supercharge-wireless-100w-frontal.jpg
│   │       │       ├── Oppo/
│   │       │       │   ├── 060-oppo-enco-clip2-principal.jpg
│   │       │       │   ├── 061-oppo-enco-x3s-principal.jpg
│   │       │       │   ├── 063-oppo-find-n5-principal.jpg
│   │       │       │   ├── 064-oppo-find-x9-principal.jpg
│   │       │       │   ├── 065-oppo-find-x9-pro-principal.jpg
│   │       │       │   ├── 066-oppo-reno13-5g-principal.jpg
│   │       │       │   ├── 067-oppo-reno13-f-principal.jpg
│   │       │       │   ├── 068-oppo-reno13-f-5g-principal.jpg
│   │       │       │   ├── 069-oppo-reno13-pro-5g-principal.jpg
│   │       │       │   ├── 070-oppo-reno14-5g-principal.jpg
│   │       │       │   ├── 071-oppo-reno14-f-5g-principal.jpg
│   │       │       │   ├── 072-oppo-reno14-pro-5g-principal.jpg
│   │       │       │   ├── 073-oppo-reno15-5g-principal.jpg
│   │       │       │   ├── 074-oppo-reno15-f-5g-principal.jpg
│   │       │       │   ├── 075-oppo-reno15-pro-5g-principal.jpg
│   │       │       │   ├── 076-oppo-reno15-pro-max-5g-principal.jpg
│   │       │       │   ├── 077-oppo-reno16-principal.jpg
│   │       │       │   ├── 078-oppo-reno16-f-principal.jpg
│   │       │       │   ├── 079-oppo-reno16-pro-principal.jpg
│   │       │       │   ├── EncoClip2-OPPO-2026-0.jpg
│   │       │       │   ├── EncoClip2-OPPO-2026-1.jpg
│   │       │       │   ├── EncoClip2-OPPO-2026-2.jpg
│   │       │       │   ├── EncoClip2-OPPO-2026-3.jpg
│   │       │       │   ├── EncoX3s-OPPO-2025-0.jpg
│   │       │       │   ├── EncoX3s-OPPO-2025-1.jpg
│   │       │       │   ├── EncoX3s-OPPO-2025-2.jpg
│   │       │       │   ├── EncoX3s-OPPO-2025-3.jpg
│   │       │       │   ├── oppo-supervooc-100w-dual-detalle-puertos.jpg
│   │       │       │   ├── oppo-supervooc-100w-dual-frontal.jpg
│   │       │       │   ├── oppo-supervooc-100w-dual-lateral.jpg
│   │       │       │   └── oppo-supervooc-100w-dual-perspectiva.jpg
│   │       │       ├── samsung/
│   │       │       │   ├── samsung-galaxy-a27-5g-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-a27-5g-frontal.jpg
│   │       │       │   ├── samsung-galaxy-a27-5g-lateral.jpg
│   │       │       │   ├── samsung-galaxy-a27-5g-trasera.jpg
│   │       │       │   ├── samsung-galaxy-a37-5g-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-a37-5g-frontal.jpg
│   │       │       │   ├── samsung-galaxy-a37-5g-lateral.jpg
│   │       │       │   ├── samsung-galaxy-a37-5g-trasera.jpg
│   │       │       │   ├── samsung-galaxy-a57-5g-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-a57-5g-frontal.jpg
│   │       │       │   ├── samsung-galaxy-a57-5g-lateral.jpg
│   │       │       │   ├── samsung-galaxy-a57-5g-trasera.jpg
│   │       │       │   ├── samsung-galaxy-s25-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-s25-fe-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-s25-fe-frontal.jpg
│   │       │       │   ├── samsung-galaxy-s25-fe-lateral.jpg
│   │       │       │   ├── samsung-galaxy-s25-fe-trasera.jpg
│   │       │       │   ├── samsung-galaxy-s25-frontal.jpg
│   │       │       │   ├── samsung-galaxy-s25-lateral.jpg
│   │       │       │   ├── samsung-galaxy-s25-trasera.jpg
│   │       │       │   ├── samsung-galaxy-s25-ultra-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-s25-ultra-frontal.jpg
│   │       │       │   ├── samsung-galaxy-s25-ultra-lateral.jpg
│   │       │       │   ├── samsung-galaxy-s25-ultra-trasera.jpg
│   │       │       │   ├── samsung-galaxy-s26-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-s26-fe-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-s26-fe-frontal.jpg
│   │       │       │   ├── samsung-galaxy-s26-fe-lateral.jpg
│   │       │       │   ├── samsung-galaxy-s26-fe-trasera.jpg
│   │       │       │   ├── samsung-galaxy-s26-frontal.jpg
│   │       │       │   ├── samsung-galaxy-s26-lateral.jpg
│   │       │       │   ├── samsung-galaxy-s26-plus-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-s26-plus-frontal.jpg
│   │       │       │   ├── samsung-galaxy-s26-plus-lateral.jpg
│   │       │       │   ├── samsung-galaxy-s26-plus-trasera.jpg
│   │       │       │   ├── samsung-galaxy-s26-trasera.jpg
│   │       │       │   ├── samsung-galaxy-s26-ultra-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-s26-ultra-frontal.jpg
│   │       │       │   ├── samsung-galaxy-s26-ultra-lateral.jpg
│   │       │       │   ├── samsung-galaxy-s26-ultra-trasera.jpg
│   │       │       │   ├── samsung-galaxy-z-flip8-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-z-flip8-frontal.jpg
│   │       │       │   ├── samsung-galaxy-z-flip8-lateral.jpg
│   │       │       │   ├── samsung-galaxy-z-flip8-trasera.jpg
│   │       │       │   ├── samsung-galaxy-z-fold8-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-z-fold8-frontal.jpg
│   │       │       │   ├── samsung-galaxy-z-fold8-lateral.jpg
│   │       │       │   ├── samsung-galaxy-z-fold8-trasera.jpg
│   │       │       │   ├── samsung-galaxy-z-fold8-ultra-detalle-camara.jpg
│   │       │       │   ├── samsung-galaxy-z-fold8-ultra-frontal.jpg
│   │       │       │   ├── samsung-galaxy-z-fold8-ultra-lateral.jpg
│   │       │       │   └── samsung-galaxy-z-fold8-ultra-trasera.jpg
│   │       │       └── Xiaomi/
│   │       │           ├── 108-xiaomi-120w-hypercharge-combo-type-a-principal.jpg
│   │       │           ├── 119-xiaomi-poco-f7-principal.jpg
│   │       │           ├── 120-xiaomi-poco-f7-pro-principal.jpg
│   │       │           ├── 121-xiaomi-poco-f7-ultra-principal.jpg
│   │       │           ├── 122-xiaomi-poco-x7-principal.jpg
│   │       │           ├── 123-xiaomi-poco-x7-pro-principal.jpg
│   │       │           ├── 124-xiaomi-poco-c71-principal.jpg
│   │       │           ├── 125-xiaomi-poco-c81-pro-principal.jpg
│   │       │           ├── 126-xiaomi-poco-c85-principal.jpg
│   │       │           ├── 127-xiaomi-poco-f8-pro-principal.jpg
│   │       │           ├── 128-xiaomi-poco-f8-ultra-principal.jpg
│   │       │           ├── 129-xiaomi-poco-f9-pro-principal.jpg
│   │       │           ├── 130-xiaomi-poco-f9-ultra-principal.jpg
│   │       │           ├── 131-xiaomi-poco-m7-pro-5g-principal.jpg
│   │       │           ├── 132-xiaomi-poco-m8-5g-principal.jpg
│   │       │           ├── 133-xiaomi-poco-m8-pro-5g-principal.jpg
│   │       │           ├── 134-xiaomi-poco-m8s-5g-principal.jpg
│   │       │           ├── 135-xiaomi-poco-x8-pro-principal.jpg
│   │       │           ├── 136-xiaomi-poco-x8-pro-max-principal.jpg
│   │       │           ├── 137-xiaomi-redmi-17-principal.jpg
│   │       │           ├── 138-xiaomi-redmi-a5-principal.jpg
│   │       │           ├── 139-xiaomi-redmi-a7-pro-principal.jpg
│   │       │           ├── 140-xiaomi-redmi-15c-principal.jpg
│   │       │           ├── 141-xiaomi-redmi-note-14-principal.jpg
│   │       │           ├── 142-xiaomi-redmi-note-14-5g-principal.jpg
│   │       │           ├── 143-xiaomi-redmi-note-14-pro-principal.jpg
│   │       │           ├── 144-xiaomi-redmi-note-14-pro-5g-principal.jpg
│   │       │           ├── 145-xiaomi-redmi-note-14-pro-plus-5g-principal.jpg
│   │       │           ├── 146-xiaomi-redmi-note-15-principal.jpg
│   │       │           ├── 147-xiaomi-redmi-note-15-5g-principal.jpg
│   │       │           ├── 148-xiaomi-redmi-note-15-pro-principal.jpg
│   │       │           ├── 149-xiaomi-redmi-note-15-pro-5g-principal.jpg
│   │       │           ├── 150-xiaomi-redmi-note-15-pro-plus-5g-principal.jpg
│   │       │           ├── 151-xiaomi-14-ultra-principal.jpg
│   │       │           ├── 152-xiaomi-15-principal.jpg
│   │       │           ├── 153-xiaomi-15-ultra-principal.jpg
│   │       │           ├── 154-xiaomi-15t-principal.jpg
│   │       │           ├── 155-xiaomi-15t-pro-principal.jpg
│   │       │           ├── 156-xiaomi-17-principal.jpg
│   │       │           ├── 157-xiaomi-17-ultra-principal.jpg
│   │       │           └── xiaomi-hypercharge-67w-frontal.jpg
│   │       └── publico/
│   │           ├── inicio/
│   │           │   ├── banners/
│   │           │   │   ├── bannerInicio.png
│   │           │   │   ├── exhibicion1.jpg
│   │           │   │   ├── exhibicion2.jpg
│   │           │   │   ├── exhibicion3.jpg
│   │           │   │   └── exhibicion4.jpg
│   │           │   └── secciones/
│   │           │       └── hero-devices.png
│   │           └── nosotros/
│   │               └── local.jpg
│   ├── img/
│   │   └── productos/
│   │       ├── Accesorios/
│   │       │   ├── Apple/
│   │       │   │   ├── 001-apple-magsafe-charger-qi2-25w-principal.jpg
│   │       │   │   ├── 002-apple-40w-dynamic-power-adapter-with-60w-max-principal.jpg
│   │       │   │   ├── 004-apple-iphone-17-pro-max-techwoven-case-with-magsafe-principal.jpg
│   │       │   │   ├── 005-apple-iphone-17-pro-techwoven-case-with-magsafe-principal.jpg
│   │       │   │   ├── 006-apple-iphone-17-silicone-case-with-magsafe-principal.jpg
│   │       │   │   ├── 007-apple-iphone-air-case-with-magsafe-principal.jpg
│   │       │   │   ├── 008-apple-iphone-air-magsafe-battery-principal.jpg
│   │       │   │   ├── apple-airtag-frontal.jpg
│   │       │   │   ├── apple-bateria-magsafe-iphone-air-en-uso.jpg
│   │       │   │   ├── apple-bateria-magsafe-iphone-air-frontal.jpg
│   │       │   │   ├── apple-bateria-magsafe-iphone-air-lateral.jpg
│   │       │   │   ├── apple-cable-usb-c-60w-1m-detalle-conectores_fc3115cc.jpg
│   │       │   │   ├── apple-cable-usb-c-60w-1m-detalle-conectores.jpg
│   │       │   │   ├── apple-cable-usb-c-60w-1m-frontal_5659e941.jpg
│   │       │   │   ├── apple-cable-usb-c-60w-1m-frontal.jpg
│   │       │   │   ├── apple-cable-usb-c-a-lightning-1m-detalle-lightning.jpg
│   │       │   │   ├── apple-cable-usb-c-a-lightning-1m-detalle-usb-c.jpg
│   │       │   │   ├── apple-cable-usb-c-a-lightning-1m-frontal.jpg
│   │       │   │   ├── apple-cargador-20w-usb-c-detalle-puerto.jpg
│   │       │   │   ├── apple-cargador-20w-usb-c-frontal.jpg
│   │       │   │   ├── apple-cargador-20w-usb-c-lateral.jpg
│   │       │   │   ├── apple-cargador-35w-doble-usb-c-frontal.jpg
│   │       │   │   ├── apple-cargador-35w-doble-usb-c-lateral.jpg
│   │       │   │   ├── apple-cargador-35w-doble-usb-c-trasera.jpg
│   │       │   │   ├── apple-cargador-40w-dynamic-usb-c-frontal.jpg
│   │       │   │   ├── apple-cargador-40w-dynamic-usb-c-lateral.jpg
│   │       │   │   ├── apple-cargador-40w-dynamic-usb-c-trasera.jpg
│   │       │   │   ├── apple-cargador-magsafe-1m-detalle-conector.jpg
│   │       │   │   ├── apple-cargador-magsafe-1m-en-uso.jpg
│   │       │   │   ├── apple-cargador-magsafe-1m-frontal.jpg
│   │       │   │   ├── apple-cargador-magsafe-1m-lateral.jpg
│   │       │   │   ├── apple-cargador-magsafe-1m-perspectiva.jpg
│   │       │   │   ├── apple-cargador-usb-c-20w-comparacion-tamano.jpg
│   │       │   │   ├── apple-cargador-usb-c-20w-detalle-conector.jpg
│   │       │   │   ├── apple-cargador-usb-c-20w-frontal.jpg
│   │       │   │   ├── apple-cargador-usb-c-20w-perspectiva.jpg
│   │       │   │   ├── apple-funda-iphone-16-transparente-magsafe-trasera-negro.jpg
│   │       │   │   ├── apple-funda-iphone-16-transparente-magsafe-trasera.jpg
│   │       │   │   ├── apple-funda-iphone-17-silicona-magsafe-guava-trasera-2.jpg
│   │       │   │   ├── apple-funda-iphone-17-silicona-magsafe-guava-trasera.jpg
│   │       │   │   ├── apple-magsafe-1m-detalle-conector.jpg
│   │       │   │   ├── apple-magsafe-1m-frontal.jpg
│   │       │   │   ├── apple-magsafe-1m-perspectiva.jpg
│   │       │   │   ├── apple-magsafe-1m-trasera.jpg
│   │       │   │   ├── oppo-cable-usb-c-a-usb-c-supervooc-frontal.jpg
│   │       │   │   ├── oppo-cargador-magnetico-airvooc-50w-frontal.jpg
│   │       │   │   ├── oppo-cargador-magnetico-airvooc-50w-lateral.jpg
│   │       │   │   ├── oppo-cargador-magnetico-airvooc-50w-perspectiva.jpg
│   │       │   │   ├── oppo-cargador-magnetico-airvooc-50w-trasera.jpg
│   │       │   │   ├── oppo-cargador-supervooc-45w-detalle-puerto.jpg
│   │       │   │   ├── oppo-cargador-supervooc-45w-frontal.jpg
│   │       │   │   ├── oppo-cargador-supervooc-45w-lateral.jpg
│   │       │   │   ├── oppo-cargador-supervooc-45w-perspectiva.jpg
│   │       │   │   ├── oppo-cargador-supervooc-80w-detalle-puerto.jpg
│   │       │   │   ├── oppo-cargador-supervooc-80w-frontal.jpg
│   │       │   │   ├── oppo-cargador-supervooc-80w-lateral.jpg
│   │       │   │   ├── oppo-cargador-supervooc-80w-perspectiva.jpg
│   │       │   │   ├── oppo-cargador-supervooc-100w-doble-puerto-detalle-puerto.jpg
│   │       │   │   ├── oppo-cargador-supervooc-100w-doble-puerto-frontal.jpg
│   │       │   │   ├── oppo-cargador-supervooc-100w-doble-puerto-lateral.jpg
│   │       │   │   ├── oppo-cargador-supervooc-100w-doble-puerto-perspectiva.jpg
│   │       │   │   ├── oppo-funda-find-x9-magnetica-gris-interior.jpg
│   │       │   │   ├── oppo-funda-find-x9-magnetica-gris-lateral.jpg
│   │       │   │   ├── oppo-funda-find-x9-magnetica-gris-trasera.jpg
│   │       │   │   ├── oppo-funda-find-x9-magnetica-negra-interior.jpg
│   │       │   │   ├── oppo-funda-find-x9-magnetica-negra-lateral.jpg
│   │       │   │   ├── oppo-funda-find-x9-magnetica-negra-trasera.jpg
│   │       │   │   ├── oppo-funda-reno16-magnetica-con-telefono.jpg
│   │       │   │   ├── oppo-funda-reno16-magnetica-lateral.jpg
│   │       │   │   ├── oppo-funda-reno16-magnetica-trasera.jpg
│   │       │   │   ├── samsung-cargador-25w-usb-c-negro-detalle-puerto.jpg
│   │       │   │   ├── samsung-cargador-25w-usb-c-negro-frontal.jpg
│   │       │   │   ├── samsung-cargador-25w-usb-c-negro-lateral.jpg
│   │       │   │   ├── samsung-cargador-25w-usb-c-negro-perspectiva.jpg
│   │       │   │   ├── samsung-cargador-45w-usb-c-negro-detalle-puerto.jpg
│   │       │   │   ├── samsung-cargador-45w-usb-c-negro-frontal.jpg
│   │       │   │   ├── samsung-cargador-45w-usb-c-negro-lateral.jpg
│   │       │   │   ├── samsung-funda-galaxy-a27-con-tarjetero-negra-detalle-tarjetero.jpg
│   │       │   │   ├── samsung-funda-galaxy-a27-con-tarjetero-negra-frontal.jpg
│   │       │   │   ├── samsung-funda-galaxy-a27-con-tarjetero-negra-lateral.jpg
│   │       │   │   ├── samsung-funda-galaxy-a27-con-tarjetero-negra-trasera.jpg
│   │       │   │   ├── samsung-funda-galaxy-s26-fe-silicona-magnetica-negra-detalle-magnetico.jpg
│   │       │   │   ├── samsung-funda-galaxy-s26-fe-silicona-magnetica-negra-frontal.jpg
│   │       │   │   ├── samsung-funda-galaxy-s26-fe-silicona-magnetica-negra-lateral.jpg
│   │       │   │   ├── samsung-funda-galaxy-s26-fe-silicona-magnetica-negra-trasera.jpg
│   │       │   │   ├── samsung-funda-galaxy-s26-fe-transparente-frontal.jpg
│   │       │   │   ├── samsung-funda-galaxy-s26-fe-transparente-lateral.jpg
│   │       │   │   └── samsung-funda-galaxy-s26-fe-transparente-trasera.jpg
│   │       │   ├── Oppo/
│   │       │   │   ├── 047-oppo-airvooc-50w-magnetic-charger-2-principal.jpg
│   │       │   │   ├── 048-oppo-supervooc-100w-power-adapter-kit-principal.jpg
│   │       │   │   ├── 049-oppo-supervooc-45w-charger-principal.jpg
│   │       │   │   ├── 050-oppo-supervooc-45w-dual-ports-gan-power-adapter-principal.jpg
│   │       │   │   ├── 051-oppo-supervooc-80w-charger-principal.jpg
│   │       │   │   ├── 052-oppo-supervooc-80w-dual-ports-gan-power-adapter-principal.jpg
│   │       │   │   ├── 053-oppo-find-x9-light-luxury-magnetic-case-principal.jpg
│   │       │   │   ├── 054-oppo-reno14-f-gradient-blue-magnetic-case-principal.jpg
│   │       │   │   ├── 055-oppo-reno14-gradient-blue-magnetic-case-principal.jpg
│   │       │   │   ├── 056-oppo-reno16-f-fs-magnetic-protective-case-principal.jpg
│   │       │   │   ├── 057-oppo-reno16-16-pro-magnetic-protective-case-principal.jpg
│   │       │   │   ├── 058-oppo-22-5w-magnetic-ring-power-bank-principal.jpg
│   │       │   │   ├── 059-oppo-magnetic-power-bank-air-5000mah-principal.jpg
│   │       │   │   ├── oppo-cable-supervooc-usb-c-8a-1m-detalle-conectores.jpg
│   │       │   │   └── oppo-cable-supervooc-usb-c-8a-1m-en-uso.jpg
│   │       │   ├── Samsung/
│   │       │   │   ├── 080-samsung-galaxy-s26-slim-magnet-case-principal.jpg
│   │       │   │   ├── 081-samsung-galaxy-s26-ultra-slim-magnet-case-principal.jpg
│   │       │   │   ├── 082-samsung-galaxy-s26-plus-slim-magnet-case-principal.jpg
│   │       │   │   ├── samsung-cable-usb-c-3a-1-8m-detalle-conectores.jpg
│   │       │   │   ├── samsung-cable-usb-c-3a-1-8m-detalle-contactos.jpg
│   │       │   │   ├── samsung-cable-usb-c-3a-1-8m-empaque.jpg
│   │       │   │   ├── samsung-cable-usb-c-3a-1-8m-frontal.jpg
│   │       │   │   ├── samsung-cargador-usb-c-25w-con-cable.jpg
│   │       │   │   ├── samsung-cargador-usb-c-25w-detalle-conector.jpg
│   │       │   │   ├── samsung-cargador-usb-c-25w-lateral.jpg
│   │       │   │   ├── samsung-cargador-usb-c-25w-perspectiva.jpg
│   │       │   │   ├── samsung-funda-galaxy-a55-silicone-negra-frontal.jpg
│   │       │   │   ├── samsung-funda-galaxy-a55-silicone-negra-interior.jpg
│   │       │   │   ├── samsung-funda-galaxy-a55-silicone-negra-perspectiva.jpg
│   │       │   │   └── samsung-funda-galaxy-a55-silicone-negra-trasera.jpg
│   │       │   └── Xiaomi/
│   │       │       ├── 109-xiaomi-67w-gan-charger-2c1a-principal.jpg
│   │       │       ├── 110-xiaomi-ultrathin-magnetic-power-bank-5000-15w-principal.jpg
│   │       │       ├── xiaomi-cable-usb-a-usb-c-6a-detalle-conectores.jpg
│   │       │       ├── xiaomi-cable-usb-a-usb-c-6a-en-uso.jpg
│   │       │       ├── xiaomi-power-bank-33w-20000mah-colores.jpg
│   │       │       ├── xiaomi-power-bank-33w-20000mah-detalle-puertos.jpg
│   │       │       ├── xiaomi-power-bank-33w-20000mah-en-uso.jpg
│   │       │       └── xiaomi-power-bank-33w-20000mah-frontal.jpg
│   │       ├── Audio/
│   │       │   ├── Apple/
│   │       │   │   ├── 003-apple-iphone-17-clear-case-with-magsafe-principal.jpg
│   │       │   │   ├── 009-apple-airpods-5-principal.jpg
│   │       │   │   ├── 010-apple-airpods-pro-3-principal.jpg
│   │       │   │   ├── 011-apple-airpods-max-2-principal.jpg
│   │       │   │   ├── AirPods5-2026-Apple-0.jpg
│   │       │   │   ├── AirPods5-2026-Apple-1.jpg
│   │       │   │   ├── AirPods5-2026-Apple-2.jpg
│   │       │   │   ├── AirPods5-2026-Apple-3.jpg
│   │       │   │   ├── AirPodsMax2-2026-Apple-0.jpg
│   │       │   │   ├── AirPodsMax2-2026-Apple-1.jpg
│   │       │   │   ├── AirPodsMax2-2026-Apple-2.jpg
│   │       │   │   ├── AirPodsMax2-2026-Apple-3.jpg
│   │       │   │   ├── AirPodsMax2-2026-Apple-4.jpg
│   │       │   │   ├── AirPodsPro3-2025-Apple-0.jpg
│   │       │   │   ├── AirPodsPro3-2025-Apple-1.jpg
│   │       │   │   ├── AirPodsPro3-2025-Apple-2.jpg
│   │       │   │   ├── apple-funda-iphone-16-clear-magsafe-frontal.jpg
│   │       │   │   └── apple-funda-iphone-16-clear-magsafe-trasera.jpg
│   │       │   ├── Honor/
│   │       │   │   ├── 019-honor-earbuds-4-principal.jpg
│   │       │   │   ├── 020-honor-earbuds-5e-principal.jpg
│   │       │   │   ├── 021-honor-earbuds-a-pro-principal.jpg
│   │       │   │   ├── 022-honor-earbuds-open-principal.jpg
│   │       │   │   ├── 023-honor-earbuds-x10-lite-principal.jpg
│   │       │   │   ├── honor-choice-earbuds-x-detalle-auricular.jpg
│   │       │   │   ├── honor-choice-earbuds-x-estuche.jpg
│   │       │   │   ├── honor-choice-earbuds-x-frontal.jpg
│   │       │   │   ├── HonorEarbuds4-Honor-2026-0.jpg
│   │       │   │   ├── HonorEarbuds4-Honor-2026-1.jpg
│   │       │   │   ├── HonorEarbuds4-Honor-2026-2.jpg
│   │       │   │   ├── HonorEarbuds4-Honor-2026-3.jpg
│   │       │   │   ├── HonorEarbuds4-Honor-2026-4.jpg
│   │       │   │   ├── honorEarBuds5e-Honor-2026-0.webp
│   │       │   │   ├── honorEarBuds5e-Honor-2026-1.webp
│   │       │   │   ├── honorEarBuds5e-Honor-2026-2.webp
│   │       │   │   ├── honorEarBuds5e-Honor-2026-3.webp
│   │       │   │   ├── HonorEarbudsAPro-Honor-2026-0.jpg
│   │       │   │   ├── HonorEarbudsAPro-Honor-2026-1.jpg
│   │       │   │   ├── HonorEarbudsAPro-Honor-2026-2.jpg
│   │       │   │   ├── HonorEarbudsAPro-Honor-2026-3.jpg
│   │       │   │   ├── HonorEarbudsOpen-Honor-2026-0.jpg
│   │       │   │   ├── HonorEarbudsOpen-Honor-2026-1.jpg
│   │       │   │   ├── HonorEarbudsOpen-Honor-2026-2.jpg
│   │       │   │   ├── HonorEarbudsX10Lite-Honor-2026-0.jpg
│   │       │   │   ├── HonorEarbudsX10Lite-Honor-2026-1.jpg
│   │       │   │   ├── HonorEarbudsX10Lite-Honor-2026-2.jpg
│   │       │   │   └── HonorEarbudsX10Lite-Honor-2026-3.jpg
│   │       │   ├── JBL/
│   │       │   │   ├── 039-jbl-live-680nc-plegado.jpg
│   │       │   │   ├── 039-jbl-live-680nc-principal.jpg
│   │       │   │   ├── 040-jbl-live-780nc-plegado.jpg
│   │       │   │   ├── 040-jbl-live-780nc-principal.jpg
│   │       │   │   ├── 041-jbl-tour-one-m3-principal.jpg
│   │       │   │   ├── 042-jbl-clip-5-principal.jpg
│   │       │   │   ├── 043-jbl-go-5-principal.jpg
│   │       │   │   ├── 044-jbl-charge-6-principal.jpg
│   │       │   │   ├── 045-jbl-flip-7-principal.jpg
│   │       │   │   ├── 046-jbl-grip-principal.jpg
│   │       │   │   ├── beats-cable-usb-c-3m-azul-detalle-conectores.jpg
│   │       │   │   ├── beats-cable-usb-c-3m-azul-en-uso.jpg
│   │       │   │   ├── beats-cable-usb-c-3m-azul-frontal.jpg
│   │       │   │   ├── beats-solo-4-rosa-frontal.jpg
│   │       │   │   ├── beats-solo-4-rosa-lateral.jpg
│   │       │   │   ├── beats-solo-4-rosa-plegado.jpg
│   │       │   │   ├── beats-solo-4-rosa-sobre-mesa.jpg
│   │       │   │   ├── beats-studio-pro-arena-con-estuche.jpg
│   │       │   │   ├── beats-studio-pro-arena-detalle-controles.jpg
│   │       │   │   ├── beats-studio-pro-arena-frontal.jpg
│   │       │   │   ├── beats-studio-pro-arena-perspectiva-trasera.jpg
│   │       │   │   ├── beats-studio-pro-arena-plegado.jpg
│   │       │   │   ├── Charge6-JBL-2025-0.jpg
│   │       │   │   ├── Charge6-JBL-2025-1.jpg
│   │       │   │   ├── Charge6-JBL-2025-2.jpg
│   │       │   │   ├── Charge6-JBL-2025-3.jpg
│   │       │   │   ├── Charge6-JBL-2025-4.jpg
│   │       │   │   ├── Charge6-JBL-2025-5.jpg
│   │       │   │   ├── Clip5-JBL-2024-0.jpg
│   │       │   │   ├── Clip5-JBL-2024-1.jpg
│   │       │   │   ├── Clip5-JBL-2024-2.jpg
│   │       │   │   ├── Clip5-JBL-2024-3.jpg
│   │       │   │   ├── Flip7-JBL-2025-0.jpg
│   │       │   │   ├── Flip7-JBL-2025-1.jpg
│   │       │   │   ├── Flip7-JBL-2025-2.jpg
│   │       │   │   ├── Flip7-JBL-2025-3.jpg
│   │       │   │   ├── Flip7-JBL-2025-4.jpg
│   │       │   │   ├── Go5-JBL-2026-0.jpg
│   │       │   │   ├── Go5-JBL-2026-1.jpg
│   │       │   │   ├── Go5-JBL-2026-2.jpg
│   │       │   │   ├── Go5-JBL-2026-3.jpg
│   │       │   │   ├── Grip-JBL-2025-0.jpg
│   │       │   │   ├── Grip-JBL-2025-1.jpg
│   │       │   │   ├── Grip-JBL-2025-2.jpg
│   │       │   │   ├── Grip-JBL-2025-3.jpg
│   │       │   │   ├── jbl-tune-520bt-blanco-detalle-almohadillas.jpg
│   │       │   │   ├── jbl-tune-520bt-blanco-frontal.jpg
│   │       │   │   ├── jbl-tune-520bt-blanco-lateral.jpg
│   │       │   │   ├── jbl-tune-520bt-blanco-perspectiva.jpg
│   │       │   │   ├── jbl-tune-520bt-blanco-plegado.jpg
│   │       │   │   ├── Live680NC-JBL-2026-0.jpg
│   │       │   │   ├── Live680NC-JBL-2026-1.jpg
│   │       │   │   ├── Live680NC-JBL-2026-2.jpg
│   │       │   │   ├── Live680NC-JBL-2026-3.jpg
│   │       │   │   ├── Live680NC-JBL-2026-4.jpg
│   │       │   │   ├── Live780NC-JBL-2026-0.jpg
│   │       │   │   ├── Live780NC-JBL-2026-1.jpg
│   │       │   │   ├── Live780NC-JBL-2026-2.jpg
│   │       │   │   ├── TourONEM3-JBL-2025-0.jpg
│   │       │   │   ├── TourONEM3-JBL-2025-1.jpg
│   │       │   │   └── TourONEM3-JBL-2025-2.jpg
│   │       │   ├── Oppo/
│   │       │   │   ├── 062-oppo-portable-wireless-speaker-principal.jpg
│   │       │   │   ├── oppo-enco-buds2-pro-colores.jpg
│   │       │   │   ├── oppo-enco-buds2-pro-detalle-auricular.jpg
│   │       │   │   ├── oppo-enco-buds2-pro-estuche.jpg
│   │       │   │   ├── oppo-enco-buds2-pro-frontal.jpg
│   │       │   │   ├── Speaker-OPPO-2025-0.jpg
│   │       │   │   └── Speaker-OPPO-2025-1.jpg
│   │       │   ├── Samsung/
│   │       │   │   ├── 083-samsung-galaxy-buds3-fe-principal.jpg
│   │       │   │   ├── 084-samsung-galaxy-buds3-pro-principal.jpg
│   │       │   │   ├── 085-samsung-galaxy-buds4-posterior.jpg
│   │       │   │   ├── 085-samsung-galaxy-buds4-principal.jpg
│   │       │   │   ├── 086-samsung-galaxy-buds4-pro-posterior.jpg
│   │       │   │   ├── 086-samsung-galaxy-buds4-pro-principal.jpg
│   │       │   │   ├── Buds3FE-SAMSUNG-2025-0.jpg
│   │       │   │   ├── Buds3FE-SAMSUNG-2025-1.jpg
│   │       │   │   ├── Buds3FE-SAMSUNG-2025-2.jpg
│   │       │   │   ├── Buds3Pro-SAMSUNG-2024-0.jpg
│   │       │   │   ├── Buds3Pro-SAMSUNG-2024-1.jpg
│   │       │   │   ├── Buds3Pro-SAMSUNG-2024-2.jpg
│   │       │   │   ├── Buds4-SAMSUNG-2026-0.jpg
│   │       │   │   ├── Buds4-SAMSUNG-2026-1.jpg
│   │       │   │   ├── Buds4-SAMSUNG-2026-2.jpg
│   │       │   │   ├── Buds4Pro-SAMSUNG-2026-0.jpg
│   │       │   │   └── Buds4Pro-SAMSUNG-2026-1.jpg
│   │       │   └── Xiaomi/
│   │       │       ├── 111-xiaomi-redmi-buds-8-principal.jpg
│   │       │       ├── 112-xiaomi-redmi-buds-8-active-principal.jpg
│   │       │       ├── 113-xiaomi-redmi-buds-8-lite-principal.jpg
│   │       │       ├── 114-xiaomi-redmi-buds-8-pro-principal.jpg
│   │       │       ├── 115-xiaomi-buds-5-pro-principal.jpg
│   │       │       ├── 116-xiaomi-buds-6-principal.jpg
│   │       │       ├── 117-xiaomi-openwear-stereo-pro-principal.jpg
│   │       │       ├── 118-xiaomi-redmi-headphones-neo-principal.jpg
│   │       │       ├── Buds5Pro-REDMI-2025-0.jpg
│   │       │       ├── Buds5Pro-REDMI-2025-1.jpg
│   │       │       ├── Buds6-REDMI-2026-0.jpg
│   │       │       ├── Buds6-REDMI-2026-1.jpg
│   │       │       ├── Buds6-REDMI-2026-2.jpg
│   │       │       ├── Buds8-REDMI-2026-0.jpg
│   │       │       ├── Buds8-REDMI-2026-1.jpg
│   │       │       ├── Buds8-REDMI-2026-2.jpg
│   │       │       ├── Buds8Active-REDMI-2026-0.jpg
│   │       │       ├── Buds8Active-REDMI-2026-1.jpg
│   │       │       ├── Buds8Active-REDMI-2026-2.jpg
│   │       │       ├── Buds8Active-REDMI-2026-3.jpg
│   │       │       ├── Buds8Lite-REDMI-2026-0.jpg
│   │       │       ├── Buds8Lite-REDMI-2026-1.jpg
│   │       │       ├── Buds8Lite-REDMI-2026-2.jpg
│   │       │       ├── Buds8Pro-REDMI-2026-0.jpg
│   │       │       ├── Buds8Pro-REDMI-2026-1.jpg
│   │       │       ├── HeadphonesNeo-REDMI-2026-0.jpg
│   │       │       ├── HeadphonesNeo-REDMI-2026-1.jpg
│   │       │       ├── HeadphonesNeo-REDMI-2026-2.jpg
│   │       │       ├── jbl-go-4-militar-detalle-conector.jpg
│   │       │       ├── jbl-go-4-militar-detalle-controles.jpg
│   │       │       ├── jbl-go-4-militar-empaque.jpg
│   │       │       ├── jbl-go-4-militar-frontal.jpg
│   │       │       ├── jbl-go-4-militar-perspectiva.jpg
│   │       │       ├── jbl-go-4-militar-trasera.jpg
│   │       │       ├── OpenWearStereoPro-REDMI-2025-0.jpg
│   │       │       └── OpenWearStereoPro-REDMI-2025-1.jpg
│   │       └── Celulares/
│   │           ├── Apple/
│   │           │   ├── 012-apple-iphone-17-principal.jpg
│   │           │   ├── 013-apple-iphone-17-pro-principal.jpg
│   │           │   ├── 014-apple-iphone-17-pro-max-principal.jpg
│   │           │   ├── 015-apple-iphone-17e-principal.jpg
│   │           │   ├── 016-apple-iphone-18-pro-principal.jpg
│   │           │   ├── 017-apple-iphone-18-pro-max-principal.jpg
│   │           │   ├── 018-apple-iphone-air-principal.jpg
│   │           │   ├── oppo-palo-selfie-magnetico-3-en-1-extendido.jpg
│   │           │   ├── oppo-palo-selfie-magnetico-3-en-1-frontal.jpg
│   │           │   ├── oppo-palo-selfie-magnetico-3-en-1-modo-tripode.jpg
│   │           │   ├── oppo-palo-selfie-magnetico-3-en-1-perspectiva.jpg
│   │           │   ├── oppo-tarjeta-enfriamiento-magnetica-frontal.jpg
│   │           │   ├── oppo-tarjeta-enfriamiento-magnetica-lateral.jpg
│   │           │   ├── oppo-tarjeta-enfriamiento-magnetica-perspectiva.jpg
│   │           │   ├── oppo-tarjeta-enfriamiento-magnetica-trasera.jpg
│   │           │   ├── samsung-galaxy-smarttag2-negro-frontal.jpg
│   │           │   ├── samsung-galaxy-smarttag2-negro-lateral.jpg
│   │           │   ├── samsung-galaxy-smarttag2-negro-perspectiva.jpg
│   │           │   └── samsung-galaxy-smarttag2-negro-trasera.jpg
│   │           ├── Honor/
│   │           │   ├── 024-honor-200-pro-posterior.jpg
│   │           │   ├── 024-honor-200-pro-principal.jpg
│   │           │   ├── 025-honor-400-principal.jpg
│   │           │   ├── 026-honor-400-lite-principal.jpg
│   │           │   ├── 027-honor-400-pro-principal.jpg
│   │           │   ├── 028-honor-600-posterior.jpg
│   │           │   ├── 028-honor-600-principal.jpg
│   │           │   ├── 029-honor-600-lite-posterior.jpg
│   │           │   ├── 029-honor-600-lite-principal.jpg
│   │           │   ├── 030-honor-600-pro-posterior.jpg
│   │           │   ├── 030-honor-600-pro-principal.jpg
│   │           │   ├── 031-honor-600-smart-5g-posterior.jpg
│   │           │   ├── 031-honor-600-smart-5g-principal.jpg
│   │           │   ├── 032-honor-magic-v5-principal.jpg
│   │           │   ├── 033-honor-magic-v6-principal.jpg
│   │           │   ├── 034-honor-magic7-pro-posterior.jpg
│   │           │   ├── 034-honor-magic7-pro-principal.jpg
│   │           │   ├── 035-honor-porsche-design-honor-magic7-rsr-posterior.jpg
│   │           │   ├── 035-honor-porsche-design-honor-magic7-rsr-principal.jpg
│   │           │   ├── 036-honor-magic8-pro-principal.jpg
│   │           │   ├── 037-honor-magic9-pro-max-posterior.jpg
│   │           │   ├── 037-honor-magic9-pro-max-principal.jpg
│   │           │   ├── 038-honor-robot-phone-principal.jpg
│   │           │   ├── honor-supercharge-66w-perspectiva.jpg
│   │           │   └── honor-supercharge-wireless-100w-frontal.jpg
│   │           ├── Oppo/
│   │           │   ├── 060-oppo-enco-clip2-principal.jpg
│   │           │   ├── 061-oppo-enco-x3s-principal.jpg
│   │           │   ├── 063-oppo-find-n5-principal.jpg
│   │           │   ├── 064-oppo-find-x9-principal.jpg
│   │           │   ├── 065-oppo-find-x9-pro-principal.jpg
│   │           │   ├── 066-oppo-reno13-5g-principal.jpg
│   │           │   ├── 067-oppo-reno13-f-principal.jpg
│   │           │   ├── 068-oppo-reno13-f-5g-principal.jpg
│   │           │   ├── 069-oppo-reno13-pro-5g-principal.jpg
│   │           │   ├── 070-oppo-reno14-5g-principal.jpg
│   │           │   ├── 071-oppo-reno14-f-5g-principal.jpg
│   │           │   ├── 072-oppo-reno14-pro-5g-principal.jpg
│   │           │   ├── 073-oppo-reno15-5g-principal.jpg
│   │           │   ├── 074-oppo-reno15-f-5g-principal.jpg
│   │           │   ├── 075-oppo-reno15-pro-5g-principal.jpg
│   │           │   ├── 076-oppo-reno15-pro-max-5g-principal.jpg
│   │           │   ├── 077-oppo-reno16-principal.jpg
│   │           │   ├── 078-oppo-reno16-f-principal.jpg
│   │           │   ├── 079-oppo-reno16-pro-principal.jpg
│   │           │   ├── EncoClip2-OPPO-2026-0.jpg
│   │           │   ├── EncoClip2-OPPO-2026-1.jpg
│   │           │   ├── EncoClip2-OPPO-2026-2.jpg
│   │           │   ├── EncoClip2-OPPO-2026-3.jpg
│   │           │   ├── EncoX3s-OPPO-2025-0.jpg
│   │           │   ├── EncoX3s-OPPO-2025-1.jpg
│   │           │   ├── EncoX3s-OPPO-2025-2.jpg
│   │           │   ├── EncoX3s-OPPO-2025-3.jpg
│   │           │   ├── oppo-supervooc-100w-dual-detalle-puertos.jpg
│   │           │   ├── oppo-supervooc-100w-dual-frontal.jpg
│   │           │   ├── oppo-supervooc-100w-dual-lateral.jpg
│   │           │   └── oppo-supervooc-100w-dual-perspectiva.jpg
│   │           ├── samsung/
│   │           │   ├── samsung-galaxy-a27-5g-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-a27-5g-frontal.jpg
│   │           │   ├── samsung-galaxy-a27-5g-lateral.jpg
│   │           │   ├── samsung-galaxy-a27-5g-trasera.jpg
│   │           │   ├── samsung-galaxy-a37-5g-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-a37-5g-frontal.jpg
│   │           │   ├── samsung-galaxy-a37-5g-lateral.jpg
│   │           │   ├── samsung-galaxy-a37-5g-trasera.jpg
│   │           │   ├── samsung-galaxy-a57-5g-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-a57-5g-frontal.jpg
│   │           │   ├── samsung-galaxy-a57-5g-lateral.jpg
│   │           │   ├── samsung-galaxy-a57-5g-trasera.jpg
│   │           │   ├── samsung-galaxy-s25-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-s25-fe-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-s25-fe-frontal.jpg
│   │           │   ├── samsung-galaxy-s25-fe-lateral.jpg
│   │           │   ├── samsung-galaxy-s25-fe-trasera.jpg
│   │           │   ├── samsung-galaxy-s25-frontal.jpg
│   │           │   ├── samsung-galaxy-s25-lateral.jpg
│   │           │   ├── samsung-galaxy-s25-trasera.jpg
│   │           │   ├── samsung-galaxy-s25-ultra-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-s25-ultra-frontal.jpg
│   │           │   ├── samsung-galaxy-s25-ultra-lateral.jpg
│   │           │   ├── samsung-galaxy-s25-ultra-trasera.jpg
│   │           │   ├── samsung-galaxy-s26-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-s26-fe-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-s26-fe-frontal.jpg
│   │           │   ├── samsung-galaxy-s26-fe-lateral.jpg
│   │           │   ├── samsung-galaxy-s26-fe-trasera.jpg
│   │           │   ├── samsung-galaxy-s26-frontal.jpg
│   │           │   ├── samsung-galaxy-s26-lateral.jpg
│   │           │   ├── samsung-galaxy-s26-plus-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-s26-plus-frontal.jpg
│   │           │   ├── samsung-galaxy-s26-plus-lateral.jpg
│   │           │   ├── samsung-galaxy-s26-plus-trasera.jpg
│   │           │   ├── samsung-galaxy-s26-trasera.jpg
│   │           │   ├── samsung-galaxy-s26-ultra-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-s26-ultra-frontal.jpg
│   │           │   ├── samsung-galaxy-s26-ultra-lateral.jpg
│   │           │   ├── samsung-galaxy-s26-ultra-trasera.jpg
│   │           │   ├── samsung-galaxy-z-flip8-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-z-flip8-frontal.jpg
│   │           │   ├── samsung-galaxy-z-flip8-lateral.jpg
│   │           │   ├── samsung-galaxy-z-flip8-trasera.jpg
│   │           │   ├── samsung-galaxy-z-fold8-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-z-fold8-frontal.jpg
│   │           │   ├── samsung-galaxy-z-fold8-lateral.jpg
│   │           │   ├── samsung-galaxy-z-fold8-trasera.jpg
│   │           │   ├── samsung-galaxy-z-fold8-ultra-detalle-camara.jpg
│   │           │   ├── samsung-galaxy-z-fold8-ultra-frontal.jpg
│   │           │   ├── samsung-galaxy-z-fold8-ultra-lateral.jpg
│   │           │   └── samsung-galaxy-z-fold8-ultra-trasera.jpg
│   │           └── Xiaomi/
│   │               ├── 108-xiaomi-120w-hypercharge-combo-type-a-principal.jpg
│   │               ├── 119-xiaomi-poco-f7-principal.jpg
│   │               ├── 120-xiaomi-poco-f7-pro-principal.jpg
│   │               ├── 121-xiaomi-poco-f7-ultra-principal.jpg
│   │               ├── 122-xiaomi-poco-x7-principal.jpg
│   │               ├── 123-xiaomi-poco-x7-pro-principal.jpg
│   │               ├── 124-xiaomi-poco-c71-principal.jpg
│   │               ├── 125-xiaomi-poco-c81-pro-principal.jpg
│   │               ├── 126-xiaomi-poco-c85-principal.jpg
│   │               ├── 127-xiaomi-poco-f8-pro-principal.jpg
│   │               ├── 128-xiaomi-poco-f8-ultra-principal.jpg
│   │               ├── 129-xiaomi-poco-f9-pro-principal.jpg
│   │               ├── 130-xiaomi-poco-f9-ultra-principal.jpg
│   │               ├── 131-xiaomi-poco-m7-pro-5g-principal.jpg
│   │               ├── 132-xiaomi-poco-m8-5g-principal.jpg
│   │               ├── 133-xiaomi-poco-m8-pro-5g-principal.jpg
│   │               ├── 134-xiaomi-poco-m8s-5g-principal.jpg
│   │               ├── 135-xiaomi-poco-x8-pro-principal.jpg
│   │               ├── 136-xiaomi-poco-x8-pro-max-principal.jpg
│   │               ├── 137-xiaomi-redmi-17-principal.jpg
│   │               ├── 138-xiaomi-redmi-a5-principal.jpg
│   │               ├── 139-xiaomi-redmi-a7-pro-principal.jpg
│   │               ├── 140-xiaomi-redmi-15c-principal.jpg
│   │               ├── 141-xiaomi-redmi-note-14-principal.jpg
│   │               ├── 142-xiaomi-redmi-note-14-5g-principal.jpg
│   │               ├── 143-xiaomi-redmi-note-14-pro-principal.jpg
│   │               ├── 144-xiaomi-redmi-note-14-pro-5g-principal.jpg
│   │               ├── 145-xiaomi-redmi-note-14-pro-plus-5g-principal.jpg
│   │               ├── 146-xiaomi-redmi-note-15-principal.jpg
│   │               ├── 147-xiaomi-redmi-note-15-5g-principal.jpg
│   │               ├── 148-xiaomi-redmi-note-15-pro-principal.jpg
│   │               ├── 149-xiaomi-redmi-note-15-pro-5g-principal.jpg
│   │               ├── 150-xiaomi-redmi-note-15-pro-plus-5g-principal.jpg
│   │               ├── 151-xiaomi-14-ultra-principal.jpg
│   │               ├── 152-xiaomi-15-principal.jpg
│   │               ├── 153-xiaomi-15-ultra-principal.jpg
│   │               ├── 154-xiaomi-15t-principal.jpg
│   │               ├── 155-xiaomi-15t-pro-principal.jpg
│   │               ├── 156-xiaomi-17-principal.jpg
│   │               ├── 157-xiaomi-17-ultra-principal.jpg
│   │               └── xiaomi-hypercharge-67w-frontal.jpg
│   └── vendor/
│       └── bootstrap-icons/
│           ├── bootstrap-icons.min.css
│           └── fonts/
│               ├── bootstrap-icons.woff
│               └── bootstrap-icons.woff2
├── favicon_technology_cell.ico
├── index.php
└── robots.txt
```

## 8. Árbol de archivos personalizados de `config/`

```text
config/
├── app.php
├── auth.php
├── cache.php
├── database.php
├── filesystems.php
├── gestion/
│   ├── administracion/
│   │   ├── integraciones.php
│   │   ├── notificaciones.php
│   │   ├── respaldos-mantenimiento.php
│   │   └── roles-permisos.php
│   ├── analitica/
│   │   ├── clientes.php
│   │   ├── metricas-marketing.php
│   │   └── reportes-financieros.php
│   ├── configuracion/
│   │   ├── comercial.php
│   │   ├── empresa.php
│   │   ├── general.php
│   │   └── seguridad.php
│   ├── finanzas/
│   │   ├── facturacion.php
│   │   ├── pagos.php
│   │   └── reembolsos.php
│   ├── finanzas-facturacion.php
│   ├── inventario/
│   │   ├── devoluciones-garantias.php
│   │   └── envios-entregas.php
│   └── soporte/
│       └── reclamos.php
├── logging.php
├── mail.php
├── queue.php
├── services.php
└── session.php
```

## 9. Árbol completo de `tests/`

```text
tests/
├── Feature/
│   ├── Api/
│   │   └── CatalogoControllerTest.php
│   ├── CarritoEcommerceAuthorizationTest.php
│   ├── CellSacCommerceTest.php
│   ├── CompraEdicionTest.php
│   ├── ConsultasFinancierasTest.php
│   ├── CuentaPerfilDinamicoTest.php
│   ├── CuentasFinancierasTest.php
│   ├── EcommercePersistenciaTest.php
│   ├── Gestion/
│   │   ├── AlmacenControllerTest.php
│   │   ├── EmpresaControllerTest.php
│   │   ├── SedeControllerTest.php
│   │   └── ViewsTest.php
│   ├── HabilitarSubmenusOrganizacionTest.php
│   ├── IntegracionVentasComprasMariaAisladaTest.php
│   ├── MigrationCoverageTest.php
│   ├── Organizacion/
│   │   └── ResponsablesOrganizacionalesTest.php
│   ├── Paneles/
│   │   ├── Administrador/
│   │   │   ├── AlertasGlobalesControllerTest.php
│   │   │   ├── DispositivosControllerTest.php
│   │   │   └── EnviosPedidosControllerTest.php
│   │   └── DashboardControllerTest.php
│   ├── PersonalSedeControllerTest.php
│   ├── Publico/
│   │   └── Comercio/
│   │       └── PedidoControllerTest.php
│   ├── RbacDinamicoTest.php
│   ├── RbacMatrizGraficaTest.php
│   ├── SeguridadDelegacionRecorridoTest.php
│   ├── ServicioConsultasCuentasTest.php
│   └── UnidadII/
│       └── ModuloAndSecurityIntegrationTest.php
├── IsolatedMariaDatabase.php
├── Pest.php
├── RefreshSpanishTestDatabase.php
├── SpanishAccessFixture.php
├── TestCase.php
└── Unit/
    ├── ApiContractTest.php
    ├── ArquitecturaMvcTest.php
    ├── CajasOperativasTest.php
    ├── ConsultasFinancierasQueryTest.php
    ├── DashboardGlobalExportTest.php
    ├── DashboardGlobalPedidosTest.php
    ├── DashboardGlobalSedesTest.php
    ├── EsquemaEspanolRbacTest.php
    ├── ExportadorTabularTest.php
    ├── Gestion/
    │   ├── Cupones/
    │   │   └── ServicioCuponesTest.php
    │   ├── Inventario/
    │   │   └── ServicioTransferenciasExistenciasTest.php
    │   ├── Productos/
    │   │   ├── GestionProductosControllerTest.php
    │   │   └── ServicioGestionProductosTest.php
    │   └── Ventas/
    │       └── FiltroVentaRequestTest.php
    ├── InicializadorMariaAisladaTest.php
    ├── IntegracionMariaDbTechnologyCellTest.php
    ├── IntegracionTransferenciasMariaDbAisladaTest.php
    ├── MariaFinanzasAisladaTest.php
    ├── MariaMigracionesReentradaAisladaTest.php
    ├── MariaPedidosTransferenciasAisladaTest.php
    ├── PdoProductoDaoMariaAisladaTest.php
    ├── PdoProductoDaoTest.php
    ├── RbacAuthorizationTest.php
    ├── RbacRutaCoberturaTest.php
    ├── SeguridadEstructuralTest.php
    ├── SeguridadFrontendTest.php
    └── ServicioCatalogoPdoTest.php
```

## 10. Otros archivos y carpetas relevantes

```text
.claude/skills/deploying-to-cloud/reference/checklists.md
.claude/skills/deploying-to-cloud/SKILL.md
.claude/skills/infer-conventions/references/checklist.md
.claude/skills/infer-conventions/SKILL.md
.claude/skills/laravel-best-practices/rules/advanced-queries.md
.claude/skills/laravel-best-practices/rules/architecture.md
.claude/skills/laravel-best-practices/rules/blade-views.md
.claude/skills/laravel-best-practices/rules/caching.md
.claude/skills/laravel-best-practices/rules/collections.md
.claude/skills/laravel-best-practices/rules/config.md
.claude/skills/laravel-best-practices/rules/db-performance.md
.claude/skills/laravel-best-practices/rules/eloquent.md
.claude/skills/laravel-best-practices/rules/error-handling.md
.claude/skills/laravel-best-practices/rules/events-notifications.md
.claude/skills/laravel-best-practices/rules/http-client.md
.claude/skills/laravel-best-practices/rules/mail.md
.claude/skills/laravel-best-practices/rules/migrations.md
.claude/skills/laravel-best-practices/rules/queue-jobs.md
.claude/skills/laravel-best-practices/rules/routing.md
.claude/skills/laravel-best-practices/rules/scheduling.md
.claude/skills/laravel-best-practices/rules/security.md
.claude/skills/laravel-best-practices/rules/style.md
.claude/skills/laravel-best-practices/rules/validation.md
.claude/skills/laravel-best-practices/SKILL.md
.claude/skills/tailwindcss-development/SKILL.md
.claude/skills/testing-best-practices/rules/assertions.md
.claude/skills/testing-best-practices/rules/endpoint-tests.md
.claude/skills/testing-best-practices/rules/finding-features.md
.claude/skills/testing-best-practices/rules/isolation.md
.claude/skills/testing-best-practices/rules/naming.md
.claude/skills/testing-best-practices/rules/performance.md
.claude/skills/testing-best-practices/rules/review.md
.claude/skills/testing-best-practices/rules/security.md
.claude/skills/testing-best-practices/rules/test-data.md
.claude/skills/testing-best-practices/SKILL.md
.editorconfig
.env.example
.gitattributes
.gitignore
.mcp.json
.npmrc
AGENTS.md
artisan
boost.json
bootstrap/app.php
bootstrap/providers.php
CLAUDE.md
composer.json
composer.lock
docs/DAW2-UND2 - Guia para elaborar Trabajo de Unidad 2 (2).pdf
docs/rbac-diagnostico-y-diccionario.md
docs/rbac-relaciones.md
md_tecnologia_digital_cell.sql
opencode.json
package.json
package-lock.json
phpunit.xml
query
README.md
vite.config.js
```

## Inventario resumido

| Carpeta principal | Subcarpetas | Archivos | Tipos | Función general |
|---|---:|---:|---|---|
| `app/` | 71 | 296 | .php | Código de aplicación: controladores, modelos, dominio, infraestructura, requests, middleware, policies, servicios y providers. |
| `resources/views/` | 96 | 179 | .php | Vistas Blade, layouts, componentes y pantallas públicas y administrativas. |
| `resources/css/` | 63 | 100 | .css | Hojas de estilo propias por layouts, módulos y áreas funcionales. |
| `resources/js/` | 65 | 86 | .js | JavaScript propio para layouts, módulos, formularios y comportamiento interactivo. |
| `routes/` | 1 | 3 | .php | Definición de rutas web, API y consola. |
| `database/` | 10 | 73 | .php, .sql, [sin extensión] | Migraciones, seeders y factories del modelo de datos. |
| `public/` | 52 | 938 | .css, .ico, .jpeg, .jpg, .php, .png, .txt, .webp, .woff, .woff2, [sin extensión] | Recursos estáticos servidos públicamente. |
| `config/` | 8 | 28 | .php | Configuración de Laravel y configuraciones funcionales de Technology Cell. |
| `tests/` | 16 | 59 | .php | Pruebas Pest/PHPUnit, fixtures y utilidades de bases aisladas. |
| Otros archivos/directorios | 13 | 60 | .example, .js, .json, .lock, .md, .pdf, .php, .sql, .xml, [sin extensión] | Configuración raíz, documentación, bootstrap y metadatos del proyecto. |

**Total inventariado en el alcance:** 1822 archivos y 395 carpetas.

Los conteos consideran carpetas que contienen al menos un archivo incluido. `composer.json`, `package.json`, `vite.config.js` y `phpunit.xml` son configuración/tooling del proyecto, no módulos funcionales.
