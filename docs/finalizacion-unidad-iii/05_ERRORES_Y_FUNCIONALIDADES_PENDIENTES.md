# Errores y pendientes

## Reproducibles

1. PHPUnit/Pest no puede abrir `vendor/autoload.php` como bootstrap.
2. `git status` y `git diff` indican que la operación debe ejecutarse en un work tree.

## Pendientes críticos

- Determinar por qué PHPUnit no puede leer un autoload que PHP sí puede requerir.
- Verificar si el flujo de tests usa SQLite, SQL de referencia o MariaDB aislada.
- Validar que las 2 migraciones de `database/migrations` no oculten una instalación no reproducible.
- Confirmar stock, dinero, estados, autorización por sede, IDOR, uploads, reportes, correo y exports.

No se han modificado archivos para resolverlos.
