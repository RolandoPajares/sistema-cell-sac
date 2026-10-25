{{-- Menú específico del rol Cliente Minorista. La autorización sigue en PermisosRoles. --}}
@php
    $visibleRoutes = [
        'home',
        'catalog.index',
        'dashboard',
        'orders.index',
        'recommendations',
        'comparison',
        'assistant',
        'smart.optimizer',
    ];
    $links = array_values(array_filter($links ?? [], static fn (array $link): bool => in_array($link['route'], $visibleRoutes, true)));
@endphp

@include('components.panel.menu-enlaces', ['links' => $links])
