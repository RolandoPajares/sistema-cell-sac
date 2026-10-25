@extends('layouts.administracion')

@section('title', 'Categorías')

@section('content')
    <section class="admin-page" data-admin-table-container>
        <div class="admin-page-head">
            <div>
                <span class="product-category">Catálogo</span>
                <h1>Categorías</h1>
                <p>Clasifica el catálogo sin eliminar productos históricos.</p>
            </div>
        </div>

        <div class="two-column">
            @include('gestion.categorias.crear')

            <div class="form-card">
                <h2>Listado</h2>

                @forelse($categories as $category)
                    <article class="data-row" data-data-row>
                        <div>
                            <b>{{ $category->nombre }}</b>
                            <small>{{ $category->products_count }} producto(s) · {{ $category->activo ? 'Activa' : 'Inactiva' }}</small>
                            <details>
                                <summary>Editar</summary>
                                @include('gestion.categorias.editar', ['category' => $category])
                            </details>
                        </div>

                        @if($category->activo)
                            <form method="post" action="{{ route('management.categories.deactivate', $category) }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    class="btn btn-secondary"
                                    data-confirm="¿Desactivar esta categoría?"
                                >
                                    Desactivar
                                </button>
                            </form>
                        @endif
                    </article>
                @empty
                    <p>No hay categorías registradas.</p>
                @endforelse

                {{ $categories->links() }}
            </div>
        </div>
    </section>
@endsection

