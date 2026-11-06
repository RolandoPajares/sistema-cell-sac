<?php

/**
 * Ejecuta la operación contenidoApiQa definida por la clase ApiContractTest.
 *
 * @param  mixed  $ruta  Parámetro recibido por la operación.
 * @return string Resultado de la operación.
 */
function contenidoApiQa(string $ruta): string
{
    $contenido = file_get_contents(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.$ruta);

    expect($contenido)->toBeString();

    return $contenido;
}

test('el catálogo API está limitado a lectura y protegido por rate limiting', function (): void {
    $rutas = contenidoApiQa('routes/api.php');

    expect($rutas)->toContain("Route::prefix('v1')->middleware('throttle:catalog-api')")
        ->toContain("Route::get('/catalogo'")
        ->toContain("Route::get('/catalogo/{product}'")
        ->not->toContain('Route::post(')
        ->not->toContain('Route::put(')
        ->not->toContain('Route::patch(')
        ->not->toContain('Route::delete(');
});

test('el controlador de catálogo API devuelve recursos JSON y valida el producto activo', function (): void {
    $controlador = contenidoApiQa('app/Http/Controllers/Api/CatalogoController.php');

    expect($controlador)->toContain('AnonymousResourceCollection')
        ->toContain('return RecursoProducto::collection($products);')
        ->toContain('return new RecursoProducto($product);')
        ->toContain('abort_unless($product->activo, 404)')
        ->toContain('FiltroCatalogoRequest');
});

test('las rutas API tienen nombres versionados y controlador explícito', function (): void {
    $rutas = contenidoApiQa('routes/api.php');

    expect($rutas)->toContain("->name('api.v1.')")
        ->toContain('CatalogoController::class')
        ->toContain("->name('catalog.index')")
        ->toContain("->name('catalog.show')");
});
