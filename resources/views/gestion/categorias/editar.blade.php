<form class="categorias-modal-form" method="post" action="{{ route('management.categories.update', $category) }}">
    @csrf
    @method('PUT')
    @include('gestion.categorias.formulario-modal', ['category' => $category])
    <footer class="categorias-form-actions">
        <button class="categorias-button categorias-button--secondary" type="button" data-close-category-edit>Cancelar</button>
        <button class="categorias-button categorias-button--primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button>
    </footer>
</form>
