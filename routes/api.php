<?php

use App\Http\Controllers\Api\CatalogoController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:catalog-api')->name('api.v1.')->group(function (): void {
    Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalog.index');
    Route::get('/catalogo/{product}', [CatalogoController::class, 'show'])
        ->whereNumber('product')
        ->name('catalog.show');
});
