@if ($paginator->hasPages())
    <nav class="catalog-pagination" aria-label="Paginación del catálogo">
        <p class="catalog-pagination__summary">
            Mostrando
            <strong>{{ $paginator->firstItem() }}</strong>
            a <strong>{{ $paginator->lastItem() }}</strong>
            de <strong>{{ $paginator->total() }}</strong> productos
        </p>

        <div class="catalog-pagination__controls">
            @if ($paginator->onFirstPage())
                <span class="catalog-pagination__disabled" aria-disabled="true">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    <span>Anterior</span>
                </span>
            @else
                <a class="catalog-pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    <span>Anterior</span>
                </a>
            @endif

            <div class="catalog-pagination__pages">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="catalog-pagination__ellipsis" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="catalog-pagination__current" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="catalog-pagination__link" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a class="catalog-pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    <span>Siguiente</span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
            @else
                <span class="catalog-pagination__disabled" aria-disabled="true">
                    <span>Siguiente</span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
