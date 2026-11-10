<?php

namespace App\Providers;

use App\Contratos\Productos\AlcanceProductos;
use App\Contratos\Productos\AuditoriaProductos;
use App\Dominio\Catalogo\ProductoDao;
use App\Infraestructura\Persistencia\Pdo\PdoConexion;
use App\Infraestructura\Persistencia\Pdo\PdoProductoDao;
use App\Models\Marketing\Campania;
use App\Models\User;
use App\Servicios\AdaptadorAlcanceProductos;
use App\Servicios\AdaptadorAuditoriaProductos;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioMenuDinamico;
use App\Servicios\ServicioPresentacionCampanias;
use App\Servicios\ServicioPresentacionFinanzasFacturacion;
use App\Servicios\ServicioPresentacionReembolsos;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Define la responsabilidad de la clase AppServiceProvider dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra la información recibida y coordina su persistencia.
     *
     * @return void Resultado de la operación.
     */
    public function register(): void
    {
        $this->app->singleton(ProductoDao::class, function (): ProductoDao {
            return new PdoProductoDao(new PdoConexion(app('db')->connection()->getPdo()));
        });
        $this->app->bind(AlcanceProductos::class, AdaptadorAlcanceProductos::class);
        $this->app->bind(AuditoriaProductos::class, AdaptadorAuditoriaProductos::class);
    }

    /**
     * Ejecuta la operación boot definida por la clase AppServiceProvider.
     *
     * @return void Resultado de la operación.
     */
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

        View::composer(['layouts.tienda.principal', 'layouts.auth.principal'], function ($view): void {
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

            $principalCampaign = $eligible->firstWhere('ubicacion', 'emergente') ?? $eligible->first();
            $contextualCampaign = $eligible->first(
                fn (Campania $campaign): bool => $principalCampaign !== null
                    && $campaign->id !== $principalCampaign->id,
            );
            $cartItemCount = collect((array) session('cart', []))->sum(
                fn (mixed $line): int => is_array($line) ? (int) ($line['cantidad'] ?? 0) : (int) $line,
            );

            $view->with([
                'publicCampaigns' => $eligible,
                'principalCampaign' => $principalCampaign,
                'contextualCampaign' => $contextualCampaign,
                'advertisingContext' => request()->routeIs('home') ? 'inicio' : 'general',
                'headerAuthenticated' => $user !== null,
                'headerUser' => $user,
                'cartItemCount' => $cartItemCount,
            ]);
        });

        View::composer('layouts.panel.principal', function ($view): void {
            $user = auth()->user();
            $menu = app(ServicioMenuDinamico::class)->paraUsuario($user);
            $canManage = $user?->can('gestion.seguridad.consultar') ?? false;
            $panelUserInitials = mb_strtoupper(collect(preg_split('/\s+/', trim((string) ($user?->nombre ?? ''))) ?: [])
                ->filter()
                ->take(2)
                ->map(static fn (string $part): string => mb_substr($part, 0, 1))
                ->implode(''));

            $menu['grupos'] = collect($menu['grupos'] ?? [])
                ->map(function (array $group): array {
                    $group['hasActiveItem'] = collect($group['items'] ?? [])
                        ->contains(fn (array $item): bool => $item['active'] ?? false);
                    $group['submenuId'] = 'admin-menu-submenu-'.Str::slug($group['id']);

                    return $group;
                })
                ->all();

            $view->with([
                'sidebarLinks' => $menu,
                'panelUser' => $user,
                'panelUserPreferences' => (array) ($user?->preferencias ?? []),
                'panelUserInitials' => $panelUserInitials,
                'panelIsAdministrator' => $canManage,
                'panelBrandRoute' => $canManage ? 'management.dashboard' : 'dashboard',
                'panelAccountRoute' => $canManage ? 'management.account' : 'account.profile.show',
            ]);
        });

        View::composer('cuenta.perfil.navegacion', function ($view): void {
            $user = request()->user();
            $canManageSecurity = $user instanceof User
                && app(ServicioAutorizacion::class)->allows($user, 'gestion.seguridad.consultar');

            $view->with('profileNavigationRoute', $canManageSecurity
                ? 'management.account'
                : 'account.profile.show');
        });

        View::composer('gestion.finanzas.finanzas-facturacion.pagina', function ($view): void {
            $pagina = $view->getData()['pagina'] ?? [];

            if (is_array($pagina)) {
                $view->with('pagina', app(ServicioPresentacionFinanzasFacturacion::class)->preparar($pagina));
            }
        });

        View::composer('gestion.finanzas.reembolsos.listar', function ($view): void {
            $module = $view->getData()['module'] ?? [];

            if (is_array($module)) {
                $view->with('module', app(ServicioPresentacionReembolsos::class)->preparar($module));
            }
        });

        View::composer('gestion.ecommerce-marketing.campanas.listar', function ($view): void {
            $data = $view->getData();

            if (isset($data['campaigns'])) {
                $view->with(app(ServicioPresentacionCampanias::class)->preparar($data));
            }
        });

        View::composer([
            'gestion.*',
            'inteligente.cotizador.resumen',
            'layouts.tienda.encabezado',
            'publico.comercio.pedidos*',
            'publico.comercio.pedido',
            'publico.comercio.confirmacion',
            'publico.comercio.seguimiento',
        ], function ($view): void {
            $user = request()->user();
            $canManageSecurity = $user instanceof User
                && app(ServicioAutorizacion::class)->allows($user, 'gestion.seguridad.consultar');
            $canManageSales = $user instanceof User
                && app(ServicioAutorizacion::class)->allows($user, 'gestion.ventas.consultar');
            $canManageQuotes = $user instanceof User
                && app(ServicioAutorizacion::class)->allows($user, 'gestion.cotizaciones.consultar');

            $view->with([
                'canManageSecurity' => $canManageSecurity,
                'canManageSales' => $canManageSales,
                'canManageQuotes' => $canManageQuotes,
                'isAuthenticated' => $user instanceof User,
                'currentUserName' => (string) ($user?->nombre ?? ''),
                'currentUserEmail' => (string) ($user?->correo ?? ''),
                'isAdminPanel' => (bool) ($view->getData()['adminView'] ?? false),
                'isAdmin' => (bool) ($view->getData()['adminView'] ?? false)
                    || $canManageSecurity
                    || $canManageSales
                    || $canManageQuotes,
            ]);
        });
    }
}
