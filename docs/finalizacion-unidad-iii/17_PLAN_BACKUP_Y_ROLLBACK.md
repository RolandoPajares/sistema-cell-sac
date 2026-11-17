# Backup y rollback

Antes de cambios: backup verificable de MariaDB, copia de archivos, checksum, restauración en instancia aislada y registro del commit/migración. Rollback de aplicación: artefacto anterior y cachés controladas. Rollback de datos: únicamente migraciones inversas diseñadas y probadas; nunca improvisar `DROP` o `TRUNCATE`.
