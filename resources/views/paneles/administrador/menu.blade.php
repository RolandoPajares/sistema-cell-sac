{{-- Menú específico del rol Administrador. La autorización sigue en PermisosRoles. --}}
@php
    $visibleRoutes = [
        'management.dashboard',
        'management.products',
        'management.categories',
        'management.inventory',
        'management.orders',
        'management.customers',
        'management.suppliers',
        'management.campaigns',
        'management.users',
        'management.reports',
    ];
    $links = array_values(array_filter($links ?? [], static fn (array $link): bool => in_array($link['route'], $visibleRoutes, true)));
@endphp

@include('components.panel.menu-enlaces', ['links' => $links])
