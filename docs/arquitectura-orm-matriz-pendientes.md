# Matriz de arquitectura ORM artesanal

| Area | Estado | Evidencia | Pendiente externo |
| --- | --- | --- | --- |
| Conexion PDO segura | Completa | `PdoConexion` configura excepciones, fetch asociativo y emulacion desactivada | Ninguno |
| Consultas parametrizadas | Completa | `PdoProductoDao` no concatena valores en SQL | Ninguno |
| Entidades y mapeo | Completa | `ProductoEntidad` y `VarianteProductoEntidad` | Ninguno |
| Relaciones | Completa | Producto carga sus variantes y valida el producto padre | Ninguno |
| CRUD artesanal | Completa | CRUD de productos y variantes | Ninguno |
| Transacciones | Completa | Sincronizacion de portada con rollback ante excepcion | Ejecutar tambien en MariaDB aislada cuando el entorno sea entregado |
| Integracion empresarial | Completa | `ServicioCatalogo` delega la portada al DAO durante alta/edicion de producto | Ninguno |
| MVC/n-capas | Completa para el flujo elegido | Controlador de productos -> `ServicioGestionProductos` -> contratos/adaptadores -> `ServicioCatalogo`/`ProductoDao` -> PDO | Otros modulos son propiedad de agentes distintos |
| Prueba DAO local | Completa | `PdoProductoDaoTest`: 2 tests, 7 assertions | Ninguno |
| Prueba servicio MVC | Completa | Alta, actualizacion, efectos empresariales y rollback: conjunto especifico de 4 tests y 32 assertions | Ninguno |
| Contratos SOLID | Completa | `AlcanceProductos` y `AuditoriaProductos` desacoplan el servicio de clases finales | Ninguno |
| Verificacion MariaDB | Preparada, pendiente de ejecucion externa | `PdoProductoDaoMariaAisladaTest` fue omitida por variables QA ausentes; valida identidad, escritura, relacion y rollback | Integrador debe ejecutar con la instancia QA aislada |
| Regresion Feature web | Pendiente de ejecucion externa | La prueba llega al arnes y falla en una migracion compartida que consulta `information_schema` usando SQLite | Ejecutar con MariaDB aislada o corregir el arnes/migracion en la rama responsable |
| Suite Unit global | Parcial por entorno | 88 de 114 pasan; fallas restantes requieren `APP_KEY` y `public/build/manifest.json` | Integrador debe preparar configuracion de pruebas completa |
