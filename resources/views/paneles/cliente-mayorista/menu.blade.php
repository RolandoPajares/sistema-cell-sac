{{-- Menú específico del rol Cliente Mayorista. La autorización sigue en PermisosRoles. --}}
@php
    $visibleRoutes = [
        'home',
        'dashboard',
        'recommendations',
        'comparison',
        'assistant',
        'smart.optimizer',
        'smart.wholesale',
        'quotations.index',
    ];
    $links = array_values(array_filter($links ?? [], static fn (array $link): bool => in_array($link['route'], $visibleRoutes, true)));
@endphp

@include('components.panel.menu-enlaces', ['links' => $links])
