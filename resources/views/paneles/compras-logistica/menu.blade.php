
{{-- Menú específico del rol Compras y Logística. --}}
{{-- Se conservan las rutas y la autorización existente. --}}

@php
    $visibleRoutes = [
        'home',
        'dashboard',
        'recommendations',
        'comparison',
        'assistant',
        'smart.optimizer',
        'management.inventory',
        'management.suppliers',
        'management.purchases',
    ];

    $links = array_values(array_filter(
        $links ?? [],
        static fn (array $link): bool =>
            in_array($link['route'], $visibleRoutes, true)
    ));

    $groups = [
        [
            'id' => 'herramientas',
            'label' => 'Herramientas inteligentes',
            'icon' => 'bi-stars',
            'routes' => [
                'recommendations',
                'comparison',
                'assistant',
            ],
        ],
        [
            'id' => 'operaciones',
            'label' => 'Operaciones comerciales',
            'icon' => 'bi-briefcase',
            'routes' => [
                'smart.optimizer',
                'management.purchases',
            ],
        ],
        [
            'id' => 'logistica',
            'label' => 'Gestión logística',
            'icon' => 'bi-box-seam',
            'routes' => [
                'management.inventory',
                'management.suppliers',
            ],
        ],
    ];

    $directRoutes = ['home', 'dashboard'];

    $activeGroup = null;

    foreach ($groups as $group) {
        foreach ($group['routes'] as $routeName) {
            if (request()->routeIs($routeName)) {
                $activeGroup = $group['id'];
                break 2;
            }
        }
    }
@endphp

<nav class="cl-menu" aria-label="Menú de Compras y Logística">

    {{-- Enlaces principales --}}
    @foreach ($links as $link)
        @if (
            in_array($link['route'], $directRoutes, true)
            && \Illuminate\Support\Facades\Route::has($link['route'])
        )
            <a
                class="cl-menu__link {{ request()->routeIs($link['route']) ? 'is-active' : '' }}"
                href="{{ route($link['route']) }}"
                title="{{ $link['label'] }}"
            >
                <i class="bi {{ $link['icon'] }}" aria-hidden="true"></i>
                <span>{{ $link['label'] }}</span>
            </a>
        @endif
    @endforeach

    {{-- Categorías desplegables --}}
    @foreach ($groups as $group)
        @php
            $groupLinks = array_values(array_filter(
                $links,
                static fn (array $link): bool =>
                    in_array($link['route'], $group['routes'], true)
                    && \Illuminate\Support\Facades\Route::has($link['route'])
            ));

            $isOpen = $activeGroup === $group['id'];
        @endphp

        @if (count($groupLinks))
            <section class="cl-menu__group {{ $isOpen ? 'is-open' : '' }}">

                <button
                    type="button"
                    class="cl-menu__toggle"
                    aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                    aria-controls="cl-submenu-{{ $group['id'] }}"
                >
                    <span class="cl-menu__group-title">
                        <i class="bi {{ $group['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $group['label'] }}</span>
                    </span>

                    <i class="bi bi-chevron-down cl-menu__arrow"
                       aria-hidden="true"></i>
                </button>

                <div
                    class="cl-menu__submenu"
                    id="cl-submenu-{{ $group['id'] }}"
                    @if (!$isOpen) hidden @endif
                >
                    @foreach ($groupLinks as $link)
                        <a
                            class="cl-menu__sublink {{ request()->routeIs($link['route']) ? 'is-active' : '' }}"
                            href="{{ route($link['route']) }}"
                        >
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>

            </section>
        @endif
    @endforeach

</nav>
