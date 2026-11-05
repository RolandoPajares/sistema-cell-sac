{{-- Menú específico del rol Ventas Minoristas. La autorización sigue en PermisosRoles. --}}
@php
    $visibleRoutes = [
        'home',
        'dashboard',
        'orders.index',
        'recommendations',
        'comparison',
        'assistant',
        'smart.optimizer',
        'management.inventory',
    ];
    $links = array_values(array_filter($links ?? [], static fn (array $link): bool => in_array($link['route'], $visibleRoutes, true)));
@endphp

@include('components.panel.menu-enlaces', ['links' => $links])
