# Guía de funciones y métodos

Esta guía se genera a partir del código ejecutable propio del proyecto. Describe las funciones nombradas y los métodos encontrados en app/, routes/, database/, tests/ y resources/js/; no incluye dependencias externas ni bloques Blade que solo renderizan la interfaz.

> Nota de auditoría: las fichas de los recorridos prioritarios que aparecen al
> final de este documento fueron revisadas contra el cuerpo actual de cada
> método. En caso de discrepancia, prevalecen el PHPDoc/JSDoc del archivo fuente
> y su firma real. No se modificó código ejecutable para generar esta guía.

## Fichas verificadas contra el cuerpo actual

Estas fichas complementan el inventario y describen los recorridos de mayor riesgo
con información comprobada en el código fuente vigente.

### Carrito, checkout y DAO

- **`ServicioCarrito::normalizar`** (`app/Servicios/ServicioCarrito.php`): recibe las líneas de sesión, acepta cantidad simple o arreglo con variante, descarta identificadores/cantidades no enteros positivos y devuelve `array<int, array{cantidad:int, variante_id:?int}>`; no escribe en sesión ni base de datos.
- **`CarritoController::index/add/update`** (`app/Http/Controllers/Publico/Comercio/CarritoController.php`): normaliza la sesión, carga productos y variantes activas, valida pertenencia de variante y stock, acumula o elimina líneas y prepara la vista/redirección correspondiente.
- **`FinalizarCompraController::index/store`** (`app/Http/Controllers/Publico/Comercio/FinalizarCompraController.php`): prepara token, disponibilidad y subtotales del checkout; al confirmar rechaza carrito vacío, delega el registro transaccional al servicio de pedidos y limpia la sesión después de crear el pedido.
- **`PdoConexion::ejecutarEnTransaccion`** (`app/Infraestructura/Persistencia/Pdo/PdoConexion.php`): inicia transacción solo si no existe una externa, ejecuta el callback, confirma con `commit` y revierte con `rollback` ante `Throwable`.
- **`PdoProductoDao::buscarPorId/crear/actualizar/crearVariante/actualizarVariante`** (`app/Infraestructura/Persistencia/Pdo/PdoProductoDao.php`): ejecuta consultas preparadas, mapea filas a entidades, valida datos de producto/variante y usa el wrapper transaccional para las escrituras; devuelve entidades o `null` según la operación.

### Negocio, inventario y RBAC

- **`ServicioVentas::registrarFisica/anular`** (`app/Servicios/ServicioVentas.php`): valida e idempotiza ventas, bloquea existencias, crea detalles, actualiza inventario y audita; la anulación verifica estado, repone existencias y registra el cambio dentro de la transacción existente.
- **`ServicioInventario::disponibilidadParaOperacion/registerNewProduct/adjust`** (`app/Servicios/ServicioInventario.php`): restringe almacenes y productos activos, calcula disponibilidad, registra stock inicial cero y ajusta cantidades bajo autorización, bloqueo, transacción y auditoría.
- **`ServicioAutorizacion::allows/assertAllowed`** y los middleware **`AsegurarRol::handle` / `VerificarPermiso::handle`**: resuelven roles, asignaciones, módulos y pantallas activas; autorizan o interrumpen con HTTP 403 y dejan el contexto RBAC autorizado en la solicitud.

Estas fichas se mantienen deliberadamente separadas del inventario automático: la
fuente PHPDoc/JSDoc es la referencia de tipos y excepciones, y no se inventan
relaciones que no estén presentes en el cuerpo del método.

## Resultado de la auditoría de limpieza

- No se eliminó ningún archivo: no se encontró un candidato cuya ausencia pudiera
  demostrarse sin riesgo para rutas, autoload, migraciones, recursos empresariales o pruebas.
- `changeLockedProduct` y `changeLockedProductInWarehouse` no son duplicados seguros
  para eliminar: el primero resuelve el almacén de la operación y el segundo recibe
  explícitamente empresa, sede y almacén para transferencias y movimientos dirigidos.
- `PdoProductoDao` y los modelos Eloquent cumplen capas distintas; el DAO se conserva
  porque las pruebas PDO aisladas lo ejercitan directamente.
- Las pruebas MariaDB aisladas se conservan aunque estén omitidas sin la instancia
  descartable autorizada; contienen comprobaciones de identidad para no tocar
  `md_tecnologia_digital_cell`.
- Los candidatos de CSS, JavaScript y vistas no se marcaron como obsoletos solo por
  no tener referencias textuales directas: Blade, Vite, rutas dinámicas y atributos
  `data-*` pueden resolver esas dependencias indirectamente.

## Recorridos arquitectónicos

- **MVC:** Ruta → Middleware → Controlador → Servicio → DAO/Modelo → MariaDB → Vista o recurso.
- **DAO:** conexión → consulta preparada → mapeo → transacción → commit/rollback.
- **Negocio:** cliente → cotización → venta → inventario → auditoría.
- **RBAC:** usuario → rol → permisos → menú dinámico → autorización HTTP 403.

## Módulo: app/Console/Commands

### Archivo: `app/Console/Commands/CrearUsuarioCell.php`

#### Clase: `CrearUsuarioCell`

- **Método/función:** `handle` (línea 24)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `app/Console/Commands/HabilitarSubmenusFinancieros.php`

#### Clase: `HabilitarSubmenusFinancieros`

- **Método/función:** `handle` (línea 48)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** `RegistradorAuditoria $registradorAuditoria`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `hayAdministradorVigente` (línea 189)
  - **Responsabilidad:** Ejecuta la operación hayAdministradorVigente definida por la clase HabilitarSubmenusFinancieros.
  - **Parámetros principales:** `int $rolId`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `consultarEstado` (línea 208)
  - **Responsabilidad:** Ejecuta la operación consultarEstado definida por la clase HabilitarSubmenusFinancieros.
  - **Parámetros principales:** `int $rolId, int $menuId`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `app/Console/Commands/HabilitarSubmenusOrganizacion.php`

#### Clase: `HabilitarSubmenusOrganizacion`

- **Método/función:** `handle` (línea 61)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** `RegistradorAuditoria $registradorAuditoria`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `consultarEstado` (línea 225)
  - **Responsabilidad:** Ejecuta la operación consultarEstado definida por la clase HabilitarSubmenusOrganizacion.
  - **Parámetros principales:** `array $pantallasObjetivo`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `crearRespaldo` (línea 418)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `array $estado, array $pantallasObjetivo`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `pantallasObjetivo` (línea 453)
  - **Responsabilidad:** Ejecuta la operación pantallasObjetivo definida por la clase HabilitarSubmenusOrganizacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `hayAdministradorVigente` (línea 469)
  - **Responsabilidad:** Ejecuta la operación hayAdministradorVigente definida por la clase HabilitarSubmenusOrganizacion.
  - **Parámetros principales:** `int $rolId`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `app/Console/Commands/InicializarCatalogoRbac.php`

#### Clase: `InicializarCatalogoRbac`

- **Método/función:** `handle` (línea 33)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** `RbacInicialSeeder $inicializador, RegistradorAuditoria $registradorAuditoria`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `contarAdministradoresVigentes` (línea 159)
  - **Responsabilidad:** Ejecuta la operación contarAdministradoresVigentes definida por la clase InicializarCatalogoRbac.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `contarAlmacenesActivos` (línea 182)
  - **Responsabilidad:** Ejecuta la operación contarAlmacenesActivos definida por la clase InicializarCatalogoRbac.
  - **Parámetros principales:** `int $empresaId`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `app/Console/Commands/InicializarTechnologyCell.php`

#### Clase: `InicializarTechnologyCell`

- **Método/función:** `handle` (línea 37)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** `RegistradorAuditoria $audit`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `mostrarPlan` (línea 221)
  - **Responsabilidad:** Ejecuta la operación mostrarPlan definida por la clase InicializarTechnologyCell.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `mostrarConciliacion` (línea 233)
  - **Responsabilidad:** Ejecuta la operación mostrarConciliacion definida por la clase InicializarTechnologyCell.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Consola
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: app/Contratos/Productos

### Archivo: `app/Contratos/Productos/AlcanceProductos.php`

#### Clase: `AlcanceProductos`

- **Método/función:** `atributosDeAlcance` (línea 15)
  - **Responsabilidad:** Ejecuta la operación atributosDeAlcance definida por la clase AlcanceProductos.
  - **Parámetros principales:** `User $usuario, string $tabla`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Contrato
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `app/Contratos/Productos/AuditoriaProductos.php`

#### Clase: `AuditoriaProductos`

- **Método/función:** `registrar` (línea 13)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `?int $usuarioId, string $accion, string $entidad, ?int $entidadId, ?array $antes, ?array $despues, ?string $ip`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Dominio / Contrato
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: app/Dominio/Catalogo

### Archivo: `app/Dominio/Catalogo/ProductoDao.php`

#### Clase: `ProductoDao`

- **Método/función:** `enTransaccion` (línea 15)
  - **Responsabilidad:** Ejecuta la operación indicada dentro del contexto transaccional establecido.
  - **Parámetros principales:** `Closure $operacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `buscarPorId` (línea 26)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `int $id, bool $incluirVariantes = true`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `crear` (línea 35)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `ProductoEntidad $producto`
  - **Resultado:** `ProductoEntidad` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `actualizar` (línea 44)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ProductoEntidad $producto`
  - **Resultado:** `ProductoEntidad` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `crearVariante` (línea 53)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `VarianteProductoEntidad $variante`
  - **Resultado:** `VarianteProductoEntidad` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `actualizarVariante` (línea 62)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `VarianteProductoEntidad $variante`
  - **Resultado:** `VarianteProductoEntidad` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `eliminarVariante` (línea 71)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `int $id`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `eliminar` (línea 80)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `int $id`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `sincronizarImagenPortada` (línea 91)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `int $productoId, string $rutaImagen`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.

### Archivo: `app/Dominio/Catalogo/ProductoEntidad.php`

#### Clase: `(sin clase)`

- **Método/función:** `__construct` (línea 8)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `app/Dominio/Catalogo/VarianteProductoEntidad.php`

#### Clase: `(sin clase)`

- **Método/función:** `__construct` (línea 12)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: app/Http/Controllers/Api

### Archivo: `app/Http/Controllers/Api/CatalogoController.php`

#### Clase: `CatalogoController`

- **Método/función:** `index` (línea 29)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `FiltroCatalogoRequest $request, ServicioCatalogo $catalogService`
  - **Resultado:** `AnonymousResourceCollection` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `show` (línea 51)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `Request $request, Producto $product, ServicioCatalogo $catalogService`
  - **Resultado:** `RecursoProducto` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Auth

### Archivo: `app/Http/Controllers/Auth/AutenticacionController.php`

#### Clase: `AutenticacionController`

- **Método/función:** `loginForm` (línea 29)
  - **Responsabilidad:** Ejecuta la operación loginForm definida por la clase AutenticacionController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `registerForm` (línea 39)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `login` (línea 53)
  - **Responsabilidad:** Ejecuta la operación login definida por la clase AutenticacionController.
  - **Parámetros principales:** `InicioSesionRequest $request, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `register` (línea 96)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `RegistroRequest $request, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `logout` (línea 159)
  - **Responsabilidad:** Ejecuta la operación logout definida por la clase AutenticacionController.
  - **Parámetros principales:** `RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Cuenta

### Archivo: `app/Http/Controllers/Cuenta/CuentaAdministracionController.php`

#### Clase: `CuentaAdministracionController`

- **Método/función:** `updateProfile` (línea 37)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarPerfilRequest $request, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updatePassword` (línea 61)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarContrasenaRequest $request, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `security` (línea 84)
  - **Responsabilidad:** Ejecuta la operación security definida por la clase CuentaAdministracionController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `sessions` (línea 101)
  - **Responsabilidad:** Ejecuta la operación sessions definida por la clase CuentaAdministracionController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `revokeSession` (línea 123)
  - **Responsabilidad:** Ejecuta la operación revokeSession definida por la clase CuentaAdministracionController.
  - **Parámetros principales:** `Request $request, int $sessionId, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `preferences` (línea 150)
  - **Responsabilidad:** Ejecuta la operación preferences definida por la clase CuentaAdministracionController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updatePreferences` (línea 166)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarPreferenciasRequest $request, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updateAvatar` (línea 188)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarAvatarRequest $request, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `activity` (línea 219)
  - **Responsabilidad:** Ejecuta la operación activity definida por la clase CuentaAdministracionController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `notifications` (línea 238)
  - **Responsabilidad:** Ejecuta la operación notifications definida por la clase CuentaAdministracionController.
  - **Parámetros principales:** `Request $request, ServicioNotificaciones $servicio`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `markNotificationRead` (línea 257)
  - **Responsabilidad:** Ejecuta la operación markNotificationRead definida por la clase CuentaAdministracionController.
  - **Parámetros principales:** `Request $request, string $notificationId, ServicioNotificaciones $servicio`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Cuenta/CuentaController.php`

#### Clase: `CuentaController`

- **Método/función:** `__construct` (línea 25)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ResolutorDashboard $dashboardResolver`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `dashboard` (línea 34)
  - **Responsabilidad:** Ejecuta la operación dashboard definida por la clase CuentaController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion

### Archivo: `app/Http/Controllers/Gestion/CajaController.php`

#### Clase: `CajaController`

- **Método/función:** `index` (línea 35)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `FiltroCajasRequest $request, ServicioAutorizacion $authorization`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 89)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarCajaRequest $request, ServicioCajas $boxes`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 107)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarCajaRequest $request, Caja $caja, ServicioCajas $boxes`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updateStatus` (línea 125)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `CambiarEstadoCajaRequest $request, Caja $caja, ServicioCajas $boxes`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/ConfiguracionSistemaController.php`

#### Clase: `ConfiguracionSistemaController`

- **Método/función:** `general` (línea 38)
  - **Responsabilidad:** Ejecuta la operación general definida por la clase ConfiguracionSistemaController.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $autorizacion, ServicioConfiguracionSistema $servicio`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `integraciones` (línea 56)
  - **Responsabilidad:** Ejecuta la operación integraciones definida por la clase ConfiguracionSistemaController.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $autorizacion, ServicioConfiguracionSistema $servicio`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `actualizarIntegracion` (línea 76)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `guardarIntegracion` (línea 131)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarIntegracionRequest $request, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `editarIntegracion` (línea 175)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `GuardarIntegracionRequest $request, int $integracion, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `notificaciones` (línea 205)
  - **Responsabilidad:** Ejecuta la operación notificaciones definida por la clase ConfiguracionSistemaController.
  - **Parámetros principales:** `Request $request, ServicioNotificaciones $servicio`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `seguridad` (línea 235)
  - **Responsabilidad:** Ejecuta la operación seguridad definida por la clase ConfiguracionSistemaController.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $autorizacion, ServicioConfiguracionSistema $servicio`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `respaldos` (línea 256)
  - **Responsabilidad:** Ejecuta la operación respaldos definida por la clase ConfiguracionSistemaController.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $autorizacion, ServicioRespaldos $respaldos`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `crearRespaldo` (línea 277)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `Request $request, ServicioRespaldos $respaldos, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `descargarRespaldo` (línea 293)
  - **Responsabilidad:** Ejecuta la operación descargarRespaldo definida por la clase ConfiguracionSistemaController.
  - **Parámetros principales:** `string $archivo, ServicioRespaldos $respaldos`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `pagina` (línea 309)
  - **Responsabilidad:** Ejecuta la operación pagina definida por la clase ConfiguracionSistemaController.
  - **Parámetros principales:** `string $tipo, array $datos`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/FacturacionController.php`

#### Clase: `FacturacionController`

- **Método/función:** `index` (línea 32)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 132)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/FinanzasController.php`

#### Clase: `FinanzasController`

- **Método/función:** `pagos` (línea 53)
  - **Responsabilidad:** Ejecuta la operación pagos definida por la clase FinanzasController.
  - **Parámetros principales:** `FiltroPagosRequest $request, ServicioConsultasFinancieras $financialQueries, ServicioAutorizacion $authorization`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `reembolsos` (línea 118)
  - **Responsabilidad:** Ejecuta la operación reembolsos definida por la clase FinanzasController.
  - **Parámetros principales:** `FiltroReembolsosRequest $request, ServicioConsultasFinancieras $financialQueries, ServicioAutorizacion $authorization`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `registrarPago` (línea 184)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `RegistrarPagoRequest $request, ServicioOperacionesFinancieras $operations`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `detallePago` (línea 203)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `Request $request, Pago $pago, ServicioAutorizacion $authorization`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `confirmarPagoEfectivo` (línea 235)
  - **Responsabilidad:** Ejecuta la operación confirmarPagoEfectivo definida por la clase FinanzasController.
  - **Parámetros principales:** `ConfirmarPagoEfectivoRequest $request, Pago $pago, ServicioOperacionesFinancieras $operations`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `anularPago` (línea 256)
  - **Responsabilidad:** Ejecuta la operación anularPago definida por la clase FinanzasController.
  - **Parámetros principales:** `AnularOperacionFinancieraRequest $request, Pago $pago, ServicioOperacionesFinancieras $operations`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `solicitarReembolso` (línea 272)
  - **Responsabilidad:** Ejecuta la operación solicitarReembolso definida por la clase FinanzasController.
  - **Parámetros principales:** `SolicitarReembolsoRequest $request, ServicioOperacionesFinancieras $operations`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `detalleReembolso` (línea 291)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `Request $request, Reembolso $reembolso, ServicioAutorizacion $authorization`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `aprobarReembolso` (línea 321)
  - **Responsabilidad:** Ejecuta la operación aprobarReembolso definida por la clase FinanzasController.
  - **Parámetros principales:** `AprobarReembolsoRequest $request, Reembolso $reembolso, ServicioOperacionesFinancieras $operations`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `anularReembolso` (línea 340)
  - **Responsabilidad:** Ejecuta la operación anularReembolso definida por la clase FinanzasController.
  - **Parámetros principales:** `AnularOperacionFinancieraRequest $request, Reembolso $reembolso, ServicioOperacionesFinancieras $operations`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `exportarPagos` (línea 364)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `FiltroPagosRequest $request, string $format, ServicioConsultasFinancieras $financialQueries, ServicioAutorizacion $authorization, ExportadorTabular $exporter, RegistradorAuditoria $auditor`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `exportarReembolsos` (línea 395)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `FiltroReembolsosRequest $request, string $format, ServicioConsultasFinancieras $financialQueries, ServicioAutorizacion $authorization, ExportadorTabular $exporter, RegistradorAuditoria $auditor`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 413)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `User $actor, array $filters, string $entity, Builder $query, array $columns, callable $map, string $format, ExportadorTabular $exporter, RegistradorAuditoria $auditor, ?string $ip`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `paymentCells` (línea 441)
  - **Responsabilidad:** Ejecuta la operación paymentCells definida por la clase FinanzasController.
  - **Parámetros principales:** `Pago $payment`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `paymentExportCells` (línea 456)
  - **Responsabilidad:** Ejecuta la operación paymentExportCells definida por la clase FinanzasController.
  - **Parámetros principales:** `Pago $payment, string $format`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `paymentCounterparty` (línea 478)
  - **Responsabilidad:** Ejecuta la operación paymentCounterparty definida por la clase FinanzasController.
  - **Parámetros principales:** `Pago $payment`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `paymentOrigin` (línea 497)
  - **Responsabilidad:** Ejecuta la operación paymentOrigin definida por la clase FinanzasController.
  - **Parámetros principales:** `Pago $payment`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `refundCells` (línea 508)
  - **Responsabilidad:** Ejecuta la operación refundCells definida por la clase FinanzasController.
  - **Parámetros principales:** `Reembolso $refund`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `refundExportCells` (línea 522)
  - **Responsabilidad:** Ejecuta la operación refundExportCells definida por la clase FinanzasController.
  - **Parámetros principales:** `Reembolso $refund, string $format`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `metric` (línea 537)
  - **Responsabilidad:** Ejecuta la operación metric definida por la clase FinanzasController.
  - **Parámetros principales:** `string $label, string|int $value, string $icon, string $tone, string $helper`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `stateSummary` (línea 543)
  - **Responsabilidad:** Ejecuta la operación stateSummary definida por la clase FinanzasController.
  - **Parámetros principales:** `Collection $states`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `money` (línea 562)
  - **Responsabilidad:** Ejecuta la operación money definida por la clase FinanzasController.
  - **Parámetros principales:** `float $amount, string $currency`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `moneyTotals` (línea 574)
  - **Responsabilidad:** Ejecuta la operación moneyTotals definida por la clase FinanzasController.
  - **Parámetros principales:** `Collection $totals`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `safeExportText` (línea 593)
  - **Responsabilidad:** Ejecuta la operación safeExportText definida por la clase FinanzasController.
  - **Parámetros principales:** `string $value, string $format`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/SesionCajaController.php`

#### Clase: `SesionCajaController`

- **Método/función:** `index` (línea 36)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `FiltroSesionesCajaRequest $request, ServicioAutorizacion $authorization`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 83)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `AbrirSesionCajaRequest $request, ServicioCajas $boxes`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `show` (línea 106)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `Request $request, SesionCaja $sesionCaja, ServicioAutorizacion $authorization, ServicioCajas $boxes`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `storeMovement` (línea 137)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `RegistrarMovimientoCajaRequest $request, SesionCaja $sesionCaja, ServicioCajas $boxes`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 158)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `CerrarSesionCajaRequest $request, SesionCaja $sesionCaja, ServicioCajas $boxes`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Campanias

### Archivo: `app/Http/Controllers/Gestion/Campanias/CampaniaController.php`

#### Clase: `CampaniaController`

- **Método/función:** `index` (línea 41)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 91)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarCampaniaRequest $request, RegistradorAuditoria $audit, ServicioAutorizacion $autorizacion`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `deactivate` (línea 117)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Campania $campaign, RegistradorAuditoria $audit, ServicioAutorizacion $autorizacion`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 139)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $autorizacion, ExportadorTabular $exporter, RegistradorAuditoria $audit`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 190)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarCampaniaRequest $request, Campania $campaign, RegistradorAuditoria $audit, ServicioAutorizacion $autorizacion`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `import` (línea 215)
  - **Responsabilidad:** Procesa e incorpora la información importada.
  - **Parámetros principales:** `ImportarCampaniasRequest $request, RegistradorAuditoria $audit, ServicioAutorizacion $autorizacion`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `campaignQuery` (línea 300)
  - **Responsabilidad:** Ejecuta la operación campaignQuery definida por la clase CampaniaController.
  - **Parámetros principales:** `array $filters, int $empresaId`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filters` (línea 319)
  - **Responsabilidad:** Ejecuta la operación filters definida por la clase CampaniaController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `campaignData` (línea 338)
  - **Responsabilidad:** Ejecuta la operación campaignData definida por la clase CampaniaController.
  - **Parámetros principales:** `array $data, GuardarCampaniaRequest $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `trendPercent` (línea 358)
  - **Responsabilidad:** Ejecuta la operación trendPercent definida por la clase CampaniaController.
  - **Parámetros principales:** `int $current, int $previous`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/Campanias/PublicidadInteligenteController.php`

#### Clase: `PublicidadInteligenteController`

- **Método/función:** `__construct` (línea 23)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioInteligenciaComercial $commerce`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `ads` (línea 30)
  - **Responsabilidad:** Ejecuta la operación ads definida por la clase PublicidadInteligenteController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Categorias

### Archivo: `app/Http/Controllers/Gestion/Categorias/CategoriaController.php`

#### Clase: `CategoriaController`

- **Método/función:** `index` (línea 38)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioInventario $inventario`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 67)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, ServicioInventario $inventario, ExportadorTabular $exporter, RegistradorAuditoria $audit`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 112)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarCategoriaRequest $request, ServicioInventario $inventario, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 138)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarCategoriaRequest $request, Categoria $category, ServicioInventario $inventario, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `deactivate` (línea 163)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Request $request, Categoria $category, ServicioInventario $inventario, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `consultaCategorias` (línea 180)
  - **Responsabilidad:** Ejecuta la operación consultaCategorias definida por la clase CategoriaController.
  - **Parámetros principales:** `array $filtros, int $empresaId`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filtros` (línea 202)
  - **Responsabilidad:** Ejecuta la operación filtros definida por la clase CategoriaController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `resumenCategorias` (línea 218)
  - **Responsabilidad:** Ejecuta la operación resumenCategorias definida por la clase CategoriaController.
  - **Parámetros principales:** `int $empresaId`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `alcanceActual` (línea 237)
  - **Responsabilidad:** Ejecuta la operación alcanceActual definida por la clase CategoriaController.
  - **Parámetros principales:** `Request $request, ServicioInventario $inventario`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `verificarEmpresa` (línea 257)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `Categoria $categoria, int $empresaId`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Clientes

### Archivo: `app/Http/Controllers/Gestion/Clientes/ClienteController.php`

#### Clase: `ClienteController`

- **Método/función:** `__construct` (línea 39)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioAutorizacion $autorizacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 48)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 109)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, string $format, ExportadorTabular $exporter, RegistradorAuditoria $audit`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 157)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarClienteRequest $request, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `import` (línea 180)
  - **Responsabilidad:** Procesa e incorpora la información importada.
  - **Parámetros principales:** `ImportarClientesRequest $request, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 248)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarClienteRequest $request, Cliente $customer, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `deactivate` (línea 272)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Request $request, Cliente $customer, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `customerQuery` (línea 288)
  - **Responsabilidad:** Ejecuta la operación customerQuery definida por la clase ClienteController.
  - **Parámetros principales:** `array $filters, User $usuario`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `verificarAccesoCliente` (línea 318)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `Cliente $cliente, User $usuario, string $permiso`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `consultaClientesEnAlcance` (línea 336)
  - **Responsabilidad:** Ejecuta la operación consultaClientesEnAlcance definida por la clase ClienteController.
  - **Parámetros principales:** `User $usuario, string $permiso`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filters` (línea 356)
  - **Responsabilidad:** Ejecuta la operación filters definida por la clase ClienteController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `recentActivity` (línea 378)
  - **Responsabilidad:** Ejecuta la operación recentActivity definida por la clase ClienteController.
  - **Parámetros principales:** `Collection $recentOrders, Collection $recentCustomers`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Compras

### Archivo: `app/Http/Controllers/Gestion/Compras/CompraController.php`

#### Clase: `CompraController`

- **Método/función:** `__construct` (línea 39)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 51)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 267)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, string $format, ExportadorTabular $exporter, RegistradorAuditoria $audit`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 332)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarCompraRequest $request`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `import` (línea 348)
  - **Responsabilidad:** Procesa e incorpora la información importada.
  - **Parámetros principales:** `ImportarComprasRequest $request`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `approve` (línea 412)
  - **Responsabilidad:** Ejecuta la operación approve definida por la clase CompraController.
  - **Parámetros principales:** `Request $request, Compra $compra`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `receive` (línea 428)
  - **Responsabilidad:** Ejecuta la operación receive definida por la clase CompraController.
  - **Parámetros principales:** `Request $request, Compra $compra`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `cancel` (línea 444)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Request $request, Compra $compra`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `recibirParcial` (línea 460)
  - **Responsabilidad:** Ejecuta la operación recibirParcial definida por la clase CompraController.
  - **Parámetros principales:** `RegistrarRecepcionParcialCompraRequest $request, Compra $compra`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Cotizaciones

### Archivo: `app/Http/Controllers/Gestion/Cotizaciones/CotizacionController.php`

#### Clase: `CotizacionController`

- **Método/función:** `__construct` (línea 38)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 50)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `administratorIndex` (línea 90)
  - **Responsabilidad:** Ejecuta la operación administratorIndex definida por la clase CotizacionController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 220)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 295)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarCotizacionRequest $request, Cotizacion $quotation`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 309)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarCotizacionRequest $request`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `aprobar` (línea 325)
  - **Responsabilidad:** Ejecuta la operación aprobar definida por la clase CotizacionController.
  - **Parámetros principales:** `Request $request, Cotizacion $quotation`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `deactivate` (línea 341)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Request $request, Cotizacion $quotation`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `registrarEnvioManual` (línea 357)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `RegistrarEnvioCotizacionRequest $request, Cotizacion $quotation`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `descargar` (línea 377)
  - **Responsabilidad:** Ejecuta la operación descargar definida por la clase CotizacionController.
  - **Parámetros principales:** `Request $request, Cotizacion $quotation, ExportadorTabular $exportador, RegistradorAuditoria $auditoria`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Cupones

### Archivo: `app/Http/Controllers/Gestion/Cupones/CuponController.php`

#### Clase: `CuponController`

- **Método/función:** `index` (línea 38)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioCupones $coupons, ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 66)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarCuponRequest $request, ServicioCupones $coupons`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 84)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarCuponRequest $request, Cupon $coupon, ServicioCupones $coupons`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `deactivate` (línea 102)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `DesactivarCuponRequest $request, Cupon $coupon, ServicioCupones $coupons`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 124)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, ServicioCupones $coupons, ServicioAutorizacion $autorizacion, ExportadorTabular $exporter, RegistradorAuditoria $audit`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filters` (línea 161)
  - **Responsabilidad:** Ejecuta la operación filters definida por la clase CuponController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `safeCsvValue` (línea 186)
  - **Responsabilidad:** Ejecuta la operación safeCsvValue definida por la clase CuponController.
  - **Parámetros principales:** `string $value`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/DevolucionesGarantias

### Archivo: `app/Http/Controllers/Gestion/DevolucionesGarantias/DevolucionGarantiaController.php`

#### Clase: `DevolucionGarantiaController`

- **Método/función:** `__construct` (línea 39)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioAutorizacion $authorization`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 48)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 134)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarDevolucionGarantiaRequest $request, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 163)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarDevolucionGarantiaRequest $request, DevolucionGarantia $devolucionGarantia, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `destroy` (línea 195)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Request $request, DevolucionGarantia $devolucionGarantia, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 227)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, ExportadorTabular $exporter, RegistradorAuditoria $audit, string $format = 'csv'`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `assertOrderInScope` (línea 270)
  - **Responsabilidad:** Ejecuta la operación assertOrderInScope definida por la clase DevolucionGarantiaController.
  - **Parámetros principales:** `Request $request, Pedido $order, string $permission`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filters` (línea 289)
  - **Responsabilidad:** Ejecuta la operación filters definida por la clase DevolucionGarantiaController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `applyFilters` (línea 318)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `Builder $query, array $filters`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `tableRow` (línea 343)
  - **Responsabilidad:** Ejecuta la operación tableRow definida por la clase DevolucionGarantiaController.
  - **Parámetros principales:** `DevolucionGarantia $returnRequest`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `statusTone` (línea 390)
  - **Responsabilidad:** Ejecuta la operación statusTone definida por la clase DevolucionGarantiaController.
  - **Parámetros principales:** `string $status`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Finanzas

### Archivo: `app/Http/Controllers/Gestion/Finanzas/CuentasFinancierasController.php`

#### Clase: `CuentasFinancierasController`

- **Método/función:** `__construct` (línea 32)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private ServicioConsultasCuentas $consultasCuentas`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 43)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `crearCuentaPorPagar` (línea 124)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `CrearCuentaPorPagarRequest $request, ServicioCuentasFinancieras $cuentas`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `aplicarCobro` (línea 142)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `AplicarCobroCuentaRequest $request, ServicioCuentasFinancieras $cuentas`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `aplicarPago` (línea 159)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `AplicarPagoCuentaRequest $request, ServicioCuentasFinancieras $cuentas`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Inventario

### Archivo: `app/Http/Controllers/Gestion/Inventario/InventarioController.php`

#### Clase: `InventarioController`

- **Método/función:** `__construct` (línea 32)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 44)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `adjust` (línea 124)
  - **Responsabilidad:** Ejecuta la operación adjust definida por la clase InventarioController.
  - **Parámetros principales:** `AjustarInventarioRequest $request, Producto $product`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `adjustLegacy` (línea 151)
  - **Responsabilidad:** Ejecuta la operación adjustLegacy definida por la clase InventarioController.
  - **Parámetros principales:** `AjustarInventarioRequest $request`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filters` (línea 161)
  - **Responsabilidad:** Ejecuta la operación filters definida por la clase InventarioController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filtrosMovimientos` (línea 177)
  - **Responsabilidad:** Ejecuta la operación filtrosMovimientos definida por la clase InventarioController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `aplicarFiltrosMovimientos` (línea 196)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `Builder $consulta, array $filtros`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `latestMovements` (línea 231)
  - **Responsabilidad:** Ejecuta la operación latestMovements definida por la clase InventarioController.
  - **Parámetros principales:** `Collection $products, User $user`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `trendPercent` (línea 259)
  - **Responsabilidad:** Ejecuta la operación trendPercent definida por la clase InventarioController.
  - **Parámetros principales:** `int $current, int $previous`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/Inventario/TransferenciaExistenciaController.php`

#### Clase: `TransferenciaExistenciaController`

- **Método/función:** `index` (línea 34)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioTransferenciasExistencias $transfers`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 59)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarTransferenciaExistenciaRequest $request, ServicioTransferenciasExistencias $transfers`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `approve` (línea 77)
  - **Responsabilidad:** Ejecuta la operación approve definida por la clase TransferenciaExistenciaController.
  - **Parámetros principales:** `AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `dispatch` (línea 95)
  - **Responsabilidad:** Ejecuta la operación dispatch definida por la clase TransferenciaExistenciaController.
  - **Parámetros principales:** `AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `receive` (línea 113)
  - **Responsabilidad:** Ejecuta la operación receive definida por la clase TransferenciaExistenciaController.
  - **Parámetros principales:** `AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `returnToOrigin` (línea 131)
  - **Responsabilidad:** Ejecuta la operación returnToOrigin definida por la clase TransferenciaExistenciaController.
  - **Parámetros principales:** `AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `reconcileDifference` (línea 149)
  - **Responsabilidad:** Ejecuta la operación reconcileDifference definida por la clase TransferenciaExistenciaController.
  - **Parámetros principales:** `AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `reject` (línea 167)
  - **Responsabilidad:** Ejecuta la operación reject definida por la clase TransferenciaExistenciaController.
  - **Parámetros principales:** `AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `cancel` (línea 185)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `close` (línea 203)
  - **Responsabilidad:** Ejecuta la operación close definida por la clase TransferenciaExistenciaController.
  - **Parámetros principales:** `AccionTransferenciaExistenciaRequest $request, TransferenciaExistencia $transfer, ServicioTransferenciasExistencias $transfers`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 223)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, ServicioTransferenciasExistencias $transfers, ExportadorTabular $exporter, string $format = 'csv'`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `actor` (línea 264)
  - **Responsabilidad:** Ejecuta la operación actor definida por la clase TransferenciaExistenciaController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `User` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `success` (línea 278)
  - **Responsabilidad:** Ejecuta la operación success definida por la clase TransferenciaExistenciaController.
  - **Parámetros principales:** `string $message`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Organizacion

### Archivo: `app/Http/Controllers/Gestion/Organizacion/AlmacenController.php`

#### Clase: `AlmacenController`

- **Método/función:** `index` (línea 36)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `FiltroAlmacenesRequest $request, ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 158)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 198)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `cambiarEstado` (línea 242)
  - **Responsabilidad:** Ejecuta la operación cambiarEstado definida por la clase AlmacenController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `consultaSedesAutorizadas` (línea 275)
  - **Responsabilidad:** Ejecuta la operación consultaSedesAutorizadas definida por la clase AlmacenController.
  - **Parámetros principales:** `User $usuario, ServicioAutorizacion $autorizacion, string $codigoPermiso`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `consultaAlmacenesAutorizados` (línea 281)
  - **Responsabilidad:** Ejecuta la operación consultaAlmacenesAutorizados definida por la clase AlmacenController.
  - **Parámetros principales:** `User $usuario, ServicioAutorizacion $autorizacion, string $codigoPermiso`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/Organizacion/EmpresaController.php`

#### Clase: `EmpresaController`

- **Método/función:** `index` (línea 33)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 62)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/Organizacion/PersonalSedeController.php`

#### Clase: `PersonalSedeController`

- **Método/función:** `listar` (línea 31)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `FiltroPersonalSedeRequest $request, ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `consultaAsignaciones` (línea 110)
  - **Responsabilidad:** Ejecuta la operación consultaAsignaciones definida por la clase PersonalSedeController.
  - **Parámetros principales:** `array $idsSede`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `restringirAsignacionesVigentes` (línea 129)
  - **Responsabilidad:** Ejecuta la operación restringirAsignacionesVigentes definida por la clase PersonalSedeController.
  - **Parámetros principales:** `Builder $consulta, Carbon $ahora`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `resumenPersonal` (línea 142)
  - **Responsabilidad:** Ejecuta la operación resumenPersonal definida por la clase PersonalSedeController.
  - **Parámetros principales:** `Builder $consultaBase, Carbon $ahora`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `personalPorSede` (línea 161)
  - **Responsabilidad:** Ejecuta la operación personalPorSede definida por la clase PersonalSedeController.
  - **Parámetros principales:** `Builder $consultaBase, Carbon $ahora`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `personalPorRol` (línea 171)
  - **Responsabilidad:** Ejecuta la operación personalPorRol definida por la clase PersonalSedeController.
  - **Parámetros principales:** `Builder $consultaBase, Carbon $ahora`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/Organizacion/ResponsableOrganizacionalController.php`

#### Clase: `ResponsableOrganizacionalController`

- **Método/función:** `index` (línea 32)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `ServicioAutorizacion $autorizacion, ServicioResponsablesOrganizacionales $servicio`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 69)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarResponsableOrganizacionalRequest $request, ServicioResponsablesOrganizacionales $servicio, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/Organizacion/SedeController.php`

#### Clase: `SedeController`

- **Método/función:** `index` (línea 35)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `FiltroSedesRequest $request, ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 88)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 126)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `cambiarEstado` (línea 170)
  - **Responsabilidad:** Ejecuta la operación cambiarEstado definida por la clase SedeController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Productos

### Archivo: `app/Http/Controllers/Gestion/Productos/GestionProductosController.php`

#### Clase: `GestionProductosController`

- **Método/función:** `preciosCanales` (línea 62)
  - **Responsabilidad:** Ejecuta la operación preciosCanales definida por la clase GestionProductosController.
  - **Parámetros principales:** `Request $request, ServicioInventario $inventario, ServicioAutorizacion $autorizacion, ServicioPreciosMayoristas $servicioPrecios`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `guardarPrecioMayorista` (línea 168)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarPrecioMayoristaRequest $request, ServicioPreciosMayoristas $servicioPrecios`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `importarPreciosMayoristas` (línea 184)
  - **Responsabilidad:** Procesa e incorpora la información importada.
  - **Parámetros principales:** `ImportarPreciosMayoristasRequest $request, ServicioPreciosMayoristas $servicioPrecios`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `configuracionTienda` (línea 204)
  - **Responsabilidad:** Ejecuta la operación configuracionTienda definida por la clase GestionProductosController.
  - **Parámetros principales:** `Request $request, ServicioInventario $inventario, ServicioAutorizacion $autorizacion, ServicioConfiguracionTienda $servicioConfiguracion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `guardarConfiguracionTienda` (línea 292)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarConfiguracionTiendaRequest $request, ServicioConfiguracionTienda $servicioConfiguracion`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 308)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioInventario $inventario`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 398)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, string $format, ServicioInventario $inventario, ?ExportadorTabular $exporter = null`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 421)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 462)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarProductoRequest $request, Producto $product, ServicioGestionProductos $productos`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `deactivate` (línea 498)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Producto $product, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `registrarVariante` (línea 512)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `actualizarVariante` (línea 530)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `desactivarVariante` (línea 549)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `registrarImagenVariante` (línea 568)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `eliminarImagenVariante` (línea 600)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `attributes` (línea 619)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** `array $data`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `usuarioConAccesoProducto` (línea 633)
  - **Responsabilidad:** Ejecuta la operación usuarioConAccesoProducto definida por la clase GestionProductosController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `productQuery` (línea 655)
  - **Responsabilidad:** Ejecuta la operación productQuery definida por la clase GestionProductosController.
  - **Parámetros principales:** `array $filters, array $alcance, ServicioInventario $inventario`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filters` (línea 663)
  - **Responsabilidad:** Ejecuta la operación filters definida por la clase GestionProductosController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `trendPercent` (línea 689)
  - **Responsabilidad:** Ejecuta la operación trendPercent definida por la clase GestionProductosController.
  - **Parámetros principales:** `int $current, int $previous`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `downloadCsv` (línea 701)
  - **Responsabilidad:** Ejecuta la operación downloadCsv definida por la clase GestionProductosController.
  - **Parámetros principales:** `Builder $query`
  - **Resultado:** `StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `downloadCorporateReport` (línea 724)
  - **Responsabilidad:** Ejecuta la operación downloadCorporateReport definida por la clase GestionProductosController.
  - **Parámetros principales:** `Collection $products, string $format, array $filters, array $alcance, Request $request, ExportadorTabular $exporter`
  - **Resultado:** `Response` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `downloadExcel` (línea 776)
  - **Responsabilidad:** Ejecuta la operación downloadExcel definida por la clase GestionProductosController.
  - **Parámetros principales:** `Collection $products`
  - **Resultado:** `Response` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `downloadPdf` (línea 793)
  - **Responsabilidad:** Ejecuta la operación downloadPdf definida por la clase GestionProductosController.
  - **Parámetros principales:** `Collection $products`
  - **Resultado:** `Response` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `exportRow` (línea 834)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Producto $product`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `buildPdfDocument` (línea 852)
  - **Responsabilidad:** Ejecuta la operación buildPdfDocument definida por la clase GestionProductosController.
  - **Parámetros principales:** `array $pages`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `escapePdfText` (línea 911)
  - **Responsabilidad:** Ejecuta la operación escapePdfText definida por la clase GestionProductosController.
  - **Parámetros principales:** `string $value`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Proveedores

### Archivo: `app/Http/Controllers/Gestion/Proveedores/ProveedorController.php`

#### Clase: `ProveedorController`

- **Método/función:** `index` (línea 43)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioInventario $inventario`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 120)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, ServicioInventario $inventario, ExportadorTabular $exporter, RegistradorAuditoria $audit`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 170)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarProveedorRequest $request, ServicioInventario $inventario, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 196)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarProveedorRequest $request, Proveedor $supplier, ServicioInventario $inventario, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `deactivate` (línea 221)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Request $request, Proveedor $supplier, ServicioInventario $inventario, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `consultaProveedores` (línea 239)
  - **Responsabilidad:** Ejecuta la operación consultaProveedores definida por la clase ProveedorController.
  - **Parámetros principales:** `array $filtros, array $alcance`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filtros` (línea 264)
  - **Responsabilidad:** Ejecuta la operación filtros definida por la clase ProveedorController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `detalleProveedor` (línea 283)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `int $proveedorId, array $alcance`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `comprasPorAlcance` (línea 311)
  - **Responsabilidad:** Ejecuta la operación comprasPorAlcance definida por la clase ProveedorController.
  - **Parámetros principales:** `array $alcance`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filtrarComprasPorAlcance` (línea 320)
  - **Responsabilidad:** Ejecuta la operación filtrarComprasPorAlcance definida por la clase ProveedorController.
  - **Parámetros principales:** `Builder $consulta, array $alcance`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `actividadReciente` (línea 331)
  - **Responsabilidad:** Ejecuta la operación actividadReciente definida por la clase ProveedorController.
  - **Parámetros principales:** `array $alcance`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `alcanceActual` (línea 387)
  - **Responsabilidad:** Ejecuta la operación alcanceActual definida por la clase ProveedorController.
  - **Parámetros principales:** `Request $request, ServicioInventario $inventario`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `verificarEmpresa` (línea 407)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `Proveedor $proveedor, int $empresaId`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Reportes

### Archivo: `app/Http/Controllers/Gestion/Reportes/PanelAnalisisAuditoriaController.php`

#### Clase: `PanelAnalisisAuditoriaController`

- **Método/función:** `globales` (línea 40)
  - **Responsabilidad:** Ejecuta la operación globales definida por la clase PanelAnalisisAuditoriaController.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `operaciones` (línea 56)
  - **Responsabilidad:** Ejecuta la operación operaciones definida por la clase PanelAnalisisAuditoriaController.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `mostrar` (línea 74)
  - **Responsabilidad:** Ejecuta la operación mostrar definida por la clase PanelAnalisisAuditoriaController.
  - **Parámetros principales:** `Request $request, string $tipo, ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `exportar` (línea 129)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, string $tipo, ServicioAutorizacion $autorizacion, ExportadorTabular $exportador, RegistradorAuditoria $auditoria, string $format = 'csv'`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `cargarReporte` (línea 212)
  - **Responsabilidad:** Ejecuta la operación cargarReporte definida por la clase PanelAnalisisAuditoriaController.
  - **Parámetros principales:** `array &$pagina, string $tipo, User $usuario, ServicioAutorizacion $autorizacion, array $filtros`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `cargarReportePorTipo` (línea 219)
  - **Responsabilidad:** Ejecuta la operación cargarReportePorTipo definida por la clase PanelAnalisisAuditoriaController.
  - **Parámetros principales:** `array &$pagina, string $reporte, User $usuario, ServicioAutorizacion $autorizacion, array $filtros`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `cargarAuditoria` (línea 278)
  - **Responsabilidad:** Ejecuta la operación cargarAuditoria definida por la clase PanelAnalisisAuditoriaController.
  - **Parámetros principales:** `array &$pagina, string $tipo, User $usuario, ServicioAutorizacion $autorizacion, array $filtros`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `estructura` (línea 348)
  - **Responsabilidad:** Ejecuta la operación estructura definida por la clase PanelAnalisisAuditoriaController.
  - **Parámetros principales:** `string $tipo`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `etiquetaAuditoria` (línea 390)
  - **Responsabilidad:** Ejecuta la operación etiquetaAuditoria definida por la clase PanelAnalisisAuditoriaController.
  - **Parámetros principales:** `?string $valor`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `vista` (línea 418)
  - **Responsabilidad:** Ejecuta la operación vista definida por la clase PanelAnalisisAuditoriaController.
  - **Parámetros principales:** `string $tipo`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `directorio` (línea 430)
  - **Responsabilidad:** Ejecuta la operación directorio definida por la clase PanelAnalisisAuditoriaController.
  - **Parámetros principales:** `string $tipo`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/Reportes/ReporteController.php`

#### Clase: `ReporteController`

- **Método/función:** `index` (línea 37)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `FiltroReporteRequest $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 107)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `FiltroReporteRequest $request, ExportadorTabular $exporter, RegistradorAuditoria $audit, string $format = 'csv'`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `salesQuery` (línea 150)
  - **Responsabilidad:** Ejecuta la operación salesQuery definida por la clase ReporteController.
  - **Parámetros principales:** `array $filters`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `detailQuery` (línea 162)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `array $filters`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `period` (línea 173)
  - **Responsabilidad:** Ejecuta la operación period definida por la clase ReporteController.
  - **Parámetros principales:** `array $filters`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `reportFilters` (línea 189)
  - **Responsabilidad:** Ejecuta la operación reportFilters definida por la clase ReporteController.
  - **Parámetros principales:** `array $filters`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `salesTrend` (línea 203)
  - **Responsabilidad:** Ejecuta la operación salesTrend definida por la clase ReporteController.
  - **Parámetros principales:** `array $filters, CarbonImmutable $from, CarbonImmutable $to`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `categorySales` (línea 253)
  - **Responsabilidad:** Ejecuta la operación categorySales definida por la clase ReporteController.
  - **Parámetros principales:** `Collection $details`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `topProducts` (línea 280)
  - **Responsabilidad:** Ejecuta la operación topProducts definida por la clase ReporteController.
  - **Parámetros principales:** `Collection $details`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `inventorySummary` (línea 303)
  - **Responsabilidad:** Ejecuta la operación inventorySummary definida por la clase ReporteController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `latestOrders` (línea 316)
  - **Responsabilidad:** Ejecuta la operación latestOrders definida por la clase ReporteController.
  - **Parámetros principales:** `array $filters`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `orderStatusSummary` (línea 334)
  - **Responsabilidad:** Ejecuta la operación orderStatusSummary definida por la clase ReporteController.
  - **Parámetros principales:** `array $filters`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `customerReport` (línea 363)
  - **Responsabilidad:** Ejecuta la operación customerReport definida por la clase ReporteController.
  - **Parámetros principales:** `array $filters`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `inventoryReport` (línea 397)
  - **Responsabilidad:** Ejecuta la operación inventoryReport definida por la clase ReporteController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `campaignReport` (línea 424)
  - **Responsabilidad:** Ejecuta la operación campaignReport definida por la clase ReporteController.
  - **Parámetros principales:** `CarbonImmutable $from, CarbonImmutable $to`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `activeView` (línea 451)
  - **Responsabilidad:** Ejecuta la operación activeView definida por la clase ReporteController.
  - **Parámetros principales:** `FiltroReporteRequest $request`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `periodLabel` (línea 463)
  - **Responsabilidad:** Ejecuta la operación periodLabel definida por la clase ReporteController.
  - **Parámetros principales:** `FiltroReporteRequest $request, CarbonImmutable $from, CarbonImmutable $to`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `applyDateFilters` (línea 475)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `Builder $query, array $filters`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Segmentos

### Archivo: `app/Http/Controllers/Gestion/Segmentos/SegmentoClienteController.php`

#### Clase: `SegmentoClienteController`

- **Método/función:** `index` (línea 30)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 68)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarSegmentoClienteRequest $request, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 93)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, ExportadorTabular $exporter, RegistradorAuditoria $audit`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 134)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarSegmentoClienteRequest $request, SegmentoCliente $segment, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `deactivate` (línea 159)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Request $request, SegmentoCliente $segment, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Seguridad

### Archivo: `app/Http/Controllers/Gestion/Seguridad/AsignacionRolesController.php`

#### Clase: `AsignacionRolesController`

- **Método/función:** `index` (línea 34)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 50)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `sync` (línea 123)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `revokeGlobal` (línea 200)
  - **Responsabilidad:** Ejecuta la operación revokeGlobal definida por la clase AsignacionRolesController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `revokeAtSite` (línea 215)
  - **Responsabilidad:** Ejecuta la operación revokeAtSite definida por la clase AsignacionRolesController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `revokeAssignment` (línea 231)
  - **Responsabilidad:** Ejecuta la operación revokeAssignment definida por la clase AsignacionRolesController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `assignmentSnapshot` (línea 268)
  - **Responsabilidad:** Ejecuta la operación assignmentSnapshot definida por la clase AsignacionRolesController.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/Seguridad/MenusController.php`

#### Clase: `MenusController`

- **Método/función:** `index` (línea 35)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updateMenu` (línea 64)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Request $request, Menu $menu, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updateElement` (línea 95)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Request $request, ElementoMenu $element, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updateRoles` (línea 120)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `modules` (línea 171)
  - **Responsabilidad:** Ejecuta la operación modules definida por la clase MenusController.
  - **Parámetros principales:** `ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updateScreen` (línea 202)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Request $request, Pantalla $screen, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updateModule` (línea 233)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Request $request, Modulo $module, ServicioAutorizacion $autorizacion, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `isActionableRoute` (línea 255)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `string $routeName`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `isIndispensableScreen` (línea 269)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `Pantalla $screen`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Gestion/Seguridad/RolesPermisosController.php`

#### Clase: `RolesPermisosController`

- **Método/función:** `index` (línea 33)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `matrix` (línea 54)
  - **Responsabilidad:** Ejecuta la operación matrix definida por la clase RolesPermisosController.
  - **Parámetros principales:** `ServicioAutorizacion $autorizacion`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `permissionCatalog` (línea 75)
  - **Responsabilidad:** Ejecuta la operación permissionCatalog definida por la clase RolesPermisosController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 94)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarRolRequest $request, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updatePermissions` (línea 119)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 179)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarRolRequest $request, Rol $role, ServicioDelegacion $delegacion, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updateStatus` (línea 216)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Request $request, Rol $role, ServicioDelegacion $delegacion, RegistradorAuditoria $auditoria`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/SoporteReclamos

### Archivo: `app/Http/Controllers/Gestion/SoporteReclamos/SoporteReclamoController.php`

#### Clase: `SoporteReclamoController`

- **Método/función:** `index` (línea 37)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $authorization`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 92)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarCasoSoporteRequest $request, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 118)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarCasoSoporteRequest $request, CasoSoporte $casoSoporte, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `deactivate` (línea 142)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Request $request, CasoSoporte $casoSoporte, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `assertCaseInScope` (línea 166)
  - **Responsabilidad:** Ejecuta la operación assertCaseInScope definida por la clase SoporteReclamoController.
  - **Parámetros principales:** `Request $request, CasoSoporte $case, string $permission`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `assertCustomerInScope` (línea 182)
  - **Responsabilidad:** Ejecuta la operación assertCustomerInScope definida por la clase SoporteReclamoController.
  - **Parámetros principales:** `Request $request, ?int $customerId, int $companyId`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 208)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, ExportadorTabular $exporter, RegistradorAuditoria $audit, string $format = 'csv'`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `authorizationSites` (línea 233)
  - **Responsabilidad:** Ejecuta la operación authorizationSites definida por la clase SoporteReclamoController.
  - **Parámetros principales:** `Request $request, string $permission`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Usuarios

### Archivo: `app/Http/Controllers/Gestion/Usuarios/GestionUsuariosController.php`

#### Clase: `GestionUsuariosController`

- **Método/función:** `index` (línea 41)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 87)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, string $format, ExportadorTabular $exporter, RegistradorAuditoria $audit`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 139)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarUsuarioRequest $request, ServicioDelegacion $delegacion, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updateStatus` (línea 226)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `CambiarEstadoUsuarioRequest $request, User $user, ServicioDelegacion $delegacion, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 267)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarUsuarioRequest $request, User $user, ServicioDelegacion $delegacion, RegistradorAuditoria $audit`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `userQuery` (línea 290)
  - **Responsabilidad:** Ejecuta la operación userQuery definida por la clase GestionUsuariosController.
  - **Parámetros principales:** `array $filters`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `roleDistribution` (línea 313)
  - **Responsabilidad:** Ejecuta la operación roleDistribution definida por la clase GestionUsuariosController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filters` (línea 340)
  - **Responsabilidad:** Ejecuta la operación filters definida por la clase GestionUsuariosController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `roleCodes` (línea 360)
  - **Responsabilidad:** Ejecuta la operación roleCodes definida por la clase GestionUsuariosController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `roleLabels` (línea 374)
  - **Responsabilidad:** Ejecuta la operación roleLabels definida por la clase GestionUsuariosController.
  - **Parámetros principales:** `array $roleCodes`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `roleScopes` (línea 384)
  - **Responsabilidad:** Ejecuta la operación roleScopes definida por la clase GestionUsuariosController.
  - **Parámetros principales:** `array $roleCodes`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Gestion/Ventas

### Archivo: `app/Http/Controllers/Gestion/Ventas/VentaController.php`

#### Clase: `VentaController`

- **Método/función:** `__construct` (línea 46)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 60)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `FiltroVentaRequest $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `panelMayorista` (línea 187)
  - **Responsabilidad:** Ejecuta la operación panelMayorista definida por la clase VentaController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `panelMinorista` (línea 199)
  - **Responsabilidad:** Ejecuta la operación panelMinorista definida por la clase VentaController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `panelPorTipoCliente` (línea 213)
  - **Responsabilidad:** Ejecuta la operación panelPorTipoCliente definida por la clase VentaController.
  - **Parámetros principales:** `Request $request, string $tipoCliente`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 325)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `GuardarVentaRequest $request`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `convertirCotizacion` (línea 344)
  - **Responsabilidad:** Ejecuta la operación convertirCotizacion definida por la clase VentaController.
  - **Parámetros principales:** `ConvertirCotizacionRequest $request, Cotizacion $quotation`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `confirmarBorrador` (línea 360)
  - **Responsabilidad:** Ejecuta la operación confirmarBorrador definida por la clase VentaController.
  - **Parámetros principales:** `ConfirmarVentaCotizacionRequest $request, Venta $venta`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 381)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarVentaRequest $request, Venta $order`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `cancel` (línea 397)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `AnularVentaRequest $request, Venta $venta`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 417)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `FiltroVentaRequest $request, string $format = 'csv', ?ExportadorTabular $exporter = null, ?RegistradorAuditoria $audit = null`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filters` (línea 464)
  - **Responsabilidad:** Ejecuta la operación filters definida por la clase VentaController.
  - **Parámetros principales:** `array $input`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `parseDate` (línea 495)
  - **Responsabilidad:** Ejecuta la operación parseDate definida por la clase VentaController.
  - **Parámetros principales:** `string $value, CarbonImmutable $fallback`
  - **Resultado:** `CarbonImmutable` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Paneles

### Archivo: `app/Http/Controllers/Paneles/PanelDashboardController.php`

#### Clase: `PanelDashboardController`

- **Método/función:** `__construct` (línea 25)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `protected readonly ServicioPaneles $panelService`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `__invoke` (línea 34)
  - **Responsabilidad:** Ejecuta la operación __invoke definida por la clase PanelDashboardController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `role` (línea 48)
  - **Responsabilidad:** Ejecuta la operación role definida por la clase PanelDashboardController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `viewName` (línea 55)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `viewData` (línea 60)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `Request $request, User $user`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Paneles/ResolutorDashboard.php`

#### Clase: `ResolutorDashboard`

- **Método/función:** `__construct` (línea 27)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `for` (línea 45)
  - **Responsabilidad:** Ejecuta la operación for definida por la clase ResolutorDashboard.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `PanelDashboardController` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Paneles/Administrador

### Archivo: `app/Http/Controllers/Paneles/Administrador/AlertasGlobalesController.php`

#### Clase: `AlertasGlobalesController`

- **Método/función:** `mostrar` (línea 35)
  - **Responsabilidad:** Ejecuta la operación mostrar definida por la clase AlertasGlobalesController.
  - **Parámetros principales:** `Request $solicitud, ServicioNotificaciones $servicio`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `exportar` (línea 84)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $solicitud, string $format, ExportadorTabular $exportador, RegistradorAuditoria $auditoria`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Paneles/Administrador/DashboardController.php`

#### Clase: `DashboardController`

- **Método/función:** `__construct` (línea 35)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `role` (línea 49)
  - **Responsabilidad:** Ejecuta la operación role definida por la clase DashboardController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `viewName` (línea 59)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `viewData` (línea 67)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `Request $request, User $user`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `monthlySales` (línea 225)
  - **Responsabilidad:** Ejecuta la operación monthlySales definida por la clase DashboardController.
  - **Parámetros principales:** `Collection $totales, Carbon $now`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `trendPercent` (línea 258)
  - **Responsabilidad:** Ejecuta la operación trendPercent definida por la clase DashboardController.
  - **Parámetros principales:** `float|int $current, float|int $previous`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `statusChart` (línea 270)
  - **Responsabilidad:** Ejecuta la operación statusChart definida por la clase DashboardController.
  - **Parámetros principales:** `Collection $statuses, int $total`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `reminders` (línea 291)
  - **Responsabilidad:** Ejecuta la operación reminders definida por la clase DashboardController.
  - **Parámetros principales:** `int $lowStock, int $pendingOrders, int $expiringCampaigns`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Paneles/Administrador/DashboardGlobalPedidosController.php`

#### Clase: `DashboardGlobalPedidosController`

- **Método/función:** `index` (línea 41)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $authorization`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 89)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, string $format, ExportadorTabular $exporter, ServicioAutorizacion $authorization, RegistradorAuditoria $auditor`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filters` (línea 172)
  - **Responsabilidad:** Ejecuta la operación filters definida por la clase DashboardGlobalPedidosController.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $authorization`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `orders` (línea 210)
  - **Responsabilidad:** Ejecuta la operación orders definida por la clase DashboardGlobalPedidosController.
  - **Parámetros principales:** `User $user, array $filters, ServicioAutorizacion $authorization, bool $applySelectedFilters = true`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `authorizeDashboard` (línea 252)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `User $user, ServicioAutorizacion $authorization`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `availableSites` (línea 259)
  - **Responsabilidad:** Ejecuta la operación availableSites definida por la clase DashboardGlobalPedidosController.
  - **Parámetros principales:** `User $user, ServicioAutorizacion $authorization`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `hasGlobalAdministratorAssignment` (línea 284)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `assignedSiteIds` (línea 296)
  - **Responsabilidad:** Ejecuta la operación assignedSiteIds definida por la clase DashboardGlobalPedidosController.
  - **Parámetros principales:** `User $user, ServicioAutorizacion $authorization`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `hasSite` (línea 324)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `hasChannel` (línea 334)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `safeExportText` (línea 348)
  - **Responsabilidad:** Ejecuta la operación safeExportText definida por la clase DashboardGlobalPedidosController.
  - **Parámetros principales:** `string $value, string $format`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Paneles/Administrador/DashboardGlobalSedesController.php`

#### Clase: `DashboardGlobalSedesController`

- **Método/función:** `index` (línea 34)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $authorization`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 65)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, string $format, ExportadorTabular $exporter, ServicioAutorizacion $authorization, RegistradorAuditoria $auditor`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filters` (línea 120)
  - **Responsabilidad:** Ejecuta la operación filters definida por la clase DashboardGlobalSedesController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `sites` (línea 136)
  - **Responsabilidad:** Ejecuta la operación sites definida por la clase DashboardGlobalSedesController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `results` (línea 146)
  - **Responsabilidad:** Ejecuta la operación results definida por la clase DashboardGlobalSedesController.
  - **Parámetros principales:** `array $filters, Collection $sites`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `hasSite` (línea 184)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `hasChannel` (línea 194)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Paneles/Administrador/DashboardGlobalVentasController.php`

#### Clase: `DashboardGlobalVentasController`

- **Método/función:** `index` (línea 45)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $authorization`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 85)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, string $format, ExportadorTabular $exporter, ServicioAutorizacion $authorization, RegistradorAuditoria $auditor`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `filters` (línea 160)
  - **Responsabilidad:** Ejecuta la operación filters definida por la clase DashboardGlobalVentasController.
  - **Parámetros principales:** `Request $request, ServicioAutorizacion $authorization`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `orders` (línea 195)
  - **Responsabilidad:** Ejecuta la operación orders definida por la clase DashboardGlobalVentasController.
  - **Parámetros principales:** `User $user, array $filters, ServicioAutorizacion $authorization`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `authorizeDashboard` (línea 240)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `User $user, ServicioAutorizacion $authorization`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `availableSites` (línea 247)
  - **Responsabilidad:** Ejecuta la operación availableSites definida por la clase DashboardGlobalVentasController.
  - **Parámetros principales:** `User $user, ServicioAutorizacion $authorization`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `hasGlobalAdministratorAssignment` (línea 273)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `assignedSiteIds` (línea 285)
  - **Responsabilidad:** Ejecuta la operación assignedSiteIds definida por la clase DashboardGlobalVentasController.
  - **Parámetros principales:** `User $user, ServicioAutorizacion $authorization`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `hasSite` (línea 314)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `hasChannel` (línea 324)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `safeExportText` (línea 338)
  - **Responsabilidad:** Ejecuta la operación safeExportText definida por la clase DashboardGlobalVentasController.
  - **Parámetros principales:** `string $value, string $format`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Paneles/Administrador/DispositivosController.php`

#### Clase: `DispositivosController`

- **Método/función:** `__construct` (línea 24)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioDispositivos $dispositivos`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `mostrar` (línea 33)
  - **Responsabilidad:** Ejecuta la operación mostrar definida por la clase DispositivosController.
  - **Parámetros principales:** `ListadoDispositivosRequest $solicitud`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Paneles/Administrador/EnviosPedidosController.php`

#### Clase: `EnviosPedidosController`

- **Método/función:** `__construct` (línea 34)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioEnviosPedidos $envios, private readonly ServicioAutorizacion $autorizacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `mostrar` (línea 43)
  - **Responsabilidad:** Ejecuta la operación mostrar definida por la clase EnviosPedidosController.
  - **Parámetros principales:** `ListadoEnviosPedidosRequest $solicitud`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `actualizarEstado` (línea 60)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarEstadoEnvioPedidoRequest $solicitud, EnvioPedido $envioPedido`
  - **Resultado:** `\Illuminate\Http\RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `detalle` (línea 76)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `Request $request, EnvioPedido $envio`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `exportar` (línea 99)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `ListadoEnviosPedidosRequest $solicitud, ExportadorTabular $exporter, RegistradorAuditoria $audit, string $format = 'csv'`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Paneles/ClienteMayorista

### Archivo: `app/Http/Controllers/Paneles/ClienteMayorista/DashboardController.php`

#### Clase: `DashboardController`

- **Método/función:** `role` (línea 19)
  - **Responsabilidad:** Ejecuta la operación role definida por la clase DashboardController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `viewName` (línea 29)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Paneles/ClienteMinorista

### Archivo: `app/Http/Controllers/Paneles/ClienteMinorista/DashboardController.php`

#### Clase: `DashboardController`

- **Método/función:** `role` (línea 19)
  - **Responsabilidad:** Ejecuta la operación role definida por la clase DashboardController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `viewName` (línea 29)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Paneles/ComprasLogistica

### Archivo: `app/Http/Controllers/Paneles/ComprasLogistica/DashboardController.php`

#### Clase: `DashboardController`

- **Método/función:** `role` (línea 19)
  - **Responsabilidad:** Ejecuta la operación role definida por la clase DashboardController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `viewName` (línea 29)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Paneles/Marketing

### Archivo: `app/Http/Controllers/Paneles/Marketing/DashboardController.php`

#### Clase: `DashboardController`

- **Método/función:** `role` (línea 19)
  - **Responsabilidad:** Ejecuta la operación role definida por la clase DashboardController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `viewName` (línea 29)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Paneles/VentasMayoristas

### Archivo: `app/Http/Controllers/Paneles/VentasMayoristas/DashboardController.php`

#### Clase: `DashboardController`

- **Método/función:** `role` (línea 19)
  - **Responsabilidad:** Ejecuta la operación role definida por la clase DashboardController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `viewName` (línea 29)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Paneles/VentasMinoristas

### Archivo: `app/Http/Controllers/Paneles/VentasMinoristas/DashboardController.php`

#### Clase: `DashboardController`

- **Método/función:** `role` (línea 19)
  - **Responsabilidad:** Ejecuta la operación role definida por la clase DashboardController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `viewName` (línea 29)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Publico/Asistente

### Archivo: `app/Http/Controllers/Publico/Asistente/AsistenteController.php`

#### Clase: `AsistenteController`

- **Método/función:** `__construct` (línea 29)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 39)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `ask` (línea 51)
  - **Responsabilidad:** Ejecuta la operación ask definida por la clase AsistenteController.
  - **Parámetros principales:** `AsistenteRequest $request`
  - **Resultado:** `JsonResponse|RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Publico/Catalogo

### Archivo: `app/Http/Controllers/Publico/Catalogo/CatalogoController.php`

#### Clase: `CatalogoController`

- **Método/función:** `index` (línea 28)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `FiltroCatalogoRequest $request, ServicioCatalogo $catalogService`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `show` (línea 53)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `Request $request, Producto $product, ServicioCatalogo $catalogService`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Publico/Catalogo/ComparadorController.php`

#### Clase: `ComparadorController`

- **Método/función:** `index` (línea 24)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `ComparacionRequest $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Publico/Catalogo/RecomendacionController.php`

#### Clase: `RecomendacionController`

- **Método/función:** `__construct` (línea 24)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioRecomendaciones $recommendationService`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 33)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `RecomendacionRequest $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Publico/Comercio

### Archivo: `app/Http/Controllers/Publico/Comercio/CampaniaPublicaController.php`

#### Clase: `CampaniaPublicaController`

- **Método/función:** `track` (línea 28)
  - **Responsabilidad:** Ejecuta la operación track definida por la clase CampaniaPublicaController.
  - **Parámetros principales:** `Request $request, Campania $campaign`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Publico/Comercio/CarritoController.php`

#### Clase: `CarritoController`

- **Método/función:** `__construct` (línea 30)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 42)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `add` (línea 70)
  - **Responsabilidad:** Ejecuta la operación add definida por la clase CarritoController.
  - **Parámetros principales:** `AgregarCarritoRequest $request, Producto $product`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `update` (línea 95)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarCarritoRequest $request, Producto $product`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `remove` (línea 123)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Request $request, Producto $product`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `clear` (línea 139)
  - **Responsabilidad:** Ejecuta la operación clear definida por la clase CarritoController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updateLegacy` (línea 153)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `applyCoupon` (línea 181)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `AplicarCuponRequest $request`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Publico/Comercio/FinalizarCompraController.php`

#### Clase: `FinalizarCompraController`

- **Método/función:** `__construct` (línea 26)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 38)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `store` (línea 78)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `FinalizarCompraRequest $request`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

### Archivo: `app/Http/Controllers/Publico/Comercio/PedidoController.php`

#### Clase: `PedidoController`

- **Método/función:** `__construct` (línea 32)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `index` (línea 44)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `adminIndex` (línea 83)
  - **Responsabilidad:** Ejecuta la operación adminIndex definida por la clase PedidoController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `export` (línea 150)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `Request $request, string $format, ExportadorTabular $exportador, RegistradorAuditoria $auditoria`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `show` (línea 194)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `Pedido $order`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `updateStatus` (línea 211)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ActualizarEstadoPedidoRequest $request, Pedido $order`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `adminOrderQuery` (línea 224)
  - **Responsabilidad:** Ejecuta la operación adminOrderQuery definida por la clase PedidoController.
  - **Parámetros principales:** `Request $request, array $filters`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `adminFilters` (línea 244)
  - **Responsabilidad:** Ejecuta la operación adminFilters definida por la clase PedidoController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `countStatuses` (línea 274)
  - **Responsabilidad:** Ejecuta la operación countStatuses definida por la clase PedidoController.
  - **Parámetros principales:** `Collection $statusCounts, array $statuses`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `statusSummary` (línea 283)
  - **Responsabilidad:** Ejecuta la operación statusSummary definida por la clase PedidoController.
  - **Parámetros principales:** `Collection $statusCounts, int $totalOrders`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `customerSummary` (línea 306)
  - **Responsabilidad:** Ejecuta la operación customerSummary definida por la clase PedidoController.
  - **Parámetros principales:** `Request $request, int $totalOrders`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `scopedOrders` (línea 325)
  - **Responsabilidad:** Ejecuta la operación scopedOrders definida por la clase PedidoController.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `trendPercent` (línea 339)
  - **Responsabilidad:** Ejecuta la operación trendPercent definida por la clase PedidoController.
  - **Parámetros principales:** `int $current, int $previous`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Publico/Inicio

### Archivo: `app/Http/Controllers/Publico/Inicio/InicioController.php`

#### Clase: `InicioController`

- **Método/función:** `__invoke` (línea 27)
  - **Responsabilidad:** Ejecuta la operación __invoke definida por la clase InicioController.
  - **Parámetros principales:** `ServicioCatalogo $catalogo`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `categoryId` (línea 70)
  - **Responsabilidad:** Ejecuta la operación categoryId definida por la clase InicioController.
  - **Parámetros principales:** `Collection $categories, string $needle`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Publico/Inteligente

### Archivo: `app/Http/Controllers/Publico/Inteligente/InteligenciaComercialController.php`

#### Clase: `InteligenciaComercialController`

- **Método/función:** `__construct` (línea 24)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioInteligenciaComercial $commerce`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `optimizer` (línea 33)
  - **Responsabilidad:** Ejecuta la operación optimizer definida por la clase InteligenciaComercialController.
  - **Parámetros principales:** `OptimizadorInteligenteRequest $request`
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `wholesale` (línea 50)
  - **Responsabilidad:** Ejecuta la operación wholesale definida por la clase InteligenciaComercialController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Controllers/Publico/Paginas

### Archivo: `app/Http/Controllers/Publico/Paginas/PaginaController.php`

#### Clase: `PaginaController`

- **Método/función:** `__construct` (línea 25)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioContacto $contactService`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `about` (línea 32)
  - **Responsabilidad:** Ejecuta la operación about definida por la clase PaginaController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `contact` (línea 42)
  - **Responsabilidad:** Ejecuta la operación contact definida por la clase PaginaController.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `View` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.
- **Método/función:** `sendContact` (línea 54)
  - **Responsabilidad:** Ejecuta la operación sendContact definida por la clase PaginaController.
  - **Parámetros principales:** `GuardarMensajeContactoRequest $request`
  - **Resultado:** `RedirectResponse` según la firma observada.
  - **Capa arquitectónica:** Presentación / HTTP
  - **Relación:** Recibe la solicitud, invoca validación/servicios y devuelve vista, redirección o recurso.

## Módulo: app/Http/Middleware

### Archivo: `app/Http/Middleware/AsegurarRol.php`

#### Clase: `AsegurarRol`

- **Método/función:** `__construct` (línea 24)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioAutorizacion $autorizacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Seguridad / Middleware
  - **Relación:** Filtra la solicitud antes del controlador y aplica autenticación, contexto o permisos.
- **Método/función:** `handle` (línea 37)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** `Request $request, Closure $next, string ...$roles`
  - **Resultado:** `Response` según la firma observada.
  - **Capa arquitectónica:** Seguridad / Middleware
  - **Relación:** Filtra la solicitud antes del controlador y aplica autenticación, contexto o permisos.

### Archivo: `app/Http/Middleware/EstablecerContextoAuditoria.php`

#### Clase: `EstablecerContextoAuditoria`

- **Método/función:** `__construct` (línea 27)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioAutorizacion $autorizacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Seguridad / Middleware
  - **Relación:** Filtra la solicitud antes del controlador y aplica autenticación, contexto o permisos.
- **Método/función:** `handle` (línea 38)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** `Request $request, Closure $next`
  - **Resultado:** `Response` según la firma observada.
  - **Capa arquitectónica:** Seguridad / Middleware
  - **Relación:** Filtra la solicitud antes del controlador y aplica autenticación, contexto o permisos.

### Archivo: `app/Http/Middleware/RegistrarSesionUsuario.php`

#### Clase: `RegistrarSesionUsuario`

- **Método/función:** `handle` (línea 23)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** `Request $request, Closure $next`
  - **Resultado:** `Response` según la firma observada.
  - **Capa arquitectónica:** Seguridad / Middleware
  - **Relación:** Filtra la solicitud antes del controlador y aplica autenticación, contexto o permisos.

### Archivo: `app/Http/Middleware/VerificarCuentaActiva.php`

#### Clase: `VerificarCuentaActiva`

- **Método/función:** `handle` (línea 23)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** `Request $request, Closure $next`
  - **Resultado:** `Response` según la firma observada.
  - **Capa arquitectónica:** Seguridad / Middleware
  - **Relación:** Filtra la solicitud antes del controlador y aplica autenticación, contexto o permisos.

### Archivo: `app/Http/Middleware/VerificarPermiso.php`

#### Clase: `VerificarPermiso`

- **Método/función:** `__construct` (línea 25)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioAutorizacion $autorizacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Seguridad / Middleware
  - **Relación:** Filtra la solicitud antes del controlador y aplica autenticación, contexto o permisos.
- **Método/función:** `handle` (línea 38)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** `Request $request, Closure $next, string ...$permissions`
  - **Resultado:** `Response` según la firma observada.
  - **Capa arquitectónica:** Seguridad / Middleware
  - **Relación:** Filtra la solicitud antes del controlador y aplica autenticación, contexto o permisos.

## Módulo: app/Http/Requests

### Archivo: `app/Http/Requests/AbrirSesionCajaRequest.php`

#### Clase: `AbrirSesionCajaRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 41)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarAvatarRequest.php`

#### Clase: `ActualizarAvatarRequest`

- **Método/función:** `authorize` (línea 18)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 28)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarCajaRequest.php`

#### Clase: `ActualizarCajaRequest`

- **Método/función:** `rules` (línea 16)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 28)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarCampaniaRequest.php`

#### Clase: `ActualizarCampaniaRequest`

- **Método/función:** `rules` (línea 17)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarCarritoRequest.php`

#### Clase: `ActualizarCarritoRequest`

- **Método/función:** `authorize` (línea 19)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 29)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarCasoSoporteRequest.php`

#### Clase: `ActualizarCasoSoporteRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarCategoriaRequest.php`

#### Clase: `ActualizarCategoriaRequest`

- **Método/función:** `rules` (línea 19)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarClienteRequest.php`

#### Clase: `ActualizarClienteRequest`

- **Método/función:** `rules` (línea 19)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarContrasenaRequest.php`

#### Clase: `ActualizarContrasenaRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarCotizacionRequest.php`

#### Clase: `ActualizarCotizacionRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarCuponRequest.php`

#### Clase: `ActualizarCuponRequest`

- **Método/función:** `authorize` (línea 23)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 33)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `attributes` (línea 54)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 67)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarDevolucionGarantiaRequest.php`

#### Clase: `ActualizarDevolucionGarantiaRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarEmpresaRequest.php`

#### Clase: `ActualizarEmpresaRequest`

- **Método/función:** `authorize` (línea 22)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 33)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 51)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarEstadoEnvioPedidoRequest.php`

#### Clase: `ActualizarEstadoEnvioPedidoRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarEstadoPedidoRequest.php`

#### Clase: `ActualizarEstadoPedidoRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 32)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarIntegracionRequest.php`

#### Clase: `ActualizarIntegracionRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarPerfilRequest.php`

#### Clase: `ActualizarPerfilRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarPermisosRolRequest.php`

#### Clase: `ActualizarPermisosRolRequest`

- **Método/función:** `authorize` (línea 19)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarPreferenciasRequest.php`

#### Clase: `ActualizarPreferenciasRequest`

- **Método/función:** `authorize` (línea 18)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 28)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarProductoRequest.php`

#### Clase: `ActualizarProductoRequest`

- **Método/función:** `rules` (línea 17)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarProveedorRequest.php`

#### Clase: `ActualizarProveedorRequest`

- **Método/función:** `rules` (línea 19)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarRolRequest.php`

#### Clase: `ActualizarRolRequest`

- **Método/función:** `authorize` (línea 22)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 33)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarSegmentoClienteRequest.php`

#### Clase: `ActualizarSegmentoClienteRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarUsuarioRequest.php`

#### Clase: `ActualizarUsuarioRequest`

- **Método/función:** `authorize` (línea 23)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 34)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ActualizarVentaRequest.php`

#### Clase: `ActualizarVentaRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/AgregarCarritoRequest.php`

#### Clase: `AgregarCarritoRequest`

- **Método/función:** `authorize` (línea 19)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 29)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/AjustarInventarioRequest.php`

#### Clase: `AjustarInventarioRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/AnularOperacionFinancieraRequest.php`

#### Clase: `AnularOperacionFinancieraRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 28)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 34)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/AnularVentaRequest.php`

#### Clase: `AnularVentaRequest`

- **Método/función:** `authorize` (línea 19)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 29)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/AplicarCobroCuentaRequest.php`

#### Clase: `AplicarCobroCuentaRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 29)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/AplicarCuponRequest.php`

#### Clase: `AplicarCuponRequest`

- **Método/función:** `authorize` (línea 19)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 29)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/AplicarPagoCuentaRequest.php`

#### Clase: `AplicarPagoCuentaRequest`

- **Método/función:** `authorize` (línea 19)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 29)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/AprobarReembolsoRequest.php`

#### Clase: `AprobarReembolsoRequest`

- **Método/función:** `authorize` (línea 23)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 36)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/AsignarRolUsuarioRequest.php`

#### Clase: `AsignarRolUsuarioRequest`

- **Método/función:** `authorize` (línea 19)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/AsistenteRequest.php`

#### Clase: `AsistenteRequest`

- **Método/función:** `prepareForValidation` (línea 19)
  - **Responsabilidad:** Ejecuta la operación prepareForValidation definida por la clase AsistenteRequest.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `authorize` (línea 31)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 41)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/CambiarEstadoAlmacenRequest.php`

#### Clase: `CambiarEstadoAlmacenRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 40)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/CambiarEstadoCajaRequest.php`

#### Clase: `CambiarEstadoCajaRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 37)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/CambiarEstadoSedeRequest.php`

#### Clase: `CambiarEstadoSedeRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 32)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 41)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/CambiarEstadoUsuarioRequest.php`

#### Clase: `CambiarEstadoUsuarioRequest`

- **Método/función:** `authorize` (línea 18)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 29)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/CerrarSesionCajaRequest.php`

#### Clase: `CerrarSesionCajaRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 40)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ComparacionRequest.php`

#### Clase: `ComparacionRequest`

- **Método/función:** `authorize` (línea 19)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 29)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ConfirmarPagoEfectivoRequest.php`

#### Clase: `ConfirmarPagoEfectivoRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 29)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 38)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ConfirmarVentaCotizacionRequest.php`

#### Clase: `ConfirmarVentaCotizacionRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 40)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ConvertirCotizacionRequest.php`

#### Clase: `ConvertirCotizacionRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/CrearCuentaPorPagarRequest.php`

#### Clase: `CrearCuentaPorPagarRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/DesactivarCuponRequest.php`

#### Clase: `DesactivarCuponRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 36)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/FiltroAlmacenesRequest.php`

#### Clase: `FiltroAlmacenesRequest`

- **Método/función:** `authorize` (línea 22)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 32)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/FiltroCajasRequest.php`

#### Clase: `FiltroCajasRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 41)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/FiltroCatalogoRequest.php`

#### Clase: `FiltroCatalogoRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/FiltroPagosRequest.php`

#### Clase: `FiltroPagosRequest`

- **Método/función:** `authorize` (línea 23)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `attributes` (línea 42)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 54)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/FiltroReembolsosRequest.php`

#### Clase: `FiltroReembolsosRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 28)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `attributes` (línea 39)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 50)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/FiltroReporteRequest.php`

#### Clase: `FiltroReporteRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/FiltroSedesRequest.php`

#### Clase: `FiltroSedesRequest`

- **Método/función:** `authorize` (línea 22)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 33)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/FiltroSesionesCajaRequest.php`

#### Clase: `FiltroSesionesCajaRequest`

- **Método/función:** `authorize` (línea 23)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 33)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 45)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/FiltroVentaRequest.php`

#### Clase: `FiltroVentaRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `attributes` (línea 45)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 54)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/FinalizarCompraRequest.php`

#### Clase: `FinalizarCompraRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarAlmacenRequest.php`

#### Clase: `GuardarAlmacenRequest`

- **Método/función:** `authorize` (línea 23)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 33)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 53)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarCajaRequest.php`

#### Clase: `GuardarCajaRequest`

- **Método/función:** `authorize` (línea 23)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 33)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 44)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarCampaniaRequest.php`

#### Clase: `GuardarCampaniaRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarCasoSoporteRequest.php`

#### Clase: `GuardarCasoSoporteRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarCategoriaRequest.php`

#### Clase: `GuardarCategoriaRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarClienteRequest.php`

#### Clase: `GuardarClienteRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarCompraRequest.php`

#### Clase: `GuardarCompraRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarConfiguracionTiendaRequest.php`

#### Clase: `GuardarConfiguracionTiendaRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarCotizacionRequest.php`

#### Clase: `GuardarCotizacionRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarCuponRequest.php`

#### Clase: `GuardarCuponRequest`

- **Método/función:** `authorize` (línea 22)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 32)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `attributes` (línea 51)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 65)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarDevolucionGarantiaRequest.php`

#### Clase: `GuardarDevolucionGarantiaRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 32)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarImagenVarianteRequest.php`

#### Clase: `GuardarImagenVarianteRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarIntegracionRequest.php`

#### Clase: `GuardarIntegracionRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarMensajeContactoRequest.php`

#### Clase: `GuardarMensajeContactoRequest`

- **Método/función:** `authorize` (línea 19)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 29)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarPrecioMayoristaRequest.php`

#### Clase: `GuardarPrecioMayoristaRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarProductoRequest.php`

#### Clase: `GuardarProductoRequest`

- **Método/función:** `authorize` (línea 23)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 33)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarProveedorRequest.php`

#### Clase: `GuardarProveedorRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarRolRequest.php`

#### Clase: `GuardarRolRequest`

- **Método/función:** `authorize` (línea 19)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 45)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarSedeRequest.php`

#### Clase: `GuardarSedeRequest`

- **Método/función:** `authorize` (línea 23)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 34)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 55)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarSegmentoClienteRequest.php`

#### Clase: `GuardarSegmentoClienteRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarUsuarioRequest.php`

#### Clase: `GuardarUsuarioRequest`

- **Método/función:** `authorize` (línea 24)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 35)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `attributes` (línea 60)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 81)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `availableRoles` (línea 114)
  - **Responsabilidad:** Ejecuta la operación availableRoles definida por la clase GuardarUsuarioRequest.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `isCustomerRole` (línea 130)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarVarianteProductoRequest.php`

#### Clase: `GuardarVarianteProductoRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `prepareForValidation` (línea 53)
  - **Responsabilidad:** Ejecuta la operación prepareForValidation definida por la clase GuardarVarianteProductoRequest.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/GuardarVentaRequest.php`

#### Clase: `GuardarVentaRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ImportarCampaniasRequest.php`

#### Clase: `ImportarCampaniasRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ImportarClientesRequest.php`

#### Clase: `ImportarClientesRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 43)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ImportarComprasRequest.php`

#### Clase: `ImportarComprasRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ImportarPreciosMayoristasRequest.php`

#### Clase: `ImportarPreciosMayoristasRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/InicioSesionRequest.php`

#### Clase: `InicioSesionRequest`

- **Método/función:** `authorize` (línea 19)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 29)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ListadoDispositivosRequest.php`

#### Clase: `ListadoDispositivosRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `attributes` (línea 41)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 52)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/ListadoEnviosPedidosRequest.php`

#### Clase: `ListadoEnviosPedidosRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `attributes` (línea 40)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 50)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/OptimizadorInteligenteRequest.php`

#### Clase: `OptimizadorInteligenteRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/RecomendacionRequest.php`

#### Clase: `RecomendacionRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/RegistrarEnvioCotizacionRequest.php`

#### Clase: `RegistrarEnvioCotizacionRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/RegistrarMovimientoCajaRequest.php`

#### Clase: `RegistrarMovimientoCajaRequest`

- **Método/función:** `authorize` (línea 23)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 33)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 44)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/RegistrarPagoRequest.php`

#### Clase: `RegistrarPagoRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 28)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 42)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/RegistrarRecepcionParcialCompraRequest.php`

#### Clase: `RegistrarRecepcionParcialCompraRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/RegistroRequest.php`

#### Clase: `RegistroRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/SolicitarReembolsoRequest.php`

#### Clase: `SolicitarReembolsoRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 28)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 38)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

## Módulo: app/Http/Requests/Gestion

### Archivo: `app/Http/Requests/Gestion/FiltroPersonalSedeRequest.php`

#### Clase: `FiltroPersonalSedeRequest`

- **Método/función:** `authorize` (línea 22)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 33)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

## Módulo: app/Http/Requests/Gestion/Inventario

### Archivo: `app/Http/Requests/Gestion/Inventario/AccionTransferenciaExistenciaRequest.php`

#### Clase: `AccionTransferenciaExistenciaRequest`

- **Método/función:** `authorize` (línea 20)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 30)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `attributes` (línea 43)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 52)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

### Archivo: `app/Http/Requests/Gestion/Inventario/GuardarTransferenciaExistenciaRequest.php`

#### Clase: `GuardarTransferenciaExistenciaRequest`

- **Método/función:** `authorize` (línea 21)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 31)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `withValidator` (línea 52)
  - **Responsabilidad:** Ejecuta la operación withValidator definida por la clase GuardarTransferenciaExistenciaRequest.
  - **Parámetros principales:** `Validator $validator`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `attributes` (línea 77)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `messages` (línea 88)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

## Módulo: app/Http/Requests/Gestion/Organizacion

### Archivo: `app/Http/Requests/Gestion/Organizacion/GuardarResponsableOrganizacionalRequest.php`

#### Clase: `GuardarResponsableOrganizacionalRequest`

- **Método/función:** `authorize` (línea 22)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.
- **Método/función:** `rules` (línea 29)
  - **Responsabilidad:** Define la configuración de validación utilizada por el componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Entrada / Validación
  - **Relación:** Proporciona datos validados y autorización de entrada al controlador o servicio.

## Módulo: app/Http/Resources

### Archivo: `app/Http/Resources/RecursoProducto.php`

#### Clase: `RecursoProducto`

- **Método/función:** `toArray` (línea 17)
  - **Responsabilidad:** Transforma la información interna al formato requerido por la capa consumidora.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: app/Infraestructura/Persistencia/Pdo

### Archivo: `app/Infraestructura/Persistencia/Pdo/PdoConexion.php`

#### Clase: `PdoConexion`

- **Método/función:** `__construct` (línea 23)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly PDO $pdo`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `ejecutarEnTransaccion` (línea 37)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** `Closure $operacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `preparar` (línea 67)
  - **Responsabilidad:** Ejecuta la operación preparar definida por la clase PdoConexion.
  - **Parámetros principales:** `string $sql`
  - **Resultado:** `\PDOStatement` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `ultimoId` (línea 77)
  - **Responsabilidad:** Ejecuta la operación ultimoId definida por la clase PdoConexion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.

### Archivo: `app/Infraestructura/Persistencia/Pdo/PdoProductoDao.php`

#### Clase: `PdoProductoDao`

- **Método/función:** `__construct` (línea 27)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly PdoConexion $conexion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `enTransaccion` (línea 36)
  - **Responsabilidad:** Ejecuta la operación indicada dentro del contexto transaccional establecido.
  - **Parámetros principales:** `Closure $operacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `buscarPorId` (línea 50)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `int $id, bool $incluirVariantes = true`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `crear` (línea 77)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `ProductoEntidad $producto`
  - **Resultado:** `ProductoEntidad` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `actualizar` (línea 104)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `ProductoEntidad $producto`
  - **Resultado:** `ProductoEntidad` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `crearVariante` (línea 133)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `VarianteProductoEntidad $variante`
  - **Resultado:** `VarianteProductoEntidad` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `actualizarVariante` (línea 161)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `VarianteProductoEntidad $variante`
  - **Resultado:** `VarianteProductoEntidad` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `eliminarVariante` (línea 187)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `int $id`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `eliminar` (línea 202)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `int $id`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `sincronizarImagenPortada` (línea 219)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `int $productoId, string $rutaImagen`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `buscarVariantes` (línea 255)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `int $productoId`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `buscarVariantePorId` (línea 274)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `int $id`
  - **Resultado:** `VarianteProductoEntidad` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `validarProducto` (línea 297)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `ProductoEntidad $producto`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.
- **Método/función:** `validarVariante` (línea 311)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `VarianteProductoEntidad $variante`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Infraestructura / Persistencia DAO
  - **Relación:** Ejecuta persistencia preparada, mapeo y transacciones sobre MariaDB.

## Módulo: app/Models

### Archivo: `app/Models/User.php`

#### Clase: `User`

- **Método/función:** `getAuthPasswordName` (línea 56)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `setPasswordAttribute` (línea 68)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `string $password`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `getEmailForPasswordReset` (línea 78)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `routeNotificationForMail` (línea 88)
  - **Responsabilidad:** Ejecuta la operación routeNotificationForMail definida por la clase User.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `isRole` (línea 100)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `string $role`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `roles` (línea 110)
  - **Responsabilidad:** Ejecuta la operación roles definida por la clase User.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `rolesPorSede` (línea 121)
  - **Responsabilidad:** Ejecuta la operación rolesPorSede definida por la clase User.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `rolesVigentes` (línea 132)
  - **Responsabilidad:** Ejecuta la operación rolesVigentes definida por la clase User.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `rolesPorSedeVigentes` (línea 146)
  - **Responsabilidad:** Ejecuta la operación rolesPorSedeVigentes definida por la clase User.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `customer` (línea 167)
  - **Responsabilidad:** Ejecuta la operación customer definida por la clase User.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasOne` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `orders` (línea 177)
  - **Responsabilidad:** Ejecuta la operación orders definida por la clase User.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `inventoryMovements` (línea 187)
  - **Responsabilidad:** Ejecuta la operación inventoryMovements definida por la clase User.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `getRouteKeyName` (línea 197)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `getAuthIdentifierName` (línea 207)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `getEmailAttribute` (línea 217)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `setEmailAttribute` (línea 229)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `string $email`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `casts` (línea 239)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase User.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

## Módulo: app/Models/Auditoria

### Archivo: `app/Models/Auditoria/RegistroAuditoria.php`

#### Clase: `RegistroAuditoria`

- **Método/función:** `casts` (línea 34)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase RegistroAuditoria.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `user` (línea 50)
  - **Responsabilidad:** Ejecuta la operación user definida por la clase RegistroAuditoria.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

## Módulo: app/Models/Catalogo

### Archivo: `app/Models/Catalogo/CaracteristicaProducto.php`

#### Clase: `CaracteristicaProducto`

- **Método/función:** `product` (línea 26)
  - **Responsabilidad:** Ejecuta la operación product definida por la clase CaracteristicaProducto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Catalogo/Categoria.php`

#### Clase: `Categoria`

- **Método/función:** `newFactory` (línea 23)
  - **Responsabilidad:** Ejecuta la operación newFactory definida por la clase Categoria.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Factory` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `casts` (línea 42)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Categoria.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `products` (línea 52)
  - **Responsabilidad:** Ejecuta la operación products definida por la clase Categoria.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Catalogo/ImagenProducto.php`

#### Clase: `ImagenProducto`

- **Método/función:** `casts` (línea 26)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase ImagenProducto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `product` (línea 36)
  - **Responsabilidad:** Ejecuta la operación product definida por la clase ImagenProducto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Catalogo/ImagenVariante.php`

#### Clase: `ImagenVariante`

- **Método/función:** `casts` (línea 26)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase ImagenVariante.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `variant` (línea 36)
  - **Responsabilidad:** Ejecuta la operación variant definida por la clase ImagenVariante.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Catalogo/PerfilInteligenciaProducto.php`

#### Clase: `PerfilInteligenciaProducto`

- **Método/función:** `casts` (línea 30)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase PerfilInteligenciaProducto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `product` (línea 40)
  - **Responsabilidad:** Ejecuta la operación product definida por la clase PerfilInteligenciaProducto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Catalogo/Producto.php`

#### Clase: `Producto`

- **Método/función:** `newFactory` (línea 31)
  - **Responsabilidad:** Ejecuta la operación newFactory definida por la clase Producto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Factory` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `casts` (línea 51)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Producto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `scopeActive` (línea 69)
  - **Responsabilidad:** Ejecuta la operación scopeActive definida por la clase Producto.
  - **Parámetros principales:** `Builder $query`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `category` (línea 79)
  - **Responsabilidad:** Ejecuta la operación category definida por la clase Producto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `images` (línea 89)
  - **Responsabilidad:** Ejecuta la operación images definida por la clase Producto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `features` (línea 99)
  - **Responsabilidad:** Ejecuta la operación features definida por la clase Producto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `variants` (línea 109)
  - **Responsabilidad:** Ejecuta la operación variants definida por la clase Producto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `intelligenceProfile` (línea 119)
  - **Responsabilidad:** Ejecuta la operación intelligenceProfile definida por la clase Producto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasOne` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `suppliers` (línea 129)
  - **Responsabilidad:** Ejecuta la operación suppliers definida por la clase Producto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `getEffectivePriceAttribute` (línea 139)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `getUrlImagenPrincipalAttribute` (línea 149)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `urlPublicaDeImagen` (línea 166)
  - **Responsabilidad:** Ejecuta la operación urlPublicaDeImagen definida por la clase Producto.
  - **Parámetros principales:** `?string $rutaImagen`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Catalogo/VarianteProducto.php`

#### Clase: `VarianteProducto`

- **Método/función:** `casts` (línea 27)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase VarianteProducto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `product` (línea 37)
  - **Responsabilidad:** Ejecuta la operación product definida por la clase VarianteProducto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `images` (línea 47)
  - **Responsabilidad:** Ejecuta la operación images definida por la clase VarianteProducto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

## Módulo: app/Models/Clientes

### Archivo: `app/Models/Clientes/Cliente.php`

#### Clase: `Cliente`

- **Método/función:** `casts` (línea 32)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Cliente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `user` (línea 42)
  - **Responsabilidad:** Ejecuta la operación user definida por la clase Cliente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `quotations` (línea 52)
  - **Responsabilidad:** Ejecuta la operación quotations definida por la clase Cliente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

## Módulo: app/Models/Comercio

### Archivo: `app/Models/Comercio/CasoSoporte.php`

#### Clase: `CasoSoporte`

- **Método/función:** `casts` (línea 32)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase CasoSoporte.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `cliente` (línea 42)
  - **Responsabilidad:** Ejecuta la operación cliente definida por la clase CasoSoporte.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `sede` (línea 52)
  - **Responsabilidad:** Ejecuta la operación sede definida por la clase CasoSoporte.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `asignado` (línea 62)
  - **Responsabilidad:** Ejecuta la operación asignado definida por la clase CasoSoporte.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/ConfiguracionTienda.php`

#### Clase: `ConfiguracionTienda`

- **Método/función:** `casts` (línea 27)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase ConfiguracionTienda.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/Cotizacion.php`

#### Clase: `Cotizacion`

- **Método/función:** `casts` (línea 29)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Cotizacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `customer` (línea 39)
  - **Responsabilidad:** Ejecuta la operación customer definida por la clase Cotizacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `creator` (línea 49)
  - **Responsabilidad:** Ejecuta la operación creator definida por la clase Cotizacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `details` (línea 59)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/Cupon.php`

#### Clase: `Cupon`

- **Método/función:** `casts` (línea 30)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Cupon.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/DetalleCotizacion.php`

#### Clase: `DetalleCotizacion`

- **Método/función:** `casts` (línea 27)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase DetalleCotizacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `quotation` (línea 37)
  - **Responsabilidad:** Ejecuta la operación quotation definida por la clase DetalleCotizacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `product` (línea 47)
  - **Responsabilidad:** Ejecuta la operación product definida por la clase DetalleCotizacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/DetallePedido.php`

#### Clase: `DetallePedido`

- **Método/función:** `casts` (línea 27)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase DetallePedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `order` (línea 37)
  - **Responsabilidad:** Ejecuta la operación order definida por la clase DetallePedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `product` (línea 47)
  - **Responsabilidad:** Ejecuta la operación product definida por la clase DetallePedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/DetalleVenta.php`

#### Clase: `DetalleVenta`

- **Método/función:** `venta` (línea 27)
  - **Responsabilidad:** Ejecuta la operación venta definida por la clase DetalleVenta.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `producto` (línea 37)
  - **Responsabilidad:** Ejecuta la operación producto definida por la clase DetalleVenta.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/DevolucionGarantia.php`

#### Clase: `DevolucionGarantia`

- **Método/función:** `casts` (línea 45)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase DevolucionGarantia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `order` (línea 60)
  - **Responsabilidad:** Ejecuta la operación order definida por la clase DevolucionGarantia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `customer` (línea 70)
  - **Responsabilidad:** Ejecuta la operación customer definida por la clase DevolucionGarantia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `product` (línea 80)
  - **Responsabilidad:** Ejecuta la operación product definida por la clase DevolucionGarantia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `sale` (línea 90)
  - **Responsabilidad:** Ejecuta la operación sale definida por la clase DevolucionGarantia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `creator` (línea 100)
  - **Responsabilidad:** Ejecuta la operación creator definida por la clase DevolucionGarantia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/EnvioPedido.php`

#### Clase: `EnvioPedido`

- **Método/función:** `casts` (línea 34)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase EnvioPedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `pedido` (línea 51)
  - **Responsabilidad:** Ejecuta la operación pedido definida por la clase EnvioPedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `almacen` (línea 61)
  - **Responsabilidad:** Ejecuta la operación almacen definida por la clase EnvioPedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `eventos` (línea 71)
  - **Responsabilidad:** Ejecuta la operación eventos definida por la clase EnvioPedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `ultimoEvento` (línea 81)
  - **Responsabilidad:** Ejecuta la operación ultimoEvento definida por la clase EnvioPedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasOne` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/EventoEnvioPedido.php`

#### Clase: `EventoEnvioPedido`

- **Método/función:** `casts` (línea 29)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase EventoEnvioPedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `envio` (línea 43)
  - **Responsabilidad:** Ejecuta la operación envio definida por la clase EventoEnvioPedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/LogisticaMayorista.php`

#### Clase: `LogisticaMayorista`

- **Método/función:** `order` (línea 37)
  - **Responsabilidad:** Ejecuta la operación order definida por la clase LogisticaMayorista.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/Pedido.php`

#### Clase: `Pedido`

- **Método/función:** `casts` (línea 29)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Pedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `user` (línea 44)
  - **Responsabilidad:** Ejecuta la operación user definida por la clase Pedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `details` (línea 54)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `wholesaleLogistics` (línea 64)
  - **Responsabilidad:** Ejecuta la operación wholesaleLogistics definida por la clase Pedido.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasOne` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/PrecioMayorista.php`

#### Clase: `PrecioMayorista`

- **Método/función:** `casts` (línea 29)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase PrecioMayorista.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comercio/Venta.php`

#### Clase: `Venta`

- **Método/función:** `casts` (línea 32)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Venta.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `cliente` (línea 42)
  - **Responsabilidad:** Ejecuta la operación cliente definida por la clase Venta.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `usuario` (línea 52)
  - **Responsabilidad:** Ejecuta la operación usuario definida por la clase Venta.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `pedido` (línea 62)
  - **Responsabilidad:** Ejecuta la operación pedido definida por la clase Venta.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `detalles` (línea 72)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `pagos` (línea 82)
  - **Responsabilidad:** Ejecuta la operación pagos definida por la clase Venta.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

## Módulo: app/Models/Compras

### Archivo: `app/Models/Compras/Compra.php`

#### Clase: `Compra`

- **Método/función:** `casts` (línea 28)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Compra.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `supplier` (línea 38)
  - **Responsabilidad:** Ejecuta la operación supplier definida por la clase Compra.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `creator` (línea 48)
  - **Responsabilidad:** Ejecuta la operación creator definida por la clase Compra.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `details` (línea 58)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Compras/DetalleCompra.php`

#### Clase: `DetalleCompra`

- **Método/función:** `casts` (línea 28)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase DetalleCompra.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `purchase` (línea 38)
  - **Responsabilidad:** Ejecuta la operación purchase definida por la clase DetalleCompra.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `product` (línea 48)
  - **Responsabilidad:** Ejecuta la operación product definida por la clase DetalleCompra.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `variante` (línea 58)
  - **Responsabilidad:** Ejecuta la operación variante definida por la clase DetalleCompra.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Compras/Proveedor.php`

#### Clase: `Proveedor`

- **Método/función:** `casts` (línea 28)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Proveedor.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `products` (línea 38)
  - **Responsabilidad:** Ejecuta la operación products definida por la clase Proveedor.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `purchases` (línea 48)
  - **Responsabilidad:** Ejecuta la operación purchases definida por la clase Proveedor.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

## Módulo: app/Models/Comunicacion

### Archivo: `app/Models/Comunicacion/MensajeContacto.php`

#### Clase: `MensajeContacto`

- **Método/función:** `casts` (línea 25)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase MensajeContacto.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Comunicacion/Notificacion.php`

#### Clase: `Notificacion`

- **Método/función:** `casts` (línea 41)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Notificacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `nombreTipo` (línea 56)
  - **Responsabilidad:** Ejecuta la operación nombreTipo definida por la clase Notificacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `datosLegibles` (línea 66)
  - **Responsabilidad:** Ejecuta la operación datosLegibles definida por la clase Notificacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

## Módulo: app/Models/Finanzas

### Archivo: `app/Models/Finanzas/Caja.php`

#### Clase: `Caja`

- **Método/función:** `casts` (línea 33)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Caja.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `sede` (línea 43)
  - **Responsabilidad:** Ejecuta la operación sede definida por la clase Caja.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `sesiones` (línea 53)
  - **Responsabilidad:** Ejecuta la operación sesiones definida por la clase Caja.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Finanzas/CuentaBancaria.php`

#### Clase: `CuentaBancaria`

- **Método/función:** `casts` (línea 37)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase CuentaBancaria.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `empresa` (línea 47)
  - **Responsabilidad:** Ejecuta la operación empresa definida por la clase CuentaBancaria.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Finanzas/MedioPago.php`

#### Clase: `MedioPago`

- **Método/función:** `casts` (línea 31)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase MedioPago.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `pagos` (línea 45)
  - **Responsabilidad:** Ejecuta la operación pagos definida por la clase MedioPago.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Finanzas/MovimientoCaja.php`

#### Clase: `MovimientoCaja`

- **Método/función:** `casts` (línea 35)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase MovimientoCaja.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `sesion` (línea 45)
  - **Responsabilidad:** Ejecuta la operación sesion definida por la clase MovimientoCaja.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `pago` (línea 55)
  - **Responsabilidad:** Ejecuta la operación pago definida por la clase MovimientoCaja.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `creador` (línea 65)
  - **Responsabilidad:** Ejecuta la operación creador definida por la clase MovimientoCaja.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Finanzas/Pago.php`

#### Clase: `Pago`

- **Método/función:** `casts` (línea 40)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Pago.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `medioPago` (línea 54)
  - **Responsabilidad:** Ejecuta la operación medioPago definida por la clase Pago.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `sesionCaja` (línea 64)
  - **Responsabilidad:** Ejecuta la operación sesionCaja definida por la clase Pago.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `cuentaBancaria` (línea 74)
  - **Responsabilidad:** Ejecuta la operación cuentaBancaria definida por la clase Pago.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `usuario` (línea 84)
  - **Responsabilidad:** Ejecuta la operación usuario definida por la clase Pago.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `pedido` (línea 94)
  - **Responsabilidad:** Ejecuta la operación pedido definida por la clase Pago.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `venta` (línea 104)
  - **Responsabilidad:** Ejecuta la operación venta definida por la clase Pago.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `compra` (línea 114)
  - **Responsabilidad:** Ejecuta la operación compra definida por la clase Pago.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `reembolso` (línea 124)
  - **Responsabilidad:** Ejecuta la operación reembolso definida por la clase Pago.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasOne` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Finanzas/Reembolso.php`

#### Clase: `Reembolso`

- **Método/función:** `casts` (línea 33)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Reembolso.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `devolucion` (línea 46)
  - **Responsabilidad:** Ejecuta la operación devolucion definida por la clase Reembolso.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `pago` (línea 56)
  - **Responsabilidad:** Ejecuta la operación pago definida por la clase Reembolso.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `autorizadoPor` (línea 66)
  - **Responsabilidad:** Ejecuta la operación autorizadoPor definida por la clase Reembolso.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Finanzas/SesionCaja.php`

#### Clase: `SesionCaja`

- **Método/función:** `casts` (línea 36)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase SesionCaja.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `caja` (línea 49)
  - **Responsabilidad:** Ejecuta la operación caja definida por la clase SesionCaja.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `usuarioApertura` (línea 59)
  - **Responsabilidad:** Ejecuta la operación usuarioApertura definida por la clase SesionCaja.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `usuarioCierre` (línea 69)
  - **Responsabilidad:** Ejecuta la operación usuarioCierre definida por la clase SesionCaja.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `movimientos` (línea 79)
  - **Responsabilidad:** Ejecuta la operación movimientos definida por la clase SesionCaja.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

## Módulo: app/Models/Inventario

### Archivo: `app/Models/Inventario/DetalleTransferenciaExistencia.php`

#### Clase: `DetalleTransferenciaExistencia`

- **Método/función:** `casts` (línea 36)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase DetalleTransferenciaExistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `transferencia` (línea 52)
  - **Responsabilidad:** Ejecuta la operación transferencia definida por la clase DetalleTransferenciaExistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `producto` (línea 62)
  - **Responsabilidad:** Ejecuta la operación producto definida por la clase DetalleTransferenciaExistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `variante` (línea 72)
  - **Responsabilidad:** Ejecuta la operación variante definida por la clase DetalleTransferenciaExistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Inventario/Dispositivo.php`

#### Clase: `Dispositivo`

- **Método/función:** `newFactory` (línea 30)
  - **Responsabilidad:** Ejecuta la operación newFactory definida por la clase Dispositivo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `DispositivoFactory` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `casts` (línea 49)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Dispositivo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `producto` (línea 62)
  - **Responsabilidad:** Ejecuta la operación producto definida por la clase Dispositivo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `variante` (línea 72)
  - **Responsabilidad:** Ejecuta la operación variante definida por la clase Dispositivo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `almacen` (línea 82)
  - **Responsabilidad:** Ejecuta la operación almacen definida por la clase Dispositivo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `movimientos` (línea 92)
  - **Responsabilidad:** Ejecuta la operación movimientos definida por la clase Dispositivo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `ultimoMovimiento` (línea 102)
  - **Responsabilidad:** Ejecuta la operación ultimoMovimiento definida por la clase Dispositivo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasOne` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `identificadorLegible` (línea 112)
  - **Responsabilidad:** Ejecuta la operación identificadorLegible definida por la clase Dispositivo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Inventario/MovimientoDispositivo.php`

#### Clase: `MovimientoDispositivo`

- **Método/función:** `casts` (línea 28)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase MovimientoDispositivo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `dispositivo` (línea 38)
  - **Responsabilidad:** Ejecuta la operación dispositivo definida por la clase MovimientoDispositivo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Inventario/MovimientoInventario.php`

#### Clase: `MovimientoInventario`

- **Método/función:** `casts` (línea 30)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase MovimientoInventario.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `product` (línea 40)
  - **Responsabilidad:** Ejecuta la operación product definida por la clase MovimientoInventario.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `user` (línea 50)
  - **Responsabilidad:** Ejecuta la operación user definida por la clase MovimientoInventario.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `almacen` (línea 60)
  - **Responsabilidad:** Ejecuta la operación almacen definida por la clase MovimientoInventario.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `variante` (línea 70)
  - **Responsabilidad:** Ejecuta la operación variante definida por la clase MovimientoInventario.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Inventario/TransferenciaExistencia.php`

#### Clase: `TransferenciaExistencia`

- **Método/función:** `casts` (línea 38)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase TransferenciaExistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `detalles` (línea 48)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `sedeOrigen` (línea 58)
  - **Responsabilidad:** Ejecuta la operación sedeOrigen definida por la clase TransferenciaExistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `sedeDestino` (línea 68)
  - **Responsabilidad:** Ejecuta la operación sedeDestino definida por la clase TransferenciaExistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `almacenOrigen` (línea 78)
  - **Responsabilidad:** Ejecuta la operación almacenOrigen definida por la clase TransferenciaExistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `almacenDestino` (línea 88)
  - **Responsabilidad:** Ejecuta la operación almacenDestino definida por la clase TransferenciaExistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `solicitante` (línea 98)
  - **Responsabilidad:** Ejecuta la operación solicitante definida por la clase TransferenciaExistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `aprobador` (línea 108)
  - **Responsabilidad:** Ejecuta la operación aprobador definida por la clase TransferenciaExistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

## Módulo: app/Models/Marketing

### Archivo: `app/Models/Marketing/Campania.php`

#### Clase: `Campania`

- **Método/función:** `casts` (línea 31)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Campania.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `segments` (línea 49)
  - **Responsabilidad:** Ejecuta la operación segments definida por la clase Campania.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `events` (línea 59)
  - **Responsabilidad:** Ejecuta la operación events definida por la clase Campania.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Marketing/EventoCampania.php`

#### Clase: `EventoCampania`

- **Método/función:** `casts` (línea 27)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase EventoCampania.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `campaign` (línea 37)
  - **Responsabilidad:** Ejecuta la operación campaign definida por la clase EventoCampania.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `user` (línea 47)
  - **Responsabilidad:** Ejecuta la operación user definida por la clase EventoCampania.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Marketing/SegmentoCliente.php`

#### Clase: `SegmentoCliente`

- **Método/función:** `casts` (línea 24)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase SegmentoCliente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `campaigns` (línea 34)
  - **Responsabilidad:** Ejecuta la operación campaigns definida por la clase SegmentoCliente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

## Módulo: app/Models/Organizacion

### Archivo: `app/Models/Organizacion/Almacen.php`

#### Clase: `Almacen`

- **Método/función:** `casts` (línea 33)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Almacen.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `sede` (línea 43)
  - **Responsabilidad:** Ejecuta la operación sede definida por la clase Almacen.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Organizacion/Empresa.php`

#### Clase: `Empresa`

- **Método/función:** `casts` (línea 33)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Empresa.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `sedes` (línea 43)
  - **Responsabilidad:** Ejecuta la operación sedes definida por la clase Empresa.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Organizacion/ResponsableOrganizacional.php`

#### Clase: `ResponsableOrganizacional`

- **Método/función:** `casts` (línea 33)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase ResponsableOrganizacional.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `responsable` (línea 43)
  - **Responsabilidad:** Ejecuta la operación responsable definida por la clase ResponsableOrganizacional.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `sede` (línea 53)
  - **Responsabilidad:** Ejecuta la operación sede definida por la clase ResponsableOrganizacional.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `almacen` (línea 63)
  - **Responsabilidad:** Ejecuta la operación almacen definida por la clase ResponsableOrganizacional.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `relevoDe` (línea 73)
  - **Responsabilidad:** Ejecuta la operación relevoDe definida por la clase ResponsableOrganizacional.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Organizacion/Sede.php`

#### Clase: `Sede`

- **Método/función:** `casts` (línea 34)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Sede.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `empresa` (línea 44)
  - **Responsabilidad:** Ejecuta la operación empresa definida por la clase Sede.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `almacenes` (línea 54)
  - **Responsabilidad:** Ejecuta la operación almacenes definida por la clase Sede.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

## Módulo: app/Models/Seguridad

### Archivo: `app/Models/Seguridad/ElementoMenu.php`

#### Clase: `ElementoMenu`

- **Método/función:** `casts` (línea 34)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase ElementoMenu.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `menu` (línea 47)
  - **Responsabilidad:** Ejecuta la operación menu definida por la clase ElementoMenu.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `padre` (línea 57)
  - **Responsabilidad:** Ejecuta la operación padre definida por la clase ElementoMenu.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `elementosHijos` (línea 67)
  - **Responsabilidad:** Ejecuta la operación elementosHijos definida por la clase ElementoMenu.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `pantalla` (línea 77)
  - **Responsabilidad:** Ejecuta la operación pantalla definida por la clase ElementoMenu.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `permiso` (línea 87)
  - **Responsabilidad:** Ejecuta la operación permiso definida por la clase ElementoMenu.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Seguridad/Menu.php`

#### Clase: `Menu`

- **Método/función:** `casts` (línea 34)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Menu.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `elementos` (línea 44)
  - **Responsabilidad:** Ejecuta la operación elementos definida por la clase Menu.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `roles` (línea 54)
  - **Responsabilidad:** Ejecuta la operación roles definida por la clase Menu.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Seguridad/Modulo.php`

#### Clase: `Modulo`

- **Método/función:** `casts` (línea 33)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Modulo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `pantallas` (línea 43)
  - **Responsabilidad:** Ejecuta la operación pantallas definida por la clase Modulo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `HasMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Seguridad/Pantalla.php`

#### Clase: `Pantalla`

- **Método/función:** `casts` (línea 33)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Pantalla.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `modulo` (línea 43)
  - **Responsabilidad:** Ejecuta la operación modulo definida por la clase Pantalla.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsTo` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Seguridad/Permiso.php`

#### Clase: `Permiso`

- **Método/función:** `casts` (línea 33)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Permiso.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `roles` (línea 43)
  - **Responsabilidad:** Ejecuta la operación roles definida por la clase Permiso.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

### Archivo: `app/Models/Seguridad/Rol.php`

#### Clase: `Rol`

- **Método/función:** `casts` (línea 34)
  - **Responsabilidad:** Ejecuta la operación casts definida por la clase Rol.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `permisos` (línea 47)
  - **Responsabilidad:** Ejecuta la operación permisos definida por la clase Rol.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `menus` (línea 58)
  - **Responsabilidad:** Ejecuta la operación menus definida por la clase Rol.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.
- **Método/función:** `usuarios` (línea 68)
  - **Responsabilidad:** Ejecuta la operación usuarios definida por la clase Rol.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `BelongsToMany` según la firma observada.
  - **Capa arquitectónica:** Dominio / Persistencia Eloquent
  - **Relación:** Representa entidades, relaciones, casts y consultas del dominio.

## Módulo: app/Policies

### Archivo: `app/Policies/PedidoPolicy.php`

#### Clase: `PedidoPolicy`

- **Método/función:** `view` (línea 25)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `User $user, Pedido $order`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Seguridad / Autorización
  - **Relación:** Resuelve la autorización de acciones y el resultado HTTP 403 cuando corresponde.
- **Método/función:** `updateStatus` (línea 63)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `User $user, Pedido $order`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Seguridad / Autorización
  - **Relación:** Resuelve la autorización de acciones y el resultado HTTP 403 cuando corresponde.

### Archivo: `app/Policies/ProductoPolicy.php`

#### Clase: `ProductoPolicy`

- **Método/función:** `update` (línea 25)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `User $user, Producto $product`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Seguridad / Autorización
  - **Relación:** Resuelve la autorización de acciones y el resultado HTTP 403 cuando corresponde.
- **Método/función:** `delete` (línea 44)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `User $user, Producto $product`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Seguridad / Autorización
  - **Relación:** Resuelve la autorización de acciones y el resultado HTTP 403 cuando corresponde.

## Módulo: app/Providers

### Archivo: `app/Providers/AppServiceProvider.php`

#### Clase: `AppServiceProvider`

- **Método/función:** `register` (línea 36)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `boot` (línea 50)
  - **Responsabilidad:** Ejecuta la operación boot definida por la clase AppServiceProvider.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: app/Servicios

### Archivo: `app/Servicios/AdaptadorAlcanceProductos.php`

#### Clase: `AdaptadorAlcanceProductos`

- **Método/función:** `__construct` (línea 22)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioAutorizacion $autorizacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `atributosDeAlcance` (línea 33)
  - **Responsabilidad:** Ejecuta la operación atributosDeAlcance definida por la clase AdaptadorAlcanceProductos.
  - **Parámetros principales:** `User $usuario, string $tabla`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/AdaptadorAuditoriaProductos.php`

#### Clase: `AdaptadorAuditoriaProductos`

- **Método/función:** `__construct` (línea 21)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly RegistradorAuditoria $auditoria`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `registrar` (línea 24)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `?int $usuarioId, string $accion, string $entidad, ?int $entidadId, ?array $antes, ?array $despues, ?string $ip`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ConsultaVentas.php`

#### Clase: `ConsultaVentas`

- **Método/función:** `__construct` (línea 28)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioAutorizacion $autorizacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `query` (línea 31)
  - **Responsabilidad:** Ejecuta la operación query definida por la clase ConsultaVentas.
  - **Parámetros principales:** `User $usuario, array $filtros`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `obtenerTotalesMensualesConfirmados` (línea 62)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `User $usuario, Carbon $desde, Carbon $hasta`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `obtenerProductosMasVendidos` (línea 84)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `User $usuario`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `resumen` (línea 109)
  - **Responsabilidad:** Ejecuta la operación resumen definida por la clase ConsultaVentas.
  - **Parámetros principales:** `User $usuario, array $filtros`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ExportadorTabular.php`

#### Clase: `ExportadorTabular`

- **Método/función:** `download` (línea 51)
  - **Responsabilidad:** Ejecuta la operación download definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $format, string $filename, array $columns, array $rows, array $report = []`
  - **Resultado:** `Response|StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `csv` (línea 65)
  - **Responsabilidad:** Ejecuta la operación csv definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $filename, array $columns, array $rows`
  - **Resultado:** `StreamedResponse` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `excel` (línea 86)
  - **Responsabilidad:** Ejecuta la operación excel definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $filename, array $columns, array $rows, array $report`
  - **Resultado:** `Response` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `excelWithoutZipArchive` (línea 142)
  - **Responsabilidad:** Ejecuta la operación excelWithoutZipArchive definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $filename, array $columns, array $rows, array $report`
  - **Resultado:** `Response` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `buildZipPackage` (línea 176)
  - **Responsabilidad:** Ejecuta la operación buildZipPackage definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $entries`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `buildWorksheet` (línea 211)
  - **Responsabilidad:** Ejecuta la operación buildWorksheet definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $columns, array $rows, array $context, bool $hasLogo`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `addWorksheetCell` (línea 415)
  - **Responsabilidad:** Ejecuta la operación addWorksheetCell definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array &$cells, int $row, int $column, mixed $value, int $style, string $type = 'text'`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `addMergedWorksheetCell` (línea 448)
  - **Responsabilidad:** Ejecuta la operación addMergedWorksheetCell definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array &$cells, array &$merges, int $row, int $start, int $end, mixed $value, int $style`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `excelColumnWidths` (línea 465)
  - **Responsabilidad:** Ejecuta la operación excelColumnWidths definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $columns, array $rows, int $layoutColumnCount`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `worksheetBodyStyle` (línea 492)
  - **Responsabilidad:** Ejecuta la operación worksheetBodyStyle definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $type, mixed $value, bool $alternate, string $heading`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `stylesXml` (línea 517)
  - **Responsabilidad:** Ejecuta la operación stylesXml definida por la clase ExportadorTabular.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `workbookXml` (línea 575)
  - **Responsabilidad:** Ejecuta la operación workbookXml definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $lastColumn, int $lastRow, int $headerRow`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `drawingXml` (línea 587)
  - **Responsabilidad:** Ejecuta la operación drawingXml definida por la clase ExportadorTabular.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdf` (línea 597)
  - **Responsabilidad:** Ejecuta la operación pdf definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $filename, array $columns, array $rows, array $report`
  - **Resultado:** `Response` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfPageHeader` (línea 779)
  - **Responsabilidad:** Ejecuta la operación pdfPageHeader definida por la clase ExportadorTabular.
  - **Parámetros principales:** `int $width, int $height, int $margin, array $context, bool $first, bool $hasLogo`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfFirstPageTableTop` (línea 852)
  - **Responsabilidad:** Ejecuta la operación pdfFirstPageTableTop definida por la clase ExportadorTabular.
  - **Parámetros principales:** `int $width, int $height, int $margin, array $context`
  - **Resultado:** `float` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfSummaryLayout` (línea 861)
  - **Responsabilidad:** Ejecuta la operación pdfSummaryLayout definida por la clase ExportadorTabular.
  - **Parámetros principales:** `int $width, int $height, int $margin, array $context`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfIndicatorLayout` (línea 888)
  - **Responsabilidad:** Ejecuta la operación pdfIndicatorLayout definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $indicators, int $width, int $margin`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfTableHeader` (línea 911)
  - **Responsabilidad:** Ejecuta la operación pdfTableHeader definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $columns, array $widths, int $margin, float $top`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfTableHeaderHeight` (línea 933)
  - **Responsabilidad:** Ejecuta la operación pdfTableHeaderHeight definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $columns, array $widths`
  - **Resultado:** `float` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfTableRow` (línea 950)
  - **Responsabilidad:** Ejecuta la operación pdfTableRow definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $wrapped, array $widths, int $margin, float $top, float $height, bool $alternate, array $columns, array $context, bool $summary = false`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfFooter` (línea 980)
  - **Responsabilidad:** Ejecuta la operación pdfFooter definida por la clase ExportadorTabular.
  - **Parámetros principales:** `int $width, int $height, int $margin, array $context, int $page, int $pageCount`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfColumnWidths` (línea 996)
  - **Responsabilidad:** Ejecuta la operación pdfColumnWidths definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $columns, array $rows, float $totalWidth`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `wrapRow` (línea 1016)
  - **Responsabilidad:** Ejecuta la operación wrapRow definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $values, array $widths, float $fontSize`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `wrapText` (línea 1030)
  - **Responsabilidad:** Ejecuta la operación wrapText definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $value, int $limit`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfRect` (línea 1065)
  - **Responsabilidad:** Ejecuta la operación pdfRect definida por la clase ExportadorTabular.
  - **Parámetros principales:** `float $x, float $y, float $width, float $height, array $fill, ?array $stroke = null`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfText` (línea 1081)
  - **Responsabilidad:** Ejecuta la operación pdfText definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $value, float $x, float $y, float $size, array $color, bool $bold = false`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfSectionBar` (línea 1105)
  - **Responsabilidad:** Ejecuta la operación pdfSectionBar definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $label, float $x, float $top, float $width, float $height`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfIndicatorPalette` (línea 1119)
  - **Responsabilidad:** Ejecuta la operación pdfIndicatorPalette definida por la clase ExportadorTabular.
  - **Parámetros principales:** `int $index`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfProgressBar` (línea 1146)
  - **Responsabilidad:** Ejecuta la operación pdfProgressBar definida por la clase ExportadorTabular.
  - **Parámetros principales:** `float $x, float $y, float $width, float $height, float $percentage`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pdfLogoObjects` (línea 1160)
  - **Responsabilidad:** Ejecuta la operación pdfLogoObjects definida por la clase ExportadorTabular.
  - **Parámetros principales:** `int $firstId`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `decodePng` (línea 1191)
  - **Responsabilidad:** Ejecuta la operación decodePng definida por la clase ExportadorTabular.
  - **Parámetros principales:** `?string $png`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `paethPredictor` (línea 1267)
  - **Responsabilidad:** Ejecuta la operación paethPredictor definida por la clase ExportadorTabular.
  - **Parámetros principales:** `int $left, int $up, int $upperLeft`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `reportContext` (línea 1281)
  - **Responsabilidad:** Ejecuta la operación reportContext definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $filename, array $report, int $columnCount`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `dynamicDetailTitle` (línea 1322)
  - **Responsabilidad:** Ejecuta la operación dynamicDetailTitle definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $context`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `primaryIndicatorLines` (línea 1360)
  - **Responsabilidad:** Ejecuta la operación primaryIndicatorLines definida por la clase ExportadorTabular.
  - **Parámetros principales:** `?array $indicator`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `primaryProgress` (línea 1391)
  - **Responsabilidad:** Ejecuta la operación primaryProgress definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $indicator`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `columnType` (línea 1406)
  - **Responsabilidad:** Ejecuta la operación columnType definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $context, int $index`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `isNumericType` (línea 1420)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `string $type`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `excelDateSerial` (línea 1434)
  - **Responsabilidad:** Ejecuta la operación excelDateSerial definida por la clase ExportadorTabular.
  - **Parámetros principales:** `mixed $value, bool $dateOnly`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `dateParts` (línea 1450)
  - **Responsabilidad:** Ejecuta la operación dateParts definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $value`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `toDateTime` (línea 1467)
  - **Responsabilidad:** Ejecuta la operación toDateTime definida por la clase ExportadorTabular.
  - **Parámetros principales:** `mixed $value`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `displayValue` (línea 1492)
  - **Responsabilidad:** Ejecuta la operación displayValue definida por la clase ExportadorTabular.
  - **Parámetros principales:** `mixed $value, string $type`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `filterSummary` (línea 1519)
  - **Responsabilidad:** Ejecuta la operación filterSummary definida por la clase ExportadorTabular.
  - **Parámetros principales:** `array $filters`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `statusTone` (línea 1546)
  - **Responsabilidad:** Ejecuta la operación statusTone definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $status`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `columnLetter` (línea 1569)
  - **Responsabilidad:** Ejecuta la operación columnLetter definida por la clase ExportadorTabular.
  - **Parámetros principales:** `int $index`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `logoBytes` (línea 1585)
  - **Responsabilidad:** Ejecuta la operación logoBytes definida por la clase ExportadorTabular.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `safeFilename` (línea 1599)
  - **Responsabilidad:** Ejecuta la operación safeFilename definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $filename`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `safeSpreadsheetText` (línea 1611)
  - **Responsabilidad:** Ejecuta la operación safeSpreadsheetText definida por la clase ExportadorTabular.
  - **Parámetros principales:** `string $value`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/RegistradorAuditoria.php`

#### Clase: `RegistradorAuditoria`

- **Método/función:** `record` (línea 25)
  - **Responsabilidad:** Ejecuta la operación record definida por la clase RegistradorAuditoria.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `hasColumn` (línea 135)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `string $column`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `safePayload` (línea 143)
  - **Responsabilidad:** Ejecuta la operación safePayload definida por la clase RegistradorAuditoria.
  - **Parámetros principales:** `?array $payload`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `moduleForEntity` (línea 172)
  - **Responsabilidad:** Ejecuta la operación moduleForEntity definida por la clase RegistradorAuditoria.
  - **Parámetros principales:** `string $entity`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `entityLocation` (línea 196)
  - **Responsabilidad:** Ejecuta la operación entityLocation definida por la clase RegistradorAuditoria.
  - **Parámetros principales:** `string $entity, ?int $entityId, string $column`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `changes` (línea 216)
  - **Responsabilidad:** Ejecuta la operación changes definida por la clase RegistradorAuditoria.
  - **Parámetros principales:** `?array $before, ?array $after`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioAutorizacion.php`

#### Clase: `ServicioAutorizacion`

- **Método/función:** `schemaAvailable` (línea 58)
  - **Responsabilidad:** Ejecuta la operación schemaAvailable definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `isSeeded` (línea 78)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `hasRole` (línea 94)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `User $user, string $roleCode`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `allows` (línea 146)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `User $user, string $permissionCode`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `assertAllowed` (línea 164)
  - **Responsabilidad:** Ejecuta la operación assertAllowed definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** `User $user, string $permissionCode`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `operationLocation` (línea 170)
  - **Responsabilidad:** Ejecuta la operación operationLocation definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `operationSite` (línea 193)
  - **Responsabilidad:** Ejecuta la operación operationSite definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `activeSiteIdFor` (línea 231)
  - **Responsabilidad:** Ejecuta la operación activeSiteIdFor definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `activeCompanyId` (línea 241)
  - **Responsabilidad:** Ejecuta la operación activeCompanyId definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `operationScopeAttributes` (línea 247)
  - **Responsabilidad:** Ejecuta la operación operationScopeAttributes definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** `User $user, string $table`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `allowsRecord` (línea 267)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `authorizedSiteIds` (línea 312)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `User $user, string $permissionCode`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `scopeRecordsFor` (línea 339)
  - **Responsabilidad:** Ejecuta la operación scopeRecordsFor definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** `Builder $query, User $user, string $permissionCode, ?string $ownerColumn = null`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `allowsGlobal` (línea 427)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `User $user, string $permissionCode`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `allowsRoute` (línea 456)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `permissionForRoute` (línea 519)
  - **Responsabilidad:** Ejecuta la operación permissionForRoute definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** `Request $request`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `permissionIds` (línea 610)
  - **Responsabilidad:** Ejecuta la operación permissionIds definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `hasControlledAssignments` (línea 637)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `activeSiteId` (línea 649)
  - **Responsabilidad:** Ejecuta la operación activeSiteId definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `isActiveSite` (línea 719)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `int $siteId`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `hasPermissionAtScope` (línea 742)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `User $user, string $permissionCode, string $scope`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `siteIdsWithPermission` (línea 789)
  - **Responsabilidad:** Ejecuta la operación siteIdsWithPermission definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** `User $user, string $permissionCode`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `singleActiveSiteId` (línea 826)
  - **Responsabilidad:** Ejecuta la operación singleActiveSiteId definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `singleActiveCompanyId` (línea 848)
  - **Responsabilidad:** Ejecuta la operación singleActiveCompanyId definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `permissionCodes` (línea 860)
  - **Responsabilidad:** Ejecuta la operación permissionCodes definida por la clase ServicioAutorizacion.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioCajas.php`

#### Clase: `ServicioCajas`

- **Método/función:** `__construct` (línea 26)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `crear` (línea 32)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `array $data, User $actor, ?string $ip`
  - **Resultado:** `Caja` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `actualizar` (línea 57)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Caja $box, array $data, User $actor, ?string $ip`
  - **Resultado:** `Caja` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `cambiarEstado` (línea 98)
  - **Responsabilidad:** Ejecuta la operación cambiarEstado definida por la clase ServicioCajas.
  - **Parámetros principales:** `Caja $box, bool $active, User $actor, ?string $ip`
  - **Resultado:** `Caja` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `abrirSesion` (línea 134)
  - **Responsabilidad:** Ejecuta la operación abrirSesion definida por la clase ServicioCajas.
  - **Parámetros principales:** `Caja $box, string $initialBalance, string $token, User $actor, ?string $ip`
  - **Resultado:** `SesionCaja` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `registrarMovimiento` (línea 174)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `arqueo` (línea 227)
  - **Responsabilidad:** Ejecuta la operación arqueo definida por la clase ServicioCajas.
  - **Parámetros principales:** `SesionCaja $session`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `cerrarSesion` (línea 261)
  - **Responsabilidad:** Ejecuta la operación cerrarSesion definida por la clase ServicioCajas.
  - **Parámetros principales:** `SesionCaja $session, string $declaredBalance, string $token, User $actor, ?string $ip`
  - **Resultado:** `SesionCaja` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `lockCompany` (línea 303)
  - **Responsabilidad:** Ejecuta la operación lockCompany definida por la clase ServicioCajas.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `eventEntityId` (línea 321)
  - **Responsabilidad:** Ejecuta la operación eventEntityId definida por la clase ServicioCajas.
  - **Parámetros principales:** `string $token, string $entity, string $action`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `amount` (línea 345)
  - **Responsabilidad:** Ejecuta la operación amount definida por la clase ServicioCajas.
  - **Parámetros principales:** `string $value, bool $positive`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `assertAdministrator` (línea 364)
  - **Responsabilidad:** Ejecuta la operación assertAdministrator definida por la clase ServicioCajas.
  - **Parámetros principales:** `User $actor, string $permission`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `assertSite` (línea 381)
  - **Responsabilidad:** Ejecuta la operación assertSite definida por la clase ServicioCajas.
  - **Parámetros principales:** `User $actor, Sede $site, string $permission`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `hasOpenSession` (línea 394)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `Caja $box`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioCatalogo.php`

#### Clase: `ServicioCatalogo`

- **Método/función:** `__construct` (línea 29)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `query` (línea 38)
  - **Responsabilidad:** Ejecuta la operación query definida por la clase ServicioCatalogo.
  - **Parámetros principales:** `array $filters`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `sincronizarImagenPortada` (línea 83)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `Producto $producto`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `crearProductoConVariante` (línea 99)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `filterOptions` (línea 131)
  - **Responsabilidad:** Ejecuta la operación filterOptions definida por la clase ServicioCatalogo.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `conExistenciasPotenciales` (línea 146)
  - **Responsabilidad:** Ejecuta la operación conExistenciasPotenciales definida por la clase ServicioCatalogo.
  - **Parámetros principales:** `Builder $productos`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `agregarDisponibilidad` (línea 166)
  - **Responsabilidad:** Ejecuta la operación agregarDisponibilidad definida por la clase ServicioCatalogo.
  - **Parámetros principales:** `Collection $productos, ?User $usuario`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioCompras.php`

#### Clase: `ServicioCompras`

- **Método/función:** `__construct` (línea 28)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `registrar` (línea 47)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `array $datos, User $actor, string $token, ?string $ip`
  - **Resultado:** `Compra` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `aprobar` (línea 128)
  - **Responsabilidad:** Ejecuta la operación aprobar definida por la clase ServicioCompras.
  - **Parámetros principales:** `Compra $compra, User $actor, ?string $ip`
  - **Resultado:** `Compra` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `recibir` (línea 144)
  - **Responsabilidad:** Ejecuta la operación recibir definida por la clase ServicioCompras.
  - **Parámetros principales:** `Compra $compra, User $actor, ?string $ip`
  - **Resultado:** `Compra` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `anular` (línea 160)
  - **Responsabilidad:** Ejecuta la operación anular definida por la clase ServicioCompras.
  - **Parámetros principales:** `Compra $compra, User $actor, ?string $ip`
  - **Resultado:** `Compra` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `registrarRecepcionParcial` (línea 181)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `Compra $compra, array $items, ?string $observaciones, string $token, User $actor, ?string $ip`
  - **Resultado:** `Compra` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `cambiarEstado` (línea 306)
  - **Responsabilidad:** Ejecuta la operación cambiarEstado definida por la clase ServicioCompras.
  - **Parámetros principales:** `Compra $compra, User $actor, ?string $ip, bool $recibir`
  - **Resultado:** `Compra` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `comprobarRecepcion` (línea 361)
  - **Responsabilidad:** Ejecuta la operación comprobarRecepcion definida por la clase ServicioCompras.
  - **Parámetros principales:** `Compra $compra, bool $completa`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `cantidadesRecibidasPorDetalle` (línea 379)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `Compra $compra, Collection $lineas`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `registrarEntradaDeCompra` (línea 406)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `normalizarIdentificadores` (línea 497)
  - **Responsabilidad:** Ejecuta la operación normalizarIdentificadores definida por la clase ServicioCompras.
  - **Parámetros principales:** `string $texto`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `esImeiValido` (línea 542)
  - **Responsabilidad:** Ejecuta la operación esImeiValido definida por la clase ServicioCompras.
  - **Parámetros principales:** `string $imei`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `centimos` (línea 570)
  - **Responsabilidad:** Ejecuta la operación centimos definida por la clase ServicioCompras.
  - **Parámetros principales:** `string $valor`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `importe` (línea 585)
  - **Responsabilidad:** Procesa e incorpora la información importada.
  - **Parámetros principales:** `int $centimos`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioConfiguracionSistema.php`

#### Clase: `ServicioConfiguracionSistema`

- **Método/función:** `obtenerDatosGenerales` (línea 23)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `User $usuario, array $ubicacion`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `consultarIntegraciones` (línea 51)
  - **Responsabilidad:** Ejecuta la operación consultarIntegraciones definida por la clase ServicioConfiguracionSistema.
  - **Parámetros principales:** `array $ubicacion, ?string $busqueda, ?string $estado`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `obtenerDatosSeguridad` (línea 94)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `User $usuario, array $ubicacion`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `consultaAuditoria` (línea 126)
  - **Responsabilidad:** Ejecuta la operación consultaAuditoria definida por la clase ServicioConfiguracionSistema.
  - **Parámetros principales:** `User $usuario, ?int $empresaId, ?int $sedeId`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioConfiguracionTienda.php`

#### Clase: `ServicioConfiguracionTienda`

- **Método/función:** `guardar` (línea 27)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `array $datos, User $usuario, ?string $ip`
  - **Resultado:** `ConfiguracionTienda` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `consultar` (línea 78)
  - **Responsabilidad:** Ejecuta la operación consultar definida por la clase ServicioConfiguracionTienda.
  - **Parámetros principales:** `int $empresaId`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioConsultasCuentas.php`

#### Clase: `ServicioConsultasCuentas`

- **Método/función:** `__construct` (línea 25)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private ServicioAutorizacion $autorizacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `obtenerVista` (línea 30)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `User $usuario`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `formatearFila` (línea 133)
  - **Responsabilidad:** Ejecuta la operación formatearFila definida por la clase ServicioConsultasCuentas.
  - **Parámetros principales:** `object $cuenta, string $tipo, string $tercero, string $documento`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `nombreReceptor` (línea 158)
  - **Responsabilidad:** Ejecuta la operación nombreReceptor definida por la clase ServicioConsultasCuentas.
  - **Parámetros principales:** `string $datos`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `metricaReal` (línea 171)
  - **Responsabilidad:** Ejecuta la operación metricaReal definida por la clase ServicioConsultasCuentas.
  - **Parámetros principales:** `string $titulo, Collection $cuentas, string $icono, string $tono, array $monedasAutorizadas`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `importeAgrupado` (línea 177)
  - **Responsabilidad:** Procesa e incorpora la información importada.
  - **Parámetros principales:** `Collection $cuentas, array $monedasAutorizadas`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `formatearImporte` (línea 197)
  - **Responsabilidad:** Ejecuta la operación formatearImporte definida por la clase ServicioConsultasCuentas.
  - **Parámetros principales:** `float $importe, string $moneda`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `porcentaje` (línea 211)
  - **Responsabilidad:** Ejecuta la operación porcentaje definida por la clase ServicioConsultasCuentas.
  - **Parámetros principales:** `int $parte, int $total`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `gradienteSaldos` (línea 229)
  - **Responsabilidad:** Ejecuta la operación gradienteSaldos definida por la clase ServicioConsultasCuentas.
  - **Parámetros principales:** `int $vigentes, int $vencidos, int $pagados, int $total`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioConsultasFinancieras.php`

#### Clase: `ServicioConsultasFinancieras`

- **Método/función:** `__construct` (línea 33)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private ServicioAutorizacion $authorization`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pagos` (línea 39)
  - **Responsabilidad:** Ejecuta la operación pagos definida por la clase ServicioConsultasFinancieras.
  - **Parámetros principales:** `User $actor, array $filters`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `reembolsos` (línea 75)
  - **Responsabilidad:** Ejecuta la operación reembolsos definida por la clase ServicioConsultasFinancieras.
  - **Parámetros principales:** `User $actor, array $filters`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `opcionesPago` (línea 103)
  - **Responsabilidad:** Ejecuta la operación opcionesPago definida por la clase ServicioConsultasFinancieras.
  - **Parámetros principales:** `User $actor`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `opcionesReembolso` (línea 148)
  - **Responsabilidad:** Ejecuta la operación opcionesReembolso definida por la clase ServicioConsultasFinancieras.
  - **Parámetros principales:** `User $actor`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `siteForForm` (línea 170)
  - **Responsabilidad:** Ejecuta la operación siteForForm definida por la clase ServicioConsultasFinancieras.
  - **Parámetros principales:** `User $actor`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioContacto.php`

#### Clase: `ServicioContacto`

- **Método/función:** `register` (línea 15)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `array $data`
  - **Resultado:** `MensajeContacto` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioCotizaciones.php`

#### Clase: `ServicioCotizaciones`

- **Método/función:** `__construct` (línea 28)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `create` (línea 42)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `array $data, User $user`
  - **Resultado:** `Cotizacion` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `aprobar` (línea 106)
  - **Responsabilidad:** Ejecuta la operación aprobar definida por la clase ServicioCotizaciones.
  - **Parámetros principales:** `Cotizacion $cotizacion, User $usuario, ?string $ip`
  - **Resultado:** `Cotizacion` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `registrarEnvioManual` (línea 146)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `Cotizacion $cotizacion, User $usuario, ?string $ip`
  - **Resultado:** `Cotizacion` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `desactivar` (línea 191)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Cotizacion $cotizacion, User $usuario, ?string $ip`
  - **Resultado:** `Cotizacion` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `actualizar` (línea 227)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Cotizacion $cotizacion, array $data, User $usuario, ?string $ip`
  - **Resultado:** `Cotizacion` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `convertirAventa` (línea 287)
  - **Responsabilidad:** Ejecuta la operación convertirAventa definida por la clase ServicioCotizaciones.
  - **Parámetros principales:** `Cotizacion $cotizacion, int $almacenId, User $usuario, ?string $ip`
  - **Resultado:** `Venta` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `importeCotizacion` (línea 450)
  - **Responsabilidad:** Procesa e incorpora la información importada.
  - **Parámetros principales:** `int $centimos`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioCuentasFinancieras.php`

#### Clase: `ServicioCuentasFinancieras`

- **Método/función:** `__construct` (línea 22)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `crearCuentaPorPagar` (línea 40)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `int $compraId, string $venceEn, User $usuario, ?string $ip`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `aplicarPagoRegistrado` (línea 86)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `int $cuentaId, int $pagoId, string $importe, User $usuario, ?string $ip`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `aplicarCobroRegistrado` (línea 149)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `int $cuentaId, int $pagoId, string $importe, User $usuario, ?string $ip`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `centimos` (línea 206)
  - **Responsabilidad:** Ejecuta la operación centimos definida por la clase ServicioCuentasFinancieras.
  - **Parámetros principales:** `string $importe`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioCupones.php`

#### Clase: `ServicioCupones`

- **Método/función:** `query` (línea 22)
  - **Responsabilidad:** Ejecuta la operación query definida por la clase ServicioCupones.
  - **Parámetros principales:** `array $filters, ?int $empresaId`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `dashboard` (línea 57)
  - **Responsabilidad:** Ejecuta la operación dashboard definida por la clase ServicioCupones.
  - **Parámetros principales:** `?int $empresaId`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `register` (línea 78)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `array $attributes, User $actor, ?string $ip`
  - **Resultado:** `Cupon` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `update` (línea 99)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Cupon $coupon, array $attributes, User $actor, ?string $ip`
  - **Resultado:** `Cupon` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `deactivate` (línea 129)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Cupon $coupon, User $actor, ?string $ip`
  - **Resultado:** `Cupon` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `status` (línea 157)
  - **Responsabilidad:** Ejecuta la operación status definida por la clase ServicioCupones.
  - **Parámetros principales:** `Cupon $coupon`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `assertCouponRules` (línea 168)
  - **Responsabilidad:** Ejecuta la operación assertCouponRules definida por la clase ServicioCupones.
  - **Parámetros principales:** `array $attributes, int $registeredUses`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `authorization` (línea 181)
  - **Responsabilidad:** Ejecuta la operación authorization definida por la clase ServicioCupones.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `ServicioAutorizacion` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `empresaDisponible` (línea 191)
  - **Responsabilidad:** Ejecuta la operación empresaDisponible definida por la clase ServicioCupones.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `empresaActual` (línea 201)
  - **Responsabilidad:** Ejecuta la operación empresaActual definida por la clase ServicioCupones.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioDelegacion.php`

#### Clase: `ServicioDelegacion`

- **Método/función:** `__construct` (línea 24)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioAutorizacion $autorizacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `puedeDelegarPermisos` (línea 27)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `User $actor, array $permissionIds, string $requestedScope = 'sede'`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `puedeAsignarRol` (línea 53)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `User $actor, Rol $role`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `puedeAdministrarUsuario` (línea 80)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `User $actor, User $target`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `actorScopesForPermission` (línea 106)
  - **Responsabilidad:** Ejecuta la operación actorScopesForPermission definida por la clase ServicioDelegacion.
  - **Parámetros principales:** `User $actor, int $permissionId`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `canCoverScope` (línea 148)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `array $actorScopes, string $requestedScope`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `esUltimoAdministradorGlobal` (línea 167)
  - **Responsabilidad:** Ejecuta la operación esUltimoAdministradorGlobal definida por la clase ServicioDelegacion.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `bloquearAdministradoresGlobales` (línea 200)
  - **Responsabilidad:** Ejecuta la operación bloquearAdministradoresGlobales definida por la clase ServicioDelegacion.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioDispositivos.php`

#### Clase: `ServicioDispositivos`

- **Método/función:** `__construct` (línea 26)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioAutorizacion $autorizacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `listar` (línea 39)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `User $usuario, array $filtros`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `consultaPorSedes` (línea 91)
  - **Responsabilidad:** Ejecuta la operación consultaPorSedes definida por la clase ServicioDispositivos.
  - **Parámetros principales:** `array $sedesAutorizadas`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `aplicarFiltros` (línea 108)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `Builder $consulta, array $filtros`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioEnviosPedidos.php`

#### Clase: `ServicioEnviosPedidos`

- **Método/función:** `__construct` (línea 26)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `actualizarEstado` (línea 36)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `EnvioPedido $envio, array $datos, User $usuario, ?string $ip = null`
  - **Resultado:** `EventoEnvioPedido` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `listar` (línea 97)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `User $usuario, array $filtros`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `consultaExportacion` (línea 144)
  - **Responsabilidad:** Ejecuta la operación consultaExportacion definida por la clase ServicioEnviosPedidos.
  - **Parámetros principales:** `User $usuario, array $filtros`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `aplicarFiltros` (línea 162)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `Builder $consulta, array $filtros`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioGestionProductos.php`

#### Clase: `ServicioGestionProductos`

- **Método/función:** `__construct` (línea 23)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `crear` (línea 31)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `array $datos, ?int $proveedorId, User $usuario, string $ip`
  - **Resultado:** `Producto` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `actualizar` (línea 51)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Producto $producto, array $datos, array $datosValidados, array $antes, User $usuario, string $ip`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioInteligenciaComercial.php`

#### Clase: `ServicioInteligenciaComercial`

- **Método/función:** `optimize` (línea 30)
  - **Responsabilidad:** Ejecuta la operación optimize definida por la clase ServicioInteligenciaComercial.
  - **Parámetros principales:** `float $budget, string $goal`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `wholesaleProducts` (línea 93)
  - **Responsabilidad:** Ejecuta la operación wholesaleProducts definida por la clase ServicioInteligenciaComercial.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `advertisingSummary` (línea 105)
  - **Responsabilidad:** Ejecuta la operación advertisingSummary definida por la clase ServicioInteligenciaComercial.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `cellPhones` (línea 121)
  - **Responsabilidad:** Ejecuta la operación cellPhones definida por la clase ServicioInteligenciaComercial.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioInventario.php`

#### Clase: `ServicioInventario`

- **Método/función:** `__construct` (línea 25)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `alcanceConsulta` (línea 31)
  - **Responsabilidad:** Ejecuta la operación alcanceConsulta definida por la clase ServicioInventario.
  - **Parámetros principales:** `User $usuario, string $permiso = 'gestion.inventario.consultar'`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `almacenesParaConsulta` (línea 56)
  - **Responsabilidad:** Ejecuta la operación almacenesParaConsulta definida por la clase ServicioInventario.
  - **Parámetros principales:** `array $alcance`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `productosParaConsulta` (línea 76)
  - **Responsabilidad:** Ejecuta la operación productosParaConsulta definida por la clase ServicioInventario.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `resumenProductos` (línea 154)
  - **Responsabilidad:** Ejecuta la operación resumenProductos definida por la clase ServicioInventario.
  - **Parámetros principales:** `array $alcance`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `saldosPorVariante` (línea 186)
  - **Responsabilidad:** Ejecuta la operación saldosPorVariante definida por la clase ServicioInventario.
  - **Parámetros principales:** `array $alcance, array $productos`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `resumenConsulta` (línea 209)
  - **Responsabilidad:** Ejecuta la operación resumenConsulta definida por la clase ServicioInventario.
  - **Parámetros principales:** `array $alcance`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `disponibilidadParaOperacion` (línea 236)
  - **Responsabilidad:** Ejecuta la operación disponibilidadParaOperacion definida por la clase ServicioInventario.
  - **Parámetros principales:** `User $usuario, array $identificadores`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `registerNewProduct` (línea 293)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `Producto $product, User $user`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `adjust` (línea 328)
  - **Responsabilidad:** Ejecuta la operación adjust definida por la clase ServicioInventario.
  - **Parámetros principales:** `Producto $product, string $movementType, int $quantity, string $notes, User $user`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `registrarMovimientoEnAlmacen` (línea 354)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `changeLockedProduct` (línea 443)
  - **Responsabilidad:** Ejecuta la operación changeLockedProduct definida por la clase ServicioInventario.
  - **Parámetros principales:** `Producto $product, string $movementType, int $quantity, string $notes, User $user`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `changeLockedProductInWarehouse` (línea 507)
  - **Responsabilidad:** Ejecuta la operación changeLockedProductInWarehouse definida por la clase ServicioInventario.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioMenuDinamico.php`

#### Clase: `ServicioMenuDinamico`

- **Método/función:** `__construct` (línea 25)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly ServicioAutorizacion $autorizacion`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `paraUsuario` (línea 28)
  - **Responsabilidad:** Ejecuta la operación paraUsuario definida por la clase ServicioMenuDinamico.
  - **Parámetros principales:** `?User $user`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `schemaAvailable` (línea 120)
  - **Responsabilidad:** Ejecuta la operación schemaAvailable definida por la clase ServicioMenuDinamico.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `roleIdsFor` (línea 132)
  - **Responsabilidad:** Ejecuta la operación roleIdsFor definida por la clase ServicioMenuDinamico.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `mergeGroups` (línea 164)
  - **Responsabilidad:** Ejecuta la operación mergeGroups definida por la clase ServicioMenuDinamico.
  - **Parámetros principales:** `array $groups`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `makeGroup` (línea 195)
  - **Responsabilidad:** Ejecuta la operación makeGroup definida por la clase ServicioMenuDinamico.
  - **Parámetros principales:** `object $root, $children, User $user`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `makeItem` (línea 214)
  - **Responsabilidad:** Ejecuta la operación makeItem definida por la clase ServicioMenuDinamico.
  - **Parámetros principales:** `object $row, User $user`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `activeRoute` (línea 263)
  - **Responsabilidad:** Ejecuta la operación activeRoute definida por la clase ServicioMenuDinamico.
  - **Parámetros principales:** `string $route`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioNotificaciones.php`

#### Clase: `ServicioNotificaciones`

- **Método/función:** `__construct` (línea 29)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly RegistradorAuditoria $registradorAuditoria`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `listarDelUsuario` (línea 36)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `obtenerResumenDelUsuario` (línea 70)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `User $usuario`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `buscarDelUsuario` (línea 122)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `User $usuario, string $identificador`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `marcarComoLeida` (línea 138)
  - **Responsabilidad:** Ejecuta la operación marcarComoLeida definida por la clase ServicioNotificaciones.
  - **Parámetros principales:** `User $usuario, string $identificador, ?string $direccionIp`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `consultaDelUsuario` (línea 169)
  - **Responsabilidad:** Ejecuta la operación consultaDelUsuario definida por la clase ServicioNotificaciones.
  - **Parámetros principales:** `User $usuario`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioOperacionesFinancieras.php`

#### Clase: `ServicioOperacionesFinancieras`

- **Método/función:** `__construct` (línea 33)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `registrarPago` (línea 42)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `array $data, User $actor, ?string $ip`
  - **Resultado:** `Pago` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `confirmarPagoEfectivo` (línea 174)
  - **Responsabilidad:** Ejecuta la operación confirmarPagoEfectivo definida por la clase ServicioOperacionesFinancieras.
  - **Parámetros principales:** `Pago $payment, string $countedAmount, string $observation, User $actor, ?string $ip`
  - **Resultado:** `Pago` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `anularPago` (línea 247)
  - **Responsabilidad:** Ejecuta la operación anularPago definida por la clase ServicioOperacionesFinancieras.
  - **Parámetros principales:** `Pago $payment, User $actor, string $reason, ?string $ip`
  - **Resultado:** `Pago` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `solicitarReembolso` (línea 269)
  - **Responsabilidad:** Ejecuta la operación solicitarReembolso definida por la clase ServicioOperacionesFinancieras.
  - **Parámetros principales:** `array $data, User $actor, ?string $ip`
  - **Resultado:** `Reembolso` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `aprobarReembolso` (línea 319)
  - **Responsabilidad:** Ejecuta la operación aprobarReembolso definida por la clase ServicioOperacionesFinancieras.
  - **Parámetros principales:** `Reembolso $refund, User $actor, ?string $ip`
  - **Resultado:** `Reembolso` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `anularReembolso` (línea 358)
  - **Responsabilidad:** Ejecuta la operación anularReembolso definida por la clase ServicioOperacionesFinancieras.
  - **Parámetros principales:** `Reembolso $refund, User $actor, string $reason, ?string $ip`
  - **Resultado:** `Reembolso` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `document` (línea 389)
  - **Responsabilidad:** Ejecuta la operación document definida por la clase ServicioOperacionesFinancieras.
  - **Parámetros principales:** `string $kind, int $id`
  - **Resultado:** `Pedido|Venta|Compra` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `assertRefundOrigin` (línea 407)
  - **Responsabilidad:** Ejecuta la operación assertRefundOrigin definida por la clase ServicioOperacionesFinancieras.
  - **Parámetros principales:** `Pago $payment, DevolucionGarantia $return`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `hasPaymentEvidence` (línea 431)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `Pago $payment`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `cents` (línea 455)
  - **Responsabilidad:** Ejecuta la operación cents definida por la clase ServicioOperacionesFinancieras.
  - **Parámetros principales:** `string $amount`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `money` (línea 471)
  - **Responsabilidad:** Ejecuta la operación money definida por la clase ServicioOperacionesFinancieras.
  - **Parámetros principales:** `int $cents`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioPaneles.php`

#### Clase: `ServicioPaneles`

- **Método/función:** `__construct` (línea 23)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `administratorSummary` (línea 31)
  - **Responsabilidad:** Ejecuta la operación administratorSummary definida por la clase ServicioPaneles.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `roleDashboard` (línea 45)
  - **Responsabilidad:** Ejecuta la operación roleDashboard definida por la clase ServicioPaneles.
  - **Parámetros principales:** `User $user, array $cart, string $role`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `customerOrders` (línea 67)
  - **Responsabilidad:** Ejecuta la operación customerOrders definida por la clase ServicioPaneles.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioPedidos.php`

#### Clase: `ServicioPedidos`

- **Método/función:** `__construct` (línea 28)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `checkout` (línea 37)
  - **Responsabilidad:** Ejecuta la operación checkout definida por la clase ServicioPedidos.
  - **Parámetros principales:** `User $user, array $cart, ?string $couponCode, ?string $ip, string $checkoutToken`
  - **Resultado:** `Pedido` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `updateStatus` (línea 135)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Pedido $order, User $user, string $status, ?string $ip`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `discountFor` (línea 180)
  - **Responsabilidad:** Ejecuta la operación discountFor definida por la clase ServicioPedidos.
  - **Parámetros principales:** `?string $couponCode, int $subtotalCents, int $empresaId`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioPreciosMayoristas.php`

#### Clase: `ServicioPreciosMayoristas`

- **Método/función:** `registrar` (línea 23)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `array $datos, User $usuario, ?string $ip`
  - **Resultado:** `PrecioMayorista` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `importarCsv` (línea 94)
  - **Responsabilidad:** Procesa e incorpora la información importada.
  - **Parámetros principales:** `UploadedFile $archivo, User $usuario, ?string $ip`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `precioVigente` (línea 187)
  - **Responsabilidad:** Ejecuta la operación precioVigente definida por la clase ServicioPreciosMayoristas.
  - **Parámetros principales:** `int $empresaId, int $productoId`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `preciosVigentes` (línea 202)
  - **Responsabilidad:** Ejecuta la operación preciosVigentes definida por la clase ServicioPreciosMayoristas.
  - **Parámetros principales:** `int $empresaId, array $productosIds`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioRecomendaciones.php`

#### Clase: `ServicioRecomendaciones`

- **Método/función:** `recommend` (línea 18)
  - **Responsabilidad:** Ejecuta la operación recommend definida por la clase ServicioRecomendaciones.
  - **Parámetros principales:** `array $filters`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioRespaldos.php`

#### Clase: `ServicioRespaldos`

- **Método/función:** `listar` (línea 18)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `crear` (línea 46)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `User $usuario, ?string $ip, RegistradorAuditoria $auditoria`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `rutaSegura` (línea 101)
  - **Responsabilidad:** Ejecuta la operación rutaSegura definida por la clase ServicioRespaldos.
  - **Parámetros principales:** `string $nombre`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `directorio` (línea 115)
  - **Responsabilidad:** Ejecuta la operación directorio definida por la clase ServicioRespaldos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `binario` (línea 125)
  - **Responsabilidad:** Ejecuta la operación binario definida por la clase ServicioRespaldos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioResponsablesOrganizacionales.php`

#### Clase: `ServicioResponsablesOrganizacionales`

- **Método/función:** `listar` (línea 27)
  - **Responsabilidad:** Obtiene y prepara la información solicitada para la operación.
  - **Parámetros principales:** `array $idsSede`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `reasignar` (línea 40)
  - **Responsabilidad:** Ejecuta la operación reasignar definida por la clase ServicioResponsablesOrganizacionales.
  - **Parámetros principales:** `User $actor, array $datos, RegistradorAuditoria $auditoria, ?string $ip`
  - **Resultado:** `ResponsableOrganizacional` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioTransferenciasExistencias.php`

#### Clase: `ServicioTransferenciasExistencias`

- **Método/función:** `__construct` (línea 30)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `pageData` (línea 37)
  - **Responsabilidad:** Ejecuta la operación pageData definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `User $actor, array $filters`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `exportQuery` (línea 91)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `User $actor, array $filters`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `request` (línea 100)
  - **Responsabilidad:** Ejecuta la operación request definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `array $data, User $actor, ?string $ip`
  - **Resultado:** `TransferenciaExistencia` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `approve` (línea 156)
  - **Responsabilidad:** Ejecuta la operación approve definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, User $actor, string $key, ?string $ip`
  - **Resultado:** `TransferenciaExistencia` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `dispatch` (línea 183)
  - **Responsabilidad:** Ejecuta la operación dispatch definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip`
  - **Resultado:** `TransferenciaExistencia` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `receive` (línea 235)
  - **Responsabilidad:** Ejecuta la operación receive definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip`
  - **Resultado:** `TransferenciaExistencia` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `returnToOrigin` (línea 292)
  - **Responsabilidad:** Ejecuta la operación returnToOrigin definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip`
  - **Resultado:** `TransferenciaExistencia` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `reject` (línea 338)
  - **Responsabilidad:** Ejecuta la operación reject definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip`
  - **Resultado:** `TransferenciaExistencia` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `reconcileDifference` (línea 346)
  - **Responsabilidad:** Ejecuta la operación reconcileDifference definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip`
  - **Resultado:** `TransferenciaExistencia` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `cancel` (línea 400)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip`
  - **Resultado:** `TransferenciaExistencia` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `close` (línea 408)
  - **Responsabilidad:** Ejecuta la operación close definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip`
  - **Resultado:** `TransferenciaExistencia` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `finishBeforeDispatch` (línea 445)
  - **Responsabilidad:** Ejecuta la operación finishBeforeDispatch definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, array $data, User $actor, ?string $ip, string $targetStatus`
  - **Resultado:** `TransferenciaExistencia` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `transition` (línea 464)
  - **Responsabilidad:** Ejecuta la operación transition definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, User $actor, string $action, string $key, ?string $ip, callable $operation`
  - **Resultado:** `TransferenciaExistencia` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `warehouseContext` (línea 490)
  - **Responsabilidad:** Ejecuta la operación warehouseContext definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `User $actor, int $warehouseId, ?string $permission, ?int $companyId = null`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `currentCompany` (línea 507)
  - **Responsabilidad:** Ejecuta la operación currentCompany definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `User $actor`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `transferQuery` (línea 528)
  - **Responsabilidad:** Ejecuta la operación transferQuery definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `array $location, array $filters`
  - **Resultado:** `Builder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `authorizeTransferSite` (línea 558)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `User $actor, TransferenciaExistencia $transfer, string $siteColumn, string $permission`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `assertWarehouseStillActive` (línea 570)
  - **Responsabilidad:** Ejecuta la operación assertWarehouseStillActive definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `balance` (línea 593)
  - **Responsabilidad:** Ejecuta la operación balance definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, DetalleTransferenciaExistencia $detail, int $warehouseId, bool $lock`
  - **Resultado:** `object` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `assertProductVariant` (línea 621)
  - **Responsabilidad:** Ejecuta la operación assertProductVariant definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `int $productId, ?int $variantId`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `isSerialized` (línea 635)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `DetalleTransferenciaExistencia $detail`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `availableDeviceQuery` (línea 654)
  - **Responsabilidad:** Ejecuta la operación availableDeviceQuery definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `DetalleTransferenciaExistencia $detail, int $warehouseId`
  - **Resultado:** `QueryBuilder` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `findAvailableDevices` (línea 662)
  - **Responsabilidad:** Ejecuta la operación findAvailableDevices definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `DetalleTransferenciaExistencia $detail, int $warehouseId, array $serials`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `findTransitDevices` (línea 674)
  - **Responsabilidad:** Ejecuta la operación findTransitDevices definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, DetalleTransferenciaExistencia $detail, array $serials`
  - **Resultado:** `Collection` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `changeDevice` (línea 705)
  - **Responsabilidad:** Ejecuta la operación changeDevice definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `object $device, MovimientoInventario $movement, string $oldStatus, string $newStatus, ?int $warehouseId`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `requestedDetails` (línea 718)
  - **Responsabilidad:** Ejecuta la operación requestedDetails definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `TransferenciaExistencia $transfer, array $items`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `assertUniqueSerials` (línea 735)
  - **Responsabilidad:** Ejecuta la operación assertUniqueSerials definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `array $serials`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `returnedQuantity` (línea 748)
  - **Responsabilidad:** Ejecuta la operación returnedQuantity definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `DetalleTransferenciaExistencia $detail`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `reconciledQuantity` (línea 760)
  - **Responsabilidad:** Ejecuta la operación reconciledQuantity definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `DetalleTransferenciaExistencia $detail`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `newCode` (línea 772)
  - **Responsabilidad:** Ejecuta la operación newCode definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `int $companyId`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `eventId` (línea 794)
  - **Responsabilidad:** Ejecuta la operación eventId definida por la clase ServicioTransferenciasExistencias.
  - **Parámetros principales:** `int $transferId, int $actorId, string $action, string $key`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioVariantesProducto.php`

#### Clase: `ServicioVariantesProducto`

- **Método/función:** `__construct` (línea 28)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** `private readonly RegistradorAuditoria $auditoria`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `registrar` (línea 33)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `Producto $producto, array $datos, User $usuario, ?string $ip`
  - **Resultado:** `VarianteProducto` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `actualizar` (línea 72)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Producto $producto, VarianteProducto $variante, array $datos, User $usuario, ?string $ip`
  - **Resultado:** `VarianteProducto` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `desactivar` (línea 143)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `Producto $producto, VarianteProducto $variante, User $usuario, ?string $ip`
  - **Resultado:** `VarianteProducto` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `registrarImagen` (línea 175)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `eliminarImagen` (línea 241)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `eliminarArchivoSiNoEstaReferenciado` (línea 303)
  - **Responsabilidad:** Modifica el estado o elimina el registro según la operación solicitada.
  - **Parámetros principales:** `string $rutaImagen`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

### Archivo: `app/Servicios/ServicioVentas.php`

#### Clase: `ServicioVentas`

- **Método/función:** `__construct` (línea 29)
  - **Responsabilidad:** Inicializa la instancia con las dependencias y el estado requeridos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `registrarFisica` (línea 36)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `array $datos, User $actor, string $token, ?string $ip`
  - **Resultado:** `Venta` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `anular` (línea 124)
  - **Responsabilidad:** Ejecuta la operación anular definida por la clase ServicioVentas.
  - **Parámetros principales:** `Venta $venta, User $actor, string $motivo, ?string $ip`
  - **Resultado:** `Venta` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `actualizarBorrador` (línea 181)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `Venta $venta, array $datos, User $actor, ?string $ip`
  - **Resultado:** `Venta` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `confirmarBorradorCotizacion` (línea 262)
  - **Responsabilidad:** Ejecuta la operación confirmarBorradorCotizacion definida por la clase ServicioVentas.
  - **Parámetros principales:** `Venta $venta, User $actor, ?string $ip, array $dispositivosPorDetalle = []`
  - **Resultado:** `Venta` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `movimientosDeVentaCoinciden` (línea 387)
  - **Responsabilidad:** Ejecuta la operación movimientosDeVentaCoinciden definida por la clase ServicioVentas.
  - **Parámetros principales:** `Venta $venta, Collection $detalles`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `dispositivosDeVentaCoinciden` (línea 415)
  - **Responsabilidad:** Ejecuta la operación dispositivosDeVentaCoinciden definida por la clase ServicioVentas.
  - **Parámetros principales:** `Collection $detalles, array $dispositivosPorDetalle`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `centimos` (línea 437)
  - **Responsabilidad:** Ejecuta la operación centimos definida por la clase ServicioVentas.
  - **Parámetros principales:** `string $precio`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.
- **Método/función:** `importe` (línea 452)
  - **Responsabilidad:** Procesa e incorpora la información importada.
  - **Parámetros principales:** `int $centimos`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Aplicación / Negocio
  - **Relación:** Orquesta reglas empresariales y coordina modelos, DAO, transacciones y auditoría.

## Módulo: app/Soporte

### Archivo: `app/Soporte/PermisosRoles.php`

#### Clase: `PermisosRoles`

- **Método/función:** `canView` (línea 34)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `User $user, string $module`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Aplicación
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `navigation` (línea 47)
  - **Responsabilidad:** Ejecuta la operación navigation definida por la clase PermisosRoles.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `administratorNavigation` (línea 87)
  - **Responsabilidad:** Ejecuta la operación administratorNavigation definida por la clase PermisosRoles.
  - **Parámetros principales:** `User $user`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Aplicación
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: database/factories

### Archivo: `database/factories/CategoriaFactory.php`

#### Clase: `CategoriaFactory`

- **Método/función:** `definition` (línea 20)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase CategoriaFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/ProductoFactory.php`

#### Clase: `ProductoFactory`

- **Método/función:** `definition` (línea 21)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase ProductoFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/UserFactory.php`

#### Clase: `UserFactory`

- **Método/función:** `definition` (línea 25)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase UserFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `administrator` (línea 41)
  - **Responsabilidad:** Ejecuta la operación administrator definida por la clase UserFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `static` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: database/factories/Comunicacion

### Archivo: `database/factories/Comunicacion/NotificacionFactory.php`

#### Clase: `NotificacionFactory`

- **Método/función:** `definition` (línea 19)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase NotificacionFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: database/factories/Finanzas

### Archivo: `database/factories/Finanzas/CajaFactory.php`

#### Clase: `CajaFactory`

- **Método/función:** `definition` (línea 19)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase CajaFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Finanzas/CuentaBancariaFactory.php`

#### Clase: `CuentaBancariaFactory`

- **Método/función:** `definition` (línea 19)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase CuentaBancariaFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Finanzas/MedioPagoFactory.php`

#### Clase: `MedioPagoFactory`

- **Método/función:** `definition` (línea 18)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase MedioPagoFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Finanzas/MovimientoCajaFactory.php`

#### Clase: `MovimientoCajaFactory`

- **Método/función:** `definition` (línea 19)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase MovimientoCajaFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Finanzas/PagoFactory.php`

#### Clase: `PagoFactory`

- **Método/función:** `definition` (línea 18)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase PagoFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Finanzas/ReembolsoFactory.php`

#### Clase: `ReembolsoFactory`

- **Método/función:** `definition` (línea 18)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase ReembolsoFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Finanzas/SesionCajaFactory.php`

#### Clase: `SesionCajaFactory`

- **Método/función:** `definition` (línea 20)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase SesionCajaFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: database/factories/Inventario

### Archivo: `database/factories/Inventario/DetalleTransferenciaExistenciaFactory.php`

#### Clase: `DetalleTransferenciaExistenciaFactory`

- **Método/función:** `definition` (línea 19)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase DetalleTransferenciaExistenciaFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Inventario/DispositivoFactory.php`

#### Clase: `DispositivoFactory`

- **Método/función:** `definition` (línea 20)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase DispositivoFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Inventario/TransferenciaExistenciaFactory.php`

#### Clase: `TransferenciaExistenciaFactory`

- **Método/función:** `configure` (línea 22)
  - **Responsabilidad:** Ejecuta la operación configure definida por la clase TransferenciaExistenciaFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `static` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `definition` (línea 57)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase TransferenciaExistenciaFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: database/factories/Organizacion

### Archivo: `database/factories/Organizacion/AlmacenFactory.php`

#### Clase: `AlmacenFactory`

- **Método/función:** `definition` (línea 19)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase AlmacenFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Organizacion/EmpresaFactory.php`

#### Clase: `EmpresaFactory`

- **Método/función:** `definition` (línea 18)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase EmpresaFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Organizacion/SedeFactory.php`

#### Clase: `SedeFactory`

- **Método/función:** `definition` (línea 19)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase SedeFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: database/factories/Seguridad

### Archivo: `database/factories/Seguridad/ElementoMenuFactory.php`

#### Clase: `ElementoMenuFactory`

- **Método/función:** `definition` (línea 19)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase ElementoMenuFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Seguridad/MenuFactory.php`

#### Clase: `MenuFactory`

- **Método/función:** `definition` (línea 18)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase MenuFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Seguridad/ModuloFactory.php`

#### Clase: `ModuloFactory`

- **Método/función:** `definition` (línea 18)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase ModuloFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Seguridad/PantallaFactory.php`

#### Clase: `PantallaFactory`

- **Método/función:** `definition` (línea 19)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase PantallaFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Seguridad/PermisoFactory.php`

#### Clase: `PermisoFactory`

- **Método/función:** `definition` (línea 19)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase PermisoFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/factories/Seguridad/RolFactory.php`

#### Clase: `RolFactory`

- **Método/función:** `definition` (línea 18)
  - **Responsabilidad:** Ejecuta la operación definition definida por la clase RolFactory.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: database/migrations

### Archivo: `database/migrations/0001_01_01_000000_create_users_table.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 12)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 43)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/0001_01_01_000001_create_cache_table.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 12)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 30)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/0001_01_01_000002_create_jobs_table.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 12)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 53)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_10_10_171500_create_cell_sac_commerce_tables.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 15)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `completeExistingSchema` (línea 331)
  - **Responsabilidad:** Ejecuta la operación completeExistingSchema definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `foreignKeyExists` (línea 452)
  - **Responsabilidad:** Ejecuta la operación foreignKeyExists definida por la clase (sin clase).
  - **Parámetros principales:** `string $table, string $constraint`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 466)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_10_10_172618_migrate_legacy_users_to_laravel_users.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 12)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 49)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `syncForeignKeys` (línea 67)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `string $referencedTable, string $columnType`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `foreignKeyExists` (línea 124)
  - **Responsabilidad:** Ejecuta la operación foreignKeyExists definida por la clase (sin clase).
  - **Parámetros principales:** `string $table, string $constraint`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_10_28_203424_create_devoluciones_garantias_table.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 12)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 57)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_000753_create_enterprise_rbac_tables.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 12)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 197)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_000754_add_admin_profile_security_fields.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 14)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 114)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_013915_create_rbac_seed_versions_table.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 15)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 44)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_014019_add_business_operation_site_scope_columns.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 13)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 74)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `ensureForeignKey` (línea 82)
  - **Responsabilidad:** Ejecuta la operación ensureForeignKey definida por la clase (sin clase).
  - **Parámetros principales:** `string $table, array $columns, string $foreignTable, array $foreignColumns, string $name`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `hasOrphanRows` (línea 107)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `string $table, array $columns, string $foreignTable, array $foreignColumns`
  - **Resultado:** `bool` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `assertUnsignedBigintColumns` (línea 124)
  - **Responsabilidad:** Ejecuta la operación assertUnsignedBigintColumns definida por la clase (sin clase).
  - **Parámetros principales:** `string $table, array $columns`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_015103_reconcile_devoluciones_garantias_foreign_key_types.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 13)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 99)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `assertValuesFitTarget` (línea 113)
  - **Responsabilidad:** Ejecuta la operación assertValuesFitTarget definida por la clase (sin clase).
  - **Parámetros principales:** `string $column, bool $unsigned`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `assertNoOrphans` (línea 135)
  - **Responsabilidad:** Ejecuta la operación assertNoOrphans definida por la clase (sin clase).
  - **Parámetros principales:** `string $column, string $referenceTable, string $referenceColumn`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `ensureForeignKey` (línea 164)
  - **Responsabilidad:** Ejecuta la operación ensureForeignKey definida por la clase (sin clase).
  - **Parámetros principales:** `string $column, string $referenceTable, string $referenceColumn, bool $nullable`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_015104_create_ecommerce_extension_tables.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 14)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 104)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_015105_create_stock_sales_device_tables.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 14)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 222)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_015106_create_electronic_billing_tables.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 14)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 145)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_015107_create_finance_accounting_tables.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 14)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 332)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_015108_create_logistics_returns_tables.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 15)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 201)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_015109_create_support_legal_integration_tables.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 14)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 168)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_015110_add_commerce_integration_columns.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 15)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 97)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_015111_backfill_main_organization_and_stock.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 13)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 121)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_015112_link_commerce_enterprise_foreign_keys.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 15)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 64)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_01_015113_standardize_audit_events.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 15)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 63)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_03_192348_agregar_canal_y_observaciones_a_ventas.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 13)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 39)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_03_220504_add_cantidad_devuelta_to_detalles_transferencias_existencias.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 15)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 39)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_120000_enable_admin_responsibles_screen.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 14)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 84)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_120100_create_responsables_organizacionales_table.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 15)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 87)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_120200_enable_configuration_screens.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 23)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 95)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_120300_enable_billing_query_screen.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 14)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 80)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_120400_enable_inventory_read_screens.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 20)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 90)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_120500_enable_security_sessions_screen.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 14)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 79)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_121000_enable_refunds_export_permission.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 14)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 64)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_121100_enable_support_export_permission.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 14)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 44)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_121200_unificar_menu_operaciones_comerciales.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 12)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 56)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_130000_reduce_security_catalog_to_six_interfaces.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 15)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 153)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_140000_add_tipo_documento_to_clientes.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 14)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 30)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_140000_remove_organization_submenus.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 13)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 58)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/migrations/2026_11_05_235900_reduce_analysis_audit_menu_to_two_interfaces.php`

#### Clase: `(sin clase)`

- **Método/función:** `up` (línea 15)
  - **Responsabilidad:** Ejecuta la operación up definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `down` (línea 68)
  - **Responsabilidad:** Ejecuta la operación down definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: database/seeders

### Archivo: `database/seeders/CategoriaSeeder.php`

#### Clase: `CategoriaSeeder`

- **Método/función:** `run` (línea 20)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/seeders/DatabaseSeeder.php`

#### Clase: `DatabaseSeeder`

- **Método/función:** `run` (línea 20)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `database/seeders/RbacInicialSeeder.php`

#### Clase: `RbacInicialSeeder`

- **Método/función:** `diagnosticarPreparacion` (línea 47)
  - **Responsabilidad:** Ejecuta la operación diagnosticarPreparacion definida por la clase RbacInicialSeeder.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `run` (línea 98)
  - **Responsabilidad:** Ejecuta la operación principal asignada al componente.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `seedRoles` (línea 156)
  - **Responsabilidad:** Ejecuta la operación seedRoles definida por la clase RbacInicialSeeder.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `erroresRolesExistentes` (línea 198)
  - **Responsabilidad:** Ejecuta la operación erroresRolesExistentes definida por la clase RbacInicialSeeder.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `seedModules` (línea 235)
  - **Responsabilidad:** Ejecuta la operación seedModules definida por la clase RbacInicialSeeder.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `screens` (línea 292)
  - **Responsabilidad:** Ejecuta la operación screens definida por la clase RbacInicialSeeder.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `seedScreens` (línea 356)
  - **Responsabilidad:** Ejecuta la operación seedScreens definida por la clase RbacInicialSeeder.
  - **Parámetros principales:** `array $moduleIds`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `seedPermissions` (línea 383)
  - **Responsabilidad:** Ejecuta la operación seedPermissions definida por la clase RbacInicialSeeder.
  - **Parámetros principales:** `array $moduleIds, array $screenIds`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `moduleActions` (línea 444)
  - **Responsabilidad:** Ejecuta la operación moduleActions definida por la clase RbacInicialSeeder.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `seedRolePermissions` (línea 482)
  - **Responsabilidad:** Ejecuta la operación seedRolePermissions definida por la clase RbacInicialSeeder.
  - **Parámetros principales:** `array $roleIds, array $permissionIds`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `seedMenus` (línea 546)
  - **Responsabilidad:** Ejecuta la operación seedMenus definida por la clase RbacInicialSeeder.
  - **Parámetros principales:** `array $roleIds, array $screenIds, array $permissionIds`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `createMenuElements` (línea 627)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `int $menuId, array $groups, array $screenIds, array $permissionIds, int $firstOrder = 1, bool $nested = true`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Persistencia / Base de datos
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: tests

### Archivo: `tests/IsolatedMariaDatabase.php`

#### Clase: `IsolatedMariaDatabase`

- **Método/función:** `createFresh` (línea 17)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `PDO $server, string $root, string $pipe`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/Pest.php`

#### Clase: `(sin clase)`

- **Método/función:** `something` (línea 47)
  - **Responsabilidad:** Ejecuta la operación something definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/RefreshSpanishTestDatabase.php`

#### Clase: `RefreshSpanishTestDatabase`

- **Método/función:** `migrateDatabases` (línea 26)
  - **Responsabilidad:** Ejecuta la operación migrateDatabases definida por la clase RefreshSpanishTestDatabase.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/SpanishAccessFixture.php`

#### Clase: `SpanishAccessFixture`

- **Método/función:** `organization` (línea 17)
  - **Responsabilidad:** Ejecuta la operación organization definida por la clase SpanishAccessFixture.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `assign` (línea 59)
  - **Responsabilidad:** Ejecuta la operación assign definida por la clase SpanishAccessFixture.
  - **Parámetros principales:** `User $user, string $roleCode`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `user` (línea 90)
  - **Responsabilidad:** Ejecuta la operación user definida por la clase SpanishAccessFixture.
  - **Parámetros principales:** `string $roleCode`
  - **Resultado:** `User` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `grantScreenReadPermission` (línea 109)
  - **Responsabilidad:** Ejecuta la operación grantScreenReadPermission definida por la clase SpanishAccessFixture.
  - **Parámetros principales:** `User $user, string $screenCode, string $routeName`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: tests/Feature

### Archivo: `tests/Feature/CellSacCommerceTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `productForCellSac` (línea 18)
  - **Responsabilidad:** Ejecuta la operación productForCellSac definida por la clase (sin clase).
  - **Parámetros principales:** `array $attributes = []`
  - **Resultado:** `Producto` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/Feature/EcommercePersistenciaTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `prepararTablasEcommercePersistente` (línea 17)
  - **Responsabilidad:** Ejecuta la operación prepararTablasEcommercePersistente definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: tests/Feature/Paneles/Administrador

### Archivo: `tests/Feature/Paneles/Administrador/AlertasGlobalesControllerTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `prepararEsquemaNotificacionesParaPrueba` (línea 15)
  - **Responsabilidad:** Ejecuta la operación prepararEsquemaNotificacionesParaPrueba definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `crearNotificacionParaPrueba` (línea 58)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `User $destinatario, string $mensaje, ?string $leidaEn = null, string $tipo = 'App\\Notifications\\AvisoSistema'`
  - **Resultado:** `Notificacion` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/Feature/Paneles/Administrador/DispositivosControllerTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `prepararEsquemaDispositivosParaPrueba` (línea 15)
  - **Responsabilidad:** Ejecuta la operación prepararEsquemaDispositivosParaPrueba definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `crearDispositivoParaPrueba` (línea 57)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `Producto $producto, int $almacenId, string $identificador, string $estado = 'disponible'`
  - **Resultado:** `Dispositivo` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/Feature/Paneles/Administrador/EnviosPedidosControllerTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `prepararEsquemaEnviosParaPrueba` (línea 15)
  - **Responsabilidad:** Ejecuta la operación prepararEsquemaEnviosParaPrueba definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `crearEnvioParaPrueba` (línea 58)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `array $organizacion, string $codigo, string $estado = 'pendiente', ?string $seguimiento = null`
  - **Resultado:** `int` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: tests/Feature/Publico/Comercio

### Archivo: `tests/Feature/Publico/Comercio/PedidoControllerTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `crearUsuarioParaPedidos` (línea 200)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `string $codigoRol, ?string $nombre = null`
  - **Resultado:** `User` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `crearPedidoParaFiltroPedidosOnline` (línea 231)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `User $customer, string $status, string $channel, DateTimeInterface $createdAt`
  - **Resultado:** `Pedido` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: tests/Feature/UnidadII

### Archivo: `tests/Feature/UnidadII/ModuloAndSecurityIntegrationTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `productoParaIntegracionUnidadDos` (línea 27)
  - **Responsabilidad:** Ejecuta la operación productoParaIntegracionUnidadDos definida por la clase (sin clase).
  - **Parámetros principales:** `array $attributes = []`
  - **Resultado:** `Producto` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `crearImagenPngParaPruebaCatalogo` (línea 56)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `string $nombre`
  - **Resultado:** `UploadedFile` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `prepararEsquemaDispositivosRecepcionCompra` (línea 68)
  - **Responsabilidad:** Ejecuta la operación prepararEsquemaDispositivosRecepcionCompra definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: tests/Unit

### Archivo: `tests/Unit/ApiContractTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `contenidoApiQa` (línea 10)
  - **Responsabilidad:** Ejecuta la operación contenidoApiQa definida por la clase (sin clase).
  - **Parámetros principales:** `string $ruta`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/Unit/ArquitecturaMvcTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `archivosDeProyecto` (línea 12)
  - **Responsabilidad:** Ejecuta la operación archivosDeProyecto definida por la clase (sin clase).
  - **Parámetros principales:** `string $directorio, string $extension`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `raizProyectoQa` (línea 37)
  - **Responsabilidad:** Ejecuta la operación raizProyectoQa definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/Unit/CajasOperativasTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `prepareCajaFixture` (línea 23)
  - **Responsabilidad:** Ejecuta la operación prepareCajaFixture definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/Unit/ConsultasFinancierasQueryTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `prepareFinanzasAuthorization` (línea 26)
  - **Responsabilidad:** Ejecuta la operación prepareFinanzasAuthorization definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `ServicioAutorizacion` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `prepareFinancialOperationsTables` (línea 250)
  - **Responsabilidad:** Ejecuta la operación prepareFinancialOperationsTables definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `prepareFinancialOperations` (línea 391)
  - **Responsabilidad:** Ejecuta la operación prepareFinancialOperations definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/Unit/DashboardGlobalExportTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `prepareGlobalDashboardAuthorization` (línea 25)
  - **Responsabilidad:** Ejecuta la operación prepareGlobalDashboardAuthorization definida por la clase (sin clase).
  - **Parámetros principales:** `int $userId = 1`
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/Unit/DashboardGlobalPedidosTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `preparePedidosDashboardAuthorization` (línea 21)
  - **Responsabilidad:** Ejecuta la operación preparePedidosDashboardAuthorization definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/Unit/RbacAuthorizationTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `unsavedUser` (línea 20)
  - **Responsabilidad:** Ejecuta la operación unsavedUser definida por la clase (sin clase).
  - **Parámetros principales:** `array $attributes`
  - **Resultado:** `User` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `unsavedProduct` (línea 35)
  - **Responsabilidad:** Ejecuta la operación unsavedProduct definida por la clase (sin clase).
  - **Parámetros principales:** `int $id = 10`
  - **Resultado:** `Producto` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `unsavedOrder` (línea 46)
  - **Responsabilidad:** Ejecuta la operación unsavedOrder definida por la clase (sin clase).
  - **Parámetros principales:** `array $attributes`
  - **Resultado:** `Pedido` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/Unit/SeguridadEstructuralTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `formulariosBlade` (línea 10)
  - **Responsabilidad:** Ejecuta la operación formulariosBlade definida por la clase (sin clase).
  - **Parámetros principales:** `string $directorio`
  - **Resultado:** `array` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `raizProyectoQaSeguridad` (línea 35)
  - **Responsabilidad:** Ejecuta la operación raizProyectoQaSeguridad definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

### Archivo: `tests/Unit/SeguridadFrontendTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `raizFrontendQa` (línea 8)
  - **Responsabilidad:** Ejecuta la operación raizFrontendQa definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `contenidoFrontendQa` (línea 20)
  - **Responsabilidad:** Ejecuta la operación contenidoFrontendQa definida por la clase (sin clase).
  - **Parámetros principales:** `string $ruta`
  - **Resultado:** `string` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: tests/Unit/Gestion/Cupones

### Archivo: `tests/Unit/Gestion/Cupones/ServicioCuponesTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `createCouponSyntheticAuthorization` (línea 66)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `makeCouponForServiceTest` (línea 144)
  - **Responsabilidad:** Ejecuta la operación makeCouponForServiceTest definida por la clase (sin clase).
  - **Parámetros principales:** `array $attributes = []`
  - **Resultado:** `Cupon` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `couponTestActor` (línea 166)
  - **Responsabilidad:** Ejecuta la operación couponTestActor definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `User` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: tests/Unit/Gestion/Inventario

### Archivo: `tests/Unit/Gestion/Inventario/ServicioTransferenciasExistenciasTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `createTransferSyntheticAuthorization` (línea 171)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `void` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `transferTestActor` (línea 236)
  - **Responsabilidad:** Ejecuta la operación transferTestActor definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `User` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.
- **Método/función:** `createTransferForTest` (línea 254)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `ServicioTransferenciasExistencias $service, int $quantity = 4`
  - **Resultado:** `TransferenciaExistencia` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: tests/Unit/Gestion/Productos

### Archivo: `tests/Unit/Gestion/Productos/ServicioGestionProductosTest.php`

#### Clase: `(sin clase)`

- **Método/función:** `usuarioGestionProductos` (línea 48)
  - **Responsabilidad:** Ejecuta la operación usuarioGestionProductos definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `User` según la firma observada.
  - **Capa arquitectónica:** Verificación / Pruebas
  - **Relación:** Participa en el flujo del módulo según la responsabilidad de su archivo.

## Módulo: resources/js

### Archivo: `resources/js/tienda.js`

#### Clase: `(sin clase)`

- **Método/función:** `inicializarBotonSubir` (línea 20)
  - **Responsabilidad:** Ejecuta la operación inicializarBotonSubir definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `cargarModulosDePagina` (línea 39)
  - **Responsabilidad:** Ejecuta la operación cargarModulosDePagina definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `inicializarTienda` (línea 53)
  - **Responsabilidad:** Ejecuta la operación inicializarTienda definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/componentes/gestion

### Archivo: `resources/js/componentes/gestion/tabla-datos.js`

#### Clase: `(sin clase)`

- **Método/función:** `exportarTablaVisible` (línea 8)
  - **Responsabilidad:** Genera la salida exportable de la información disponible.
  - **Parámetros principales:** `tabla, nombreArchivo = 'reporte.csv'`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/componentes/inteligencia/asistente

### Archivo: `resources/js/componentes/inteligencia/asistente/asistente-panel.js`

#### Clase: `(sin clase)`

- **Método/función:** `posicionGuardada` (línea 19)
  - **Responsabilidad:** Ejecuta la operación posicionGuardada definida por la clase (sin clase).
  - **Parámetros principales:** `(`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `posicionPanelGuardada` (línea 39)
  - **Responsabilidad:** Ejecuta la operación posicionPanelGuardada definida por la clase (sin clase).
  - **Parámetros principales:** `(`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `cambiarEstadoPanel` (línea 58)
  - **Responsabilidad:** Ejecuta la operación cambiarEstadoPanel definida por la clase (sin clase).
  - **Parámetros principales:** `abierto`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `terminarArrastre` (línea 98)
  - **Responsabilidad:** Ejecuta la operación terminarArrastre definida por la clase (sin clase).
  - **Parámetros principales:** `evento`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `terminarMovimientoPanel` (línea 132)
  - **Responsabilidad:** Ejecuta la operación terminarMovimientoPanel definida por la clase (sin clase).
  - **Parámetros principales:** `evento`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `terminarRedimension` (línea 174)
  - **Responsabilidad:** Ejecuta la operación terminarRedimension definida por la clase (sin clase).
  - **Parámetros principales:** `evento`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `agregarMensaje` (línea 204)
  - **Responsabilidad:** Ejecuta la operación agregarMensaje definida por la clase (sin clase).
  - **Parámetros principales:** `textoMensaje, esPropio = false`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `consultarAsistente` (línea 225)
  - **Responsabilidad:** Ejecuta la operación consultarAsistente definida por la clase (sin clase).
  - **Parámetros principales:** `pregunta`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/componentes/publico

### Archivo: `resources/js/componentes/publico/publicidad.js`

#### Clase: `(sin clase)`

- **Método/función:** `guardarEstado` (línea 31)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `registrarCampania` (línea 39)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `elemento, evento`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `cerrarModal` (línea 57)
  - **Responsabilidad:** Ejecuta la operación cerrarModal definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `mostrarModal` (línea 62)
  - **Responsabilidad:** Ejecuta la operación mostrarModal definida por la clase (sin clase).
  - **Parámetros principales:** `contabilizar = true`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `mostrarContextual` (línea 78)
  - **Responsabilidad:** Ejecuta la operación mostrarContextual definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/layouts/panel

### Archivo: `resources/js/layouts/panel/encabezado.js`

#### Clase: `(sin clase)`

- **Método/función:** `abrirSelector` (línea 16)
  - **Responsabilidad:** Ejecuta la operación abrirSelector definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

### Archivo: `resources/js/layouts/panel/menu-lateral.js`

#### Clase: `(sin clase)`

- **Método/función:** `esVistaMovil` (línea 20)
  - **Responsabilidad:** Ejecuta la operación esVistaMovil definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `anchoDisponible` (línea 27)
  - **Responsabilidad:** Ejecuta la operación anchoDisponible definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `ajustarAncho` (línea 34)
  - **Responsabilidad:** Ejecuta la operación ajustarAncho definida por la clase (sin clase).
  - **Parámetros principales:** `ancho`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `leerPreferencia` (línea 41)
  - **Responsabilidad:** Ejecuta la operación leerPreferencia definida por la clase (sin clase).
  - **Parámetros principales:** `clave`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `guardarPreferencia` (línea 54)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `clave, valor`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `mostrarAncho` (línea 74)
  - **Responsabilidad:** Ejecuta la operación mostrarAncho definida por la clase (sin clase).
  - **Parámetros principales:** `ancho`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `contraerBarra` (línea 87)
  - **Responsabilidad:** Ejecuta la operación contraerBarra definida por la clase (sin clase).
  - **Parámetros principales:** `contraida, guardar = true`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `cerrarBarraLateralMovil` (línea 108)
  - **Responsabilidad:** Ejecuta la operación cerrarBarraLateralMovil definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `terminarArrastre` (línea 148)
  - **Responsabilidad:** Ejecuta la operación terminarArrastre definida por la clase (sin clase).
  - **Parámetros principales:** `event`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/layouts/publico

### Archivo: `resources/js/layouts/publico/encabezado.js`

#### Clase: `(sin clase)`

- **Método/función:** `inicializarEncabezado` (línea 7)
  - **Responsabilidad:** Ejecuta la operación inicializarEncabezado definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/gestion

### Archivo: `resources/js/modulos/gestion/dialogos-panel.js`

#### Clase: `(sin clase)`

- **Método/función:** `abrirDialogo` (línea 4)
  - **Responsabilidad:** Ejecuta la operación abrirDialogo definida por la clase (sin clase).
  - **Parámetros principales:** `id`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

### Archivo: `resources/js/modulos/gestion/filtros-tablas.js`

#### Clase: `(sin clase)`

- **Método/función:** `filtrarFilas` (línea 4)
  - **Responsabilidad:** Ejecuta la operación filtrarFilas definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `filasOrdenadas` (línea 132)
  - **Responsabilidad:** Ejecuta la operación filasOrdenadas definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `renderizarPaginacion` (línea 152)
  - **Responsabilidad:** Ejecuta la operación renderizarPaginacion definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

### Archivo: `resources/js/modulos/gestion/filtros.js`

#### Clase: `(sin clase)`

- **Método/función:** `abrirSelectorFecha` (línea 9)
  - **Responsabilidad:** Ejecuta la operación abrirSelectorFecha definida por la clase (sin clase).
  - **Parámetros principales:** `input`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `formatearFecha` (línea 24)
  - **Responsabilidad:** Ejecuta la operación formatearFecha definida por la clase (sin clase).
  - **Parámetros principales:** `valor`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `aplicarFiltros` (línea 81)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

### Archivo: `resources/js/modulos/gestion/modulos-pendientes.js`

#### Clase: `(sin clase)`

- **Método/función:** `decodeRecord` (línea 7)
  - **Responsabilidad:** Ejecuta la operación decodeRecord definida por la clase (sin clase).
  - **Parámetros principales:** `encodedRecord`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `populateModal` (línea 27)
  - **Responsabilidad:** Ejecuta la operación populateModal definida por la clase (sin clase).
  - **Parámetros principales:** `modal, trigger`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializePendingModule` (línea 64)
  - **Responsabilidad:** Ejecuta la operación initializePendingModule definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `showFeedback` (línea 82)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `message`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `closeModal` (línea 103)
  - **Responsabilidad:** Ejecuta la operación closeModal definida por la clase (sin clase).
  - **Parámetros principales:** `modal`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `openModal` (línea 115)
  - **Responsabilidad:** Ejecuta la operación openModal definida por la clase (sin clase).
  - **Parámetros principales:** `mode, trigger`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/gestion/ventas

### Archivo: `resources/js/modulos/gestion/ventas/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `currency` (línea 12)
  - **Responsabilidad:** Ejecuta la operación currency definida por la clase (sin clase).
  - **Parámetros principales:** `value`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `escapeHtml` (línea 23)
  - **Responsabilidad:** Ejecuta la operación escapeHtml definida por la clase (sin clase).
  - **Parámetros principales:** `value`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `linesFor` (línea 36)
  - **Responsabilidad:** Ejecuta la operación linesFor definida por la clase (sin clase).
  - **Parámetros principales:** `form`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `renderTotals` (línea 44)
  - **Responsabilidad:** Ejecuta la operación renderTotals definida por la clase (sin clase).
  - **Parámetros principales:** `form`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `showEmptyLine` (línea 80)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `form`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `addProductLine` (línea 98)
  - **Responsabilidad:** Ejecuta la operación addProductLine definida por la clase (sin clase).
  - **Parámetros principales:** `form`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeForm` (línea 153)
  - **Responsabilidad:** Ejecuta la operación initializeForm definida por la clase (sin clase).
  - **Parámetros principales:** `form`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `openDialog` (línea 217)
  - **Responsabilidad:** Ejecuta la operación openDialog definida por la clase (sin clase).
  - **Parámetros principales:** `dialog`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador

### Archivo: `resources/js/modulos/paneles/administrador/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `renderSalesLine` (línea 32)
  - **Responsabilidad:** Ejecuta la operación renderSalesLine definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `applySalesRange` (línea 72)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/administracion/integraciones

### Archivo: `resources/js/modulos/paneles/administrador/administracion/integraciones/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeIntegraciones` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeIntegraciones definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/administracion/notificaciones

### Archivo: `resources/js/modulos/paneles/administrador/administracion/notificaciones/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeNotificaciones` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeNotificaciones definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/administracion/respaldos-mantenimiento

### Archivo: `resources/js/modulos/paneles/administrador/administracion/respaldos-mantenimiento/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeRespaldos` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeRespaldos definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/administracion/roles-permisos

### Archivo: `resources/js/modulos/paneles/administrador/administracion/roles-permisos/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeRolesPermisos` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeRolesPermisos definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeSystemFilterStatus` (línea 17)
  - **Responsabilidad:** Ejecuta la operación initializeSystemFilterStatus definida por la clase (sin clase).
  - **Parámetros principales:** `root, moduleName`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/analisis

### Archivo: `resources/js/modulos/paneles/administrador/analisis/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `showFeedback` (línea 32)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `message`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `closeModal` (línea 50)
  - **Responsabilidad:** Ejecuta la operación closeModal definida por la clase (sin clase).
  - **Parámetros principales:** `modal`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/analisis/analitica-clientes

### Archivo: `resources/js/modulos/paneles/administrador/analisis/analitica-clientes/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeAnaliticaClientes` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeAnaliticaClientes definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/analisis/metricas-marketing

### Archivo: `resources/js/modulos/paneles/administrador/analisis/metricas-marketing/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeMetricasMarketing` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeMetricasMarketing definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/analisis/reportes

### Archivo: `resources/js/modulos/paneles/administrador/analisis/reportes/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeReportes` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeReportes definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/analisis/reportes-financieros

### Archivo: `resources/js/modulos/paneles/administrador/analisis/reportes-financieros/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeReportesFinancieros` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeReportesFinancieros definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/analisis-auditoria

### Archivo: `resources/js/modulos/paneles/administrador/analisis-auditoria/analisis-auditoria.js`

#### Clase: `(sin clase)`

- **Método/función:** `renderizar` (línea 48)
  - **Responsabilidad:** Ejecuta la operación renderizar definida por la clase (sin clase).
  - **Parámetros principales:** `evento`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/componentes

### Archivo: `resources/js/modulos/paneles/administrador/componentes/menu-lateral-desplegable.js`

#### Clase: `(sin clase)`

- **Método/función:** `claveGrupo` (línea 16)
  - **Responsabilidad:** Ejecuta la operación claveGrupo definida por la clase (sin clase).
  - **Parámetros principales:** `grupo`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `estadoGuardado` (línea 28)
  - **Responsabilidad:** Ejecuta la operación estadoGuardado definida por la clase (sin clase).
  - **Parámetros principales:** `grupo`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `guardarEstado` (línea 48)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** `grupo, abierto`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `actualizarGrupo` (línea 68)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `grupo, abierto`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/configuracion

### Archivo: `resources/js/modulos/paneles/administrador/configuracion/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `showFeedback` (línea 38)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `message`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/configuracion/configuracion-comercial

### Archivo: `resources/js/modulos/paneles/administrador/configuracion/configuracion-comercial/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeConfiguracionComercial` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeConfiguracionComercial definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeSettingsRoot` (línea 17)
  - **Responsabilidad:** Ejecuta la operación initializeSettingsRoot definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/configuracion/configuracion-empresa

### Archivo: `resources/js/modulos/paneles/administrador/configuracion/configuracion-empresa/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeConfiguracionEmpresa` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeConfiguracionEmpresa definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeSettingsRoot` (línea 17)
  - **Responsabilidad:** Ejecuta la operación initializeSettingsRoot definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/configuracion/configuracion-general

### Archivo: `resources/js/modulos/paneles/administrador/configuracion/configuracion-general/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeConfiguracionGeneral` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeConfiguracionGeneral definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeSettingsRoot` (línea 17)
  - **Responsabilidad:** Ejecuta la operación initializeSettingsRoot definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/configuracion/configuracion-seguridad

### Archivo: `resources/js/modulos/paneles/administrador/configuracion/configuracion-seguridad/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeConfiguracionSeguridad` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeConfiguracionSeguridad definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeSettingsRoot` (línea 17)
  - **Responsabilidad:** Ejecuta la operación initializeSettingsRoot definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/ecommerce-marketing

### Archivo: `resources/js/modulos/paneles/administrador/ecommerce-marketing/ecommerce-marketing.js`

#### Clase: `(sin clase)`

- **Método/función:** `openDialog` (línea 7)
  - **Responsabilidad:** Ejecuta la operación openDialog definida por la clase (sin clase).
  - **Parámetros principales:** `dialog, trigger`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `wireDialogClose` (línea 20)
  - **Responsabilidad:** Ejecuta la operación wireDialogClose definida por la clase (sin clase).
  - **Parámetros principales:** `dialog, selector, onClose = (`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `updateRows` (línea 63)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `renderBulkStep` (línea 107)
  - **Responsabilidad:** Ejecuta la operación renderBulkStep definida por la clase (sin clase).
  - **Parámetros principales:** `step`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `resetBulkFlow` (línea 125)
  - **Responsabilidad:** Ejecuta la operación resetBulkFlow definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `validateEditPrice` (línea 140)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `mountPreviewForm` (línea 356)
  - **Responsabilidad:** Ejecuta la operación mountPreviewForm definida por la clase (sin clase).
  - **Parámetros principales:** `page, cardSelector, buttonSelector, title`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `formatPeriod` (línea 435)
  - **Responsabilidad:** Ejecuta la operación formatPeriod definida por la clase (sin clase).
  - **Parámetros principales:** `value`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `roleLabel` (línea 572)
  - **Responsabilidad:** Ejecuta la operación roleLabel definida por la clase (sin clase).
  - **Parámetros principales:** `role`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `updateSegmentPreview` (línea 585)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/finanzas/facturacion

### Archivo: `resources/js/modulos/paneles/administrador/finanzas/facturacion/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeFacturacion` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeFacturacion definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeInvoiceDemo` (línea 23)
  - **Responsabilidad:** Ejecuta la operación initializeInvoiceDemo definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `render` (línea 47)
  - **Responsabilidad:** Ejecuta la operación render definida por la clase (sin clase).
  - **Parámetros principales:** `step`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `populateReview` (línea 69)
  - **Responsabilidad:** Ejecuta la operación populateReview definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `bindFinanceFilterStatus` (línea 139)
  - **Responsabilidad:** Ejecuta la operación bindFinanceFilterStatus definida por la clase (sin clase).
  - **Parámetros principales:** `root, moduleName`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/finanzas/pagos

### Archivo: `resources/js/modulos/paneles/administrador/finanzas/pagos/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializePagos` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializePagos definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializePaymentDemo` (línea 23)
  - **Responsabilidad:** Ejecuta la operación initializePaymentDemo definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `syncOperation` (línea 43)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `bindFinanceFilterStatus` (línea 107)
  - **Responsabilidad:** Ejecuta la operación bindFinanceFilterStatus definida por la clase (sin clase).
  - **Parámetros principales:** `root, moduleName`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/finanzas/reembolsos

### Archivo: `resources/js/modulos/paneles/administrador/finanzas/reembolsos/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `initializeReembolsos` (línea 7)
  - **Responsabilidad:** Ejecuta la operación initializeReembolsos definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeRefundDemo` (línea 23)
  - **Responsabilidad:** Ejecuta la operación initializeRefundDemo definida por la clase (sin clase).
  - **Parámetros principales:** `root`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `bindFinanceFilterStatus` (línea 97)
  - **Responsabilidad:** Ejecuta la operación bindFinanceFilterStatus definida por la clase (sin clase).
  - **Parámetros principales:** `root, moduleName`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/finanzas-facturacion

### Archivo: `resources/js/modulos/paneles/administrador/finanzas-facturacion/finanzas-facturacion.js`

#### Clase: `(sin clase)`

- **Método/función:** `actualizarPagosDisponibles` (línea 16)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `applyFilters` (línea 33)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `getDialog` (línea 60)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `name`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `closeDialog` (línea 67)
  - **Responsabilidad:** Ejecuta la operación closeDialog definida por la clase (sin clase).
  - **Parámetros principales:** `dialog`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `syncMethodFields` (línea 119)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `openDialog` (línea 192)
  - **Responsabilidad:** Ejecuta la operación openDialog definida por la clase (sin clase).
  - **Parámetros principales:** `page, name, row, getDialog`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `renderDetails` (línea 219)
  - **Responsabilidad:** Ejecuta la operación renderDetails definida por la clase (sin clase).
  - **Parámetros principales:** `dialog, row`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `prepareBoxDialog` (línea 243)
  - **Responsabilidad:** Ejecuta la operación prepareBoxDialog definida por la clase (sin clase).
  - **Parámetros principales:** `dialog, row`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `prepareAccountPayment` (línea 272)
  - **Responsabilidad:** Ejecuta la operación prepareAccountPayment definida por la clase (sin clase).
  - **Parámetros principales:** `dialog, row`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeAccountAmount` (línea 292)
  - **Responsabilidad:** Ejecuta la operación initializeAccountAmount definida por la clase (sin clase).
  - **Parámetros principales:** `dialog`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `parseMoney` (línea 314)
  - **Responsabilidad:** Ejecuta la operación parseMoney definida por la clase (sin clase).
  - **Parámetros principales:** `value`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeStepper` (línea 325)
  - **Responsabilidad:** Ejecuta la operación initializeStepper definida por la clase (sin clase).
  - **Parámetros principales:** `dialog`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `render` (línea 342)
  - **Responsabilidad:** Ejecuta la operación render definida por la clase (sin clase).
  - **Parámetros principales:** `step`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `confirmSimpleDialog` (línea 394)
  - **Responsabilidad:** Ejecuta la operación confirmSimpleDialog definida por la clase (sin clase).
  - **Parámetros principales:** `dialog`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `showOperation` (línea 426)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `page, name, row`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeOperationDialog` (línea 467)
  - **Responsabilidad:** Ejecuta la operación initializeOperationDialog definida por la clase (sin clase).
  - **Parámetros principales:** `page, dialog`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeSunatDialogs` (línea 509)
  - **Responsabilidad:** Ejecuta la operación initializeSunatDialogs definida por la clase (sin clase).
  - **Parámetros principales:** `retryDialog, cdrDialog`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `initializeNoteEvidence` (línea 539)
  - **Responsabilidad:** Ejecuta la operación initializeNoteEvidence definida por la clase (sin clase).
  - **Parámetros principales:** `dialog`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/inventario-logistica

### Archivo: `resources/js/modulos/paneles/administrador/inventario-logistica/inventario-logistica.js`

#### Clase: `(sin clase)`

- **Método/función:** `applyFilters` (línea 14)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `getDialog` (línea 51)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `name`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `closeDialog` (línea 58)
  - **Responsabilidad:** Ejecuta la operación closeDialog definida por la clase (sin clase).
  - **Parámetros principales:** `dialog`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `renderDetails` (línea 70)
  - **Responsabilidad:** Ejecuta la operación renderDetails definida por la clase (sin clase).
  - **Parámetros principales:** `row`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `showOperation` (línea 101)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `row, operation`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `currentImportStep` (línea 338)
  - **Responsabilidad:** Ejecuta la operación currentImportStep definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `currentStep` (línea 384)
  - **Responsabilidad:** Ejecuta la operación currentStep definida por la clase (sin clase).
  - **Parámetros principales:** `dialog`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `resetStepper` (línea 393)
  - **Responsabilidad:** Ejecuta la operación resetStepper definida por la clase (sin clase).
  - **Parámetros principales:** `dialog, initialStep = 0`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `renderStepper` (línea 421)
  - **Responsabilidad:** Ejecuta la operación renderStepper definida por la clase (sin clase).
  - **Parámetros principales:** `dialog, step`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `validateVisibleStep` (línea 430)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `dialog`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `showStepError` (línea 449)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `dialog, message`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `updateTransferRemoveButtons` (línea 462)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** `container`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `renderTransferReview` (línea 475)
  - **Responsabilidad:** Ejecuta la operación renderTransferReview definida por la clase (sin clase).
  - **Parámetros principales:** `dialog`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `resetImportWizard` (línea 504)
  - **Responsabilidad:** Ejecuta la operación resetImportWizard definida por la clase (sin clase).
  - **Parámetros principales:** `dialog`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `renderImportStep` (línea 523)
  - **Responsabilidad:** Ejecuta la operación renderImportStep definida por la clase (sin clase).
  - **Parámetros principales:** `dialog, step`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `downloadCsv` (línea 548)
  - **Responsabilidad:** Ejecuta la operación downloadCsv definida por la clase (sin clase).
  - **Parámetros principales:** `filename, header`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

### Archivo: `resources/js/modulos/paneles/administrador/inventario-logistica/transferencias.js`

#### Clase: `(sin clase)`

- **Método/función:** `updateLineNames` (línea 16)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `syncSelection` (línea 40)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `event`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/operaciones-comerciales

### Archivo: `resources/js/modulos/paneles/administrador/operaciones-comerciales/operaciones-comerciales.js`

#### Clase: `(sin clase)`

- **Método/función:** `applyFilters` (línea 13)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/paneles/administrador/organizacion

### Archivo: `resources/js/modulos/paneles/administrador/organizacion/organizacion.js`

#### Clase: `(sin clase)`

- **Método/función:** `applyFilters` (línea 14)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `setField` (línea 68)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `name, value`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `showStep` (línea 83)
  - **Responsabilidad:** Recupera y presenta el detalle de la información solicitada.
  - **Parámetros principales:** `index`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `validateStep` (línea 105)
  - **Responsabilidad:** Determina si la operación cumple las condiciones de autorización o validación.
  - **Parámetros principales:** `step`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `rowValues` (línea 138)
  - **Responsabilidad:** Ejecuta la operación rowValues definida por la clase (sin clase).
  - **Parámetros principales:** `row`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `prefill` (línea 146)
  - **Responsabilidad:** Ejecuta la operación prefill definida por la clase (sin clase).
  - **Parámetros principales:** `row, values`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `openForm` (línea 177)
  - **Responsabilidad:** Ejecuta la operación openForm definida por la clase (sin clase).
  - **Parámetros principales:** `action, row`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `actualizarRecurso` (línea 240)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/publico/asistente

### Archivo: `resources/js/modulos/publico/asistente/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `rutaBase` (línea 7)
  - **Responsabilidad:** Ejecuta la operación rutaBase definida por la clase (sin clase).
  - **Parámetros principales:** `(`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `escaparHtml` (línea 29)
  - **Responsabilidad:** Ejecuta la operación escaparHtml definida por la clase (sin clase).
  - **Parámetros principales:** `valor`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `desplazarChat` (línea 47)
  - **Responsabilidad:** Ejecuta la operación desplazarChat definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `guardarHistorial` (línea 51)
  - **Responsabilidad:** Registra la información recibida y coordina su persistencia.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `agregarMensaje` (línea 62)
  - **Responsabilidad:** Ejecuta la operación agregarMensaje definida por la clase (sin clase).
  - **Parámetros principales:** `rol, contenidoHtml, guardar = true`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `renderizarProductos` (línea 74)
  - **Responsabilidad:** Ejecuta la operación renderizarProductos definida por la clase (sin clase).
  - **Parámetros principales:** `productos`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `restaurarHistorial` (línea 81)
  - **Responsabilidad:** Ejecuta la operación restaurarHistorial definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `preguntarAsistente` (línea 103)
  - **Responsabilidad:** Ejecuta la operación preguntarAsistente definida por la clase (sin clase).
  - **Parámetros principales:** `pregunta`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/modulos/publico/catalogo

### Archivo: `resources/js/modulos/publico/catalogo/detalle.js`

#### Clase: `(sin clase)`

- **Método/función:** `establecerImagenPrincipal` (línea 29)
  - **Responsabilidad:** Ejecuta la operación establecerImagenPrincipal definida por la clase (sin clase).
  - **Parámetros principales:** `fuenteImagen, botonMiniatura`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `actualizarFlechasNavegacion` (línea 54)
  - **Responsabilidad:** Actualiza la información existente con los datos recibidos.
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `avanzarPasoGaleria` (línea 88)
  - **Responsabilidad:** Ejecuta la operación avanzarPasoGaleria definida por la clase (sin clase).
  - **Parámetros principales:** `desplazamiento`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `ocultarMiniaturaNoDisponible` (línea 118)
  - **Responsabilidad:** Ejecuta la operación ocultarMiniaturaNoDisponible definida por la clase (sin clase).
  - **Parámetros principales:** `imagenMiniatura`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `mostrarMarcadorPosicion` (línea 144)
  - **Responsabilidad:** Ejecuta la operación mostrarMarcadorPosicion definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

### Archivo: `resources/js/modulos/publico/catalogo/index.js`

#### Clase: `(sin clase)`

- **Método/función:** `mostrarImagenModal` (línea 29)
  - **Responsabilidad:** Ejecuta la operación mostrarImagenModal definida por la clase (sin clase).
  - **Parámetros principales:** `indice`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `quitarProductoDeLaDireccion` (línea 83)
  - **Responsabilidad:** Ejecuta la operación quitarProductoDeLaDireccion definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/nucleo

### Archivo: `resources/js/nucleo/cargador-modulos.js`

#### Clase: `(sin clase)`

- **Método/función:** `cargarModulo` (línea 8)
  - **Responsabilidad:** Ejecuta la operación cargarModulo definida por la clase (sin clase).
  - **Parámetros principales:** `ruta`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `cargarModulosDesdePagina` (línea 20)
  - **Responsabilidad:** Ejecuta la operación cargarModulosDesdePagina definida por la clase (sin clase).
  - **Parámetros principales:** `selector = '[data-js-modules]'`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

### Archivo: `resources/js/nucleo/cargador-recursos-panel.js`

#### Clase: `(sin clase)`

- **Método/función:** `cargarRecurso` (línea 13)
  - **Responsabilidad:** Ejecuta la operación cargarRecurso definida por la clase (sin clase).
  - **Parámetros principales:** `cargadores, ruta`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

### Archivo: `resources/js/nucleo/http.js`

#### Clase: `(sin clase)`

- **Método/función:** `csrfToken` (línea 7)
  - **Responsabilidad:** Ejecuta la operación csrfToken definida por la clase (sin clase).
  - **Parámetros principales:** No recibe parámetros explícitos.
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `request` (línea 14)
  - **Responsabilidad:** Ejecuta la operación request definida por la clase (sin clase).
  - **Parámetros principales:** `url, options = {}`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `get` (línea 41)
  - **Responsabilidad:** Realiza la operación específica definida por este método y devuelve su resultado.
  - **Parámetros principales:** `url, options = {}`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `post` (línea 49)
  - **Responsabilidad:** Ejecuta la operación post definida por la clase (sin clase).
  - **Parámetros principales:** `url, body, options = {}`
  - **Resultado:** `Promise/valor de la función` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

## Módulo: resources/js/utilidades

### Archivo: `resources/js/utilidades/debounce.js`

#### Clase: `(sin clase)`

- **Método/función:** `debounce` (línea 6)
  - **Responsabilidad:** Ejecuta la operación debounce definida por la clase (sin clase).
  - **Parámetros principales:** `callback, delay = 250`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.

### Archivo: `resources/js/utilidades/formato.js`

#### Clase: `(sin clase)`

- **Método/función:** `formatoMoneda` (línea 6)
  - **Responsabilidad:** Ejecuta la operación formatoMoneda definida por la clase (sin clase).
  - **Parámetros principales:** `value, currency = 'PEN'`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
- **Método/función:** `formatoFecha` (línea 15)
  - **Responsabilidad:** Ejecuta la operación formatoFecha definida por la clase (sin clase).
  - **Parámetros principales:** `value, options = {}`
  - **Resultado:** `mixed` según la firma observada.
  - **Capa arquitectónica:** Presentación / JavaScript
  - **Relación:** Coordina interacción de interfaz, solicitudes HTTP y actualización de componentes.
