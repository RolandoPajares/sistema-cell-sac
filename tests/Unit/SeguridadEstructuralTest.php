<?php

function formulariosBlade(string $directorio): array
{
    $formularios = [];
    $iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directorio));

    foreach ($iterador as $archivo) {
        if (! $archivo->isFile() || strtolower($archivo->getExtension()) !== 'php' || ! str_ends_with($archivo->getFilename(), '.blade.php')) {
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

test('los formularios Blade incluyen protección CSRF', function (): void {
    $formularios = formulariosBlade(resource_path('views'));

    expect($formularios)->not->toBeEmpty();

    foreach ($formularios as [$vista, $formulario]) {
        expect($formulario)->toContain('@csrf', "Formulario sin @csrf en {$vista}");
    }
});

test('el grupo de gestión exige autenticación, cuenta activa, sesión y permiso de ruta', function (): void {
    $rutas = file_get_contents(base_path('routes/web.php'));
    $inicio = strpos($rutas, "Route::prefix('gestion')");

    expect($inicio)->not->toBeFalse();

    $bloque = substr($rutas, $inicio, 400);
    expect($bloque)->toContain("'auth'")
        ->toContain("'cuenta.activa'")
        ->toContain("'sesion.usuario'")
        ->toContain("'permiso:ruta'");
});

test('las rutas mutantes principales no usan GET', function (): void {
    $rutas = file_get_contents(base_path('routes/web.php'));

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
