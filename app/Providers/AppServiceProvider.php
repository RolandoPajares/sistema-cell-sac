<?php

namespace App\Providers;

use App\Models\Marketing\Campania;
use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioMenuDinamico;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Gate::before(function ($user, string $ability): ?bool {
            if ($user instanceof User && str_starts_with($ability, 'gestion.')) {
                return app(ServicioAutorizacion::class)->allows($user, $ability);
            }

            return null;
        });

        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)->by(mb_strtolower((string) $request->input('correo')).'|'.$request->ip()));
        RateLimiter::for('register', fn (Request $request): Limit => Limit::perMinute(3)->by($request->ip()));
        RateLimiter::for('checkout', fn (Request $request): Limit => Limit::perMinute(5)->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));
        RateLimiter::for('contact', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('catalog-api', fn (Request $request): Limit => Limit::perMinute(60)->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));

        View::composer('layouts.tienda', function ($view): void {
            $campaigns = Campania::with('segments')
                ->where('activo', true)
                ->where('inicia_en', '<=', now())
                ->where('finaliza_en', '>=', now())
                ->latest('inicia_en')
                ->get();
            $user = auth()->user();
            $roles = $user?->rolesVigentes()->pluck('roles.codigo')->all() ?? [];
            if ($user?->rol !== null) {
                $roles[] = $user->rol;
            }
            $eligible = $campaigns->filter(function (Campania $campaign) use ($roles): bool {
                if ($campaign->segments->isEmpty()) {
                    return true;
                }

                return $roles !== [] && $campaign->segments->contains(
                    fn ($segment): bool => array_intersect($roles, $segment->criterios['roles'] ?? []) !== [],
                );
            })->unique('ubicacion')->values();

            $view->with('publicCampaigns', $eligible);
        });

        View::composer('layouts.administracion', function ($view): void {
            $user = auth()->user();
            $menu = app(ServicioMenuDinamico::class)->paraUsuario($user);

            $view->with('sidebarLinks', $menu);
        });
    }
}
