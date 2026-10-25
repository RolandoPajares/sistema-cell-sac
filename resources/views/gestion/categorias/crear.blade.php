<form method="post" action="{{ route('management.categories.store') }}" class="form-card">
    @csrf
    <h2>Nueva categoría</h2>
    @include('gestion.categorias.formulario', ['category' => null])
    <button class="btn btn-primary">Registrar</button>
</form>
