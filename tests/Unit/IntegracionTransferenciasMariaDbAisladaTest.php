<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class);

it('checks MariaDB nullable-variant uniqueness and transfer reconciliation migration on an isolated MariaDB 10.4.32', function (): void {
    $pipe = getenv('TECHNOLOGY_CELL_QA_PIPE');
    $root = getenv('TECHNOLOGY_CELL_QA_ROOT');
    $database = getenv('TECHNOLOGY_CELL_QA_DATABASE');
    if (! is_string($pipe) || ! is_string($root) || ! is_string($database)) {
        $this->markTestSkipped('La prueba requiere una instancia MariaDB descartable identificada explicitamente.');
    }

    $temp = realpath(sys_get_temp_dir());
    $resolvedRoot = realpath($root);
    expect($temp)->not->toBeFalse()->and($resolvedRoot)->not->toBeFalse();
    if (! preg_match('/\Atc_ventas_[a-f0-9]{32}\z/', $pipe)
        || ! preg_match('/\Atc_ventas_laravel_[a-z0-9]+\z/', $database)
        || ! str_starts_with(strtolower($resolvedRoot), strtolower($temp.DIRECTORY_SEPARATOR.'technology_cell_ventas_pipe_'))) {
        throw new RuntimeException('La prueba rechazo una conexion que no esta aislada y desechable.');
    }

    $pdo = new PDO('mysql:host=.;port=1;unix_socket=\\\\.\\pipe\\'.$pipe.';dbname='.$database, 'root', '', [PDO::ATTR_TIMEOUT => 1]);
    $identity = $pdo->query('SELECT @@version, @@datadir, @@skip_networking, @@socket')->fetch(PDO::FETCH_NUM);
    if ($identity[0] !== '10.4.32-MariaDB' || realpath($identity[1]) !== realpath($resolvedRoot.DIRECTORY_SEPARATOR.'datos')
        || (int) $identity[2] !== 1 || $identity[3] !== $pipe) {
        throw new RuntimeException('La identidad de MariaDB no coincide con la instancia temporal indicada.');
    }

    config(['database.default' => 'mysql', 'database.connections.mysql.database' => $database, 'database.connections.mysql.url' => null]);
    DB::purge('mysql');
    DB::connection('mysql')->setPdo($pdo)->setReadPdo($pdo);
    $migration = require database_path('migrations/2026_11_03_220504_add_cantidad_devuelta_to_detalles_transferencias_existencias.php');
    $migration->up();
    $migration->up();
    expect(Schema::connection('mysql')->hasColumn('detalles_transferencias_existencias', 'cantidad_devuelta'))->toBeTrue()
        ->and(Schema::connection('mysql')->hasColumn('detalles_transferencias_existencias', 'cantidad_conciliada'))->toBeTrue();

    $before = DB::table('existencias_almacen')->count();
    DB::beginTransaction();
    try {
        $companyId = DB::table('empresas')->insertGetId([
            'codigo' => 'TRANSFER_QA_'.Str::upper(Str::random(8)), 'razon_social' => 'Empresa sintética QA',
        ]);
        $siteId = DB::table('sedes')->insertGetId([
            'empresa_id' => $companyId, 'codigo' => 'SEDE_QA', 'nombre' => 'Sede sintética QA',
        ]);
        $warehouseId = DB::table('almacenes')->insertGetId([
            'sede_id' => $siteId, 'codigo' => 'ALMACEN_QA', 'nombre' => 'Almacén sintético QA',
        ]);
        $categoryId = DB::table('categorias')->insertGetId([
            'empresa_id' => $companyId, 'nombre' => 'Categoría sintética QA',
        ]);
        $productId = DB::table('productos')->insertGetId([
            'empresa_id' => $companyId, 'categoria_id' => $categoryId,
            'categoria' => 'Categoría sintética QA', 'marca' => 'Marca QA',
            'nombre' => 'Producto QA', 'precio' => '10.00', 'existencias' => 1,
        ]);
        $balance = [
            'almacen_id' => $warehouseId, 'variante_id' => null, 'producto_id' => $productId,
            'sede_id' => $siteId, 'empresa_id' => $companyId,
            'cantidad_fisica' => 1, 'cantidad_reservada' => 0,
        ];
        DB::table('existencias_almacen')->insert($balance);

        try {
            DB::transaction(fn () => DB::table('existencias_almacen')->insert($balance));
            $this->fail('MariaDB aceptó una segunda existencia para el mismo almacén y producto.');
        } catch (QueryException $exception) {
            expect($exception->getCode())->toBe('23000');
        }
        expect(DB::table('existencias_almacen')->count())->toBe($before + 1);
    } finally {
        DB::rollBack();
    }
    expect(DB::table('existencias_almacen')->count())->toBe($before);
});
