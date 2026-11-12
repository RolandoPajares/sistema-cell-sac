<?php

use App\Http\Middleware\AsegurarCarritoEcommerce;
use App\Http\Middleware\EstablecerContextoAuditoria;
use App\Http\Middleware\RegistrarSesionUsuario;
use App\Http\Middleware\VerificarCuentaActiva;
use App\Http\Middleware\VerificarPermiso;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    // Registra las rutas web, API, comandos de consola y el endpoint de salud de Laravel.
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Configura los middleware globales y los nombres cortos usados por las rutas.
    ->withMiddleware(function (Middleware $middleware): void {
        // Ejecuta el middleware de auditoria en cada solicitud web.
        $middleware->web(append: [EstablecerContextoAuditoria::class]);

        // Permite invocar cada middleware mediante un alias dentro de las rutas.
        $middleware->alias([
            'carrito.ecommerce' => AsegurarCarritoEcommerce::class,
            'permiso' => VerificarPermiso::class,
            'sesion.usuario' => RegistrarSesionUsuario::class,
            'cuenta.activa' => VerificarCuentaActiva::class,
        ]);
    })
    // Configura la forma en que Laravel presenta las excepciones segun el tipo de solicitud.
    ->withExceptions(function (Exceptions $exceptions): void {
        // Devuelve errores JSON para la API o para solicitudes que esperan respuestas JSON.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
