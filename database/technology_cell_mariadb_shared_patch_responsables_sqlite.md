# Parche compartido pendiente: migración de responsables en SQLite

Archivo reservado al integrador:

`database/migrations/2026_11_05_120100_create_responsables_organizacionales_table.php`

## Cambio exacto

Dentro del cierre `$addChecks`, antes de consultar `information_schema.table_constraints`, agregar:

```php
if (DB::getDriverName() !== 'mysql') {
    return;
}
```

Debe quedar:

```php
$addChecks = static function (): void {
    if (DB::getDriverName() !== 'mysql') {
        return;
    }

    $checks = [
        'ck_responsables_recurso' => "(tipo_recurso = 'sede' AND almacen_id IS NULL) OR (tipo_recurso = 'almacen' AND almacen_id IS NOT NULL)",
        'ck_responsables_vigencia' => 'vigente_hasta IS NULL OR vigente_hasta >= vigente_desde',
    ];
```

Laravel identifica MariaDB con el driver `mysql`, por lo que las comprobaciones `information_schema` continúan ejecutándose en MariaDB. SQLite seguirá creando la tabla mediante Blueprint, pero no intentará consultar `information_schema` ni agregar sintaxis de restricciones exclusiva de MariaDB.

## Evidencia

`MigrationCoverageTest` falla actualmente con:

`SQLSTATE[HY000]: no such table: information_schema.table_constraints`

El fallo ocurre en la línea 23 de esa migración durante `migrate:fresh` sobre SQLite. La prueba debe repetirse después de aplicar este parche; no se modificó el archivo compartido en `agent/mariadb`.

