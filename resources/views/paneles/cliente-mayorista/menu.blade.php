{{-- Menú específico del rol Cliente Mayorista. La autorización sigue en PermisosRoles. --}}
{{-- Los enlaces se separan en grupos para que el menú se entienda mejor --}}
@php
    $grupos = [
        'Mi empresa' => ['dashboard', 'home'],
        'Compras' => ['quotations.index', 'smart.wholesale'],
        'Herramientas' => ['recommendations', 'comparison', 'smart.optimizer', 'assistant'],
    ];
    $todos = collect($links ?? []);
@endphp

@foreach ($grupos as $nombreGrupo => $rutas)
    @php
        // solo se dejan los enlaces que el rol tiene permitidos, en el orden del grupo
        $enlacesGrupo = collect($rutas)
            ->map(fn (string $ruta) => $todos->firstWhere('route', $ruta))
            ->filter()
            ->values()
            ->all();
    @endphp

    @if (count($enlacesGrupo) > 0)
        <p class="menu-cliente-grupo">{{ $nombreGrupo }}</p>
        @include('components.panel.menu-enlaces', ['links' => $enlacesGrupo])
    @endif
@endforeach
