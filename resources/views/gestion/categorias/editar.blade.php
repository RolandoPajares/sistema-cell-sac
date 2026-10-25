<form method="post" action="{{ route('management.categories.update', $category) }}" class="form-card compact-form">
    @csrf
    @method('PUT')
    @include('gestion.categorias.formulario', ['category' => $category])
    <button class="btn btn-primary">Guardar cambios</button>
</form>
