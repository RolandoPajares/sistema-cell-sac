{{-- Menú exclusivo del rol Ventas Mayoristas. Solo usa rutas ya disponibles para este rol. --}}
@php
    $menuGroups = [
        [
            'label' => 'Operación B2B',
            'icon' => 'bi-briefcase',
            'items' => [
                ['route' => 'smart.wholesale', 'label' => 'Portal mayorista', 'active' => ['smart.wholesale']],
                ['route' => 'orders.index', 'label' => 'Pedidos B2B', 'active' => ['orders.*']],
                ['route' => 'quotations.index', 'label' => 'Cotizaciones', 'active' => ['quotations.*']],
            ],
        ],
        [
            'label' => 'Herramientas comerciales',
            'icon' => 'bi-grid-3x3-gap',
            'items' => [
                ['route' => 'recommendations', 'label' => 'Recomendador', 'active' => ['recommendations']],
                ['route' => 'comparison', 'label' => 'Comparador', 'active' => ['comparison']],
                ['route' => 'smart.optimizer', 'label' => 'Optimizar compra', 'active' => ['smart.optimizer']],
                ['route' => 'assistant', 'label' => 'Asistente IA', 'active' => ['assistant']],
            ],
        ],
    ];

    foreach ($menuGroups as &$group) {
        $group['items'] = array_values(array_filter(
            $group['items'],
            static fn (array $item): bool => \Illuminate\Support\Facades\Route::has($item['route']),
        ));

        $group['active'] = false;
        foreach ($group['items'] as $item) {
            if (request()->routeIs(...$item['active'])) {
                $group['active'] = true;
                break;
            }
        }
    }
    unset($group);
@endphp

<div class="ventas-role-menu ventas-role-menu--mayoristas">
    <a
        class="ventas-role-menu__dashboard {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
        href="{{ route('dashboard') }}"
        title="Dashboard de ventas mayoristas"
        @if (request()->routeIs('dashboard')) aria-current="page" @endif
    >
        <i class="bi bi-speedometer2" aria-hidden="true"></i>
        <span class="ventas-role-menu__label">Dashboard</span>
    </a>

    <div class="ventas-role-menu__groups">
        @foreach ($menuGroups as $group)
            @if ($group['items'] !== [])
                <details class="ventas-role-menu__group {{ $group['active'] ? 'is-active' : '' }}" open>
                    <summary class="ventas-role-menu__summary" title="{{ $group['label'] }}">
                        <span class="ventas-role-menu__summary-main">
                            <i class="bi {{ $group['icon'] }}" aria-hidden="true"></i>
                            <span class="ventas-role-menu__label">{{ $group['label'] }}</span>
                        </span>
                        <i class="bi bi-chevron-down ventas-role-menu__chevron" aria-hidden="true"></i>
                    </summary>

                    <div class="ventas-role-menu__submenu">
                        @foreach ($group['items'] as $item)
                            @php($itemIsActive = request()->routeIs(...$item['active']))
                            <a
                                class="ventas-role-menu__item {{ $itemIsActive ? 'is-active' : '' }}"
                                href="{{ route($item['route']) }}"
                                title="{{ $item['label'] }}"
                                @if ($itemIsActive) aria-current="page" @endif
                            >
                                <span class="ventas-role-menu__item-marker" aria-hidden="true"></span>
                                <span class="ventas-role-menu__label">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </details>
            @endif
        @endforeach
    </div>
</div>
