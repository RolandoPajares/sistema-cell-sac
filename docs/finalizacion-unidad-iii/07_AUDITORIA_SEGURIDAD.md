# Auditoría de seguridad

Evidencia estática: autenticación Laravel, middleware de cuenta/sesión, permisos por ruta, RBAC, policies, contexto de auditoría y throttling. `.env` local contiene credenciales de base: no debe exponerse ni versionarse.

Falta verificar con pruebas: IDOR, alcance empresa/sede, revocación de roles, CSRF, XSS, SQL injection, mass assignment, uploads, descargas/exports, cookies seguras, CORS, rate limiting, errores y auditoría. El estado es **no verificado**, no “seguro”.

P0: aislar secretos, probar autorización en servidor, revisar uploads y confirmar `APP_DEBUG=false` antes de producción.
