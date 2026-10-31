<form class="categorias-modal-form" method="post" action="{{ route('management.categories.store') }}">
    @csrf
    @include('gestion.categorias.formulario-modal', ['category' => null])
    <footer class="categorias-form-actions">
        <button class="categorias-button categorias-button--secondary" type="button" data-close-category-create>Cancelar</button>
        <button class="categorias-button categorias-button--primary" type="submit"><i class="bi bi-plus-lg" aria-hidden="true"></i> Registrar categoría</button>
    </footer>
</form>
