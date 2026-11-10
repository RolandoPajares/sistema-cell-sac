# Diagnóstico y diccionario técnico del RBAC

## Diagnóstico

La matriz utiliza el catálogo RBAC existente. No se crearon tablas nuevas ni se ejecutaron migraciones para esta mejora.

La jerarquía persistida es:

`roles → roles_permisos → permisos → modulos/pantallas`

Los menús se resuelven mediante `roles_menus → menus → elementos_menu`, y cada elemento puede enlazar una pantalla y un permiso. Las asignaciones globales y por sede se resuelven mediante `usuarios_roles` y `usuarios_sedes_roles`. `ServicioDelegacion` calcula el alcance delegable a partir de esas asignaciones; no existe una tabla `delegaciones_permisos` en el esquema actual.

La matriz consulta módulos habilitados, pantallas habilitadas y permisos activos mediante relaciones Eloquent. Por eso un módulo sin pantallas, una pantalla sin acciones o un catálogo vacío se representan como estados vacíos sin inventar registros.

## Diccionario de datos

Tipos y restricciones corresponden al esquema inspeccionado en la base de datos activa. `PK` es clave primaria, `FK` es clave foránea y `UQ` es restricción única.

### `roles`

| Campo | Tipo | NULL / predeterminado | Restricción | Relación / descripción |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No / autoincremental | PK | Identificador del rol |
| `codigo` | VARCHAR(40) | No | UQ | Identificador estable del rol |
| `nombre` | VARCHAR(120) | No |  | Nombre mostrado |
| `descripcion` | VARCHAR(255) | Sí / NULL |  | Descripción funcional |
| `alcance_permitido` | VARCHAR(20) | No / `sede` | UQ compuesta con `id` | Alcance máximo del rol |
| `es_sistema` | TINYINT | No / `0` | Índice con `activo` | Marca de catálogo protegido por el sistema |
| `activo` | TINYINT | No / `1` | Índice con `es_sistema` | Estado del rol |
| `creado_en` | TIMESTAMP | Sí / NULL |  | Fecha de creación |
| `actualizado_en` | TIMESTAMP | Sí / NULL |  | Fecha de actualización |

### `permisos`

| Campo | Tipo | NULL / predeterminado | Restricción | Relación / descripción |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No / autoincremental | PK | Identificador del permiso |
| `modulo_id` | BIGINT UNSIGNED | No | FK | Referencia a `modulos.id` |
| `pantalla_id` | BIGINT UNSIGNED | Sí / NULL | FK | Referencia opcional a `pantallas.id` |
| `codigo` | VARCHAR(160) | No | UQ | Código estable del permiso |
| `accion` | VARCHAR(60) | No | Índice con `modulo_id` y `activo` | Acción catalogada |
| `nombre` | VARCHAR(160) | No |  | Etiqueta del permiso |
| `es_sistema` | TINYINT | No / `1` |  | Marca de catálogo del sistema |
| `activo` | TINYINT | No / `1` |  | Estado del permiso |
| `creado_en`, `actualizado_en` | TIMESTAMP | Sí / NULL |  | Auditoría temporal |

Índices relevantes: `pantalla_id`, `(pantalla_id, modulo_id)`, `(modulo_id, accion, activo)` y únicos `codigo` y `(id, pantalla_id)`.

### `modulos`

| Campo | Tipo | NULL / predeterminado | Restricción | Descripción |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No / autoincremental | PK | Identificador del módulo |
| `codigo` | VARCHAR(80) | No | UQ | Código estable |
| `nombre` | VARCHAR(140) | No |  | Nombre visible |
| `descripcion` | VARCHAR(255) | Sí / NULL |  | Descripción |
| `icono` | VARCHAR(80) | Sí / NULL |  | Icono visual |
| `orden` | INT UNSIGNED | No / `0` | Índice con `habilitado` | Orden de presentación |
| `habilitado` | TINYINT | No / `1` | Índice con `orden` | Disponibilidad del catálogo |
| `creado_en`, `actualizado_en` | TIMESTAMP | Sí / NULL |  | Auditoría temporal |

### `pantallas`

| Campo | Tipo | NULL / predeterminado | Restricción | Relación / descripción |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No / autoincremental | PK | Identificador de pantalla |
| `modulo_id` | BIGINT UNSIGNED | No | FK | Referencia a `modulos.id` |
| `codigo` | VARCHAR(120) | No | UQ | Código estable |
| `nombre` | VARCHAR(160) | No |  | Nombre visible |
| `ruta` | VARCHAR(160) | Sí / NULL |  | Ruta nombrada opcional |
| `icono` | VARCHAR(80) | Sí / NULL |  | Icono visual |
| `habilitada` | TINYINT | No / `1` | Índice con `modulo_id` | Estado de la pantalla |
| `creado_en`, `actualizado_en` | TIMESTAMP | Sí / NULL |  | Auditoría temporal |

### `roles_permisos`

| Campo | Tipo | NULL / predeterminado | Restricción | Relación / descripción |
|---|---|---|---|---|
| `rol_id` | BIGINT UNSIGNED | No | PK compuesta, FK | `roles.id` |
| `permiso_id` | BIGINT UNSIGNED | No | PK compuesta, FK | `permisos.id` |
| `alcance` | VARCHAR(20) | No / `sede` | PK compuesta | Ámbito concedido |
| `creado_en` | TIMESTAMP | No / current timestamp |  | Fecha de asignación |

Índices: `(rol_id, alcance)` y `(permiso_id, rol_id)`. Las FKs garantizan que no se asignen permisos inexistentes.

### `usuarios_roles`

| Campo | Tipo | NULL / predeterminado | Restricción | Relación / descripción |
|---|---|---|---|---|
| `usuario_id` | BIGINT UNSIGNED | No | PK compuesta, FK | `usuarios.id` |
| `rol_id` | BIGINT UNSIGNED | No | PK compuesta, FK | `roles.id` |
| `asignado_por_usuario_id` | BIGINT UNSIGNED | Sí / NULL | FK | Usuario que asignó |
| `asignado_en` | TIMESTAMP | No |  | Fecha de asignación |
| `vigente_hasta` | TIMESTAMP | Sí / NULL |  | Vencimiento opcional |
| `activo` | TINYINT | No / `1` |  | Estado de la asignación |

### `usuarios_sedes_roles`

| Campo | Tipo | NULL / predeterminado | Restricción | Relación / descripción |
|---|---|---|---|---|
| `usuario_id` | BIGINT UNSIGNED | No | PK compuesta, FK | `usuarios.id` |
| `sede_id` | BIGINT UNSIGNED | No | PK compuesta, FK | `sedes.id` |
| `rol_id` | BIGINT UNSIGNED | No | PK compuesta, FK | `roles.id` |
| `alcance_asignacion` | VARCHAR(20) | No / `sede` |  | Ámbito de la asignación |
| `asignado_por_usuario_id` | BIGINT UNSIGNED | Sí / NULL | FK | Usuario que asignó |
| `asignado_en` | TIMESTAMP | No |  | Fecha de asignación |
| `vigente_hasta` | TIMESTAMP | Sí / NULL |  | Vencimiento opcional |
| `activo` | TINYINT | No / `1` |  | Estado |

### `menus`

| Campo | Tipo | NULL / predeterminado | Restricción | Descripción |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No / autoincremental | PK | Identificador |
| `codigo` | VARCHAR(100) | No | UQ | Código del menú |
| `nombre` | VARCHAR(160) | No |  | Nombre |
| `contexto` | VARCHAR(40) | No / `sidebar` |  | Contexto de navegación |
| `habilitado` | TINYINT | No / `1` |  | Estado |
| `creado_en`, `actualizado_en` | TIMESTAMP | Sí / NULL |  | Auditoría temporal |

### `elementos_menu`

| Campo | Tipo | NULL / predeterminado | Restricción | Relación / descripción |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No / autoincremental | PK | Identificador |
| `menu_id` | BIGINT UNSIGNED | No | FK | `menus.id` |
| `padre_id` | BIGINT UNSIGNED | Sí / NULL | FK autorreferenciada | Elemento padre |
| `pantalla_id` | BIGINT UNSIGNED | Sí / NULL | FK | `pantallas.id` |
| `permiso_id` | BIGINT UNSIGNED | Sí / NULL | FK | `permisos.id` |
| `codigo` | VARCHAR(120) | No |  | Código del elemento |
| `etiqueta` | VARCHAR(160) | No |  | Texto visible |
| `icono` | VARCHAR(80) | Sí / NULL |  | Icono |
| `tipo` | VARCHAR(30) | No |  | Tipo de nodo |
| `orden` | INT UNSIGNED | No / `0` |  | Orden |
| `habilitado` | TINYINT | No / `1` |  | Estado |
| `creado_en`, `actualizado_en` | TIMESTAMP | Sí / NULL |  | Auditoría temporal |

### `roles_menus`

| Campo | Tipo | NULL / predeterminado | Restricción | Relación / descripción |
|---|---|---|---|---|
| `rol_id` | BIGINT UNSIGNED | No | PK compuesta, FK | `roles.id` |
| `menu_id` | BIGINT UNSIGNED | No | PK compuesta, FK | `menus.id` |
| `creado_en` | TIMESTAMP | No / current timestamp |  | Fecha de vinculación |

Índice adicional: `(menu_id, rol_id)`.

### `registros_auditoria`

| Campo | Tipo | NULL / predeterminado | Restricción / índice | Descripción |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED | No / autoincremental | PK | Identificador del evento |
| `usuario_id` | BIGINT UNSIGNED | Sí / NULL | FK a `usuarios.id`; índice con fecha | Actor |
| `accion` | VARCHAR(80) | No |  | Operación registrada |
| `entidad` | VARCHAR(80) | No | Índice con `entidad_id` | Tipo de entidad |
| `entidad_id` | BIGINT | Sí / NULL | Índice con `entidad` | Identificador de entidad |
| `valores_anteriores` | LONGTEXT | Sí / NULL |  | Estado previo serializado |
| `valores_nuevos` | LONGTEXT | Sí / NULL |  | Estado posterior serializado |
| `direccion_ip` | VARCHAR(45) | Sí / NULL |  | IP del actor |
| `creado_en` | TIMESTAMP | No / current timestamp |  | Fecha de inserción |
| `identificador_universal_evento` | CHAR(36) | Sí / NULL | UQ | Idempotencia/correlación del evento |
| `rol_codigo` | VARCHAR(40) | Sí / NULL | Índice | Código del rol contextual |
| `empresa_id` | BIGINT UNSIGNED | Sí / NULL | FK e índice | Empresa contextual |
| `sede_id` | BIGINT UNSIGNED | Sí / NULL | FK e índice compuesto | Sede contextual |
| `modulo` | VARCHAR(80) | Sí / NULL | Índice | Módulo funcional |
| `resultado` | VARCHAR(20) | Sí / NULL | Índice | Resultado de la operación |
| `agente_usuario` | TEXT | Sí / NULL |  | Agente del cliente |
| `correlacion_id` | VARCHAR(64) | Sí / NULL | Índice | Correlación técnica |
| `observaciones` | TEXT | Sí / NULL |  | Detalle humano |
| `ocurrido_en` | DATETIME(6) | Sí / NULL | Índice | Momento funcional del evento |
| `tabla_afectada` | VARCHAR(100) | Sí / NULL | Índice compuesto | Tabla afectada |
| `identificador_entidad` | VARCHAR(128) | Sí / NULL | Índice compuesto | Identificador textual |
| `cambios` | LONGTEXT | Sí / NULL |  | Diferencias estructuradas |
| `origen` | VARCHAR(20) | No / `laravel` |  | Origen del registro |

Las FKs adicionales de auditoría apuntan a `sedes( id, empresa_id )` mediante la restricción compuesta existente. Los índices cubren empresa, sede/fecha, correlación, entidad, módulo, resultado, rol, tabla/entidad/fecha y usuario/fecha.

## Tablas relacionadas no duplicadas

`roles_menus` vincula roles y menús y `registros_auditoria` registra cambios de administración. Ambas tablas ya existen y se reutilizan. No existe `delegaciones_permisos`; la delegación vigente se calcula desde las asignaciones existentes y no requiere una tabla paralela.

## Diagrama de relaciones

Consulta el diagrama Mermaid en [`docs/rbac-relaciones.md`](rbac-relaciones.md).
