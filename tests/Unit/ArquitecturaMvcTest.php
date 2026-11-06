<?php

function archivosDeProyecto(string $directorio, string $extension): array
{
    $archivos = [];
    $iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directorio));

    foreach ($iterador as $archivo) {
        $coincideExtension = strtolower($extension) === 'blade.php'
            ? str_ends_with(strtolower($archivo->getFilename()), '.blade.php')
            : strtolower($archivo->getExtension()) === strtolower($extension);

        if ($archivo->isFile() && $coincideExtension) {
            $archivos[] = $archivo->getPathname();
        }
    }

    sort($archivos);

    return $archivos;
}

function raizProyectoQa(): string
{
    return dirname(__DIR__, 2);
}

test('el front controller delega cada solicitud al bootstrap de Laravel', function (): void {
    $frontController = file_get_contents(raizProyectoQa().'/public/index.php');

    expect($frontController)->toBeString()
        ->toContain("require __DIR__.'/../vendor/autoload.php';")
        ->toContain("require_once __DIR__.'/../bootstrap/app.php';")
        ->toContain('Request::capture()')
        ->toContain('$app->handleRequest(');
});

test('todas las clases de controlador importadas por web.php tienen archivo PSR-4', function (): void {
    $routes = file_get_contents(raizProyectoQa().'/routes/web.php');
    preg_match_all('/^use (App\\\\Http\\\\Controllers\\\\[^;]+);$/m', $routes, $coincidencias);

    expect($coincidencias[1])->not->toBeEmpty();

    foreach ($coincidencias[1] as $claseCompleta) {
        $claseSinAlias = preg_replace('/\\s+as\\s+[^\\s]+$/', '', $claseCompleta);
        $rutaRelativa = str_replace('App\\', 'app\\', $claseSinAlias).'.php';
        $rutaRelativa = str_replace('\\', DIRECTORY_SEPARATOR, $rutaRelativa);

        expect(is_file(raizProyectoQa().DIRECTORY_SEPARATOR.$rutaRelativa))->toBeTrue(
            "No existe el controlador importado: {$claseCompleta} ({$rutaRelativa})"
        );
    }
});

test('las vistas Blade no contienen acceso directo a SQL o conexiones', function (): void {
    $patronesProhibidos = [
        '/\\bDB::/i',
        '/\\bPDO\\b/i',
        '/\\bmysqli\\b/i',
        '/\\bSELECT\\s+[A-Za-z*][A-Za-z0-9_*,\\s]*\\s+FROM\\b/i',
        '/\\bINSERT\\s+INTO\\b/i',
        '/\\bUPDATE\\s+[A-Za-z_][A-Za-z0-9_]*\\s+SET\\b/i',
        '/\\bDELETE\\s+FROM\\b/i',
    ];

    foreach (archivosDeProyecto(raizProyectoQa().'/resources/views', 'blade.php') as $vista) {
        $contenido = file_get_contents($vista);
        expect($contenido)->toBeString();

        foreach ($patronesProhibidos as $patron) {
            expect(preg_match($patron, $contenido))->toBe(0, "SQL/conexión embebida en {$vista}");
        }
    }
});

test('la capa de servicios no renderiza vistas directamente', function (): void {
    $servicios = archivosDeProyecto(raizProyectoQa().'/app/Servicios', 'php');

    expect($servicios)->not->toBeEmpty();

    foreach ($servicios as $servicio) {
        $contenido = file_get_contents($servicio);
        expect($contenido)->toBeString()
            ->and(preg_match('/\\bview\\s*\\(/i', $contenido))->toBe(0)
            ->and($contenido)->not->toContain("resources/views");
    }
});
