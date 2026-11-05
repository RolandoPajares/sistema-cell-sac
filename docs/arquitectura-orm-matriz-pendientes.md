# Matriz de arquitectura ORM artesanal

| Área | Estado | Evidencia | Pendiente externo |
| --- | --- | --- | --- |
| Conexión PDO segura | Completa | `PdoConexion` configura excepciones, fetch asociativo y emulación desactivada | Ninguno |
| Consultas parametrizadas | Completa | `PdoProductoDao` no concatena valores en SQL | Ninguno |
| Entidades y mapeo | Completa | `ProductoEntidad` y `VarianteProductoEntidad` | Ninguno |
| Relaciones | Completa | Producto carga sus variantes y valida el producto padre | Ninguno |
| CRUD artesanal | Completa | CRUD de productos y variantes | Ninguno |
| Transacciones | Completa | Sincronización de portada con rollback ante excepción | Ejecutar también en MariaDB aislada cuando el entorno sea entregado |
| Integración empresarial | Completa | `ServicioCatalogo` delega la portada al DAO durante alta/edición de producto | Ninguno |
| MVC/n-capas | Completa para el flujo elegido | Controlador de productos → `ServicioCatalogo` → `ProductoDao` → PDO | Otros módulos son propiedad de agentes distintos |
| Verificación MariaDB | Preparada, pendiente de ejecución externa | `PdoProductoDaoMariaAisladaTest` valida identidad, escritura, relación y rollback | Integrador debe ejecutar la prueba con la instancia QA aislada y `vendor/` disponible |
