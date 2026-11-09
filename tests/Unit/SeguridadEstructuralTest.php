<?php

/**
 * Ejecuta la operación formulariosBlade definida por la clase SeguridadEstructuralTest.
 *
 * @param  mixed  $directorio  Parámetro recibido por la operación.
 * @return array Resultado de la operación.
 */
function formulariosBlade(string $directorio): array
{
    $formularios = [];
    $iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directorio));

    foreach ($iterador as $archivo) {
        if (! $archivo->isFile() || ! str_ends_with(strtolower($archivo->getFilename()), '.blade.php')) {
            continue;
        }

        $contenido = file_get_contents($archivo->getPathname());
        preg_match_all('/<form\\b[\\s\\S]*?<\\/form>/i', $contenido, $coincidencias);
        foreach ($coincidencias[0] as $formulario) {
            $formularios[] = [$archivo->getPathname(), $formulario];
        }
    }

    return $formularios;
}

/**
 * Ejecuta la operación raizProyectoQaSeguridad definida por la clase SeguridadEstructuralTest.
 *
 * @return string Resultado de la operación.
 */
function raizProyectoQaSeguridad(): string
{
    return dirname(__DIR__, 2);
}

test('los formularios Blade mutantes incluyen protección CSRF', function (): void {
    $formularios = formulariosBlade(raizProyectoQaSeguridad().'/resources/views');

    expect($formularios)->not->toBeEmpty();

    foreach ($formularios as [$vista, $formulario]) {
        $esMutante = preg_match("~method\\s*=\\s*[\"']?(?:post|put|patch|delete)\\b~i", $formulario) === 1
            || preg_match("~(?:method_field\\s*\\(\\s*[\"']?(?:PUT|PATCH|DELETE)|@method\\s*\\(\\s*[\"']?(?:PUT|PATCH|DELETE))~i", $formulario) === 1;

        if (! $esMutante) {
            continue;
        }

        expect(str_contains($formulario, 'csrf_field') || str_contains($formulario, '@csrf'))->toBeTrue("Formulario sin CSRF en {$vista}");
    }
});

test('el grupo de gestión exige autenticación, cuenta activa, sesión y permiso de ruta', function (): void {
    $rutas = file_get_contents(raizProyectoQaSeguridad().'/routes/web.php');
    $inicio = strpos($rutas, "Route::prefix('gestion')");

    expect($inicio)->not->toBeFalse();

    $bloque = substr($rutas, $inicio, 400);
    expect($bloque)->toContain("'auth'")
        ->toContain("'cuenta.activa'")
        ->toContain("'sesion.usuario'")
        ->toContain("'permiso:ruta'");
});

test('las rutas mutantes principales no usan GET', function (): void {
    $rutas = file_get_contents(raizProyectoQaSeguridad().'/routes/web.php');

    preg_match_all('/Route::get\\([^;]+->name\\(\'([^\']+)\'\\)/s', $rutas, $coincidencias);
    $operacionesMutantes = [
        'store', 'update', 'create', 'delete', 'deactivate', 'cancel', 'approve',
        'status', 'activate', 'revoke', 'import', 'confirm', 'convert', 'send',
    ];

    foreach ($coincidencias[1] as $nombre) {
        foreach ($operacionesMutantes as $operacion) {
            expect(str_ends_with($nombre, ".{$operacion}"))->toBeFalse(
                "La operación mutante {$nombre} está declarada como GET"
            );
        }
    }
});
