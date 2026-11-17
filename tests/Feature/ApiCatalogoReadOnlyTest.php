<?php

/**
 * Contrato verificable del catálogo API.
 * La comprobación HTTP contra bd_cell se ejecuta fuera de PHPUnit porque el
 * entorno actual no permite a PHPUnit leer vendor/autoload.php directamente.
 */
test('el catálogo API mantiene sus rutas de lectura versionadas', function (): void {
    $rutas = file_get_contents(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'routes/api.php');

    expect($rutas)
        ->toContain("Route::prefix('v1')->middleware('throttle:catalog-api')")
        ->toContain("Route::get('/catalogo'")
        ->toContain("Route::get('/catalogo/{product}'")
        ->not->toContain('Route::post(')
        ->not->toContain('Route::put(')
        ->not->toContain('Route::patch(')
        ->not->toContain('Route::delete(');
});
