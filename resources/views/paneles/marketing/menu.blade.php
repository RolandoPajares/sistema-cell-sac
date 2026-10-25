{{-- Menú específico del rol Marketing. La autorización sigue en PermisosRoles. --}}
@php
    $visibleRoutes = [
        'home',
        'dashboard',
        'recommendations',
        'comparison',
        'assistant',
        'smart.optimizer',
        'management.campaigns',
        'smart.ads',
        'management.coupons',
        'management.segments',
        'management.reports',
        'management.audit',
    ];
    $links = array_values(array_filter($links ?? [], static fn (array $link): bool => in_array($link['route'], $visibleRoutes, true)));
@endphp

@include('components.panel.menu-enlaces', ['links' => $links])
