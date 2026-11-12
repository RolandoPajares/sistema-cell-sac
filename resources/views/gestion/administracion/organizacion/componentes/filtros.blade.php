
<div class="tc-org__filters" data-org-filter-root>
    <div class="tc-org__filter-top">
        <label class="tc-org__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" placeholder="{{ $buscar }}" aria-label="{{ $buscar }}" data-org-search></label>
        <button class="tc-org__button" type="button" disabled title="Más filtros disponibles con datos reales"><i class="bi bi-funnel" aria-hidden="true"></i> Filtros</button>
        <button class="tc-org__button" type="button" disabled title="Exportación pendiente de integración"><i class="bi bi-download" aria-hidden="true"></i> Exportar</button>
    </div>
    <div class="tc-org__filter-fields">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach ($filtros as $filtro)
            <label><span>{{ $filtro['titulo'] }}</span><select data-org-select="{{ $filtro['campo'] }}"><option value="">{{ $filtro['todos'] }}</option>@foreach ($filtro['opciones'] as $opcion)<option value="{{ $opcion }}">{{ $opcion }}</option>@endforeach</select></label>
        @endforeach
        <button class="tc-org__button tc-org__button--apply" type="button" data-org-apply><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Aplicar filtros</button>
    </div>
</div>
