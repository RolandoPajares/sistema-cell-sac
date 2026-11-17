# Auditoría global

## Tecnología y arquitectura

`composer.json`: PHP `^8.3`, Laravel `^13.17`, Pest `^5.3`. `package.json`: Vite 8, Tailwind 4 y Laravel Vite plugin. Se observan Controllers, FormRequests, Services, Models, Policies, Middleware, Resources y una capa PDO. Las vistas son Blade.

## Runtime

`php artisan about` funciona y reporta Laravel 13.35.0, PHP 8.5.0, entorno local y debug habilitado. `php artisan route:list` funciona y muestra 332 rutas. Esto acredita arranque básico, no flujos de negocio.

## Git y pruebas

Rama: `feature/admin-inicio`; último commit: `cbadab20`. `git status`/`git diff` fallan con error de work tree. `php artisan test --compact` y PHPUnit no inician por el bootstrap reportado. Debe corregirse el entorno antes de evaluar regresiones.
