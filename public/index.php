<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Comprueba si la aplicacion fue puesta en modo mantenimiento antes de atender la solicitud.
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    // Carga la respuesta especial que informa al usuario que el sistema esta temporalmente detenido.
    require $maintenance;
}

// Carga automaticamente Laravel, las dependencias instaladas y las clases de la aplicacion.
require __DIR__.'/../vendor/autoload.php';

// Inicia la configuracion principal de Laravel, incluyendo rutas, middleware y excepciones.
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Captura la solicitud del navegador y la entrega a Laravel para resolver la ruta,
// ejecutar los middleware y devolver la vista o respuesta correspondiente.
$app->handleRequest(Request::capture());
