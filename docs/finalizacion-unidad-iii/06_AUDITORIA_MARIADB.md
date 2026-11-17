# Auditoría MariaDB

El proyecto contiene SQL de referencia en `storage` y pruebas de MariaDB aislada. `tests/IsolatedMariaDatabase.php` exige una instancia MariaDB 10.4.32 descartable, pipes nombrados y una expectativa de 97 tablas. Esto es una salvaguarda positiva: no debe ejecutarse sobre la base empresarial.

Solo hay dos migraciones Laravel en `database/migrations`; por tanto, el SQL/fixtures son parte material de la instalación. Debe compararse el SQL de referencia con modelos, migraciones y el número esperado de tablas.

No se ejecutaron `migrate`, importaciones ni cambios de base. No usar `migrate:fresh`, `refresh`, `db:wipe`, `TRUNCATE` ni `DROP`.
