# Guía de despliegue (pendiente de infraestructura)

1. Verificar PHP 8.3+, Composer, Node/Vite, MariaDB y extensiones.
2. Instalar dependencias en staging y compilar assets.
3. Configurar `.env` mediante secretos, `APP_ENV=production`, `APP_DEBUG=false`, key segura y DB de mínimos privilegios.
4. Respaldar; probar restauración; ejecutar migraciones incrementales en staging.
5. Configurar HTTPS, servidor web, storage, correo, colas/scheduler y logs.
6. Ejecutar smoke tests y obtener autorización antes de producción.

Los comandos exactos del servidor se definirán después de conocer infraestructura y dominio.
