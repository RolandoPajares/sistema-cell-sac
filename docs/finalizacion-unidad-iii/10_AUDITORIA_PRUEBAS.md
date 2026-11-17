# Auditoría de pruebas

Hay 59 archivos de tests, incluyendo Feature y Unit para RBAC, comercio, catálogo, API, inventario, compras/ventas, finanzas, migraciones e integraciones MariaDB.

Resultado real de esta ronda: PHPUnit/Pest no inicia por el bootstrap. No se presentan como aprobadas las pruebas existentes ni los JSON históricos en `storage/test-results`; deben vincularse a commit, comando, entorno y fecha.

Antes de ejecutar: resolver bootstrap, revisar `phpunit.xml`, confirmar SQLite en memoria y reservar MariaDB descartable para pruebas específicas.
