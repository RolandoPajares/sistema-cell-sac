# Resultado de auditoría de Unidad III

Fecha de verificación: 2026-11-14  
Proyecto: `C:\xampp\htdocs\dwa2\sistema-cell-sac`  
Base inspeccionada: `bd_cell`, únicamente con consultas de lectura.

## Estado comprobado

| Requisito | Evidencia ejecutada | Resultado |
|---|---|---|
| Fetch, promesas, JSON y DOM | Revisión de consumidores Fetch y `node --check` sobre los archivos JavaScript | Conforme en sintaxis; flujos Fetch existentes identificados |
| API REST | `GET /api/v1/catalogo`, filtros, detalle existente, ID inexistente y validación `per_page` | 200, 200, 404 y 422 correctos |
| Rate limiting y validación | `routes/api.php`, `FiltroCatalogoRequest` y middleware `throttle:catalog-api` | Conforme |
| Storage e imágenes | `public/storage`, `storage/app/public`, `UploadedFile`, `Storage::disk('public')` | Enlace activo; 5 archivos públicos y 6 privados |
| CSV, Excel y PDF | `ExportadorTabular::download()` para los tres formatos | Los tres respondieron HTTP 200 con MIME y descarga correctos |
| Correo | `ConfirmacionMensajeContacto`, vista Blade y render directo del mailable | Render correcto; envío tolerante a fallos del proveedor |
| Rendimiento | Cinco consultas reales al catálogo con `per_page=20` | 5/5 HTTP 200; promedio 50.95 ms |
| Índices | `SHOW INDEX` sobre productos, categorías, existencias y movimientos | Índices de catálogo, stock y kardex presentes |
| Preflight de despliegue | `php artisan technology:preflight --strict` | Conexión y tablas mínimas OK; 3 observaciones de escritura del entorno automatizado |
| Regresión Pest | Suite Unit y Feature con `DB_URL` aislada | 281 correctas, 18 omitidas, 0 fallos y 0 errores |

## Cambios realizados

- Se agregó el mailable `App\Mail\ConfirmacionMensajeContacto`.
- Se agregó la vista `resources/views/emails/contacto/confirmacion.blade.php`.
- `ServicioContacto` conserva el registro y encola confirmación solo para contactos con correo válido; una falla de correo no elimina ni invalida el mensaje guardado.
- Se agregaron pruebas de contrato para API y correo.
- Se agregó `php artisan technology:preflight --strict` para revisar configuración y requisitos operativos sin escribir en la base.
- La suite quedó reproducible: Unit 133 pruebas (124 aprobadas, 9 omitidas) y Feature 166 pruebas (157 aprobadas, 9 omitidas). Se aisló el logger de pruebas hacia `stderr` para que la validación no dependa de permisos del sandbox sobre `storage/logs`.
- `phpunit.xml` fuerza `DB_URL` vacío para impedir que una variable global `jdbc:` contamine las pruebas SQLite.
- `tests/Pest.php` fija el entorno de pruebas en SQLite en memoria, cache array, correo array y cola sync para evitar contaminación de variables externas.

## Observaciones que no se deben ocultar

- PHPUnit estándar quedó operativo al retirar el bootstrap que esta sesión no podía leer; `php artisan test --testsuite=Unit` y `php artisan test --testsuite=Feature` pasaron 281 pruebas y omitieron 18 en total.
- `APP_DEBUG` quedó en `false` en el `.env` del proyecto; no se deben copiar secretos del `.env` a producción.
- El preflight ve `storage/framework`, `storage/framework/cache` y `storage/logs` como no escribibles desde el entorno de automatización. Deben tener permisos de escritura para el usuario del servidor web en la instalación local/producción.
- No se ejecutaron migraciones, inserts, updates, deletes ni cambios de esquema sobre `bd_cell`.

## Dictamen

La implementación verificable de la Unidad III quedó cerrada. El preflight conserva tres advertencias de escritura porque el entorno automatizado devuelve `is_writable=false` para `storage`; las ACL de Windows del proyecto sí muestran permiso de modificación. Debe confirmarse en el Windows donde se ejecute Apache/PHP.
