
{{-- Menú lateral exclusivo de Marketing --}}
{{-- Diseño y funcionamiento independientes de Compras y Logística. --}}

@php
    $visibleRoutes = [
        'home',
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
                'smart.optimizer',
            ],
        ],
        [
            'id' => 'marketing',
            'label' => 'Marketing',
            'icon' => 'bi-megaphone',
            'routes' => [
                'management.campaigns',
                'smart.ads',
                'management.coupons',
                'management.segments',
            ],
        ],
        [
            'id' => 'analisis',
            'label' => 'Análisis y reportes',
            'icon' => 'bi-bar-chart',
            'routes' => [
                'management.reports',
                'management.audit',
            ],
        ],
    ];

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

<style>
/* ==========================================
   MENÚ LATERAL - MARKETING
   Diseño equivalente a Compras y Logística
   Clases independientes
========================================== */

.mk-menu {
    display: flex;
    flex-direction: column;
    gap: 9px;
    padding: 8px 0;
}

.mk-menu__link,
.mk-menu__toggle {
    width: 100%;
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    border: 0;
    border-radius: 13px;
    background: transparent;
    color: #fff;
    font: inherit;
    font-weight: 600;
    font-size: 15px;
    text-decoration: none;
    text-align: left;
    cursor: pointer;
    transition: background .25s ease, color .25s ease;
    box-sizing: border-box;
}

.mk-menu__link {
    justify-content: flex-start;
}

.mk-menu__link > i,
.mk-menu__group-title > i {
    width: 23px;
    flex-shrink: 0;
    font-size: 19px;
    text-align: center;
}

.mk-menu__link:hover,
.mk-menu__toggle:hover {
    background: rgba(255, 255, 255, .12);
    color: #fff;
}

.mk-menu__link.is-active,
.mk-menu__group.is-open > .mk-menu__toggle {
    background: linear-gradient(100deg, #0789ed, #08bce9);
    color: #fff;
    box-shadow: 0 6px 18px rgba(0, 174, 255, .2);
}

.mk-menu__group-title {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.mk-menu__group-title > span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.mk-menu__arrow {
    flex-shrink: 0;
    font-size: 13px;
    transition: transform .25s ease;
}

.mk-menu__group.is-open .mk-menu__arrow {
    transform: rotate(180deg);
}

.mk-menu__submenu {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 7px 0 7px 22px;
}

.mk-menu__submenu[hidden] {
    display: none !important;
}

.mk-menu__sublink {
    position: relative;
    display: block;
    padding: 11px 13px;
    border-radius: 9px;
    color: rgba(255, 255, 255, .85);
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: background .2s ease, color .2s ease;
}

.mk-menu__sublink:hover {
    background: rgba(255, 255, 255, .12);
    color: #fff;
}

.mk-menu__sublink.is-active {
    background: rgba(13, 174, 242, .28);
    color: #fff;
    border-left: 3px solid #fff;
    font-weight: 700;
}

</style>

<nav class="mk-menu" aria-label="Menú de Marketing">

    {{-- Inicio --}}
    @foreach ($links as $link)
        @if (
            $link['route'] === 'home'
            && \Illuminate\Support\Facades\Route::has('home')
        )
            <a
                class="mk-menu__link {{ request()->routeIs('home') ? 'is-active' : '' }}"
                href="{{ route('home') }}"
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
            <section class="mk-menu__group {{ $isOpen ? 'is-open' : '' }}">

                <button
                    type="button"
                    class="mk-menu__toggle"
                    aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                    aria-controls="mk-submenu-{{ $group['id'] }}"
                >
                    <span class="mk-menu__group-title">
                        <i class="bi {{ $group['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $group['label'] }}</span>
                    </span>

                    <i class="bi bi-chevron-down mk-menu__arrow"
                       aria-hidden="true"></i>
                </button>

                <div
                    class="mk-menu__submenu"
                    id="mk-submenu-{{ $group['id'] }}"
                    @if (!$isOpen) hidden @endif
                >
                    @foreach ($groupLinks as $link)
                        <a
                            class="mk-menu__sublink {{ request()->routeIs($link['route']) ? 'is-active' : '' }}"
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

<script>
/* ==========================================
   MENÚ DESPLEGABLE - MARKETING
   JavaScript independiente
========================================== */

(() => {
    if (window.mkMenuInitialized) return;

    window.mkMenuInitialized = true;

    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('.mk-menu__toggle');

        if (!toggle) return;

        const menu = toggle.closest('.mk-menu');
        const currentGroup = toggle.closest('.mk-menu__group');

        if (!menu || !currentGroup) return;

        const submenu = currentGroup.querySelector(
            '.mk-menu__submenu'
        );

        if (!submenu) return;

        const willOpen = submenu.hidden;

        // Cerrar todas las categorías.
        menu.querySelectorAll('.mk-menu__group').forEach((group) => {
            const button = group.querySelector('.mk-menu__toggle');
            const content = group.querySelector('.mk-menu__submenu');

            if (!button || !content) return;

            content.hidden = true;
            button.setAttribute('aria-expanded', 'false');
            group.classList.remove('is-open');
        });

        // Abrir la categoría seleccionada.
        if (willOpen) {
            submenu.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
            currentGroup.classList.add('is-open');
        }
    });
})();
</script>
