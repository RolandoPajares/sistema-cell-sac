# Preparación para despliegue

Estado: no listo. `.env` actual es local, tiene debug habilitado y contiene credenciales; no usarlo como artefacto de producción. Faltan validar servidor web, PHP-FPM, extensiones, MariaDB, dominio, HTTPS, permisos, colas, correo, scheduler, almacenamiento, logs y monitoreo.

Debe probarse staging, backup/restauración, migración incremental y smoke test. No cambiar DNS, no publicar secretos y no migrar producción sin autorización.
