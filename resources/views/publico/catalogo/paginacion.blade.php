
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
@if ($paginator->hasPages())
    <nav class="catalog-pagination" aria-label="Paginación del catálogo">
        <p class="catalog-pagination__summary">
            Mostrando
            <strong>{{ $paginator->firstItem() }}</strong>
            a <strong>{{ $paginator->lastItem() }}</strong>
            de <strong>{{ $paginator->total() }}</strong> productos
        </p>

        <div class="catalog-pagination__controls">
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
            @if ($paginator->onFirstPage())
                <span class="catalog-pagination__disabled" aria-disabled="true">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    <span>Anterior</span>
                </span>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
            @else
                <a class="catalog-pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    <span>Anterior</span>
                </a>
            @endif

            <div class="catalog-pagination__pages">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @foreach ($elements as $element)
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                    @if (is_string($element))
                        <span class="catalog-pagination__ellipsis" aria-hidden="true">{{ $element }}</span>
                    @endif

{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                    @if (is_array($element))
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                        @foreach ($element as $page => $url)
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                            @if ($page == $paginator->currentPage())
                                <span class="catalog-pagination__current" aria-current="page">{{ $page }}</span>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                            @else
                                <a class="catalog-pagination__link" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
            @if ($paginator->hasMorePages())
                <a class="catalog-pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    <span>Siguiente</span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
            @else
                <span class="catalog-pagination__disabled" aria-disabled="true">
                    <span>Siguiente</span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
