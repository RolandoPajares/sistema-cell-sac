<?php

use App\Http\Controllers\Gestion\Productos\GestionProductosController;
use App\Models\Catalogo\Producto;
use App\Models\User;
use App\Servicios\ExportadorTabular;
use App\Servicios\ServicioInventario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

test('exporta productos filtrados en xlsx real y conserva csv operativo', function () {
    Schema::create('productos', function (Blueprint $table): void {
        $table->increments('id');
        $table->string('marca');
        $table->string('nombre');
        $table->unsignedInteger('categoria_id');
        $table->string('categoria');
        $table->decimal('precio', 10, 2);
        $table->decimal('precio_oferta', 10, 2)->nullable();
        $table->string('etiqueta')->nullable();
        $table->unsignedInteger('existencias')->default(0);
        $table->boolean('activo')->default(true);
    });
    DB::table('productos')->insert([
        ['id' => 1, 'marca' => 'Marca A', 'nombre' => 'Equipo activo', 'categoria_id' => 1, 'categoria' => 'Celulares', 'precio' => 1250, 'precio_oferta' => null, 'existencias' => 7, 'activo' => true],
        ['id' => 2, 'marca' => 'Marca B', 'nombre' => 'Equipo sin stock', 'categoria_id' => 2, 'categoria' => 'Accesorios', 'precio' => 50, 'precio_oferta' => 40, 'existencias' => 0, 'activo' => true],
    ]);
    $user = new User;
    $user->setRawAttributes(['id' => 3, 'nombre' => 'Agente de prueba', 'rol' => 'administrador']);
    $request = Request::create('/gestion/productos/exportar/excel', 'GET', ['estado' => 'activo']);
    $request->setUserResolver(static fn (): User => $user);
    $controller = new GestionProductosController;
    $inventario = Mockery::mock(ServicioInventario::class);
    $inventario->shouldReceive('alcanceConsulta')->twice()->andReturn(['empresa_id' => 1, 'sede_id' => null]);
    $inventario->shouldReceive('productosParaConsulta')->twice()->andReturnUsing(
        static fn (): Builder => Producto::query()->select('productos.*')->selectRaw('existencias AS existencias_alcance'),
    );

    $xlsx = $controller->export($request, 'excel', $inventario, new ExportadorTabular);
    $archiveFile = tempnam(sys_get_temp_dir(), 'tc-productos-');
    file_put_contents($archiveFile, $xlsx->getContent());
    $archive = new ZipArchive;
    $opened = $archive->open($archiveFile);
    $sheet = $opened === true ? $archive->getFromName('xl/worksheets/sheet1.xml') : false;
    if ($opened === true) {
        $archive->close();
    }
    unlink($archiveFile);

    $csvRequest = Request::create('/gestion/productos/exportar/csv', 'GET', ['estado' => 'activo']);
    $csvRequest->setUserResolver(static fn (): User => $user);
    $csv = $controller->export($csvRequest, 'csv', $inventario);
    ob_start();
    $csv->sendContent();
    $csvContent = ob_get_clean();

    expect($xlsx->headers->get('Content-Type'))->toBe('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->and($xlsx->headers->get('Content-Disposition'))->toContain('productos.xlsx')
        ->and($opened)->toBeTrue()
        ->and($sheet)->toContain('REPORTE DE PRODUCTOS')
        ->toContain('Equipo activo')
        ->toContain('Equipo sin stock')
        ->toContain('Precio de venta PEN')
        ->toContain('Productos sin stock')
        ->not->toContain('<f>');

    expect($csvContent)->toStartWith("\xEF\xBB\xBF")
        ->toContain('producto,marca,categoria,precio,stock,estado')
        ->toContain('Equipo activo')
        ->toContain('Equipo sin stock');
});
