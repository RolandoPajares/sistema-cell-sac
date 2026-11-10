<?php

use Tests\TestCase;

uses(TestCase::class);

it('protege cada interfaz operativa de gestión con autenticación, sesión y permiso de ruta', function (): void {
    $selfService = [
        'management.account',
        'management.account.activity',
        'management.account.avatar',
        'management.account.avatar.delete',
        'management.account.notifications',
        'management.account.notifications.read',
        'management.account.password',
        'management.account.profile',
        'management.account.preferences',
        'management.account.preferences.update',
        'management.account.security',
        'management.account.sessions',
        'management.account.sessions.revoke',
    ];

    $redirectAliases = [
        'management.customer_analytics',
        'management.commercial_settings',
        'management.marketing_metrics',
        'management.financial_reports',
    ];

    $routes = collect(app('router')->getRoutes()->getRoutes())
        ->filter(fn ($route): bool => str_starts_with((string) $route->getName(), 'management.'))
        ->reject(fn ($route): bool => in_array($route->getName(), [...$selfService, ...$redirectAliases], true));

    expect($routes)->not->toBeEmpty();

    foreach ($routes as $route) {
        $middleware = $route->gatherMiddleware();

        expect($middleware)
            ->toContain('auth')
            ->toContain('sesion.usuario')
            ->toContain('permiso:ruta');
    }
});
