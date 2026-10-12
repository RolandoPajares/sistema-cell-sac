<?php

namespace App\Providers;

use App\Models\Campaign;
use App\Support\RolePermissions;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)->by(mb_strtolower((string) $request->input('correo')).'|'.$request->ip()));
        RateLimiter::for('register', fn (Request $request): Limit => Limit::perMinute(3)->by($request->ip()));
        RateLimiter::for('checkout', fn (Request $request): Limit => Limit::perMinute(5)->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));
        RateLimiter::for('contact', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));

        View::composer('layouts.store', function ($view): void {
            $campaigns = Campaign::with('segments')
                ->where('activo', true)
                ->where('inicia_en', '<=', now())
                ->where('finaliza_en', '>=', now())
                ->latest('inicia_en')
                ->get();
            $role = auth()->user()?->rol;
            $eligible = $campaigns->filter(function (Campaign $campaign) use ($role): bool {
                if ($campaign->segments->isEmpty()) {
                    return true;
                }

                return $role !== null && $campaign->segments->contains(fn ($segment): bool => in_array($role, $segment->criterios['roles'] ?? [], true));
            })->unique('ubicacion')->values();

            $view->with('publicCampaigns', $eligible);
        });

        View::composer('layouts.admin', function ($view): void {
            $user = auth()->user();
            $view->with(
                'sidebarLinks',
                $user?->rol === 'administrador'
                    ? RolePermissions::administratorNavigation($user)
                    : ($user ? RolePermissions::navigation($user) : []),
            );
        });
    }
}
