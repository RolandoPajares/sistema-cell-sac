<?php

use App\Http\Controllers\Gestion\Analitica\DashboardGlobal\DashboardGlobalSedesController;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

test('agrupa importes reales por sede y excluye pedidos cancelados en sqlite temporal', function () {
    Schema::create('sedes', function (Blueprint $table): void {
        $table->id();
        $table->string('nombre');
    });
    Schema::create('pedidos', function (Blueprint $table): void {
        $table->increments('id');
        $table->unsignedBigInteger('sede_id');
        $table->string('canal');
        $table->string('estado');
        $table->decimal('total', 10, 2);
        $table->timestamp('creado_en');
    });
    DB::table('sedes')->insert([['id' => 1, 'nombre' => 'Central'], ['id' => 2, 'nombre' => 'Norte']]);
    DB::table('pedidos')->insert([
        ['sede_id' => 1, 'canal' => 'ecommerce', 'estado' => 'Pagado', 'total' => 40, 'creado_en' => now()],
        ['sede_id' => 1, 'canal' => 'ecommerce', 'estado' => 'Cancelado', 'total' => 90, 'creado_en' => now()],
        ['sede_id' => 1, 'canal' => 'ecommerce', 'estado' => 'Anulado', 'total' => 70, 'creado_en' => now()],
        ['sede_id' => 2, 'canal' => 'ecommerce', 'estado' => 'Pagado', 'total' => 60, 'creado_en' => now()],
    ]);
    $request = Request::create('/gestion/dashboard-global/sedes', 'GET', ['periodo' => 30, 'sede' => 1]);
    $request->setUserResolver(static function (): User {
        $user = new User;
        $user->setRawAttributes(['id' => 1, 'rol' => 'administrador']);

        return $user;
    });

    $view = (new DashboardGlobalSedesController)->index($request, app(ServicioAutorizacion::class));

    expect($view->getData()['results'])->toHaveCount(1)
        ->and($view->getData()['results']->first()->nombre)->toBe('Central')
        ->and($view->getData()['results']->first()->pedidos)->toBe(1)
        ->and($view->getData()['results']->first()->ingresos)->toBe(40.0);
});

test('muestra estado vacio cuando la copia heredada no tiene sedes', function () {
    Schema::create('pedidos', function (Blueprint $table): void {
        $table->increments('id');
        $table->string('estado');
        $table->decimal('total', 10, 2);
        $table->timestamp('creado_en');
    });
    $request = Request::create('/gestion/dashboard-global/sedes', 'GET');
    $request->setUserResolver(static function (): User {
        $user = new User;
        $user->setRawAttributes(['id' => 1, 'rol' => 'administrador']);

        return $user;
    });

    $view = (new DashboardGlobalSedesController)->index($request, app(ServicioAutorizacion::class));

    expect($view->getData()['hasSite'])->toBeFalse()
        ->and($view->getData()['results'])->toBeEmpty();
});
