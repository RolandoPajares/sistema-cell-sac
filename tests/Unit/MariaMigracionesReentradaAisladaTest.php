<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\IsolatedMariaDatabase;
use Tests\TestCase;

uses(TestCase::class);

it('verifica tipos preservacion y reentrada de ambas migraciones solo en MariaDB aislado', function (): void {
    $pipe = getenv('TECHNOLOGY_CELL_QA_PIPE');
    $root = getenv('TECHNOLOGY_CELL_QA_ROOT');
    $database = getenv('TECHNOLOGY_CELL_QA_DATABASE');
    if (! is_string($pipe) || ! is_string($root) || ! is_string($database)) {
        $this->markTestSkipped('Requiere la copia MariaDB descartable con migraciones de prueba aplicadas.');
    }

    $temp = realpath(sys_get_temp_dir());
    $resolvedRoot = realpath($root);
    if ($temp === false || $resolvedRoot === false
        || ! preg_match('/\Atc_ventas_[a-f0-9]{32}\z/', $pipe)
        || ! preg_match('/\Atc_ventas_laravel_[a-z0-9]+\z/', $database)
        || ! str_starts_with(strtolower($resolvedRoot), strtolower($temp.DIRECTORY_SEPARATOR.'technology_cell_ventas_pipe_'))) {
        throw new RuntimeException('La conexion indicada no es una instancia temporal autorizada.');
    }

    $pdo = new PDO('mysql:host=.;port=1;unix_socket=\\\\.\\pipe\\'.$pipe.';dbname='.$database, 'root', '', [PDO::ATTR_TIMEOUT => 1]);
    $identity = $pdo->query('SELECT @@version, @@datadir, @@skip_networking, @@socket')->fetch(PDO::FETCH_NUM);
    if ($identity[0] !== '10.4.32-MariaDB' || realpath($identity[1]) !== realpath($resolvedRoot.DIRECTORY_SEPARATOR.'datos')
        || (int) $identity[2] !== 1 || $identity[3] !== $pipe) {
        throw new RuntimeException('La identidad MariaDB no corresponde al directorio descartable.');
    }

    [$pdo, $database] = IsolatedMariaDatabase::createFresh($pdo, $resolvedRoot, $pipe);
    config(['database.default' => 'mysql', 'database.connections.mysql.database' => $database, 'database.connections.mysql.url' => null]);
    DB::purge('mysql');
    DB::connection('mysql')->setPdo($pdo)->setReadPdo($pdo);

    expect(Schema::hasColumn('ventas', 'canal'))->toBeFalse()
        ->and(Schema::hasColumn('ventas', 'observaciones'))->toBeFalse()
        ->and(Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_devuelta'))->toBeFalse()
        ->and(Schema::hasColumn('detalles_transferencias_existencias', 'cantidad_conciliada'))->toBeFalse();
    $companyId = DB::table('empresas')->insertGetId(['codigo' => 'MIGRACION_QA', 'razon_social' => 'Empresa sintética QA']);
    $siteId = DB::table('sedes')->insertGetId(['empresa_id' => $companyId, 'codigo' => 'SEDE_QA', 'nombre' => 'Sede sintética QA']);
    $warehouseId = DB::table('almacenes')->insertGetId(['sede_id' => $siteId, 'codigo' => 'ALMACEN_QA', 'nombre' => 'Almacén sintético QA']);
    DB::table('ventas')->insert([
        'empresa_id' => $companyId, 'sede_id' => $siteId, 'almacen_id' => $warehouseId,
        'codigo' => 'ANTERIOR', 'total' => '12.34',
    ]);
    $saleBefore = (array) DB::table('ventas')->where('codigo', 'ANTERIOR')->sole();
    $historyBefore = DB::table('migraciones_laravel')->count();

    $salesMigration = require database_path('migrations/2026_11_03_192348_agregar_canal_y_observaciones_a_ventas.php');
    $transferMigration = require database_path('migrations/2026_11_03_220504_add_cantidad_devuelta_to_detalles_transferencias_existencias.php');
    $salesMigration->up();
    $saleAfter = (array) DB::table('ventas')->where('codigo', 'ANTERIOR')->sole();
    expect(array_intersect_key($saleAfter, $saleBefore))->toBe($saleBefore)
        ->and($saleAfter['canal'])->toBeNull()
        ->and($saleAfter['observaciones'])->toBeNull();
    $salesMigration->up();
    $transferMigration->up();
    $transferMigration->up();

    expect((array) DB::table('ventas')->where('codigo', 'ANTERIOR')->sole())->toBe($saleAfter)
        ->and(DB::table('migraciones_laravel')->count())->toBe($historyBefore);
    $columns = DB::table('information_schema.COLUMNS')->where('TABLE_SCHEMA', $database)
        ->where(function ($query): void {
            $query->whereIn('COLUMN_NAME', ['canal', 'observaciones', 'cantidad_devuelta', 'cantidad_conciliada']);
        })->get(['TABLE_NAME', 'COLUMN_NAME', 'COLUMN_TYPE', 'IS_NULLABLE', 'COLUMN_DEFAULT'])
        ->keyBy('COLUMN_NAME');
    expect($columns['canal']->COLUMN_TYPE)->toBe('varchar(30)')
        ->and($columns['canal']->IS_NULLABLE)->toBe('YES')
        ->and($columns['observaciones']->COLUMN_TYPE)->toBe('varchar(500)')
        ->and($columns['observaciones']->IS_NULLABLE)->toBe('YES')
        ->and($columns['cantidad_devuelta']->COLUMN_TYPE)->toBe('decimal(18,6)')
        ->and($columns['cantidad_devuelta']->IS_NULLABLE)->toBe('NO')
        ->and($columns['cantidad_conciliada']->COLUMN_TYPE)->toBe('decimal(18,6)')
        ->and($columns['cantidad_conciliada']->IS_NULLABLE)->toBe('NO');
});
