<?php

/**
 * Ejecuta la operación raizFrontendQa definida por la clase SeguridadFrontendTest.
 *
 * @return string Resultado de la operación.
 */
function raizFrontendQa(): string
{
    return dirname(__DIR__, 2);
}

/**
 * Ejecuta la operación contenidoFrontendQa definida por la clase SeguridadFrontendTest.
 *
 * @param  mixed  $ruta  Parámetro recibido por la operación.
 * @return string Resultado de la operación.
 */
function contenidoFrontendQa(string $ruta): string
{
    $contenido = file_get_contents(raizFrontendQa().DIRECTORY_SEPARATOR.$ruta);

    expect($contenido)->toBeString();

    return $contenido;
}

test('el asistente escapa mensajes y nombres de productos antes de usar innerHTML', function (): void {
    $javascript = contenidoFrontendQa('resources/js/modulos/publico/asistente/index.js');

    expect($javascript)->toContain('const escaparHtml = (valor)')
        ->toContain('"<": "&lt;"')
        ->toContain('&#39;')
        ->toContain('escaparHtml(respuesta.mensaje')
        ->toContain('escaparHtml((producto.marca || "") + " " + (producto.nombre || ""))');
});

test('el formulario de ventas escapa el nombre de producto antes de construir HTML dinámico', function (): void {
    $javascript = contenidoFrontendQa('resources/js/modulos/gestion/ventas/index.js');

    expect($javascript)->toContain('const escapeHtml = (value)')
        ->toContain('${escapeHtml(productName)}')
        ->not->toContain('${productName}');
});

test('el preview CSV escapa caracteres HTML antes de insertarlos en la tabla', function (): void {
    $vista = contenidoFrontendQa('resources/views/gestion/clientes/listar.blade.php');

    expect($vista)->toContain("replaceAll('&', '&amp;')")
        ->toContain("replaceAll('<', '&lt;')")
        ->toContain("replaceAll('>', '&gt;')");
});

test('la descripción de producto usa e() antes de nl2br en salida HTML explícita', function (): void {
    $vista = contenidoFrontendQa('resources/views/publico/catalogo/detalle.blade.php');

    expect($vista)->toContain('{!! nl2br(e($product->descripcion)) !!}');
});

test('el cliente HTTP añade CSRF, acepta JSON y rechaza respuestas no exitosas', function (): void {
    $javascript = contenidoFrontendQa('resources/js/nucleo/http.js');

    expect($javascript)->toContain("headers.set('Accept', 'application/json')")
        ->toContain("headers.set('X-CSRF-TOKEN', csrfToken())")
        ->toContain('if (!response.ok)')
        ->toContain('throw new Error(message)');
});

test('el JavaScript de la aplicación no usa eval ni document.write', function (): void {
    $iterador = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(raizFrontendQa().'/resources/js'));

    foreach ($iterador as $archivo) {
        if (! $archivo->isFile() || $archivo->getExtension() !== 'js') {
            continue;
        }

        $javascript = file_get_contents($archivo->getPathname());
        expect(preg_match('/\\beval\\s*\\(/i', $javascript))->toBe(0, "eval() en {$archivo->getPathname()}");
        expect($javascript)->not->toContain('document.write');
    }
});
