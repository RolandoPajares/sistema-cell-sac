<header class="tc-dg__header">
    <div>
        <span class="tc-dg__eyebrow">Dashboard Global</span>
        <h1>{{ $titulo }} <i class="bi {{ $icono }}" aria-hidden="true"></i></h1>
        <p>{{ $descripcion }}</p>
    </div>
    @if (! empty($exportRoute))
        <details class="tc-dg__export-menu">
            <summary class="tc-dg__export"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <div class="tc-dg__export-options">
                <a href="{{ route($exportRoute, array_merge(['format' => 'pdf'], $exportParameters ?? [])) }}">PDF</a>
                <a href="{{ route($exportRoute, array_merge(['format' => 'excel'], $exportParameters ?? [])) }}">Excel</a>
                <a href="{{ route($exportRoute, array_merge(['format' => 'csv'], $exportParameters ?? [])) }}">CSV</a>
            </div>
        </details>
    @else
        <button class="tc-dg__export" type="button" disabled title="La exportación estará disponible cuando esta pantalla tenga datos integrados">
            <i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i>
        </button>
    @endif
</header>
