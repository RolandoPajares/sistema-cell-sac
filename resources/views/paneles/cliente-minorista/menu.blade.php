{{-- Menú específico del rol Cliente Minorista. La autorización sigue en PermisosRoles. --}}
@php
    $visibleRoutes = [
        'catalog.index',
        'dashboard',
        'orders.index',
    ];
    $links = array_values(array_filter($links ?? [], static fn (array $link): bool => in_array($link['route'], $visibleRoutes, true)));
@endphp

@include('components.panel.menu-enlaces', ['links' => $links])
