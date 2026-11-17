# Matriz de cumplimiento frente al sílabo — Unidad III

Fuente contrastada: `SÃ_LABO (2026-2) - Desarrollo Aplicaciones Web II[R].pdf`, Unidad III, páginas 4 a 8.

## Resultado honesto

El proyecto tiene implementada y comprobada la mayor parte técnica de la Unidad III en el entorno local. No se puede declarar cumplimiento académico y productivo del 100% únicamente con el repositorio: el sílabo exige actividades externas (hosting/VPS, exposición, informe y pruebas de producción) que deben ejecutarse y evidenciarse.

| Tema del sílabo | Evidencia real en el proyecto | Estado técnico | Falta para cerrar al 100% |
|---|---|---:|---|
| AJAX, Fetch API, Promises, JSON y actualización dinámica sin recarga | `resources/js/nucleo/http.js`, `resources/js/publico/catalogo/detalle.js`, API de catálogo y pruebas de sintaxis/funcionamiento | Cumple | Evidencia audiovisual o captura de la práctica, si la solicita el docente |
| API REST: recursos, verbos HTTP, estados y endpoints | `routes/api.php`, `Api\\CatalogoController`, `RecursoProducto`, validación de filtros y respuestas 200/404/422 | Cumple técnicamente | Probar y registrar consumo desde Postman; consumir una API externa si se exige literalmente esa actividad |
| Seguridad de endpoints | `throttle:catalog-api`, validación de request, autorización/RBAC en el sistema y pruebas existentes | Parcialmente comprobado | Ejecutar una prueba formal de seguridad de API: autenticación/autorización, rate limit, IDOR, entradas inválidas y CORS según el entorno final |
| Storage y manejo de archivos | `Storage::disk('public')`, cargas de imágenes, `public/storage` y preflight | Cumple técnicamente | Confirmar permisos del servidor de producción y probar carga/eliminación con el usuario del servicio web |
| Envío de correo | `app/Mail/ConfirmacionMensajeContacto.php`, vista de correo y `ServicioContacto` con cola | Cumple en código | Configurar SMTP real, worker de cola y realizar envío controlado en staging/producción |
| Generación de PDF y Excel | `app/Servicios/Compartidos/Exportacion/ExportadorTabular.php`; CSV, XLSX y PDF respondieron 200 en prueba directa | Cumple técnicamente | Verificar descarga y visualización en el servidor final |
| Imágenes y comprobantes/recibos | Gestión de imágenes y exportaciones existentes; módulos de comprobantes en rutas/vistas | Parcialmente comprobado | Ejecutar el flujo completo de comprobante con datos de venta reales y conservar el artefacto generado |
| Variables `.env` y configuración productiva | `.env` con `APP_DEBUG=false`, `.env.example`, comando `technology:preflight` | Cumple localmente | Configurar secretos, dominio, HTTPS, base de datos, correo, cola y permisos en el servidor real |
| Optimización de consultas y rendimiento | Índices de MariaDB, paginación de catálogo y medición local de respuestas | Cumple localmente | Ejecutar prueba de carga reproducible contra staging/producción y conservar resultados |
| Hosting/VPS y despliegue | Guía de despliegue y preflight disponibles | No comprobado externamente | Realizar despliegue real, configurar servidor web/PHP, HTTPS, cron/worker, almacenamiento, backup y rollback |
| Simulación de producción: estrés y seguridad | No hay evidencia de ejecución de una herramienta de carga ni de una auditoría externa | Pendiente | Ejecutar pruebas de carga y seguridad; registrar fecha, URL/entorno, herramienta, parámetros y resultados |
| Informe final y exposición oral | Documentación técnica del proyecto disponible | Pendiente académico | Entregar informe Word/PDF, código fuente, presentación y evidencia audiovisual solicitada por el sílabo |

## Bloqueadores antes de afirmar “listo para despliegue”

1. Despliegue en un servidor real o staging con HTTPS.
2. SMTP real y worker de cola funcionando.
3. Permisos reales para `storage` y `bootstrap/cache`.
4. Prueba de carga y prueba de seguridad con resultados guardados.
5. Flujo completo de comprobante/recibo validado.
6. Informe final, presentación y evidencia audiovisual de acuerdo con la evaluación del curso.

Mientras estos puntos no tengan evidencia, el estado correcto es **preparado para despliegue**, no **desplegado y certificado al 100%**.
