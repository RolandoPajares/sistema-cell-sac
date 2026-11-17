<?php

use App\Http\Controllers\Api\CatalogoController;
use Illuminate\Support\Facades\Route;

// Agrupa las rutas de la API v1 y aplica el limite de solicitudes del catalogo.
Route::prefix('v1')->middleware('throttle:catalog-api')->name('api.v1.')->group(function (): void {
    // Define el endpoint GET que consulta la informacion indicada en la URL.
    Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalog.index');
    // Define el endpoint GET que consulta la informacion indicada en la URL.
    Route::get('/catalogo/{product}', [CatalogoController::class, 'show'])
        ->whereNumber('product')
        ->name('catalog.show');
});
