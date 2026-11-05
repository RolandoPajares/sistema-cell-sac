<header class="tc-org__header">
    <div>
        <span class="tc-org__eyebrow">Organización y Sedes</span>
        <h1>{{ $titulo }} <i class="bi {{ $icono }}" aria-hidden="true"></i></h1>
        <p>{{ $descripcion }}</p>
    </div>
    <div class="tc-org__header-actions">
        @foreach ($acciones ?? [] as $accion)
            <button class="tc-org__button {{ ($accion['primaria'] ?? false) ? 'tc-org__button--primary' : '' }}" type="button" @if (isset($accion['modal'])) data-org-open="{{ $accion['modal'] }}" @elseif (isset($accion['ruta'])) data-org-link="{{ $accion['ruta'] }}" @else disabled title="Disponible cuando este módulo se conecte a datos reales" @endif>
                <i class="bi {{ $accion['icono'] }}" aria-hidden="true"></i>{{ $accion['texto'] }}
            </button>
        @endforeach
    </div>
</header>
