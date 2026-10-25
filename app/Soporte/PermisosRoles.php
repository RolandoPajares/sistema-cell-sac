<?php

namespace App\Soporte;

use App\Models\User;

final class PermisosRoles
{
    /** @var array<string, array<int, string>> */
    public const MODULES = [
        'cliente_minorista' => ['dashboard', 'perfil', 'pedidos', 'historial', 'catalogo', 'recomendador', 'comparador', 'asistente'],
        'cliente_mayorista' => ['dashboard', 'catalogo-b2b', 'perfil', 'cotizaciones', 'pedidos-mayoristas', 'historial', 'recomendador', 'comparador', 'asistente'],
        'administrador' => ['*'],
        'compras_logistica' => ['dashboard', 'inventario', 'productos', 'proveedores', 'compras', 'movimientos-stock', 'recomendador', 'comparador', 'asistente'],
        'ventas_mayoristas' => ['dashboard', 'productos', 'cotizaciones', 'pedidos-mayoristas', 'recomendador', 'comparador', 'asistente'],
        'ventas_minoristas' => ['dashboard', 'productos', 'inventario', 'ventas', 'pedidos', 'recomendador', 'comparador', 'asistente'],
        'marketing' => ['dashboard', 'campanias', 'promociones', 'analitica', 'segmentacion', 'recomendador', 'comparador', 'asistente'],
    ];

    public static function canView(User $user, string $module): bool
    {
        $modules = self::MODULES[$user->rol] ?? [];

        return in_array('*', $modules, true) || in_array($module, $modules, true);
    }

    /**
     * Navigation is kept in one place so the sidebar and account dashboard
     * cannot drift apart from the role matrix used by the application.
     *
     * @return array<int, array{label:string, route:string, icon:string, module:?string}>
     */
    public static function navigation(User $user): array
    {
        $items = [
            ['label' => 'Inicio', 'route' => 'home', 'icon' => 'bi-house', 'module' => null],
            ['label' => 'Catálogo', 'route' => 'catalog.index', 'icon' => 'bi-grid', 'module' => 'catalogo'],
            ['label' => 'Mi cuenta', 'route' => 'dashboard', 'icon' => 'bi-person-circle', 'module' => 'dashboard'],
            ['label' => 'Pedidos', 'route' => 'orders.index', 'icon' => 'bi-bag-check', 'module' => 'pedidos'],
            ['label' => 'Recomendador', 'route' => 'recommendations', 'icon' => 'bi-stars', 'module' => 'recomendador'],
            ['label' => 'Comparador', 'route' => 'comparison', 'icon' => 'bi-columns-gap', 'module' => 'comparador'],
            ['label' => 'Asistente', 'route' => 'assistant', 'icon' => 'bi-robot', 'module' => 'asistente'],
            ['label' => 'Optimizar compra', 'route' => 'smart.optimizer', 'icon' => 'bi-calculator', 'module' => 'recomendador'],
            ['label' => 'Portal mayorista', 'route' => 'smart.wholesale', 'icon' => 'bi-box-seam', 'module' => 'catalogo-b2b'],
            ['label' => 'Cotizaciones', 'route' => 'quotations.index', 'icon' => 'bi-file-earmark-text', 'module' => 'cotizaciones'],
            ['label' => 'Gestión', 'route' => 'management.dashboard', 'icon' => 'bi-speedometer2', 'module' => 'administracion'],
            ['label' => 'Productos', 'route' => 'management.products', 'icon' => 'bi-phone', 'module' => 'administracion'],
            ['label' => 'Categorías', 'route' => 'management.categories', 'icon' => 'bi-tags', 'module' => 'administracion'],
            ['label' => 'Clientes', 'route' => 'management.customers', 'icon' => 'bi-people', 'module' => 'administracion'],
            ['label' => 'Inventario', 'route' => 'management.inventory', 'icon' => 'bi-boxes', 'module' => 'inventario'],
            ['label' => 'Proveedores', 'route' => 'management.suppliers', 'icon' => 'bi-truck', 'module' => 'proveedores'],
            ['label' => 'Compras', 'route' => 'management.purchases', 'icon' => 'bi-cart-plus', 'module' => 'compras'],
            ['label' => 'Pedidos internos', 'route' => 'management.orders', 'icon' => 'bi-receipt', 'module' => 'administracion'],
            ['label' => 'Campañas', 'route' => 'management.campaigns', 'icon' => 'bi-megaphone', 'module' => 'campanias'],
            ['label' => 'Publicidad inteligente', 'route' => 'smart.ads', 'icon' => 'bi-bullseye', 'module' => 'promociones'],
            ['label' => 'Cupones', 'route' => 'management.coupons', 'icon' => 'bi-ticket-perforated', 'module' => 'promociones'],
            ['label' => 'Segmentos', 'route' => 'management.segments', 'icon' => 'bi-diagram-3', 'module' => 'segmentacion'],
            ['label' => 'Reportes', 'route' => 'management.reports', 'icon' => 'bi-bar-chart', 'module' => 'analitica'],
            ['label' => 'Auditoría', 'route' => 'management.audit', 'icon' => 'bi-shield-check', 'module' => 'analitica'],
        ];

        return array_values(array_filter($items, function (array $item) use ($user): bool {
            return $item['module'] === null || self::canView($user, $item['module']);
        }));
    }

    /**
     * Navegación principal del panel administrativo, alineada con el menú
     * visual del sistema anterior sin mezclarla con la navegación de cuenta.
     *
     * @return array<int, array{label:string, route:string, icon:string, module:?string}>
     */
    public static function administratorNavigation(User $user): array
    {
        if ($user->rol !== 'administrador') {
            return [];
        }

        return [
            ['label' => 'Dashboard', 'route' => 'management.dashboard', 'icon' => 'bi-speedometer2', 'module' => null],
            ['label' => 'Productos', 'route' => 'management.products', 'icon' => 'bi-box-seam', 'module' => null],
            ['label' => 'Categorías', 'route' => 'management.categories', 'icon' => 'bi-diagram-3', 'module' => null],
            ['label' => 'Inventario', 'route' => 'management.inventory', 'icon' => 'bi-boxes', 'module' => null],
            ['label' => 'Pedidos', 'route' => 'management.orders', 'icon' => 'bi-receipt', 'module' => null],
            ['label' => 'Clientes', 'route' => 'management.customers', 'icon' => 'bi-people', 'module' => null],
            ['label' => 'Proveedores', 'route' => 'management.suppliers', 'icon' => 'bi-truck', 'module' => null],
            ['label' => 'Campañas', 'route' => 'management.campaigns', 'icon' => 'bi-megaphone', 'module' => null],
            ['label' => 'Usuarios', 'route' => 'management.users', 'icon' => 'bi-person-gear', 'module' => null],
            ['label' => 'Reportes', 'route' => 'management.reports', 'icon' => 'bi-bar-chart', 'module' => null],
        ];
    }
}
